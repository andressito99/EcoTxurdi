<?php
// Cargar configuración general y conexión a la base de datos
require_once __DIR__ . '/../config.php';
$BASE_URL = BASE_URL;
$urlError = $BASE_URL . '/web/excepciones.php';

// -------------------------
// 1. Validar sesión y token de seguridad
// -------------------------
if (!isset($_SESSION['id_usuario'])) {

    if (isset($_COOKIE['tokenMantenerSesion'])) {
        // La cookie contiene el token "real"; en la BD guardamos su hash
        $tokenCookie = $_COOKIE['tokenMantenerSesion'];
        $tokenHash = hash('sha256', $tokenCookie);

        // Buscar el usuario por el hash
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE remember_token = ? LIMIT 1");
        $stmt->execute([$tokenHash]);
        $usuarioRecordado = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuarioRecordado) {
            // Restaurar sesión
            session_regenerate_id(true);

            $_SESSION['id_usuario'] = $usuarioRecordado['id_usuario'];
            $_SESSION['usuario'] = $usuarioRecordado['usuario'];
            $_SESSION['rol'] = $usuarioRecordado['rol'];
            $_SESSION['puntosRanking'] = $usuarioRecordado['puntosRanking'];
            $_SESSION['puntosCambio'] = $usuarioRecordado['puntosCambio'];

            // generar session_token y establecer cookie (igual que en login.php)
            $securityToken = hash('sha256', $_SERVER['HTTP_USER_AGENT'] . session_id());
            setcookie("session_token", $securityToken, [
                'expires' => 0,
                'path' => '/',
                'secure' => false,    // cambiar a true en producción con HTTPS
                'httponly' => true,
                'samesite' => 'Strict'
            ]);

            // renovar el token "recordarme": generar token real, guardar hash en BD y escribir cookie
            $newToken = bin2hex(random_bytes(32));
            $newTokenHash = hash('sha256', $newToken);
            $upd = $pdo->prepare("UPDATE usuarios SET remember_token = ? WHERE id_usuario = ?");
            $upd->execute([$newTokenHash, $usuarioRecordado['id_usuario']]);

            setcookie('tokenMantenerSesion', $newToken, [
                'expires' => time() + (86400 * 10),
                'path' => '/',
                'secure' => false,
                'httponly' => true,
                'samesite' => 'Strict'
            ]);

        } else {
            // token inválido -> limpiar cookie y enviar a error
            setcookie("tokenMantenerSesion", "", time() - 3600, "/");
            header("Location: $urlError");
            exit;
        }
    } else {
        // no hay sesión ni cookie 'tokenMantenerSesion'
        header("Location: $urlError");
        exit;
    }
}

// Ahora que hay sesión: validar session_token
if (!isset($_COOKIE['session_token']) ||
    $_COOKIE['session_token'] !== hash('sha256', $_SERVER['HTTP_USER_AGENT'] . session_id())) {
    // token de sesión no válido: destruir sesión y redirigir
    session_destroy();
    header("Location: $urlError");
    exit;
}

// -------------------------
// 2. Control de roles
// -------------------------
if (isset($rolNecesario)) {
    $rolesPermitidos = [];
    switch ($rolNecesario) {
        case 'user':
            $rolesPermitidos = ['user', 'mod', 'admin'];
            break;
        case 'mod':
            $rolesPermitidos = ['mod', 'admin'];
            break;
        case 'admin':
            $rolesPermitidos = ['admin'];
            break;
    }

    if (!in_array($_SESSION['rol'], $rolesPermitidos)) {
        header("Location: $urlError");
        exit;
    }
}
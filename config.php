<?php
// Configuración de cookies y sesión
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '', // Ajustar en producción
    'secure' => false, // Cambiar a true si usas HTTPS
    'httponly' => true,
    'samesite' => 'Strict'
]);

session_start();

// Conexión a la base de datos
$host = 'localhost';
$db = 'ecotxurdi';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    echo <<<HTML
    <html>
    <head><title>Error de Conexión</title></head>
    <body style="font-family:sans-serif;text-align:center;margin-top:100px;">
      <h1 style="color:#d50000;">Error de conexión</h1>
      <p>Estamos teniendo problemas técnicos. Por favor, intenta más tarde.</p>
      <a href="/" style="color:#d50000;text-decoration:underline;">Volver al inicio</a>
    </body>
    </html>
    HTML;
    exit;
}

// Constantes del proyecto
define('BASE_URL', '/Ecotxurdi');
define('BASE_PATH', __DIR__);

// Generar token aleatorio
function generarTokenSeguro($length = 32) {
    return bin2hex(random_bytes($length));
}

// Auto-login con cookie tokenMantenerSesion (token hasheado en BD)
if (!isset($_SESSION['id_usuario']) && isset($_COOKIE['tokenMantenerSesion'])) {
    $tokenCookie = $_COOKIE['tokenMantenerSesion'];
    $tokenHash = hash('sha256', $tokenCookie);

    // Buscar usuario con token hash
    $stmt = $pdo->prepare("SELECT id_usuario, usuario, rol, puntosRanking, puntosCambio 
                           FROM usuarios WHERE remember_token = ? LIMIT 1");
    $stmt->execute([$tokenHash]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        session_regenerate_id(true);

        // Restaurar datos en sesión
        $_SESSION['id_usuario'] = $user['id_usuario'];
        $_SESSION['usuario'] = $user['usuario'];
        $_SESSION['rol'] = $user['rol'];
        $_SESSION['puntosRanking'] = $user['puntosRanking'];
        $_SESSION['puntosCambio'] = $user['puntosCambio'];

        // Renovar token
        $newToken = generarTokenSeguro();
        $newTokenHash = hash('sha256', $newToken);
        $update = $pdo->prepare("UPDATE usuarios SET remember_token = ? WHERE id_usuario = ?");
        $update->execute([$newTokenHash, $user['id_usuario']]);

        // Actualizar cookie
        setcookie('tokenMantenerSesion', $newToken, [
            'expires' => time() + (86400 * 10),
            'path' => '/',
            'secure' => false,
            'httponly' => true,
            'samesite' => 'Strict'
        ]);
    }
}

?>

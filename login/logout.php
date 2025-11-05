<?php
require_once __DIR__ . '/../config.php';
$active = '';

// Si hay cookie tokenMantenerSesion, eliminar el token de la base de datos
if (isset($_COOKIE['tokenMantenerSesion'])) {
    $hashedToken = hash('sha256', $_COOKIE['tokenMantenerSesion']);
    $stmt = $pdo->prepare("UPDATE usuarios SET remember_token = NULL WHERE remember_token = ?");
    $stmt->execute([$hashedToken]);
}

// Vaciar todas las variables de sesión
$_SESSION = [];

// Destruir la sesión
if (session_status() === PHP_SESSION_ACTIVE) {
    session_destroy();
}

// Eliminar cookie de sesión
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]);
}

// Eliminar cookie session_token
setcookie("session_token", "", time() - 3600, "/", "", false, true);

// Eliminar cookie tokenMantenerSesion igual que se creó
setcookie("tokenMantenerSesion", "", [
    'expires' => time() - 3600,
    'path' => '/',
    'secure' => false,     // true si usas https
    'httponly' => true,
    'samesite' => 'Strict'
]);

// Redirigir al inicio
header("Location: ../index.php");
exit;
?>
<?php
// Incluir el archivo de configuración que inicializa la conexión PDO y la sesión
require_once __DIR__ . '/../config.php';

// Obtener el ID del usuario desde la sesión
$id_usuario = $_SESSION['id_usuario'] ?? null;

// Si no hay usuario logueado, devolver JSON con puntos a 0 y salir
if (!$id_usuario) {
    echo json_encode([
        'puntosCambio' => 0, // puntos actuales del usuario
    ]);
    exit; // Terminar ejecución
}

try {
    // Preparar la consulta SQL para obtener los puntos actuales del usuario
    $stmt = $pdo->prepare("SELECT puntosCambio FROM usuarios WHERE id_usuario = ?");
    $stmt->execute([$id_usuario]); // Ejecutar con el ID del usuario
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC); // Obtener resultado como array asociativo

    // Devolver los puntos actuales en formato JSON
    echo json_encode([
        'puntosCambio' => $resultado['puntosCambio'] ?? 0, // Si no existe, enviar 0
    ]);
} catch (PDOException $e) {
    // En caso de error en la base de datos, enviar un único JSON válido
    echo json_encode([
        'puntosCambio' => 0, // Valor por defecto
        'error' => 'Error en la base de datos' // Mensaje de error
    ]);
}
?>
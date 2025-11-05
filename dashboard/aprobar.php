<?php
// Importar configuración general (DB, sesión, constantes)
$rolNecesario = 'mod'; 
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../assets/auth.php';

// Obtener el ID de la misión a aprobar desde la URL, por defecto 0
$id = $_GET['id'] ?? 0;

// Preparar y ejecutar la actualización de la misión: cambiar tipo a 'mision'
$stmt = $pdo->prepare("UPDATE misiones SET tipo = 'mision' WHERE id_mision = ?");
$stmt->execute([$id]);

// Redirigir de vuelta al panel de moderador con mensaje o al panel de admin
$rol = $_SESSION['rol'];
if ($rol == "admin")
    header("Location: admin.php");
else
    header("Location: mod.php?msg=aprobada");
exit;
?>
<?php
// Importar configuración general (DB, sesiones, constantes)
$rolNecesario = 'mod'; 
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../assets/auth.php';

// Obtener el ID de la misión a eliminar desde la URL, por defecto 0
$id = $_GET['id'] ?? 0;

// Preparar y ejecutar la eliminación de la misión
$stmt = $pdo->prepare("DELETE FROM misiones WHERE id_mision = ?");
$stmt->execute([$id]);

// Redirigir de vuelta al panel de admin con mensaje
if ($_SESSION['rol'] === 'mod') {
        header("Location: " . BASE_URL . "/dashboard/mod.php");
        exit;
        }
        else {
        header("Location: " . BASE_URL . "/dashboard/admin.php");
        exit;
        }
?>
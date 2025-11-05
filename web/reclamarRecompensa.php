<?php
// Incluye la configuración principal y la conexión a la base de datos
$rolNecesario = 'user'; 
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../assets/auth.php';

// Verifica que la solicitud sea enviada mediante el método POST
// Si se accede directamente por URL, redirige a recompensas
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Location: ' . BASE_URL . '/web/recompensas.php');
  exit;
}

// Verificar si el usuario ha iniciado sesión
$id_usuario = $_SESSION['id_usuario'] ?? null;
if (!$id_usuario) {
  // Si no hay sesión activa, redirige con mensaje
  header('Location: ' . BASE_URL . '/web/recompensas.php?status=noauth');
  exit;
}

// Validar que el id de recompensa sea válido
$id_recompensa = isset($_POST['id_recompensa']) ? (int)$_POST['id_recompensa'] : 0;
if ($id_recompensa <= 0) {
  // Si es inválido, redirige con error
  header('Location: ' . BASE_URL . '/web/recompensas.php?status=error');
  exit;
}

try {
  // Iniciar transacción para asegurar consistencia en la BD
  $pdo->beginTransaction();

  // Obtener el precio de la recompensa y bloquear la fila para evitar concurrencia
  $stmt = $pdo->prepare('SELECT precio FROM recompensas WHERE id_recompensa = ? FOR UPDATE');
  $stmt->execute([$id_recompensa]);
  $precio = $stmt->fetchColumn();
  if ($precio === false) {
    // Si no existe la recompensa, cancelar transacción
    $pdo->rollBack();
    header('Location: ' . BASE_URL . '/web/recompensas.php?status=error');
    exit;
  }

  // Obtener los puntos del usuario y bloquearlos para evitar cambios simultáneos
  $stmt = $pdo->prepare('SELECT puntosCambio FROM usuarios WHERE id_usuario = ? FOR UPDATE');
  $stmt->execute([$id_usuario]);
  $puntos = $stmt->fetchColumn();
  if ($puntos === false) {
    // Si el usuario no existe (caso raro), cancelar
    $pdo->rollBack();
    header('Location: ' . BASE_URL . '/web/recompensas.php?status=error');
    exit;
  }

  // Verificar si el usuario ya reclamó esta recompensa antes
  $stmt = $pdo->prepare('SELECT 1 FROM reclama WHERE id_usuario = ? AND id_recompensa = ?');
  $stmt->execute([$id_usuario, $id_recompensa]);
  if ($stmt->fetchColumn()) {
    // Si ya reclamó, cancelar transacción
    $pdo->rollBack();
    header('Location: ' . BASE_URL . '/web/recompensas.php?status=ya');
    exit;
  }

  // Verificar si el usuario tiene puntos suficientes
  if ((int)$puntos < (int)$precio) {
    $pdo->rollBack();
    header('Location: ' . BASE_URL . '/web/recompensas.php?status=insuficientes');
    exit;
  }

  // Buscar un código disponible para esta recompensa
  $stmt = $pdo->prepare('SELECT id_codigo, codigo FROM codigos_recompensa WHERE id_recompensa = ? AND usado = 0 LIMIT 1 FOR UPDATE');
  $stmt->execute([$id_recompensa]);
  $rowCodigo = $stmt->fetch(PDO::FETCH_ASSOC);
  if (!$rowCodigo) {
    // Si no hay códigos disponibles, cancelar
    $pdo->rollBack();
    header('Location: ' . BASE_URL . '/web/recompensas.php?status=sin_codigos');
    exit;
  }

  // Marcar el código como usado por el usuario actual
  $id_codigo = (int)$rowCodigo['id_codigo'];
  $stmt = $pdo->prepare('UPDATE codigos_recompensa SET usado = 1, id_usuario = ?, fecha_usado = NOW() WHERE id_codigo = ?');
  $stmt->execute([$id_usuario, $id_codigo]);

  // Restar los puntos del usuario
  $stmt = $pdo->prepare('UPDATE usuarios SET puntosCambio = puntosCambio - :precio WHERE id_usuario = :id');
  $stmt->execute([':precio' => (int)$precio, ':id' => $id_usuario]);

  // Registrar la reclamación en la tabla "reclama"
  $stmt = $pdo->prepare('INSERT INTO reclama (id_usuario, id_recompensa, fecha, id_codigo) VALUES (?, ?, CURRENT_DATE, ?)');
  $stmt->execute([$id_usuario, $id_recompensa, $id_codigo]);

  // Confirmar toda la operación
  $pdo->commit();

  // Crear un mensaje temporal con el código conseguido para mostrarlo luego
  $_SESSION['flash_recompensa'] = [
    'id_recompensa' => $id_recompensa,
    'codigo' => $rowCodigo['codigo']
  ];

  // Redirigir con éxito
  header('Location: ' . BASE_URL . '/web/recompensas.php?status=ok&id=' . $id_recompensa);
  exit;
} catch (Throwable $e) {
  // En caso de error crítico, asegurarse de revertir la transacción
  if ($pdo->inTransaction()) { $pdo->rollBack(); }
  // Redirigir con error genérico
  header('Location: ' . BASE_URL . '/web/recompensas.php?status=error');
  exit;
}
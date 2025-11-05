<?php
// Importar configuración general (DB, sesiones, constantes)
$rolNecesario = 'admin'; 
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../assets/auth.php';

// Variable para navegación
$active = '';

// Incluir cabecera del panel
require_once __DIR__ . '/../../includes/header.php';

// Obtener ID del usuario desde la URL
$id_usuario = $_GET['id'] ?? null;

if (!$id_usuario) {
  die("Error: No se ha proporcionado un ID de usuario.");
}

// Consultar datos actuales del usuario
$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id_usuario = ?");
$stmt->execute([$id_usuario]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
  die("Error: Usuario no encontrado.");
}

// Captura datos del formulario (POST)
$username = $_POST['username'] ?? $usuario['usuario'];
$rol = $_POST['rol'] ?? $usuario['rol'];
$password = $_POST['password'] ?? '';
$confirmPassword = $_POST['confirmPassword'] ?? '';

// Si el formulario fue enviado
if (!empty($_POST)) {
  // Verifica que los campos obligatorios estén presentes
  if (!empty($username) && !empty($rol)) {
    try {
      // Verificar si se cambió el nombre de usuario y si ya está en uso por otro
      $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE usuario = ? AND id_usuario != ?");
      $stmt->execute([$username, $id_usuario]);
      $existe = $stmt->fetch();

      if (!$existe) {
        // Si se quiere cambiar la contraseña
        if (!empty($password) || !empty($confirmPassword)) {
          if ($password === $confirmPassword) {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
          } else {
            $_SESSION['error'] = "Las contraseñas no coinciden, inténtalo nuevamente";
          }
        }

        if (!isset($_SESSION['error'])) {
          // Armar la consulta SQL dinámica
          if (isset($hashedPassword)) {
            $stmt = $pdo->prepare("UPDATE usuarios SET usuario = ?, rol = ?, contrasena = ? WHERE id_usuario = ?");
            $stmt->execute([$username, $rol, $hashedPassword, $id_usuario]);
          } else {
            $stmt = $pdo->prepare("UPDATE usuarios SET usuario = ?, rol = ? WHERE id_usuario = ?");
            $stmt->execute([$username, $rol, $id_usuario]);
          }

          // Redirigir a admin
          header("Location: " . BASE_URL . "/dashboard/admin.php");
          exit;
        }
      } else {
        $_SESSION['error'] = "Este usuario ya está en uso, prueba otro";
      }
    } catch (PDOException $e) {
      echo "Error en la base de datos: " . $e->getMessage();
    }
  } else {
    $_SESSION['error'] = "No pueden haber campos vacíos";
  }
}
?>

<!-- Sección del formulario -->
<section class="section grid-2" style="display:flex; justify-content:center; align-items:center;">
  <div class="panel" style="max-width:520px;">
    <h2>Editar usuario</h2>

    <form name="edit_user_form" method="post" action="#">
      <!-- Campo de usuario -->
      <div class="label">Username</div>
      <input class="input" type="text" name="username" value="<?= htmlspecialchars($username) ?>" placeholder="Username">

      <!-- Campo de rol -->
      <div class="label">Rol</div>
      <select class="input" type="text" name="rol" value="<?= htmlspecialchars($rol) ?>" placeholder="rol">
        <option value="user" <?= $rol === 'user' ? 'selected' : '' ?>>User</option>
        <option value="admin" <?= $rol === 'admin' ? 'selected' : '' ?>>Admin</option>
        <option value="mod" <?= $rol === 'mod' ? 'selected' : '' ?>>Moderador</option>
      </select>

      <!-- Campo de contraseña -->
      <div class="label">Nueva contraseña (opcional)</div>
      <input class="input" type="password" name="password" placeholder="Nueva contraseña">

      <!-- Campo de confirmación de contraseña -->
      <div class="label">Confirmar nueva contraseña</div>
      <input class="input" type="password" name="confirmPassword" placeholder="Confirmar nueva contraseña">

      <!-- Mostrar errores si existen -->
      <?php if (!empty($_POST)): ?>
        <?php 
          $err = $_SESSION['error'] ?? '';
          unset($_SESSION['error']); // Limpiar para que no se muestre siempre
        ?>
        <?php if ($err): ?>
          <p class="error"><?= htmlspecialchars($err) ?></p>
        <?php endif; ?>
      <?php endif; ?>

      <!-- Botón de envío -->
      <input class="btn-pill" type="submit" name="submit" value="Guardar cambios">
    </form>
  </div>
</section>

<!-- Pie de página -->
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
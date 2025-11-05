<?php
// Importar configuración general (DB, sesiones, constantes)
$rolNecesario = 'admin'; 
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../assets/auth.php';

  // Variable para indicar la sección activa en la navegación
  $active = '';

  // Incluir cabecera del panel
require_once __DIR__ . '/../../includes/header.php';

  // Captura los datos enviados por POST o asigna cadena vacía si no existen
  $username = $_POST['username'] ?? "";
  $rol = $_POST['rol'] ?? "";
  $password = $_POST['password'] ?? "";
  $confirmPassword = $_POST['confirmPassword'] ?? "";

  // Si el formulario fue enviado
  if (!empty($_POST)) {
    // Verifica que los campos no estén vacíos
    if (!empty($username) && !empty($rol) && !empty($password) && !empty($confirmPassword)) {
      try {
        // Preparar consulta para verificar si el usuario ya existe
        $sql = "SELECT * FROM usuarios WHERE usuario = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$username]);
        $resultado = $stmt->fetch();

        // Si el usuario no existe aún
        if (empty($resultado)) {
          // Verifica que las contraseñas coincidan
          if ($password === $confirmPassword) {
            // Hashea la contraseña antes de guardarla
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Inserta el nuevo usuario en la base de datos
            $stmt = $pdo->prepare("INSERT INTO usuarios (imagen_user, usuario, contrasena, rol) VALUES (?, ?, ?, ?)");
            $stmt->execute(["perfil.png", $username, $hashedPassword, $rol]);

            // Redirige al admin a la página admin
            header("Location: " . BASE_URL . "/dashboard/admin.php");
            exit; // Detiene la ejecución
          } else {
            // Si las contraseñas no coinciden, guarda error en sesión
            $_SESSION['error'] = "Las contraseñas no coinciden, inténtalo nuevamente";
          }
        } else {
          // Si el usuario ya existe en la BD, guarda error en sesión
          $_SESSION['error'] = "Este usuario ya está en uso, prueba otro";
        }
      } catch (PDOException $e) {
        // Captura errores de la base de datos y los muestra (solo útil para desarrollo)
        print "Error por parte del servidor SQL<br>" . $e->getMessage();
        exit;
      }
    } else {
      // Si algún campo está vacío, guarda error en sesión
      $_SESSION['error'] = "No pueden haber campos vacíos";
    }
  }
?>

<!-- Sección principal del formulario de registro -->
<section class="section grid-2" style="display:flex; justify-content:center; align-items:center;">
  <div class="panel" style="max-width:520px;">
    <h2 data-i18n="signup.title">Crear cuenta</h2>

    <!-- Formulario de registro -->
    <form name="login_form" method="post" action="#">
      <!-- Campo de usuario -->
      <div class="label" data-i18n="login.username">Username</div>
      <input class="input" type="text" name="username" value="<?= htmlspecialchars($username) ?>" data-i18n-placeholder="login.username" placeholder="Username">

      <!-- Campo de rol -->
      <div class="label">Rol
      <select class="input" type="select" name="rol" placeholder="rol">
        <option value="user" <?= $rol === 'user' ? 'selected' : '' ?>>User</option>
        <option value="admin" <?= $rol === 'admin' ? 'selected' : '' ?>>Admin</option>
        <option value="mod" <?= $rol === 'mod' ? 'selected' : '' ?>>Moderador</option>
      </select>
      </div>
      
      <!-- Campo de contraseña -->
      <div class="label" data-i18n="login.password">Password</div>
      <input class="input" type="password" name="password" data-i18n-placeholder="login.password" placeholder="Password">

      <!-- Campo de confirmación de contraseña -->
      <div class="label" data-i18n="signup.confirm">Confirmar contraseña</div>
      <input class="input" type="password" name="confirmPassword" data-i18n-placeholder="signup.confirm" placeholder="Confirmar contraseña">

      <!-- Mostrar errores si existen -->
      <?php if (!empty($_POST)): ?>
        <?php 
          $err = $_SESSION['error'] ?? '';
          // Determina la clave i18n según el tipo de error
          $errKey = $err === 'Las contraseñas no coinciden, inténtalo nuevamente' ? 'signup.error.mismatch' 
            : ($err === 'Este usuario ya está en uso, prueba otro' ? 'signup.error.used' 
            : ($err === 'No pueden haber campos vacíos' ? 'login.error.empty' : ''));
        ?>
        <p class="error" <?= $errKey ? ('data-i18n="'.$errKey.'"') : '' ?>><?= htmlspecialchars($err) ?></p>
      <?php endif; ?>

      <!-- Botón de envío del formulario -->
      <input class="btn-pill" type="submit" name="submit" value="Crear" data-i18n-value="signup.submit">
    </form>
  </div>
</section>

<!-- Pie de página -->
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
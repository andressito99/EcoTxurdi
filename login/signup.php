<?php
  // Importa la configuración general del proyecto (DB, constantes, sesión, etc.)
  require_once __DIR__ . '/../config.php';

  // Variable para indicar la sección activa en la navegación
  $active = '';

  // Incluye el encabezado de la página (HTML inicial, navbar, etc.)
  include '../includes/header.php';

  // Captura los datos enviados por POST o asigna cadena vacía si no existen
  $username = $_POST['username'] ?? "";
  $password = $_POST['password'] ?? "";
  $confirmPassword = $_POST['confirmPassword'] ?? "";

  // Si el formulario fue enviado
  if (!empty($_POST)) {
    // Verifica que los campos no estén vacíos
    if (!empty($username) && !empty($password) && !empty($confirmPassword)) {
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
            $stmt = $pdo->prepare("INSERT INTO usuarios (usuario, contrasena, imagen_user) VALUES (?, ?, 'perfil.png')");
            $stmt->execute([$username, $hashedPassword]);

            // Recupera los datos del usuario recién creado
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$username]);
            $resultado = $stmt->fetch();

            //Guarda información del usuario en sesión
            session_regenerate_id(true);
            $_SESSION['id_usuario'] = $resultado['id_usuario'];
            $_SESSION['usuario'] = $resultado['usuario'];
            $_SESSION['rol'] = $resultado['rol'];
            $_SESSION['puntosRanking'] = $resultado['puntosRanking'];
            $_SESSION['puntosCambio'] = $resultado['puntosCambio'];
            
            // Actualizar último login
            try {
              $upd = $pdo->prepare("UPDATE usuarios SET ultimo_login = NOW() WHERE id_usuario = ?");
              $upd->execute([$_SESSION['id_usuario']]);
            } catch (Throwable $e) {}

            // Crear cookie de seguridad (previene hijacking)
            $securityToken = hash('sha256', $_SERVER['HTTP_USER_AGENT'] . session_id());
            setcookie("session_token", $securityToken, [
              'expires' => 0,
              'path' => '/',
              'secure' => false, // Poner en true con HTTPS
              'httponly' => true,
              'samesite' => 'Strict'
            ]);
            
            // Redirige al usuario a la página principal
            header("Location: ../index.php");
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

      <!-- Campo de contraseña -->
      <div class="label" data-i18n="login.password">Password</div>
      <input class="input" type="password" name="password" data-i18n-placeholder="login.password" placeholder="Password" pattern=".{8,}" title="Debe tener al menos 8 caracteres" required>

      <!-- Campo de confirmación de contraseña -->
      <div class="label" data-i18n="signup.confirm">Confirmar contraseña</div>
      <input class="input" type="password" name="confirmPassword" data-i18n-placeholder="signup.confirm" placeholder="Confirmar contraseña" pattern=".{8,}" title="Debe tener al menos 8 caracteres" required>

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

      <!-- Enlace a la página de login -->
      <div class="note" style="display:flex; flex-direction:column; justify-content:space-between; margin:8px 0 14px">
        <a href="<?= BASE_URL ?>/login/login.php" class="note" data-i18n="signup.have">¿Ya tienes cuenta? Inicia sesión aquí</a>
      </div>

      <!-- Botón de envío del formulario -->
      <input class="btn-pill" type="submit" name="submit" value="Registrarse" data-i18n-value="signup.submit">
    </form>
  </div>
</section>

<!-- Pie de página -->
<?php include '../includes/footer.php'; ?>
<?php
require_once __DIR__ . '/../config.php';
$active = '';
include '../includes/header.php';

$username = $_POST['username'] ?? "";
$password = $_POST['password'] ?? "";

if (!empty($_POST)) {
  if (!empty($username) && !empty($password)) {
    try {
      $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE usuario = ?");
      $stmt->execute([$username]);
      $resultado = $stmt->fetch();

      if ($resultado && password_verify($password, $resultado['contrasena'])) {

        // Iniciar sesión
        session_regenerate_id(true);
        $_SESSION['id_usuario'] = $resultado['id_usuario'];
        $_SESSION['usuario'] = $resultado['usuario'];
        $_SESSION['rol'] = $resultado['rol'];
        $_SESSION['puntosRanking'] = $resultado['puntosRanking'];
        $_SESSION['puntosCambio'] = $resultado['puntosCambio'];

        // Actualizar último login
        try {
          $upd = $pdo->prepare("UPDATE usuarios SET ultimo_login = NOW() WHERE id_usuario = ?");
          $upd->execute([$resultado['id_usuario']]);
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

        // RECORDAR USUARIO (Remember Me seguro con hash)
        if (isset($_POST['recordar'])) {
          $rememberToken = bin2hex(random_bytes(32)); // token real
          $hashedToken = hash('sha256', $rememberToken); // hasheado para guardar

          // Guardar hash en base de datos
          $stmt = $pdo->prepare("UPDATE usuarios SET remember_token = ? WHERE id_usuario = ?");
          $stmt->execute([$hashedToken, $resultado['id_usuario']]);

          // Guardar token real en cookie
          setcookie("tokenMantenerSesion", $rememberToken, [
            'expires' => time() + (86400 * 10), // 10 días
            'path' => '/',
            'secure' => false,
            'httponly' => true,
            'samesite' => 'Strict'
          ]);
        }

        // Redirigir al inicio
        header("Location: ../index.php");
        exit;

      } else {
        $_SESSION['error'] = "Usuario o contraseña incorrecta";
      }
    } catch (PDOException $e) {
      print "Error de servidor SQL<br>" . $e->getMessage();
      exit;
    }
  } else {
    $_SESSION['error'] = "No pueden haber campos vacíos";
  }
}
?>

<!-- Sección visual del formulario de login -->
<section class="section grid-2" style="align-items:center">
  <!-- Imagen decorativa del login -->
  <div class="media-wide" style="height:420px">
    <img src="<?= BASE_URL ?>/assets/img/login.png" alt="[Ilustración de acceso]">
  </div>

  <!-- Panel del formulario -->
  <div class="panel" style="max-width:520px; min-width: 350px;">
    <h2 data-i18n="login.title">Login</h2>

    <!-- Mensaje opcional enviado desde otras páginas -->
    <?php if (isset($_GET['mensaje']) || isset($_GET['mensaje_key'])): ?>
      <?php
        $mKey = isset($_GET['mensaje_key']) ? $_GET['mensaje_key'] : '';
        $mTxt = isset($_GET['mensaje']) ? $_GET['mensaje'] : '';
      ?>
      <div class="alert" style="background:#fff3cd;color:#856404;padding:10px;border-radius:10px;margin:10px 0;"
           <?= $mKey ? ('data-i18n="'.htmlspecialchars($mKey, ENT_QUOTES, 'UTF-8').'"') : '' ?>>
        <?= htmlspecialchars($mTxt) ?>
      </div>
    <?php endif; ?>

    <!-- Formulario de login -->
    <form name="login_form" method="post" action="#">
      <div class="label" data-i18n="login.username">Username</div>
      <!-- Campo de usuario -->
      <input class="input" type="text" name="username"
             value="<?= htmlspecialchars($username) ?>"
             data-i18n-placeholder="login.username" placeholder="Username">

      <div class="label" data-i18n="login.password">Password</div>
      <!-- Campo de contraseña -->
      <input class="input" type="password" name="password"
             data-i18n-placeholder="login.password" placeholder="Password">

      <!-- Mostrar errores si los hay -->
      <?php if (!empty($_POST)): ?>
        <?php
          $err = $_SESSION['error'] ?? '';
          $errKey = ($err === 'Usuario o contraseña incorrecta')
            ? 'login.error.auth'
            : (($err === 'No pueden haber campos vacíos') ? 'login.error.empty' : '');
        ?>
        <p class="error" <?= $errKey ? ('data-i18n="'.$errKey.'"') : '' ?>>
          <?= htmlspecialchars($err) ?>
        </p>
      <?php endif; ?>

      <!-- Otras opciones del login -->
      <div class="note" style="display:flex; flex-direction:column; justify-content:space-between; margin:8px 0 14px;">
        <label>
          <input type="checkbox" name="recordar">
          <span data-i18n="login.remember">Recordarme</span>
        </label>

        <script>
          document.addEventListener('change', function(e) {
            // Verificamos que el cambio sea en el checkbox "recordar"
            if (e.target.name === 'recordar') {
              if (e.target.checked) {
                const confirmar = confirm(
                  'Al activar esta opción, tu sesión permanecerá iniciada incluso después de cerrar el navegador.\n' +
                  'Solo debes usarla en dispositivos de confianza.\n\n¿Deseas continuar?'
                );

                if (!confirmar) {
                  // Si cancela, se desmarca el checkbox
                  e.target.checked = false;
                }
              }
            }
          });
        </script>
        <a href="<?= BASE_URL ?>/login/signup.php" class="note" data-i18n="signup.have">
          ¿No tienes cuenta? Regístrate aquí
        </a>
      </div>

      <!-- Botón de envío -->
      <input class="btn-pill" type="submit" name="submit" value="Login" data-i18n-value="login.submit">
    </form>
  </div>
</section>

<!-- Pie de página -->
<?php include '../includes/footer.php'; ?>
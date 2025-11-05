<?php
require_once __DIR__ . '/../config.php';

function render_error_and_exit(string $key, string $fallback, string $backHref): void {
  require_once BASE_PATH . '/includes/header.php';
  echo '<div class="section"><div class="panel">';
  echo '<p class="error" data-i18n="'.htmlspecialchars($key, ENT_QUOTES, 'UTF-8').'">'.htmlspecialchars($fallback, ENT_QUOTES, 'UTF-8').'</p>';
  echo '<a class="btn" href="'.htmlspecialchars($backHref, ENT_QUOTES, 'UTF-8').'" data-i18n="btn.back" style="margin-top:10px;display:inline-block">Volver</a>';
  echo '</div></div>';
  require_once BASE_PATH . '/includes/footer.php';
  exit;
}

$id_mision = isset($_GET['id_mision']) ? intval($_GET['id_mision']) : 0;

if (!isset($_SESSION['id_usuario'])) {
  $msg = urlencode('Debes iniciar sesión para realizar esta misión');
  $key = urlencode('login.require.mission');
  header('Location: ' . BASE_URL . '/login/login.php?mensaje_key='.$key.'&mensaje='.$msg);
  exit;
}

$id_usuario = $_SESSION['id_usuario'];

// Verificar existencia de usuario
$stmt = $pdo->prepare('SELECT 1 FROM usuarios WHERE id_usuario = ?');
$stmt->execute([$id_usuario]);
if (!$stmt->fetch()) {
  render_error_and_exit('errors.user.not_found', 'Error: El usuario no existe en la base de datos.', BASE_URL . '/index.php');
}

// Verificar existencia de misión
$stmt = $pdo->prepare('SELECT 1 FROM misiones WHERE id_mision = ?');
$stmt->execute([$id_mision]);
if (!$stmt->fetch()) {
  render_error_and_exit('errors.mission.not_found', 'Error: La misión no existe.', BASE_URL . '/web/misiones.php');
}

// Verificar si ya cumplió
$stmt = $pdo->prepare('SELECT * FROM cumple WHERE id_usuario = ? AND id_mision = ?');
$stmt->execute([$id_usuario, $id_mision]);
$yaCumplida = $stmt->fetch();
if ($yaCumplida) {
  header('Location: mision.php?id_mision='.$id_mision.'&status=ya_completada');
  exit;
}

$stmt = $pdo->prepare('SELECT puntuacion FROM misiones WHERE id_mision = ?');
$stmt->execute([$id_mision]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);
$puntuacion = (int)($result['puntuacion'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  require_once BASE_PATH . '/includes/header.php';
  ?> 
  <h2 style="display:flex; justify-content: center;" data-i18n="upload.title">Sube la imagen de tu misión como prueba de que la has completado.</h2>

  <form action="" method="POST" enctype="multipart/form-data" class="form-evidencia">
    <!-- Keep only the translatable text in a span so the i18n replacer won't remove the input element -->
    <label for="foto" class="file-label">
      <span data-i18n="upload.select">📂Seleccionar imagen</span>
    </label>
    <input id="foto" type="file" name="foto" accept="image/jpeg, image/png" required class="file-input">
    <p id="file-name" style="font-size:16px; color:#333; margin-top:10px;" ></p>

    <p class="nota-formatos" data-i18n="upload.note">Solo se permiten archivos JPG o PNG (máx. 3MB)</p>
    <br>
    <button type="submit" class="btn-pill" data-i18n="upload.submit">Subir y completar</button>
    <a href="mision.php?id_mision=<?= $id_mision ?>" class="btn-pill" style=" margin:15px;" data-i18n="upload.cancel">Cancelar</a>
  </form>

    <script>
    // Use the input id so the element won't be removed by i18n replacement (only the span inside the label is translated)
    const input = document.getElementById('foto');
    const fileNameEl = document.getElementById('file-name');

    if (input) {
      input.addEventListener('change', function() {
        if (this.files && this.files.length) {
          const prefix = (window.i18n && window.i18n.dict && window.i18n.dict['file.prefix']) || 'Archivo:';
          fileNameEl.removeAttribute('data-i18n');
          fileNameEl.textContent = `📎 ${prefix} ${this.files[0].name}`;
        } else {
          fileNameEl.setAttribute('data-i18n', 'file.none');
          fileNameEl.textContent = (window.i18n && window.i18n.dict && window.i18n.dict['file.none']) || 'Ningún archivo seleccionado';
        }
      });
    }
    </script>
  <?php
  require_once BASE_PATH . '/includes/footer.php';
  exit;
}

$maxSize = 3 * 1024 * 1024; // 3 MB
$allowedTypes = ['image/jpeg', 'image/png'];

if (isset($_FILES['foto']) && $_FILES['foto']['error'] === 0) {
  if (!in_array($_FILES['foto']['type'], $allowedTypes)) {
    render_error_and_exit('errors.file.type', 'Error: Solo se permiten imágenes JPG o PNG.', BASE_URL . '/web/realizarMision.php?id_mision='.(int)$id_mision);
  }
  if ($_FILES['foto']['size'] > $maxSize) {
    render_error_and_exit('errors.file.size', 'Error: La imagen es demasiado grande (máx 3MB)', BASE_URL . '/web/realizarMision.php?id_mision='.(int)$id_mision);
  }

  $nombreFinal = 'mision_' . $id_mision . '_user_' . $id_usuario . '_' . time() . '.jpg';
  move_uploaded_file($_FILES['foto']['tmp_name'], BASE_PATH . '/assets/img/evidencias/' . $nombreFinal);

  $stmt = $pdo->prepare('INSERT INTO cumple (id_usuario, id_mision, evidencia) VALUES (?, ?, ?)');
  $stmt->execute([$id_usuario, $id_mision, $nombreFinal]);
  $stmt = $pdo->prepare('UPDATE `usuarios` SET `puntosRanking`=puntosRanking+?,`puntosCambio`=puntosCambio+? WHERE id_usuario = ?');
  $stmt->execute([$puntuacion, $puntuacion, $id_usuario]);

  header('Location: mision.php?id_mision='.$id_mision.'&status=completada');
  exit;
} else { 
  render_error_and_exit('errors.upload.failed', 'Error al subir la imagen.', BASE_URL . '/web/realizarMision.php?id_mision='.(int)$id_mision);
}


<?php
// Importar configuración general (DB, sesión, constantes)
$rolNecesario = 'user'; 
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../assets/auth.php';

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['id_usuario'])) {
  header('Location: ' . BASE_URL . '/login/login.php?mensaje=Inicia sesión para ver tu perfil');
  exit;
}

// Marcar sección activa (si se usa en menú)
$active = '';

// Incluir encabezado de la página
require_once BASE_PATH . '/includes/header.php';

// Obtener ID del usuario actual
$uid = (int)$_SESSION['id_usuario'];

// Inicializar variable de mensaje
$msg = '';

// Cambio de contraseña
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
  $current = $_POST['current_password'] ?? '';
  $new = $_POST['new_password'] ?? '';
  
  if ($current === '' || $new === '') {
    $msg = 'Rellena ambas contraseñas.';
  } elseif (strlen($new) < 6) {
    $msg = 'La nueva contraseña debe tener al menos 6 caracteres.';
  } else {
    try {
      // Obtener contraseña actual de la base de datos
      $st = $pdo->prepare('SELECT contrasena FROM usuarios WHERE id_usuario = ?');
      $st->execute([$uid]);
      $row = $st->fetch(PDO::FETCH_ASSOC);

      // Verificar contraseña actual
      if ($row && password_verify($current, $row['contrasena'])) {
        // Actualizar con la nueva contraseña hasheada
        $hash = password_hash($new, PASSWORD_BCRYPT);
        $up = $pdo->prepare('UPDATE usuarios SET contrasena = ? WHERE id_usuario = ?');
        $up->execute([$hash, $uid]);
        $msg = 'Contraseña actualizada correctamente';
      } else {
        $msg = 'La contraseña actual no es correcta';
      }
    } catch (Throwable $e) {
      $msg = 'No se pudo actualizar la contraseña.';
    }
  }
}

// Cambio de imagen perfil
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_FILES['imagen']['name'])) {
    try {
      // Carpeta destino donde se guardarán las imágenes
      $carpetaDestino = __DIR__ . '/../assets/img/usuarios/';
      if (!is_dir($carpetaDestino)) {
          mkdir($carpetaDestino, 0777, true); // Crear carpeta si no existe
      }

      // Genera el nombre para la imagen
      $imagenNombre = $_SESSION['usuario'] . ".png";

      // Mover archivo temporal a carpeta de destino
      move_uploaded_file($_FILES['imagen']['tmp_name'], $carpetaDestino . $imagenNombre);

      $uim = $pdo->prepare('UPDATE `usuarios` SET `imagen_user` = ? WHERE `usuarios`.`id_usuario` = ?');
      $uim->execute([$imagenNombre, $uid]);
      $msg = 'imagen actualizada correctamente';
    } catch (Throwable $e) {
      $msg = 'No se pudo actualizar la imagen.';
    }
}

// Obtener datos del usuario
try {
  $st = $pdo->prepare('SELECT imagen_user, usuario, puntosRanking, puntosCambio FROM usuarios WHERE id_usuario = ?');
  $st->execute([$uid]);
  $user = $st->fetch(PDO::FETCH_ASSOC) ?: ['imagen_user'=>'perfil.png','usuario'=>'','puntosRanking'=>0,'puntosCambio'=>0];

  // Posición en ranking
  $stPos = $pdo->prepare('SELECT 1 + COUNT(*) AS pos FROM usuarios WHERE puntosRanking > (SELECT puntosRanking FROM usuarios WHERE id_usuario = ?)');
  $stPos->execute([$uid]);
  $posRow = $stPos->fetch(PDO::FETCH_ASSOC);
  $rankingPos = (int)($posRow['pos'] ?? 1);

  // Misiones realizadas
  $stMis = $pdo->prepare('SELECT m.id_mision, m.titulo_misiones, m.imagen_mision, c.Fecha FROM cumple c JOIN misiones m ON c.id_mision = m.id_mision WHERE c.id_usuario = ? ORDER BY c.Fecha DESC');
  $stMis->execute([$uid]);
  $misiones = $stMis->fetchAll(PDO::FETCH_ASSOC);

  // Cupones reclamados
  $stCup = $pdo->prepare('SELECT r.id_recompensa, rp.titulo_recompensa, rp.imagen_recom, r.fecha FROM reclama r JOIN recompensas rp ON r.id_recompensa = rp.id_recompensa WHERE r.id_usuario = ? ORDER BY r.fecha DESC');
  $stCup->execute([$uid]);
  $cupones = $stCup->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
  $user = ['usuario'=>'','puntosRanking'=>0,'puntosCambio'=>0];
  $rankingPos = 1;
  $misiones = [];
  $cupones = [];
}
?>

<section class="section grid-2" style="align-items:flex-start;gap:30px;min-height:60vh;">
  <!-- Columna izquierda: Perfil y listas -->
  <div class="panel" style="min-width:320px;">
    <div style="display:flex; gap:24px; align-items:center;">
      <div style="width:180px;height:180px;border-radius:50%;background:#e0f2f1;display:flex;align-items:center;justify-content:center;overflow:hidden;">
        <img class="avatar" src="<?= BASE_URL ?>/assets/img/usuarios/<?php echo $user['imagen_user'] ?>" alt="avatar">
      </div>
      <div>
        <h2 style="margin:0 0 6px; font-size:36px;"><?= htmlspecialchars($user['usuario']) ?></h2>
  <p style="font-size:22px;margin:6px 0;" data-i18n="perfil.ranking.historic">Puntos Historicos <strong><?= (int)$user['puntosRanking'] ?></strong></p>
  <p style="font-size:22px;margin:6px 0;" data-i18n="perfil.ranking.current">Puntos Actuales <strong><?= (int)$user['puntosCambio'] ?></strong></p>
  <p style="font-size:22px;margin:6px 0;" data-i18n="perfil.ranking.position">Ranking: <strong><?= $rankingPos ?></strong></p>
      </div>
    </div>

    <form method="post" action="#" enctype="multipart/form-data">
  <div class="label" data-i18n="perfil.change_image">Cambiar imagen perfil:</div>
      <input class="input" type="file" name="imagen">
      <div style="text-align:center;margin-top:16px;">
  <button class="btn-pill" type="submit" name="submit" value="Cambiar" data-i18n="perfil.change_image.button">Cambiar</button>
      </div>
    </form>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-top:28px;">
      <div>
  <h3 style="margin:0 0 10px;" data-i18n="perfil.missions.title">Misiones realizadas:</h3>
        <?php if ($misiones): ?>
          <?php foreach ($misiones as $m): ?>
            <div style="display:flex;align-items:center;gap:10px;margin:8px 0;">
              <img src="<?= BASE_URL ?>/assets/img/misiones/<?= htmlspecialchars($m['imagen_mision'] ?: 'mision1.jpg') ?>" alt="" style="width:36px;height:36px;border-radius:8px;object-fit:cover;">
              <span><?= htmlspecialchars($m['titulo_misiones']) ?></span>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <p class="note" data-i18n="perfil.missions.none">Aún no has completado misiones.</p>
        <?php endif; ?>
      </div>

      <div>
  <h3 style="margin:0 0 10px;" data-i18n="perfil.coupons.title">Cupones:</h3>
        <?php if ($cupones): ?>
          <?php foreach ($cupones as $c): ?>
            <div style="display:flex;align-items:center;gap:10px;margin:8px 0;">
              <img src="<?= BASE_URL ?>/assets/img/recompensas/<?= htmlspecialchars($c['imagen_recom'] ?: 'mision1.jpg') ?>" alt="" style="width:36px;height:36px;border-radius:8px;object-fit:cover;">
              <span><?= htmlspecialchars($c['titulo_recompensa']) ?></span>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <p class="note" data-i18n="perfil.coupons.none">Todavía no has canjeado cupones.</p>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Columna derecha: Cambiar contraseña -->
  <div class="panel" style="min-width:320px;">
  <h2 style="text-align:center;margin-top:0;" data-i18n="perfil.password.title">Cambiar Contraseña</h2>
    <?php if ($msg): ?>
      <div class="alert" style="background:#fff3cd;color:#856404;padding:10px;border-radius:10px;margin:10px 0;">
        <?= htmlspecialchars($msg) ?>
      </div>
    <?php endif; ?>
    <form method="post" action="#">
  <div class="label" style="font-size:22px;text-align:center;" data-i18n="perfil.password.current">Actual</div>
  <input class="input" type="password" name="current_password" placeholder="Contraseña actual" required style="border-radius:30px;" data-i18n-placeholder="perfil.password.current_placeholder">
  <div class="label" style="font-size:22px;text-align:center;margin-top:14px;" data-i18n="perfil.password.new">Nueva</div>
  <input class="input" type="password" name="new_password" placeholder="Nueva contraseña" pattern=".{8,}" title="Debe tener al menos 8 caracteres" style="border-radius:30px;" data-i18n-placeholder="perfil.password.new_placeholder">
      <input type="hidden" name="change_password" value="1">
      <div style="text-align:center;margin-top:16px;">
  <button class="btn-pill" type="submit" data-i18n="perfil.password.save">Guardar</button>
      </div>
    </form>
  </div>
</section>

<?php 
// Incluir pie de página
require_once BASE_PATH . '/includes/footer.php'; 
?>
<?php
// Incluye el archivo de configuración principal
require_once __DIR__ . '/../config.php';

// Indica cuál es la sección activa para el menú
$active = 'misiones';

// Obtiene el id de la misión desde la URL (GET) y lo convierte a entero
$id_mision = isset($_GET['id_mision']) ? intval($_GET['id_mision']) : 0;

// Obtiene los datos de la misión desde la base de datos
$stmt = $pdo->prepare("SELECT * FROM misiones WHERE id_mision = ?");
$stmt->execute([$id_mision]);
$mision = $stmt->fetch(PDO::FETCH_ASSOC);

// Inicializa evidencia como null
$evidencia = null;

// Si el usuario está logueado, comprobamos si ya completó esta misión
if (isset($_SESSION['id_usuario'])) {
    $stmt = $pdo->prepare("SELECT evidencia FROM cumple WHERE id_usuario = ? AND id_mision = ?");
    $stmt->execute([$_SESSION['id_usuario'], $id_mision]);
    $cumplida = $stmt->fetch();
    if ($cumplida) { $evidencia = $cumplida['evidencia']; }
}

// Incluye el header del sitio
require_once BASE_PATH . '/includes/header.php';
?>

<!-- Mensajes de estado de la misión -->
<?php if (isset($_GET['status']) && $_GET['status'] === 'completada'): ?>
  <div style="display:flex; justify-content:flex-start; margin:10px;">
    <!-- Mensaje de éxito -->
    <div class="alert success" style="background:#d4edda;color:#155724;padding:10px 16px;border-radius:8px;max-width:90%;margin:0 auto;">
      <span data-i18n="mission.done">¡Misión completada!</span>
    </div>
  </div>
<?php elseif (isset($_GET['status']) && $_GET['status'] === 'ya_completada'): ?>
  <div style="display:flex; justify-content:flex-start; margin:10px;">
    <!-- Mensaje de advertencia si ya se había completado -->
    <div class="alert warning" style="background:#fff3cd;color:#856404;padding:10px 16px;border-radius:8px;max-width:90%;margin:0 auto;">
      <span data-i18n="mission.already">Ya habías completado esta misión.</span>
    </div>
  </div>
<?php endif; ?>

<!-- Comprueba si existe la misión -->
<?php if ($mision): ?>
  <section class="section grid-2">
    <div class="panel">
      <!-- Título de la misión -->
      <h2 data-i18n="mission.<?= (int)$mision['id_mision'] ?>.title"><?= htmlspecialchars($mision['titulo_misiones']) ?></h2>

      <!-- Ubicación y puntuación -->
      <p class="note">
        <span data-i18n="mission.<?= (int)$mision['id_mision'] ?>.loc">
          <?= htmlspecialchars($mision['ubicacion']) ?>
        </span> · +<?= (int)$mision['puntuacion'] ?> <span data-i18n="points.suffix">pts</span>
      </p>

      <!-- Descripción de la tarea -->
      <div class="label" data-i18n="mission.task">Tarea:</div>
      <div class="panel" style="padding:14px">
        <div class="note" data-i18n="mission.<?= (int)$mision['id_mision'] ?>.desc" data-i18n-html="true">
          <?= nl2br(htmlspecialchars($mision['descripcion_misiones'])) ?>
        </div>
      </div>

      <!-- Recompensa -->
      <div class="label">
        <span data-i18n="mission.reward">Recompensa:</span>
        <strong style="font-size:36px">+<?= (int)$mision['puntuacion'] ?></strong>
      </div>

      <!-- Botón para realizar misión -->
      <div class="card-actions" style="margin-top:20px;">
        <a class="btn-pill" href="realizarMision.php?id_mision=<?= (int)$mision['id_mision'] ?>" data-i18n="mission.perform">Realizar</a>
      </div>

      <!-- Mostrar evidencia si existe -->
      <?php if ($evidencia): ?> 
        <div style="margin-top:20px;">
          <p><strong style="font-size:22px" data-i18n="mission.attached">Misión adjuntada:</strong></p>
          <img src="<?= BASE_URL ?>/assets/img/evidencias/<?= htmlspecialchars($evidencia) ?>" 
               alt="Evidencia subida" 
               style="width:100%;max-width:400px;border-radius:10px;border:2px solid #ccc;">
        </div>
      <?php endif; ?>

      <!-- Enlace para volver a la lista de misiones -->
      <a href="misiones.php" style="display:inline-block;margin-top:15px;color:var(--darkBlue);text-decoration:underline; font-size:var(--fuenteTamanoLg)" data-i18n="mission.back">Volver a misiones</a>
    </div>

    <!-- Imagen de la misión -->
    <div class="map">
      <?php if (!empty($mision['imagen_mision'])): ?>
        <img src="<?= BASE_URL ?>/assets/img/misiones/<?= htmlspecialchars($mision['imagen_mision']) ?>" 
             alt="<?= htmlspecialchars($mision['titulo_misiones']) ?>" 
             style="width:100%;height:100%;object-fit:cover;border-radius:var(--radius-xl);">
      <?php else: ?>
        <!-- Texto alternativo si no hay imagen -->
        <div style="height:100%;display:flex;align-items:center;justify-content:center;color:#888;" data-i18n="news.noimage">Sin imagen</div>
      <?php endif; ?>
    </div>
  </section>
<?php else: ?>
  <!-- Mensaje si no se encontró la misión -->
  <p style="text-align:center;margin:60px 0;" data-i18n="mission.notfound">Misión no encontrada.</p>
<?php endif; ?>

<?php
// Incluye el pie de página
require_once BASE_PATH . '/includes/footer.php';
?>
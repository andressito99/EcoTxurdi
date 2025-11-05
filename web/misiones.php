<?php
// Incluye el archivo de configuración principal
require_once __DIR__ . '/../config.php';

// Marca la sección activa para resaltar en el menú
$active = 'misiones';

// Incluye la cabecera del sitio
require_once BASE_PATH . '/includes/header.php';

// Obtiene el id del usuario logueado si existe
$id_usuario = $_SESSION['id_usuario'] ?? null;

// Consulta para obtener todas las misiones disponibles ordenadas por id descendente
$stmt = $pdo->query("SELECT id_mision, titulo_misiones, imagen_mision, descripcion_misiones, puntuacion, ubicacion, tipo, resuelto 
                     FROM misiones 
                     WHERE tipo = 'mision'
                     ORDER BY id_mision DESC");
$misiones = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Si hay usuario logueado, se obtienen las misiones que ya completó
$misiones_completadas = [];
if ($id_usuario) {
    $stmt = $pdo->prepare("SELECT id_mision FROM cumple WHERE id_usuario = ?");
    $stmt->execute([$id_usuario]);
    // fetchAll con FETCH_COLUMN devuelve solo la columna id_mision como array
    $misiones_completadas = $stmt->fetchAll(PDO::FETCH_COLUMN);
}
?>

<!-- Sección principal de tarjetas de misiones -->
<section class="tiles">
  <?php if (!empty($misiones)): ?>
    <?php foreach ($misiones as $mision): ?>
      <article class="tile">

        <!-- Badge que muestra si la misión es nueva o ya está completada -->
        <?php if (in_array($mision['id_mision'], $misiones_completadas)): ?>
          <div class="badge" style="background:#4CAF50;" data-i18n="mission.completed">Completada</div>
        <?php elseif (!$mision['resuelto']): ?>
          <div class="badge" data-i18n="badge.new">Nueva</div>
        <?php endif; ?>

        <!-- Imagen de la misión -->
        <div class="media">
          <?php if (!empty($mision['imagen_mision'])): ?>
            <img src="<?= BASE_URL ?>/assets/img/misiones/<?= htmlspecialchars($mision['imagen_mision']) ?>" 
                 alt="<?= htmlspecialchars($mision['titulo_misiones']) ?>" 
                 style="width:100%;height:220px;object-fit:cover;border-radius:var(--radius-xl);">
          <?php else: ?>
            <!-- Mensaje en caso de que no haya imagen -->
            <div style="height:220px;display:flex;align-items:center;justify-content:center;color:#888;" data-i18n="news.noimage">
              Sin imagen
            </div>
          <?php endif; ?>
        </div>

        <!-- Ubicación y puntos de recompensa -->
        <div class="card-date">
          <span data-i18n="mission.<?= (int)$mision['id_mision'] ?>.loc"><?= htmlspecialchars($mision['ubicacion']) ?></span>
          · +<?= (int)$mision['puntuacion'] ?> <span data-i18n="points.suffix">pts</span>
        </div>

        <!-- Título de la misión -->
        <div class="card-title" data-i18n="mission.<?= (int)$mision['id_mision'] ?>.title">
          <?= htmlspecialchars($mision['titulo_misiones']) ?>
        </div>

        <!-- Botones de acción -->
        <div class="card-actions" style="display:flex; gap:6px; justify-content:center;">
          <?php if (in_array($mision['id_mision'], $misiones_completadas)): ?>
            <!-- Si ya está completada, mostrar etiqueta y botón de detalles -->
            <span class="btn-pill" style="background:#4CAF50; cursor:default; padding:6px 12px; white-space:nowrap;" data-i18n="mission.completed">
              Completada
            </span>
            <a class="btn-pill" href="<?= BASE_URL ?>/web/mision.php?id_mision=<?= (int)$mision['id_mision'] ?>" 
              style="padding:6px 12px; white-space:nowrap;" data-i18n="mission.details">
              Detalles »
            </a>
          <?php else: ?>
            <!-- Si está disponible, mostrar botón para participar -->
            <a class="btn-pill" href="<?= BASE_URL ?>/web/mision.php?id_mision=<?= (int)$mision['id_mision'] ?>" 
              style="padding:8px 14px; white-space:nowrap;" data-i18n="mission.participate">
              Participar »
            </a>
          <?php endif; ?>
        </div>

      </article>
    <?php endforeach; ?>
  <?php else: ?>
    <!-- Mensaje si no hay misiones -->
    <p style="text-align:center;width:100%;" data-i18n="missions.none">No hay misiones disponibles por el momento.</p>
  <?php endif; ?>
</section>

<!-- Incluye el pie de página -->
<?php include '../includes/footer.php'; ?>
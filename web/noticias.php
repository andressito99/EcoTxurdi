<?php
// Incluye el archivo de configuración principal
require_once __DIR__ . '/../config.php';

// Marca la sección activa para navegación
$active = 'noticias';

// Incluye la cabecera del sitio
require_once BASE_PATH . '/includes/header.php';

// Consulta todas las noticias ordenadas por fecha (más recientes primero)
$stmt = $pdo->query("SELECT id_noticia, titulo_noticia, descripcion_noticia, imagen_noticia, fecha_noticia, destacado 
                     FROM noticias 
                     ORDER BY fecha_noticia DESC");
$noticias = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- Sección de noticias en formato de tarjetas -->
<section class="tiles">
  <?php if (!empty($noticias)): ?>
    <?php foreach ($noticias as $noticia): ?>
      <article class="tile">

        <!-- Badge para noticias destacadas o recientes -->
        <?php if ($noticia['destacado']): ?>
            <div class="badge" data-i18n="badge.new">Nueva</div>
        <?php endif; ?>

        <!-- Imagen de la noticia -->
        <div class="media">
          <?php if (!empty($noticia['imagen_noticia'])): ?>
            <img src="<?= BASE_URL ?>/assets/img/noticias/<?= htmlspecialchars($noticia['imagen_noticia']) ?>" 
                 alt="<?= htmlspecialchars($noticia['titulo_noticia']) ?>" 
                 style="width:100%;height:220px;object-fit:cover;border-radius:var(--radius-xl);">
          <?php else: ?>
            <div data-i18n="news.noimage">Sin imagen</div>
          <?php endif; ?>
        </div>

        <!-- Fecha de publicación -->
        <time class="card-date"
              data-i18n-date="true"
              data-date="<?= htmlspecialchars($noticia['fecha_noticia']) ?>">
              <?= date('F d, Y', strtotime($noticia['fecha_noticia'])) ?>
        </time>

        <!-- Título de la noticia -->
        <div class="card-title" data-i18n="news.<?= (int)$noticia['id_noticia'] ?>.title">
          <?= htmlspecialchars($noticia['titulo_noticia']) ?>
        </div>

        <!-- Botón para leer más -->
        <div class="card-actions">
          <a class="btn"
             href="<?= BASE_URL ?>/web/noticia.php?id_noticia=<?= (int)$noticia['id_noticia'] ?>"
             data-i18n="btn.readmore">
             Leer Más »
          </a>
        </div>
      </article>
    <?php endforeach; ?>
  <?php else: ?>
    <!-- Mensaje si no existen noticias -->
    <p style="text-align:center;width:100%;" data-i18n="news.none">
      No hay noticias disponibles por el momento.
    </p>
  <?php endif; ?>
</section>

<!-- Incluye el pie de página -->
<?php require_once BASE_PATH . '/includes/footer.php'; ?>
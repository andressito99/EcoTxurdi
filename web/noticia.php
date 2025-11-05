<?php
// Incluye archivo de configuración para conexión y constantes
require_once __DIR__ . '/../config.php';

// Obtiene el id de la noticia desde la URL (GET), lo convierte a entero
$id_noticia = isset($_GET['id_noticia']) ? intval($_GET['id_noticia']) : 0;

// Consulta para obtener los datos de la noticia específica
$stmt = $pdo->prepare("SELECT * FROM noticias WHERE id_noticia = ?");
$stmt->execute([$id_noticia]);
$noticia = $stmt->fetch(PDO::FETCH_ASSOC);

// Incluye la cabecera HTML del sitio
require_once BASE_PATH . '/includes/header.php';
?>

<?php if ($noticia): ?>
  <!-- Contenido principal de la noticia -->
  <article class="detalle-noticia" style="max-width:800px;margin:40px auto;padding:20px;">
    
    <!-- Título de la noticia -->
    <h1 data-i18n="news.<?= (int)$noticia['id_noticia'] ?>.title">
      <?= htmlspecialchars($noticia['titulo_noticia']) ?>
    </h1>

    <!-- Fecha de la noticia -->
    <p class="fecha"
       data-i18n-date="true"
       data-date="<?= htmlspecialchars($noticia['fecha_noticia']) ?>">
       <?= date('F d, Y', strtotime($noticia['fecha_noticia'])) ?>
    </p>

    <!-- Imagen de la noticia, si existe -->
    <?php if (!empty($noticia['imagen_noticia'])): ?>
      <img src="<?= BASE_URL ?>/assets/img/noticias/<?= htmlspecialchars($noticia['imagen_noticia']) ?>" 
           alt="<?= htmlspecialchars($noticia['titulo_noticia']) ?>"
           style="width:100%;border-radius:var(--radius-xl);margin:20px 0;">
    <?php endif; ?>

    <!-- Descripción o contenido de la noticia -->
    <p class="descripcion"
       data-i18n="news.<?= (int)$noticia['id_noticia'] ?>.desc"
       data-i18n-html="true">
       <?= nl2br(htmlspecialchars($noticia['descripcion_noticia'])) ?>
    </p>

    <!-- Botón para volver a la lista de noticias -->
    <a href="<?= BASE_URL ?>/web/noticias.php"
       class="btn"
       style="display:inline-block;margin-top:10px;"
       data-i18n="btn.back">
       Volver
    </a>
  </article>
<?php else: ?>
  <!-- Mensaje si la noticia no existe -->
  <p style="text-align:center;margin:60px 0;" data-i18n="noticia.notfound">
    Noticia no encontrada.
  </p>
<?php endif; ?>

<?php
// Incluye el pie de página
require_once BASE_PATH . '/includes/footer.php';
?>
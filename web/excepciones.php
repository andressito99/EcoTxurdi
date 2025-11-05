<?php
  // Incluye el archivo de configuración del proyecto
  require_once __DIR__ . '/../config.php';

  // Variable para indicar que esta página está activa en el menú
  $active = 'inicio';

  // Incluye la cabecera del sitio (HTML inicial, menú, etc.)
  require_once BASE_PATH . '/includes/header.php';
?>
  <section class="hero">
    <div>
      <!-- Título principal de la sección hero con soporte para traducción -->
      <h1 class="big" data-i18n-html="true" data-i18n="exception.title">Error de acceso</h1>

      <!-- Botón que lleva a la página de misiones -->
      <a class="btn" href="<?= BASE_URL ?>/index.php" data-i18n="exception.cta">Volver a estar a salvo</a>
    </div>

    <!-- Imagen ilustrativa dentro de la sección hero -->
    <div class="illus"><img width="60%" src="<?= BASE_URL ?>/assets/img/excepcion.png"></div>
  </section>

<?php
  // Incluye el pie de página del sitio
  require_once BASE_PATH . '/includes/footer.php';
?>  
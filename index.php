<?php
  // Incluye el archivo de configuración del proyecto
  require_once __DIR__ . '/config.php';

  // Variable para indicar que esta página está activa en el menú
  $active = 'inicio';

  // Incluye la cabecera del sitio (HTML inicial, menú, etc.)
  require_once BASE_PATH . '/includes/header.php';
?>
  <section class="hero">
    <div>
      <!-- Título principal de la sección hero con soporte para traducción -->
      <h1 class="big" data-i18n-html="true" data-i18n="hero.title">Ayuda a mejorar<br>el medio ambiente</h1>

      <!-- Botón que lleva a la página de misiones -->
      <a class="btn" href="<?= BASE_URL ?>/web/misiones.php" data-i18n="hero.cta">Ver misiones</a>
    </div>

    <!-- Imagen ilustrativa dentro de la sección hero -->
    <div class="illus"><img src="<?= BASE_URL ?>/assets/img/eco.png"></div>
  </section>

  <section class="tiles">
    <!-- Primer bloque informativo -->
    <article class="tile">
      <!-- Imagen relacionada con el contenido -->
      <div class="media"><img src="<?= BASE_URL ?>/assets/img/eco1.png"></div>

      <!-- Título de la tarjeta con texto traducible -->
      <div class="card-title" data-i18n="tile1.title">Cuida el planeta</div>

      <!-- Descripción o nota informativa -->
      <p class="note" data-i18n="tile1.note">Aprende cómo tus pequeñas acciones diarias pueden generar un gran impacto positivo en el medio ambiente.</p>
    </article>

    <!-- Segundo bloque informativo -->
    <article class="tile">
      <div class="media"><img src="<?= BASE_URL ?>/assets/img/eco2.png"></div>
      <div class="card-title" data-i18n="tile2.title">Actúa por la naturaleza</div>
      <p class="note" data-i18n="tile2.note">Participa en misiones ecológicas y ayuda a restaurar el equilibrio natural de tu entorno.</p>
    </article>

    <!-- Tercer bloque informativo -->
    <article class="tile">
      <div class="media"><img src="<?= BASE_URL ?>/assets/img/eco3.png"></div>
      <div class="card-title" data-i18n="tile3.title">Únete a la comunidad</div>
      <p class="note" data-i18n="tile3.note">Colabora con personas comprometidas con la protección del planeta. ¡Juntos somos el cambio!</p>
    </article>
  </section>

<?php
  // Incluye el pie de página del sitio
  require_once BASE_PATH . '/includes/footer.php';
?>  
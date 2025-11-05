<footer class="footer-pro" role="contentinfo">
  <div class="footer-pro__container" role="complementary">
    <div class="footer-pro__col">
      <h4 class="footer-pro__title" data-i18n="footer.title">Ecotxurdi</h4>
      <p class="footer-pro__text" data-i18n="footer.tagline">
        Cuidar el planeta empieza por nuestras acciones diarias
      </p>
      <ul class="footer-pro__social">
        <li>
          <a href="#" aria-label="Twitter" class="is-twitter">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M22.46 6c-.77.35-1.6.58-2.46.69a4.27 4.27 0 0 0 1.87-2.36 8.54 8.54 0 0 1-2.71 1.04 4.26 4.26 0 0 0-7.26 3.88 12.1 12.1 0 0 1-8.79-4.46 4.26 4.26 0 0 0 1.32 5.68 4.2 4.2 0 0 1-1.93-.53v.05a4.26 4.26 0 0 0 3.42 4.18 4.3 4.3 0 0 1-1.92.07 4.27 4.27 0 0 0 3.98 2.96A8.55 8.55 0 0 1 2 19.53a12.06 12.06 0 0 0 6.54 1.92c7.85 0 12.14-6.5 12.14-12.14l-.01-.55A8.6 8.6 0 0 0 24 6.59c-.55.24-1.15.41-1.77.48z"/></svg>
          </a>
        </li>
        <li>
          <a href="#" aria-label="Facebook" class="is-facebook">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M22 12.07C22 6.48 17.52 2 11.93 2S2 6.48 2 12.07c0 5 3.66 9.14 8.44 9.93v-7.03H7.9v-2.9h2.54V9.41c0-2.5 1.49-3.88 3.77-3.88 1.09 0 2.24.2 2.24.2v2.46h-1.26c-1.24 0-1.63.77-1.63 1.56v1.87h2.77l-.44 2.9h-2.33V22c4.78-.79 8.44-4.93 8.44-9.93z"/></svg>
          </a>
        </li>
        <li>
          <a href="#" aria-label="Instagram" class="is-instagram">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 7.3A4.7 4.7 0 1 0 12 16.7 4.7 4.7 0 0 0 12 7.3zm0 7.7a3 3 0 1 1 0-6 3 3 0 0 1 0 6zm6.1-7.9a1.1 1.1 0 1 1-2.2 0 1.1 1.1 0 0 1 2.2 0z"/><path d="M17.8 3H6.2A3.2 3.2 0 0 0 3 6.2v11.6A3.2 3.2 0 0 0 6.2 21h11.6A3.2 3.2 0 0 0 21 17.8V6.2A3.2 3.2 0 0 0 17.8 3zm1.9 14.8a1.9 1.9 0 0 1-1.9 1.9H6.2a1.9 1.9 0 0 1-1.9-1.9V6.2A1.9 1.9 0 0 1 6.2 4.3h11.6a1.9 1.9 0 0 1 1.9 1.9v11.6z"/></svg>
          </a>
        </li>
      </ul>
      <!-- Bloque del ODS (Objetivo de Desarrollo Sostenible) -->
        <div class="footer-pro__col footer-pro__ods">
          <h4 class="footer-pro__title">ODS 15: Vida de ecosistemas terrestres</h4>
          <img 
            src="<?= BASE_URL ?>/assets/img/ods15.jpg" 
            alt="Objetivo de Desarrollo Sostenible 15: Vida de ecosistemas terrestres" 
            class="ods-img"
            height="80"
            loading="lazy"
          >
        </div>

    </div>

    <!-- Col 2 -->
    <div class="footer-pro__col">
      <h4 class="footer-pro__title" data-i18n="footer.latest">Últimas Noticias</h4>

      <?php
        try {
          if (!isset($pdo)) { throw new Exception('Sin conexión PDO'); }
          $stmt = $pdo->query("SELECT id_noticia, titulo_noticia, imagen_noticia, fecha_noticia FROM noticias ORDER BY fecha_noticia DESC LIMIT 3");
          $ultimas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
          $ultimas = [];
        }
      ?>

      <?php if (!empty($ultimas)): ?>
        <?php foreach ($ultimas as $n): ?>
          <article class="news-item">
            <a class="news-item__thumb" href="<?= BASE_URL ?>/web/noticia.php?id_noticia=<?= (int)$n['id_noticia'] ?>">
              <?php if (!empty($n['imagen_noticia'])): ?>
                <img src="<?= BASE_URL ?>/assets/img/noticias/<?= htmlspecialchars($n['imagen_noticia']) ?>" alt="<?= htmlspecialchars($n['titulo_noticia']) ?>">
              <?php else: ?>
                <img src="<?= BASE_URL ?>/assets/img/fondo.png" alt="Sin imagen" data-i18n-alt="news.noimage">
              <?php endif; ?>
            </a>
            <div class="news-item__body">
              <a href="<?= BASE_URL ?>/web/noticia.php?id_noticia=<?= (int)$n['id_noticia'] ?>" class="news-item__title" data-i18n="news.<?= (int)$n['id_noticia'] ?>.title">
                <?= htmlspecialchars($n['titulo_noticia']) ?>
              </a>
              <div class="news-item__meta">
                <time data-i18n-date="true" data-date="<?= htmlspecialchars($n['fecha_noticia']) ?>"><?= date('d/m/Y', strtotime($n['fecha_noticia'])) ?></time>
              </div>
            </div>
          </article>
        <?php endforeach; ?>
      <?php else: ?>
        <p class="footer-pro__text" data-i18n="news.none">No hay noticias disponibles por ahora.</p>
      <?php endif; ?>
    </div>

    <!-- Col 3 -->
    <div class="footer-pro__col">
      <h4 class="footer-pro__title" data-i18n="footer.menu">Menu</h4>
      <ul class="footer-pro__links">
        <li><a href="#" data-i18n="nav.home">Inicio</a></li>
        <li><a href="#" data-i18n="nav.missions">Misiones</a></li>
        <li><a href="#" data-i18n="nav.ranking">Clasificación</a></li>
        <li><a href="#" data-i18n="nav.news">Noticias</a></li>
      </ul>
    </div>

    <!-- Col 4 -->
    <div class="footer-pro__col">
      <h4 class="footer-pro__title" data-i18n="footer.preguntas">Tienes alguna pregunta?</h4>
      <ul class="footer-pro__contact">
        <li>
          <span class="ico">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2C8.14 2 5 5.14 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.86-3.14-7-7-7zm0 9.5a2.5 2.5 0 1 1 0-5 2.5 2.5 0 0 1 0 5z"/></svg>
          </span>
          Fp Txurdinaga
        </li>
        <li>
          <span class="ico">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6.6 10.8a15.5 15.5 0 0 0 6.6 6.6l2.2-2.2a1 1 0 0 1 1.1-.24c1.2.49 2.6.76 4 .76a1 1 0 0 1 1 1V20a1 1 0 0 1-1 1C11.2 21 3 12.8 3 2a1 1 0 0 1 1-1h2.28a1 1 0 0 1 1 1c0 1.42.27 2.8.76 4a1 1 0 0 1-.24 1.1L6.6 10.8z"/></svg>
          </span>
          +34 392 3929 210
        </li>
        <li>
          <span class="ico">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 4H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2zm0 4-8 5L4 8V6l8 5 8-5v2z"/></svg>
          </span>
          info@ecotxudi.com
        </li>
      </ul>
    </div>
  </div>

  <div class="footer-pro__bottom">
    <p>Copyright ©<?php echo date("Y"); ?> All rights reserved</a></p>
  </div>
</footer>
</body>
</html>


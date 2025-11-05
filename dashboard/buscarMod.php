<?php
$rolNecesario = 'mod';
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../assets/auth.php';

$rolNecesario = 'admin';
$query = isset($_GET['query']) ? trim($_GET['query']) : '';

require_once __DIR__ . '/../includes/header.php';

if ($query === '') {
  echo '<div class="section" style="text-align:center;margin-top:40px;">';
  echo '<p style="font-size:1.1em;color:var(--text);" data-i18n="buscar.no_query">⚠️ No se ingresó ningún término de búsqueda.</p>';
  echo '<a href="mod.php" class="btn" style="margin-top:15px;background:var(--accent);color:var(--colorBase);padding:10px 16px;border-radius:var(--radius);text-decoration:none;" data-i18n="buscar.back_panel">⬅ Volver al panel</a>';
  echo '</div>';
  require_once __DIR__ . '/../includes/footer.php';
  exit;
}

$param = "%$query%";

// --- SOLICITUDES (misiones con tipo='solicitud') ---
$stmtSolicitudes = $pdo->prepare("SELECT id_mision, titulo_misiones, descripcion_misiones, ubicacion, puntuacion, imagen_mision, id_usuario 
                                  FROM misiones 
                                  WHERE tipo = 'solicitud' 
                                  AND (titulo_misiones LIKE ? OR descripcion_misiones LIKE ? OR ubicacion LIKE ?)
                                  ORDER BY id_mision DESC");
$stmtSolicitudes->execute([$param, $param, $param]);
$solicitudes = $stmtSolicitudes->fetchAll(PDO::FETCH_ASSOC);

// --- MISIONES (tipo='mision') ---
$stmtMisiones = $pdo->prepare("SELECT id_mision, titulo_misiones, descripcion_misiones, ubicacion, puntuacion, imagen_mision 
                               FROM misiones 
                               WHERE tipo = 'mision' 
                               AND (titulo_misiones LIKE ? OR descripcion_misiones LIKE ? OR ubicacion LIKE ?)
                               ORDER BY id_mision DESC");
$stmtMisiones->execute([$param, $param, $param]);
$misiones = $stmtMisiones->fetchAll(PDO::FETCH_ASSOC);
?>



<section class="search-results" role="region" aria-label="Resultados de búsqueda">
  <div class="search-header">

  <a href="mod.php" class="btn-back" aria-label="Volver al panel" data-i18n="buscar.back_panel">⬅ Volver al panel</a>
  <h2 data-i18n="buscar.results_for">Resultados para "<strong><?= htmlspecialchars($query) ?></strong>"</h2>
    
  </div>
    <!-- SOLICITUDES -->
    <section class="result-block" aria-labelledby="heading-solicitudes">
  <h3 id="heading-solicitudes" data-i18n="buscar.requests.title">Solicitudes</h3>
    <?php if (count($solicitudes) > 0): ?>
        <?php foreach ($solicitudes as $s): ?>
        <article class="result-item" tabindex="0">
            <strong><?= htmlspecialchars($s['titulo_misiones']) ?></strong>
            <p><?= nl2br(htmlspecialchars($s['descripcion_misiones'])) ?></p>
            <p><small><?= htmlspecialchars($s['ubicacion']) ?> | <?= (int)$s['puntuacion'] ?> pts</small></p>
            <?php if ($s['imagen_mision']): ?>
            <img src="../assets/img/misiones/<?= htmlspecialchars($s['imagen_mision']) ?>" alt="Imagen de <?= htmlspecialchars($s['titulo_misiones']) ?>">
            <?php endif; ?>
            <div class="actions">
            <a class="small aceptar" href="../dashboard/aprobar.php?id=<?= (int)$s['id_mision'] ?>&return=<?= urlencode($_SERVER['REQUEST_URI']) ?>" data-i18n="buscar.requests.accept">Aprobar</a>
            <a class="small denegar" href="../dashboard/denegar.php?id=<?= (int)$s['id_mision'] ?>&return=<?= urlencode($_SERVER['REQUEST_URI']) ?>" data-i18n="buscar.requests.deny">Denegar</a>
            </div>
        </article>
        <?php endforeach; ?>
    <?php else: ?>
  <p class="no-results" data-i18n="buscar.requests.none">No se encontraron solicitudes.</p>
    <?php endif; ?>
    </section>

    


  <!-- MISIONES -->
  <section class="result-block" aria-labelledby="heading-misiones">
  <h3 id="heading-misiones" data-i18n="buscar.missions.title">Misiones</h3>
    <?php if (count($misiones) > 0): ?>
      <?php foreach ($misiones as $m): ?>
        <article class="result-item" tabindex="0">
          <strong><?= htmlspecialchars($m['titulo_misiones']) ?></strong>
          <p><?= nl2br(htmlspecialchars($m['descripcion_misiones'])) ?></p>
          <p><small> <?= htmlspecialchars($m['ubicacion']) ?> | <?= (int)$m['puntuacion'] ?> pts</small></p>
          <?php if ($m['imagen_mision']): ?>
            <img src="../assets/img/misiones/<?= htmlspecialchars($m['imagen_mision']) ?>" alt="Imagen de <?= htmlspecialchars($m['titulo_misiones']) ?>">
          <?php endif; ?>
          <div class="actions">
            <a class="small editar" href="../dashboard/misiones/editar.php?id=<?= (int)$m['id_mision'] ?>" data-i18n="buscar.missions.edit">Editar</a>
            <a class="small borrar" href="../dashboard/misiones/borrar.php?id=<?= (int)$m['id_mision'] ?>" data-i18n="buscar.missions.delete">Borrar</a>
            </div>

        </article>
      <?php endforeach; ?>
    <?php else: ?>
  <p class="no-results" data-i18n="buscar.missions.none">No se encontraron misiones.</p>
    <?php endif; ?>
  </section>
</section>

<script src="../assets/js/confirmacion.js"></script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

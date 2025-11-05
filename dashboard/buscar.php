<?php
$rolNecesario = 'admin';
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../assets/auth.php';

$rolNecesario = 'admin';
$query = isset($_GET['query']) ? trim($_GET['query']) : '';

require_once __DIR__ . '/../includes/header.php';

if ($query === '') {
  echo '<div class="section" style="text-align:center;margin-top:40px;">
          <p style="font-size:1.1em;color:var(--text);">⚠️ No se ingresó ningún término de búsqueda.</p>
          <a href="admin.php" class="btn" style="margin-top:15px;background:var(--accent);color:var(--colorBase);padding:10px 16px;border-radius:var(--radius);text-decoration:none;">⬅ Volver al panel</a>
        </div>';
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

// --- USUARIOS ---
$stmtUsuarios = $pdo->prepare("SELECT id_usuario, usuario, imagen_user, puntosRanking, puntosCambio
                               FROM usuarios
                               WHERE usuario LIKE ?
                               ORDER BY id_usuario");
$stmtUsuarios->execute([$param]);
$usuarios = $stmtUsuarios->fetchAll(PDO::FETCH_ASSOC);

// --- RECOMPENSAS ---
$stmtRecompensas = $pdo->prepare("SELECT id_recompensa, titulo_recompensa, descripcion_recompensa, imagen_recom, precio
                                  FROM recompensas
                                  WHERE titulo_recompensa LIKE ? OR descripcion_recompensa LIKE ?
                                  ORDER BY id_recompensa DESC");
$stmtRecompensas->execute([$param, $param]);
$recompensas = $stmtRecompensas->fetchAll(PDO::FETCH_ASSOC);
?>



<section class="search-results" role="region" aria-label="Resultados de búsqueda">
  <div class="search-header">

    <a href="admin.php" class="btn-back" aria-label="Volver al panel">⬅ Volver al panel</a>
    <h2><span data-i18n="buscar.results_for">Resultados para</span> "<strong><?= htmlspecialchars($query) ?></strong>"</h2>
    
  </div>
    <!-- SOLICITUDES -->
    <section class="result-block" aria-labelledby="heading-solicitudes">
    <h3 id="heading-solicitudes" data-i18n="buscar.requests.title">Solicitudes</h3>
    <?php if (count($solicitudes) > 0): ?>
        <?php foreach ($solicitudes as $s): ?>
        <article class="result-item" tabindex="0">
            <strong><?= htmlspecialchars($s['titulo_misiones']) ?></strong>
            <p><?= nl2br(htmlspecialchars($s['descripcion_misiones'])) ?></p>
            <p><small><?= htmlspecialchars($s['ubicacion']) ?> | <?= (int)$s['puntuacion'] ?> <span data-i18n="points.suffix">pts</span></small></p>
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
          <p><small> <?= htmlspecialchars($m['ubicacion']) ?> | <?= (int)$m['puntuacion'] ?> <span data-i18n="points.suffix">pts</span></small></p>
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

  <!-- USUARIOS -->
  <section class="result-block" aria-labelledby="heading-usuarios">
    <h3 id="heading-usuarios" data-i18n="buscar.users.title">Usuarios</h3>
    <?php if (count($usuarios) > 0): ?>
      <?php foreach ($usuarios as $u): ?>
        <article class="result-item" tabindex="0">
          <strong><?= htmlspecialchars($u['usuario']) ?></strong>
          <p> <span data-i18n="buscar.users.ranking">Ranking:</span> <?= (int)$u['puntosRanking'] ?> | <span data-i18n="buscar.users.exchange">Cambio:</span> <?= (int)$u['puntosCambio'] ?></p>
          <?php if ($u['imagen_user']): ?>
            <img src="../assets/img/usuarios/<?= htmlspecialchars($u['imagen_user']) ?>" alt="Imagen de <?= htmlspecialchars($u['usuario']) ?>">
          <?php endif; ?>
            <div class="actions">
                <a class="small editar" href="../dashboard/usuarios/editar.php?id=<?= (int)$u['id_usuario'] ?>" data-i18n="buscar.users.edit">Editar</a>
                <a class="small borrar" href="../dashboard/usuarios/borrar.php?id=<?= (int)$u['id_usuario'] ?>" data-i18n="buscar.users.delete">Borrar</a>
            </div>
        </article>
      <?php endforeach; ?>
    <?php else: ?>
      <p class="no-results" data-i18n="buscar.users.none">No se encontraron usuarios.</p>
    <?php endif; ?>
  </section>

  <!-- RECOMPENSAS -->
  <section class="result-block" aria-labelledby="heading-recompensas">
    <h3 id="heading-recompensas" data-i18n="buscar.rewards.title">Recompensas</h3>
    <?php if (count($recompensas) > 0): ?>
      <?php foreach ($recompensas as $r): ?>
        <article class="result-item" tabindex="0">
          <strong><?= htmlspecialchars($r['titulo_recompensa']) ?></strong>
          <p><?= nl2br(htmlspecialchars($r['descripcion_recompensa'])) ?></p>
          <p><small><span data-i18n="buscar.rewards.price">Precio:</span> <?= (int)$r['precio'] ?> <span data-i18n="points.suffix">pts</span></small></p>
          <?php if ($r['imagen_recom']): ?>
            <img src="../assets/img/recompensas/<?= htmlspecialchars($r['imagen_recom']) ?>" alt="Imagen de <?= htmlspecialchars($r['titulo_recompensa']) ?>">
          <?php endif; ?>
            <div class="actions">
                <a class="small editar" href="../dashboard/recompensas/editar.php?id=<?= (int)$r['id_recompensa'] ?>" data-i18n="buscar.rewards.edit">Editar</a>
                <a class="small borrar" href="../dashboard/recompensas/borrar.php?id=<?= (int)$r['id_recompensa'] ?>" data-i18n="buscar.rewards.delete">Borrar</a>
            </div>
        </article>
      <?php endforeach; ?>
    <?php else: ?>
      <p class="no-results" data-i18n="buscar.rewards.none">No se encontraron recompensas.</p>
    <?php endif; ?>
  </section>
</section>

<script src="../assets/js/confirmacion.js"></script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

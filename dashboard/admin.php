<?php
// Importar configuración general (DB, sesiones, constantes)
$rolNecesario = 'admin'; 
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../assets/auth.php';

// Variables para estado de navegación
$active = '';
$rightButtonHref = '#';

// Incluir cabecera del panel
require_once __DIR__ . '/../includes/header.php';

// Obtener todas las solicitudes de misiones pendientes
$stmt = $pdo->query("SELECT id_mision, titulo_misiones, imagen_mision, descripcion_misiones, puntuacion, ubicacion 
                      FROM misiones 
                      WHERE tipo = 'solicitud'
                      ORDER BY id_mision DESC");
$solicitudes = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmtMisiones = $pdo->query("SELECT id_mision, titulo_misiones, imagen_mision, descripcion_misiones, puntuacion, ubicacion 
                      FROM misiones 
                      WHERE tipo = 'mision'
                      ORDER BY id_mision DESC");
$misiones = $stmtMisiones->fetchAll(PDO::FETCH_ASSOC);

$stmtUsuarios = $pdo->query("SELECT id_usuario, imagen_user, usuario, contrasena, puntosRanking, puntosCambio
                      FROM usuarios
                      ORDER BY id_usuario");
$usuarios = $stmtUsuarios->fetchAll(PDO::FETCH_ASSOC);

$stmtRecompensas = $pdo->query("SELECT id_recompensa, imagen_recom, titulo_recompensa, descripcion_recompensa, precio
                      FROM recompensas
                      ORDER BY id_recompensa DESC");
$recompensas = $stmtRecompensas->fetchAll(PDO::FETCH_ASSOC);
?> 
<section class="flex-panels" style="grid-template-columns:1fr 1fr">

  <!-- Bloque de Búsqueda-->  
  <div class="block search-block" id="busqueda-block">
  <h3 class="search-title" data-i18n="admin.search.title">Búsqueda rápida</h3>
    <form method="GET" action="buscar.php" id="search-form" class="search-form" role="search" aria-label="Buscar elementos">
      <input 
        type="text" 
        name="query" 
        id="search-input" 
        class="search-input" 
  placeholder="Escribe aquí para buscar..." data-i18n-placeholder="admin.search.placeholder"
        value="<?= isset($_GET['query']) ? htmlspecialchars($_GET['query']) : '' ?>"
        aria-label="Campo de búsqueda"
      >
  <button type="submit" class="search-btn" aria-label="Buscar" data-i18n="admin.search.button">Buscar</button>
    </form>
  </div>

  <!-- Bloque de solicitudes -->
  <div class="block" id="solicitudes-block">
  <h3 data-i18n="admin.requests.title">Solicitudes</h3>

    <?php if (count($solicitudes) > 0): ?>
      <?php foreach ($solicitudes as $sol): ?>
        <!-- fila visible -->
        <div class="item solicitud" data-id="<?= (int)$sol['id_mision'] ?>">
          <span><?= htmlspecialchars($sol['titulo_misiones']) ?></span>
          <!-- toggle: botón accesible -->
          <button type="button" class="toggle" aria-expanded="false" aria-controls="detalle-solicitud-<?= (int)$sol['id_mision'] ?>">⬇</button>
        </div>

        <!-- detalle (oculto por defecto) -->
        <div class="detalle" id="detalle-solicitud-<?= (int)$sol['id_mision'] ?>">
          <p><strong data-i18n="admin.requests.location">Ubicación:</strong> <?= htmlspecialchars($sol['ubicacion']) ?></p>
          <p><strong data-i18n="admin.requests.score">Puntuación:</strong> <?= htmlspecialchars($sol['puntuacion']) ?> <span data-i18n="points.suffix">pts</span></p>

          <?php if (!empty($sol['imagen_mision'])): ?>
            <p><strong data-i18n="admin.requests.image">Imagen:</strong></p>
            <img src="../assets/img/misiones/<?= htmlspecialchars($sol['imagen_mision']) ?>"
              alt="Imagen de la misión"
              style="max-width:200px; border-radius:6px; margin-bottom:10px;">
          <?php endif; ?>

          <p><strong data-i18n="admin.requests.description">Descripción:</strong> <?= nl2br(htmlspecialchars($sol['descripcion_misiones'])) ?></p>

          <div class="actions">
            <a class="small aceptar" href="../dashboard/aprobar.php?id=<?= (int)$sol['id_mision'] ?>" data-i18n="admin.requests.accept">Aceptar</a>
            <a class="small denegar" href="../dashboard/denegar.php?id=<?= (int)$sol['id_mision'] ?>" data-i18n="admin.requests.deny">Denegar</a>
          </div>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
  <p class="no-solicitudes" data-i18n="admin.requests.none">No hay solicitudes pendientes.</p>
    <?php endif; ?>
  </div>

  <!-- Bloque de misiones -->
  <div class="block" id="misiones-block">
  <h3 data-i18n="admin.missions.title">Misiones</h3>

    <?php if (count($misiones) > 0): ?>
      <?php foreach ($misiones as $mision): ?>
        <!-- fila visible -->
        <div class="item mision" data-id="<?= (int)$mision['id_mision'] ?>">
          <span><?= htmlspecialchars($mision['titulo_misiones']) ?></span>
          <!-- toggle: botón accesible -->
          <button type="button" class="toggle" aria-expanded="false" aria-controls="detalle-mision-<?= (int)$mision['id_mision'] ?>">⬇</button>
        </div>

        <!-- detalle (oculto por defecto) -->
        <div class="detalle" id="detalle-mision-<?= (int)$mision['id_mision'] ?>">
          <p><strong data-i18n="admin.missions.location">Ubicación:</strong> <?= htmlspecialchars($mision['ubicacion']) ?></p>
          <p><strong data-i18n="admin.missions.score">Puntuación:</strong> <?= htmlspecialchars($mision['puntuacion']) ?> <span data-i18n="points.suffix">pts</span></p>

          <?php if (!empty($mision['imagen_mision'])): ?>
            <p><strong data-i18n="admin.missions.image">Imagen:</strong></p>
            <img src="../assets/img/misiones/<?= htmlspecialchars($mision['imagen_mision']) ?>"
              alt="Imagen de la misión"
              style="max-width:200px; border-radius:6px; margin-bottom:10px;">
          <?php endif; ?>

          <p><strong data-i18n="admin.missions.description">Descripción:</strong> <?= nl2br(htmlspecialchars($mision['descripcion_misiones'])) ?></p>

          <div class="actions">
            <a class="small editar" href="../dashboard/misiones/editar.php?id=<?= (int)($mision['id_mision'] ?? 0) ?>" data-i18n="admin.missions.edit">Editar</a>
            <a class="small borrar" href="../dashboard/misiones/borrar.php?id=<?= (int)($mision['id_mision'] ?? 0) ?>" data-i18n="admin.missions.delete">Borrar</a>
          </div>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
  <p class="no-misiones" data-i18n="admin.missions.none">No hay misiones disponibles.</p>
    <?php endif; ?>
    <div class="actions" style="margin-top:10px;">
  <a class="small crear" href="../dashboard/misiones/crear.php" data-i18n="admin.missions.create">Crear misión</a>
    </div>
  </div>

  <!-- Bloque de usuarios -->
  <div class="block" id="usuarios-block">
  <h3 data-i18n="admin.users.title">Usuarios</h3>

    <?php if (count($usuarios) > 0): ?>
      <?php foreach ($usuarios as $usuario): ?>
        <!-- fila visible -->
        <div class="item usuario" data-id="<?= (int)$usuario['id_usuario'] ?>">
          <span><?= htmlspecialchars($usuario['usuario']) ?></span>
          <!-- toggle: botón accesible -->
          <button type="button" class="toggle" aria-expanded="false" aria-controls="detalle-usuario-<?= (int)$usuario['id_usuario'] ?>">⬇</button>
        </div>

        <!-- detalle (oculto por defecto) -->
        <div class="detalle" id="detalle-usuario-<?= (int)$usuario['id_usuario'] ?>">
          <?php if (!empty($usuario['imagen_user'])): ?>
            <p><strong data-i18n="admin.users.image">Imagen:</strong></p>
            <img src="../assets/img/usuarios/<?= htmlspecialchars($usuario['imagen_user']) ?>" alt="Imagen de <?= htmlspecialchars($usuario['usuario']) ?>" style="max-width:150px; border-radius:6px; margin-bottom:10px;">
          <?php endif; ?>
          <p><strong data-i18n="admin.users.username">Usuario:</strong> <?= htmlspecialchars($usuario['usuario']) ?></p>
          <p><strong data-i18n="admin.users.ranking">Puntos Ranking:</strong> <?= (int)$usuario['puntosRanking'] ?></p>
          <p><strong data-i18n="admin.users.points">Puntos para Cambio:</strong> <?= (int)$usuario['puntosCambio'] ?></p>

          <div class="actions">
            <a class="small editar" href="../dashboard/usuarios/editar.php?id=<?= (int)$usuario['id_usuario'] ?>" data-i18n="admin.users.edit">Editar</a>
            <a class="small borrar" href="../dashboard/usuarios/borrar.php?id=<?= (int)$usuario['id_usuario'] ?>" data-i18n="admin.users.delete">Borrar</a>
          </div>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
  <p class="no-solicitudes" data-i18n="admin.users.none">No hay usuarios registrados.</p>
    <?php endif; ?>

    <div class="actions" style="margin-top:10px;">
  <a class="small crear" href="../dashboard/usuarios/crear.php" data-i18n="admin.users.create">Crear usuario</a>
    </div>
  </div>

  <!-- Bloque de recompensas -->
  <div class="block" id="recompensas-block">
  <h3 data-i18n="admin.rewards.title">Recompensas</h3>

    <?php if (count($recompensas) > 0): ?>
      <?php foreach ($recompensas as $recompensa): ?>
        <!-- fila visible -->
        <div class="item recompensa" data-id="<?= (int)$recompensa['id_recompensa'] ?? '' ?>">
          <span><?= htmlspecialchars($recompensa['titulo_recompensa']) ?></span>
          <!-- toggle: botón accesible -->
          <button type="button" class="toggle" aria-expanded="false" aria-controls="detalle-recompensa-<?= (int)($recompensa['id_recompensa'] ?? 0) ?>">⬇</button>
        </div>

        <!-- detalle (oculto por defecto) -->
        <div class="detalle" id="detalle-recompensa-<?= (int)($recompensa['id_recompensa'] ?? 0) ?>">
          <?php if (!empty($recompensa['imagen_recom'])): ?>
            <p><strong data-i18n="admin.rewards.image">Imagen:</strong></p>
            <img src="../assets/img/recompensas/<?= htmlspecialchars($recompensa['imagen_recom']) ?>" alt="Imagen de <?= htmlspecialchars($recompensa['titulo_recompensa']) ?>" style="max-width:150px; border-radius:6px; margin-bottom:10px;">
          <?php endif; ?>
          <p><strong data-i18n="admin.rewards.description">Descripción:</strong> <?= nl2br(htmlspecialchars($recompensa['descripcion_recompensa'])) ?></p>
          <p><strong data-i18n="admin.rewards.price">Precio:</strong> <?= (int)$recompensa['precio'] ?> <span data-i18n="points.suffix">pts</span></p>

          <div class="actions">
            <a class="small editar" href="../dashboard/recompensas/editar.php?id=<?= (int)($recompensa['id_recompensa'] ?? 0) ?>" data-i18n="admin.rewards.edit">Editar</a>
            <a class="small borrar" href="../dashboard/recompensas/borrar.php?id=<?= (int)($recompensa['id_recompensa'] ?? 0) ?>" data-i18n="admin.rewards.delete">Borrar</a>
          </div>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
  <p class="no-solicitudes" data-i18n="admin.rewards.none">No hay recompensas disponibles.</p>
    <?php endif; ?>

    <div class="actions" style="margin-top:10px;">
  <a class="small crear" href="../dashboard/recompensas/crear.php" data-i18n="admin.rewards.create">Crear recompensa</a>
    </div>
  </div>

</section>

<script src="../assets/js/admin.js"></script>

<?php
// Incluir footer
require_once __DIR__ . '/../includes/footer.php';
?>
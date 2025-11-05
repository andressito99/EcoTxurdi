<?php 
// Importar configuración general (DB, sesión, constantes)
$rolNecesario = 'mod'; 
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../assets/auth.php';

// Marcar sección activa y botón superior
$active = '';

$rightButtonHref = '#';

// Incluir encabezado de la página
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
?> 
<section class="flex-panels">

  <!-- Bloque de Búsqueda-->  
  <div class="block search-block" id="busqueda-block">
  <h3 class="search-title" data-i18n="mod.search.title">Búsqueda rápida</h3>
    <form method="GET" action="buscarMod.php" id="search-form" class="search-form" role="search" aria-label="Buscar elementos">
      <input 
        type="text" 
        name="query" 
        id="search-input" 
        class="search-input" 
  placeholder="Escribe aquí para buscar..." data-i18n-placeholder="mod.search.placeholder"
        value="<?= isset($_GET['query']) ? htmlspecialchars($_GET['query']) : '' ?>"
        aria-label="Campo de búsqueda"
      >
  <button type="submit" class="search-btn" aria-label="Buscar" data-i18n="mod.search.button">Buscar</button>
    </form>
  </div>

  <div class="block" id="solicitudes-block">
  <h3 data-i18n="mod.requests.title">Solicitudes</h3>

    <?php if (count($solicitudes) > 0): ?>
      <?php foreach ($solicitudes as $sol): ?>
        <!-- fila visible -->
        <div class="item solicitud" data-id="<?= (int)$sol['id_mision'] ?>">
          <span><?= htmlspecialchars($sol['titulo_misiones']) ?></span>
          <!-- toggle: botón accesible -->
          <button type="button" class="toggle" aria-expanded="false" aria-controls="detalle-<?= (int)$sol['id_mision'] ?>">⬇</button>
        </div>

        <!-- detalle (oculto por defecto) -->
        <div class="detalle" id="detalle-<?= (int)$sol['id_mision'] ?>">
          <p><strong data-i18n="mod.requests.location">Ubicación:</strong> <?= htmlspecialchars($sol['ubicacion']) ?></p>
          <p><strong data-i18n="mod.requests.score">Puntuación:</strong> <?= htmlspecialchars($sol['puntuacion']) ?> <span data-i18n="points.suffix">pts</span></p>
          
          <?php if (!empty($sol['imagen_mision'])): ?>
          <p><strong data-i18n="mod.requests.image">Imagen:</strong></p>
            <img src="../assets/img/misiones/<?= htmlspecialchars($sol['imagen_mision']) ?>"
            alt="Imagen de la misión" 
            style="max-width:200px; border-radius:6px; margin-bottom:10px;">
          <?php endif; ?>
          
          <p><strong data-i18n="mod.requests.description">Descripción:</strong> <?= nl2br(htmlspecialchars($sol['descripcion_misiones'])) ?></p>

          <div class="actions">
            <a class="small aceptar" href="../dashboard/aprobar.php?id=<?= (int)$sol['id_mision'] ?>" data-i18n="mod.requests.accept">Aceptar</a>
            <a class="small denegar" href="../dashboard/denegar.php?id=<?= (int)$sol['id_mision'] ?>" data-i18n="mod.requests.deny">Denegar</a>
          </div>

        </div>
      <?php endforeach; ?>
    <?php else: ?>
  <p class="no-solicitudes" data-i18n="mod.requests.none">No hay solicitudes pendientes.</p>
    <?php endif; ?>

  </div>
      <!-- Bloque de misiones -->
  <div class="block" id="misiones-block">
  <h3 data-i18n="mod.missions.title">Misiones</h3>

    <?php if (count($misiones) > 0): ?>
      <?php foreach ($misiones as $mision): ?>
        <!-- fila visible -->
        <div class="item mision" data-id="<?= (int)$mision['id_mision'] ?>">
          <span><?= htmlspecialchars($mision['titulo_misiones']) ?></span>
          <!-- toggle: botón accesible -->
          <button type="button" class="toggle" aria-expanded="false" aria-controls="detalle-<?= (int)$mision['id_mision'] ?>">⬇</button>
        </div>

        <!-- detalle (oculto por defecto) -->
        <div class="detalle" id="detalle-<?= (int)$mision['id_mision'] ?>">
          <p><strong data-i18n="mod.missions.location">Ubicación:</strong> <?= htmlspecialchars($mision['ubicacion']) ?></p>
          <p><strong data-i18n="mod.missions.score">Puntuación:</strong> <?= htmlspecialchars($mision['puntuacion']) ?> <span data-i18n="points.suffix">pts</span></p>

          <?php if (!empty($mision['imagen_mision'])): ?>
            <p><strong data-i18n="mod.missions.image">Imagen:</strong></p>
            <img src="../assets/img/misiones/<?= htmlspecialchars($mision['imagen_mision']) ?>"
              alt="Imagen de la misión"
              style="max-width:200px; border-radius:6px; margin-bottom:10px;">
          <?php endif; ?>

          <p><strong data-i18n="mod.missions.description">Descripción:</strong> <?= nl2br(htmlspecialchars($mision['descripcion_misiones'])) ?></p>

          <div class="actions">
            <a class="small editar" href="../dashboard/misiones/editar.php?id=<?= (int)($mision['id_mision'] ?? 0) ?>" data-i18n="mod.missions.edit">Editar</a>
            <a class="small borrar" href="../dashboard/misiones/borrar.php?id=<?= (int)($mision['id_mision'] ?? 0) ?>" data-i18n="mod.missions.delete">Borrar</a>
          </div>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
  <p class="no-misiones" data-i18n="mod.missions.none">No hay misiones disponibles.</p>
    <?php endif; ?>
    <div class="actions" style="margin-top:10px;">
  <a class="small crear" href="../dashboard/misiones/crear.php" data-i18n="mod.missions.create">Crear misión</a>
    </div>
  </div>

</section>

<!-- Script para mostrar/ocultar detalles de las solicitudes y Confirmación de acciones -->
<script src="../assets/js/mod.js"></script>


<?php 
// Incluir pie de página
require_once __DIR__ . '/../includes/footer.php'; 
?>
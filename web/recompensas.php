<?php
// Incluye archivo de configuración para conexión a la base de datos y constantes
require_once __DIR__ . '/../config.php';

// Variable para resaltar sección activa en el menú
$active = 'recompensas';

// Incluye la cabecera del sitio
require_once BASE_PATH . '/includes/header.php';

// Verifica si hay usuario autenticado
$id_usuario = $_SESSION['id_usuario'] ?? null;
$puntosUsuario = null;
$yaReclamadas = [];

// Si el usuario está autenticado, obtiene sus puntos y recompensas ya reclamadas
if ($id_usuario) {
  // Obtener puntos del usuario
  $stmt = $pdo->prepare("SELECT puntosCambio FROM usuarios WHERE id_usuario = ?");
  $stmt->execute([$id_usuario]);
  $puntosUsuario = $stmt->fetchColumn();

  // Obtener ID de recompensas ya reclamadas por el usuario
  $stmt = $pdo->prepare("SELECT id_recompensa FROM reclama WHERE id_usuario = ?");
  $stmt->execute([$id_usuario]);
  $yaReclamadas = $stmt->fetchAll(PDO::FETCH_COLUMN);
}

// Consultar todas las recompensas disponibles
$stmt = $pdo->query("SELECT id_recompensa, imagen_recom, titulo_recompensa, descripcion_recompensa, precio FROM recompensas ORDER BY id_recompensa DESC");
$recompensas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php if (!empty($_GET['status'])): ?>
  <?php
    // Muestra mensajes según el estado de la operación
    $mensaje = '';
    $msgKey = '';
    switch ($_GET['status']) {
      case 'ok': $mensaje = 'Recompensa reclamada correctamente.'; $msgKey = 'rewards.status.ok'; break;
      case 'insuficientes': $mensaje = 'No tienes puntos suficientes.'; $msgKey = 'rewards.status.insuf'; break;
      case 'noauth': $mensaje = 'Debes iniciar sesión para reclamar.'; $msgKey = 'rewards.status.noauth'; break;
      case 'ya': $mensaje = 'Esta recompensa ya fue reclamada.'; $msgKey = 'rewards.status.already'; break;
      case 'sin_codigos': $mensaje = 'No quedan códigos disponibles para esta recompensa.'; $msgKey = 'rewards.status.nocodes'; break;
      default: $mensaje = 'Ha ocurrido un error al reclamar.'; $msgKey = 'rewards.status.error'; break;
    }

    // Obtener código entregado temporalmente guardado en sesión
    $codigoEntregado = null;
    if (isset($_SESSION['flash_recompensa']['codigo'])) {
      $codigoEntregado = $_SESSION['flash_recompensa']['codigo'];
      unset($_SESSION['flash_recompensa']); // Se borra después de mostrar
    }
  ?>
  <!-- Panel de notificaciones -->
  <div class="section">
    <div class="panel" style="background:var(--panel-2);">
      <div style="margin-top:8px; opacity:.9; font-size:var(--fuenteTamanoLg); font-weight: bold;" data-i18n="<?= htmlspecialchars($msgKey, ENT_QUOTES, 'UTF-8') ?>">
        <?= htmlspecialchars($mensaje) ?>
      </div>
      <?php if ($puntosUsuario !== null): ?>
        <div style="margin-top:8px; opacity:.9; font-size:var(--fuenteTamanoLg)">
          <span data-i18n="rewards.updated">Tus puntos han sido actualizados a:</span> <?= (int)$puntosUsuario ?>
        </div>
      <?php endif; ?>
      <?php if (!empty($codigoEntregado)): ?>
        <div style="margin-top:12px; font-weight:800; font-size:var(--fuenteTamanoLg)">
          <span data-i18n="rewards.code">Tu código para canjear la recompensa es:</span>
          <span style="background:var(--colorBase); color:var(--darkBlue); padding:4px 8px; border-radius:8px;">
            <?= htmlspecialchars($codigoEntregado) ?>
          </span>
        </div>
      <?php endif; ?>
    </div>
  </div>
<?php endif; ?>

<!-- Listado de recompensas -->
<section class="tiles">
  <?php if (!empty($recompensas)): ?>
    <?php foreach ($recompensas as $idx => $rec): ?>
      <article class="tile" style="position:relative;">
        <!-- Imagen de recompensa -->
        <div class="media">
          <?php
            // Usa imagen de recompensa o una imagen por defecto si no hay
            $img = !empty($rec['imagen_recom'])
              ? (BASE_URL . '/assets/img/recompensas/' . htmlspecialchars($rec['imagen_recom']))
              : (BASE_URL . '/assets/img/fondo.png');
          ?>
          <img src="<?= $img ?>"
               alt="<?= htmlspecialchars($rec['titulo_recompensa']) ?>"
               style="width:100%;height:220px;object-fit:cover;border-radius:var(--radius-xl);">
        </div>

        <!-- Título -->
        <div class="card-title" style="text-align:center;" data-i18n="rewards.<?= (int)$rec['id_recompensa'] ?>.title">
          <?= htmlspecialchars($rec['titulo_recompensa']) ?>
        </div>

        <!-- Descripción -->
        <div class="card-date" style="text-align:center; font-size: var(--fuenteTamanoXl);" data-i18n="rewards.<?= (int)$rec['id_recompensa'] ?>.desc">
          <?= htmlspecialchars($rec['descripcion_recompensa']) ?>
        </div>

        <!-- Precio -->
        <div class="card-date" style="text-align:center; font-weight:800; margin-top:10px;">
          <span data-i18n="rewards.price">Precio:</span> <?= (int)$rec['precio'] ?> <span data-i18n="points.suffix">pts</span>
        </div>

        <!-- Botón o etiqueta según disponibilidad -->
        <div class="card-actions" style="display:flex; justify-content:center;">
          <?php
            $idRec = (int)$rec['id_recompensa'];
            $precio = (int)$rec['precio'];
            // Condición para permitir reclamar
            $puede = $id_usuario && $puntosUsuario !== null && $puntosUsuario >= $precio && !in_array($idRec, $yaReclamadas);
          ?>

          <?php if ($puede): ?>
            <!-- Formulario para reclamar recompensa -->
            <form method="post" action="<?= BASE_URL ?>/web/reclamarRecompensa.php" style="margin:0;">
              <input type="hidden" name="id_recompensa" value="<?= $idRec ?>">
              <button type="submit" class="btn-pill" data-i18n="rewards.claim">Reclamar</button>
            </form>
          <?php else: ?>
            <?php
            // Mensaje de restricción según el caso
            if (!$id_usuario) { $label = 'Inicia sesión para reclamar'; $labelKey = 'rewards.status.noauth';
            } elseif (in_array($idRec, $yaReclamadas)) { $label = 'Ya reclamada'; $labelKey = 'rewards.status.already';
            } elseif ($puntosUsuario !== null && $puntosUsuario < $precio) { $label = 'Puntos insuficientes'; $labelKey = 'rewards.status.insuf';
            } else { $label = 'No disponible'; $labelKey = ''; }
            ?>
            <span class="btn-pill" style="opacity:.6; cursor:not-allowed;" <?= !empty($labelKey) ? ('data-i18n="'.$labelKey.'"') : '' ?>>
              <?= $label ?>
            </span>
          <?php endif; ?>
        </div>
      </article>
    <?php endforeach; ?>
  <?php else: ?>
    <!-- Si no hay recompensas -->
    <p style="text-align:center;width:100%;" data-i18n="rewards.none">No hay recompensas disponibles por el momento.</p>
  <?php endif; ?>
</section>

<?php
// Incluye el pie de página
require_once BASE_PATH . '/includes/footer.php';
?>
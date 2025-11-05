<?php
  // Incluye el archivo de configuración principal
  require_once __DIR__ . '/../config.php';

  // Variable que indica la sección activa del menú
  $active = 'clasificacion';

  // Incluye la cabecera de la página (HTML inicial, menú de navegación, etc.)
  require_once BASE_PATH . '/includes/header.php';

  // Obtener ID del usuario actual
  $uid = $_SESSION['id_usuario'] ?? null;

  // Obtener ranking desde la base de datos
  try {
    // Consulta que obtiene los 6 primeros usuarios ordenados por puntos y nombre
    $stmt = $pdo->query('SELECT usuario, puntosRanking, ultimo_login FROM usuarios ORDER BY puntosRanking DESC, usuario ASC LIMIT 6');
    // Obtiene todos los resultados en un arreglo asociativo
    $ranking = $stmt->fetchAll(PDO::FETCH_ASSOC);
  } catch (Throwable $e) {
    // En caso de error, se inicializa el ranking como un arreglo vacío
    $ranking = [];
  }
  
  // Función auxiliar para mostrar información del estado "Online" de cada usuario
  function renderOnlineCell(?string $ultimo) {
    // Si no hay fecha de último inicio de sesión, muestra un guion
    if (empty($ultimo)) {
      return '—<br><span class="note">&nbsp;</span>';
    }
    try {
      // Convierte la fecha proporcionada a un objeto DateTime
      $dt = new DateTime($ultimo);
      $now = new DateTime('now');
      // Verifica si la última conexión fue el mismo día
      $isToday = $dt->format('Y-m-d') === $now->format('Y-m-d');

      if ($isToday) {
        // Calcula la diferencia en segundos
        $diff = $now->getTimestamp() - $dt->getTimestamp();
        // Determina el formato de salida según el tiempo transcurrido
        if ($diff < 60) {
          $ago = 'Ahora';
        } elseif ($diff < 3600) {
          $m = floor($diff/60);
          $ago = $m.'m ago';
        } elseif ($diff < 4*3600) { // Hasta 4 horas muestra en formato de horas
          $h = floor($diff/3600);
          $ago = $h.'h ago';
        } else {
          // Si pasó más tiempo el mismo día, muestra la hora
          $ago = $dt->format('h:i A');
        }
        // Devuelve el formato visual similar al diseño mostrado
        return 'Hoy<br><span class="note">'.$ago.'</span>';
      }
      // Si fue otro día, muestra fecha completa
      return $dt->format('d/m/Y').'<br><span class="note">'.$dt->format('h:i A').'</span>';
    } catch (Throwable $e) {
      // En caso de error devuelve un valor vacío consistente
      return '—<br><span class="note">&nbsp;</span>';
    }
  }
?>
  <!-- Contenedor principal del panel de clasificación -->
  <div class="panel" style="width:min(900px,96%); margin: 20px auto;">
    <table class="table" cellpadding="0" cellspacing="0">
      <thead>
        <tr>
          <th data-i18n="table.rank">Top</th>
          <th data-i18n="table.name">Name</th>
          <th data-i18n="table.online">Online</th>
          <th data-i18n="table.points">Puntos</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($ranking)): ?>
          <?php $pos = 1; foreach ($ranking as $row): ?>
            <!-- Fila de cada usuario en el ranking -->
            <tr>
              <!-- Muestra la posición del usuario -->
              <td data-label="Top"><?= $pos ?></td>

              <!-- Muestra el nombre del usuario escapado para evitar inyecciones -->
              <td data-label="Name"><?= htmlspecialchars($row['usuario']) ?></td>

              <!-- Muestra el estado online utilizando la función renderOnlineCell -->
              <td data-label="Online"><?= renderOnlineCell($row['ultimo_login'] ?? null) ?></td>

              <!-- Muestra los puntos del usuario -->
              <td data-label="Puntos"><?= (int)$row['puntosRanking'] ?><br><span class="note" data-i18n="table.points">puntos</span></td>
            </tr>
          <?php $pos++; endforeach; ?>
  <?php else: ?>
          <!-- Mensaje si no hay usuarios suficientes en el ranking -->
          <tr>
            <td colspan="4" style="text-align:center;" data-i18n="ranking.none">No hay usuarios para mostrar</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>

    <!-- Botón para mostrar más usuarios (funcionalidad futura) -->
    <div style="text-align:center;margin:10px 0 0">
      <?php  
        try {
          // Consulta que obtiene la cantidad de usuarios con mas puntos de ranking que el actual
          $stmt = $pdo->prepare('SELECT COUNT(*)+1 FROM `usuarios` WHERE `puntosRanking` > (SELECT puntosRanking FROM `usuarios` WHERE `id_usuario` = ?); ');
          $stmt->execute([$uid]);
          $posicion = $stmt->fetchColumn();
          if (empty($uid)) {
            $posicion = "Inicia sesión para ver tu posición";
          }
        } catch (Throwable $e) {
          // En caso de error, se inicializa el ranking como un arreglo vacío
          $posicion = "Error con la base de datos";
        }
        ?>
  <h2 data-i18n="position.desc"></h2>
        <h2><?php echo empty($uid) ? '<span data-i18n="ranking.ps">Inicia sesion para ver tu posicion</span>' : $posicion; ?></h2>
    </div>
  </div>

<?php
  // Incluye el pie de página
  require_once BASE_PATH . '/includes/footer.php';
?>

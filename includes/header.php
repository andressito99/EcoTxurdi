<?php
  if (!isset($active)) { $active = ''; }

  if (!isset($rightButtonLabel)) {
    $rightButtonLabel = isset($_SESSION['usuario'], $_SESSION['rol'])
      ? $_SESSION['usuario'].' / '.$_SESSION['rol'].' ▼ '
      : 'Login';
  }

  $menuItems = []; // enlaces del desplegable
 
  switch ($_SESSION['rol'] ?? "default") {
    case "mod":
      $menuItems[] = ['href' => BASE_URL . '/dashboard/perfil.php', 'label' => 'Perfil'];
      $rightButtonHref = BASE_URL . '/dashboard/mod.php'; 
      $menuItems[] = ['href' => $rightButtonHref, 'label' => 'Panel de gestión'];
      break;

    case "admin": 
      $menuItems[] = ['href' => BASE_URL . '/dashboard/perfil.php', 'label' => 'Perfil'];
      $rightButtonHref = BASE_URL . '/dashboard/admin.php';
      $menuItems[] = ['href' => $rightButtonHref, 'label' => 'Panel de control'];
      break;

    case "user":
      $menuItems[] = ['href' => BASE_URL . '/dashboard/perfil.php', 'label' => 'Perfil'];
      $menuItems[] = ['href' => BASE_URL . '/dashboard/solicitar.php', 'label' => 'Solicitar'];
      $rightButtonHref = BASE_URL . '/dashboard/perfil.php';
      break;

    default:
      $rightButtonHref = BASE_URL . '/login/login.php';
  }
?>
<!DOCTYPE html>
<html lang="<?= $_SESSION['lang'] ?? 'eu' ?>">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="EcoTxurdi - Plataforma para mejorar el medio ambiente a través de misiones ecológicas">
  <title>Ecotxurdi</title>
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/style.css">
  <script src="<?= BASE_URL ?>/assets/js/actualizarPuntos.js"></script>
  <script src="<?= BASE_URL ?>/assets/js/lang.js" defer></script>
  <script src="<?= BASE_URL ?>/assets/js/desplegable.js" defer></script>
  <link rel="icon" href="<?= BASE_URL ?>/assets/img/favicon.png?v=2">
  <script>window.BASE_URL = '<?= BASE_URL ?>';</script>
</head>

<body>
  <header class="topbar" role="banner">
    <div class="brand">
      <div class="logo">
        <a href="<?= BASE_URL ?>/index.php">
          <img src="<?= BASE_URL ?>/assets/img/logo.png" alt="EcoMisiones Logo">
        </a>
      </div>
    </div>
  <button id="menuToggle" class="menu-toggle" aria-controls="mainNav" aria-expanded="false" aria-label="Abrir menú">☰</button>
  <nav id="mainNav" class="links" role="navigation" aria-label="Main navigation">
      <a href="<?= BASE_URL ?>/index.php" class="<?php echo $active==='inicio'?'active':''; ?>" data-i18n="nav.home" <?php echo $active==='inicio'?'aria-current="page"':''; ?>>Inicio</a>
      <a href="<?= BASE_URL ?>/web/misiones.php" class="<?php echo $active==='misiones'?'active':''; ?>" data-i18n="nav.missions" <?php echo $active==='misiones'?'aria-current="page"':''; ?>>Misiones</a>
      <a href="<?= BASE_URL ?>/web/clasificacion.php" class="<?php echo $active==='clasificacion'?'active':''; ?>" data-i18n="nav.ranking" <?php echo $active==='clasificacion'?'aria-current="page"':''; ?>>Clasificación</a>
      <a href="<?= BASE_URL ?>/web/noticias.php" class="<?php echo $active==='noticias'?'active':''; ?>" data-i18n="nav.news" <?php echo $active==='noticias'?'aria-current="page"':''; ?>>Noticias</a>
      <a href="<?= BASE_URL ?>/web/recompensas.php" class="<?php echo $active==='recompensas'?'active':''; ?>" data-i18n="nav.recom">Recompensas</a>
    </nav>


    <?php if(!empty($_SESSION['rol'])) { echo <<<HTML
    <nav class="puntosUsuario">
      <p>✦­:0</p>
    </nav>
    HTML;} ?>

    <select id="langSwitcher" aria-label="Seleccionar idioma" role="combobox">
      <option value="eu" lang="eu">Euskera</option>
      <option value="es" lang="es" selected>Español</option>
      <option value="en" lang="en">English</option>
    </select>

    <?php
      $BASE_URL = BASE_URL;

      if (empty($_SESSION['rol'])) {
        // Usuario no logueado
        echo <<<HTML
          <div class="right-btn">
            <a href="$rightButtonHref">$rightButtonLabel</a>
          </div>
        HTML;
      } else {
        // Usuario logueado (user, mod, admin)
        echo '<div class="right-btn">
                <button onclick="desplegar()" class="dropbtn">'.$rightButtonLabel.'</button>
                <div id="myDropdown" class="dropdown-content">';
        
        // Mostrar cada opción del menú según el rol
        foreach ($menuItems as $item) {
          $href = $item['href'];
          $label = htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8');
          echo "<a href=\"$href\">$label</a>";
        }

        // Cerrar sesión
        echo "<a href=\"$BASE_URL/login/logout.php\">Cerrar sesión</a>";
        echo '    </div>
              </div>';
      }
    ?>

    <script>
      window.addEventListener('DOMContentLoaded', actualizarPuntos);
    </script>
    <script>
      // Toggle mobile navigation (simple accessible toggle)
      (function(){
        var btn = document.getElementById('menuToggle');
        var nav = document.getElementById('mainNav');
        if (!btn || !nav) return;
        btn.addEventListener('click', function(){
          var expanded = this.getAttribute('aria-expanded') === 'true';
          this.setAttribute('aria-expanded', String(!expanded));
          nav.classList.toggle('open');
        });
        // Close menu when focus moves outside or on resize to large screens
        document.addEventListener('click', function(e){
          if (!nav.classList.contains('open')) return;
          if (btn.contains(e.target) || nav.contains(e.target)) return;
          nav.classList.remove('open');
          btn.setAttribute('aria-expanded','false');
        });
        window.addEventListener('resize', function(){
          if (window.innerWidth > 900 && nav.classList.contains('open')){
            nav.classList.remove('open'); btn.setAttribute('aria-expanded','false');
          }
        });
      })();
    </script>
  </header>
</body>
</html>

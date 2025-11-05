<?php
// Importar configuración general (DB, sesiones, constantes)
require_once __DIR__ . '/../../config.php';
$rolNecesario = 'admin'; 
require_once __DIR__ . '/../../assets/auth.php';
require_once __DIR__ . '/../../assets/funtion/translator.php';

// Inicializar el traductor
try {
    Translator::init('AIzaSyBVJfswOIxHFoBOXGu77nDocJ_dqYPwCwk');
} catch (TranslationException $e) {
    error_log("Error al inicializar el traductor: " . $e->getMessage());
}

// Variables para estado de navegación
$active = '';
$rightButtonHref = '#';

// Incluir cabecera del panel
require_once __DIR__ . '/../../includes/header.php';

  // Captura los datos enviados por POST
$recompensa = $_POST['recompensa'] ?? null;
$descripcion = $_POST['descripcion'] ?? null;
$precio = $_POST['precio'] ?? null;

// Obtiene el id del usuario logueado
$id_usuario = $_SESSION['id_usuario'] ?? null;

// Procesamiento de la imagen
$imagenNombre = null;
if (!empty($_FILES['imagen']['name'])) {
    // Carpeta destino donde se guardarán las imágenes
    $carpetaDestino = __DIR__ . '/../../assets/img/recompensas/';
    if (!is_dir($carpetaDestino)) { 
        mkdir($carpetaDestino, 0777, true); // Crear carpeta si no existe
    }

    // Obtener extensión del archivo
    $extension = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);

    // Generar nombre único para la imagen
    $imagenNombre = uniqid('mision_') . "." . $extension;

    // Mover archivo temporal a carpeta de destino
    move_uploaded_file($_FILES['imagen']['tmp_name'], $carpetaDestino . $imagenNombre);
}

// Validar que todos los campos estén completos
if (!empty($recompensa) && !empty($descripcion) && !empty($precio)) {
    // Validar que la puntuación sea numérica y esté en rango 0-100
    if (!is_numeric($precio) || $precio < 0 || $precio > 300) {
      $_SESSION['error'] = "La puntuación debe estar entre 0 y 300.";
      exit;
    } else {
      try {
        // Preparar inserción en la base de datos
        $stmt = $pdo->prepare("
        INSERT INTO recompensas 
        (imagen_recom, titulo_recompensa, descripcion_recompensa, precio) 
        VALUES (?, ?, ?, ?)  
        ");

        // Ejecutar la inserción
        $stmt->execute([$imagenNombre, $recompensa, $descripcion, $precio]);
        
        // Obtener el ID de la recompensa recién creada
        $recompensaId = $pdo->lastInsertId();
        
        // Guardar las traducciones en los archivos JSON
        try {
            Translator::translateAndSaveToJson('reward_title', $recompensa, $recompensaId);
            Translator::translateAndSaveToJson('reward_description', $descripcion, $recompensaId);
        } catch (TranslationException $e) {
            error_log("Error al traducir la recompensa: " . $e->getMessage());
            $_SESSION['warning'] = "La recompensa se creó correctamente, pero hubo un error con las traducciones.";
        }

        // Redirigir al inicio después de enviar la solicitud
        header("Location: " . BASE_URL . "/dashboard/admin.php");
        exit;
      } catch (PDOException $e) {
        // Mostrar error si falla la inserción
        print "Error al insertar los datos: " . $e->getMessage();
      }
    }
}
?>
<section class="section" style="max-width:760px">
  <div class="panel">
    <h2>Crear una recompensa</h2>
    <!-- Formulario de solicitud de misión -->
    <form action="" method="POST" enctype="multipart/form-data">
      <label for="recompensa" class="label">Recompensa:</label>
      <input class="input" id="recompensa" placeholder="Escribe el titulo" required name="recompensa">

      <label for="descripcion" class="label">Descripcion de la recompensa:</label>
      <input class="input" placeholder="Escribe una descripción" required name="descripcion">

      <div class="label">Imagen de la recompensa:</div>
      <input class="input" type="file" required name="imagen">

      <div class="label">Precio:</div>
      <input class="input" placeholder="0 - 300" required name="precio" pattern="^([0-9]|[1-9][0-9]|[12][0-9]{2}|300)$" title="El valor debe estar entre 0 y 300" required>

      <?php 
      // Mostrar mensaje de error si existe
      if (!empty($_POST['submit']))
        print '<p class="error">' . $_SESSION['error'] . '</p>';
      ?>

      <div style="margin-top:16px">
        <input class="btn-pill" type="submit" name="submit" value="Crear">
      </div>
    </form>
  </div>
</section>

<?php 
// Incluye el pie de página
require_once __DIR__ . '/../../includes/footer.php'; 
?>
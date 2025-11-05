<?php
// Importar configuración general (DB, sesiones, constantes)
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../assets/funtion/translator.php';
$rolNecesario = 'mod'; 
require_once __DIR__ . '/../../assets/auth.php';

// Inicializar el traductor con tu API key
try {
    Translator::init('AIzaSyBVJfswOIxHFoBOXGu77nDocJ_dqYPwCwk'); // Reemplaza con tu API key real
} catch (TranslationException $e) {
    error_log("Error al inicializar el traductor: " . $e->getMessage());
}

// Variables para estado de navegación
$active = '';
$rightButtonHref = '#';

// Incluir cabecera del panel
require_once __DIR__ . '/../../includes/header.php';

  // Captura los datos enviados por POST
$ubicacion = $_POST['ubicacion'] ?? null;
$titulo = $_POST['titulo'] ?? null;
$puntuacion = $_POST['puntuacion'] ?? null;
$descripcion = $_POST['descripcion'] ?? null;

// Obtiene el id del usuario logueado
$id_usuario = $_SESSION['id_usuario'] ?? null;

// Procesamiento de la imagen
$imagenNombre = null;
if (!empty($_FILES['imagen']['name'])) {
    // Carpeta destino donde se guardarán las imágenes
    $carpetaDestino = __DIR__ . '/../../assets/img/misiones/';
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
if (!empty($ubicacion) && !empty($titulo) && !empty($puntuacion) && !empty($descripcion)) {
    // Validar que la puntuación sea numérica y esté en rango 0-100
    if (!is_numeric($puntuacion) || $puntuacion < 0 || $puntuacion > 100) {
      $_SESSION['error'] = "La puntuación debe estar entre 0 y 100.";
      exit;
    } else {
      try {
        // Preparar inserción en la base de datos
        $stmt = $pdo->prepare("
        INSERT INTO misiones 
        (titulo_misiones, imagen_mision, descripcion_misiones, puntuacion, ubicacion, id_usuario, tipo) 
        VALUES (?, ?, ?, ?, ?, ?, 'mision')  
        ");

        // Ejecutar la inserción
        $stmt->execute([$titulo, $imagenNombre, $descripcion, $puntuacion, $ubicacion, $id_usuario]);
        
        // Obtener el ID de la misión recién creada
        $misionId = $pdo->lastInsertId();
        
        // Guardar las traducciones en los archivos JSON
        try {
            Translator::translateAndSaveToJson('title', $titulo, $misionId);
            Translator::translateAndSaveToJson('description', $descripcion, $misionId);
        } catch (TranslationException $e) {
            // Registrar el error pero continuar con la creación de la misión
            error_log("Error al traducir la misión: " . $e->getMessage());
            $_SESSION['warning'] = "La misión se creó correctamente, pero hubo un error con las traducciones.";
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
    <h2>Crear una misión</h2>
    <!-- Formulario de solicitud de misión -->
    <form action="" method="POST" enctype="multipart/form-data">
      <label for="ubicacion" class="label">Ubicación de la misión:</label>
      <input class="input" id="ubicacion" placeholder="Escribe la ubicación" required name="ubicacion">

      <label for="titulo" class="label">Título de la misión:</label>
      <input class="input" placeholder="Escribe el título" required name="titulo">

      <div class="label">Imagen de la misión:</div>
      <input class="input" type="file" required name="imagen">

      <div class="label">Puntuación de la misión:</div>
      <input class="input" placeholder="0 - 100" required name="puntuacion" pattern="(100|[0-9]?[0-9])" title="El valor debe estar entre 0 y 100" required>

      <div class="label">Descripción de la misión:</div>
      <textarea class="input" rows="4" placeholder="Escribe la descripción de la misión" required name="descripcion"></textarea>

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
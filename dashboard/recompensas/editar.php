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

// Obtener el ID de la misión a eliminar desde la URL, por defecto 0
$id = $_GET['id'] ?? 0;

$stmt = $pdo->prepare("SELECT * FROM recompensas WHERE id_recompensa = ?");
$stmt->execute([$id]);
$recompensa = $stmt->fetch(PDO::FETCH_ASSOC);

// Captura los datos enviados por POST
$titulo = $_POST['recompensa'] ?? null;
$descripcion = $_POST['descripcion'] ?? null;
$precio = $_POST['precio'] ?? null;
$imagenNombre = $recompensa['imagen_recom'] ?? null;

// Obtiene el id del usuario logueado
$id_usuario = $_SESSION['id_usuario'] ?? null;

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
if (!empty($titulo) && !empty($descripcion) && !empty($precio)) {
    // Validar que la puntuación sea numérica y esté en rango 0-100
    if (!is_numeric($precio) || $precio < 0 || $precio > 300) {
        $_SESSION['error'] = "La puntuación debe estar entre 0 y 300.";
        exit;
    } else {
        try {
            // Preparar edicion en la base de datos
            $stmt = $pdo->prepare("
        UPDATE recompensas 
            SET imagen_recom = ?, titulo_recompensa = ?, descripcion_recompensa = ?, precio = ?
            WHERE id_recompensa = ?
        ");

            // Ejecutar la edicion
            $stmt->execute([$imagenNombre, $titulo, $descripcion, $precio, $id]);
            
            // Actualizar las traducciones en los archivos JSON
            try {
                Translator::translateAndSaveToJson('reward_title', $titulo, $id);
                Translator::translateAndSaveToJson('reward_description', $descripcion, $id);
            } catch (TranslationException $e) {
                error_log("Error al actualizar las traducciones de la recompensa: " . $e->getMessage());
                $_SESSION['warning'] = "La recompensa se actualizó correctamente, pero hubo un error con las traducciones.";
            }

            // Redirigir al inicio después de enviar la solicitud
            header("Location: " . BASE_URL . "/dashboard/admin.php");
            exit;
        } catch (PDOException $e) {
            // Mostrar error si falla la edicion
            print "Error al insertar los datos: " . $e->getMessage();
        }
    }
}
?>
<section class="section" style="max-width:760px">
    <div class="panel">
        <h2>Editar una recompensa</h2>
        <!-- Formulario de solicitud de misión -->
        <form action="" method="POST" enctype="multipart/form-data">
            <label for="recompensa" class="label">Recompensa:</label>
            <input class="input" id="recompensa" value="<?= htmlspecialchars($recompensa['titulo_recompensa']) ?>" name="recompensa">

            <label for="descripcion" class="label">Descripción de la recompensa:</label>
            <input class="input" value="<?= htmlspecialchars($recompensa['descripcion_recompensa']) ?>" name="descripcion">

            <div class="label">Imagen de la recompensa:</div>
            <?php if (!empty($recompensa['imagen_recom'])): ?>
                <img src="<?= BASE_URL ?>/assets/img/recompensas/<?= htmlspecialchars($recompensa['imagen_recom']) ?>" style="max-width:200px; border-radius:6px; margin-bottom:10px;">
            <?php endif; ?>
            <input class="input" type="file" name="imagen">

            <div class="label">Precio:</div>
            <input class="input" value="<?= htmlspecialchars($recompensa['precio']) ?>" name="precio" pattern="^([0-9]|[1-9][0-9]|[12][0-9]{2}|300)$" title="El valor debe estar entre 0 y 300" required>

            <?php
            if (!empty($_POST['submit']) && isset($_SESSION['error'])) {
                echo '<p class="error">' . $_SESSION['error'] . '</p>';
                unset($_SESSION['error']);
            }
            ?>

            <div style="margin-top:16px">
                <input class="btn-pill" type="submit" name="submit" value="Editar">
            </div>
        </form>

    </div>
</section>

<?php
// Incluye el pie de página
require_once __DIR__ . '/../../includes/footer.php';
?>
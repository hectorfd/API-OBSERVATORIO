<?php
require_once 'conexion/conexion.php';
require_once 'respuestas.class.php';

$_respuestas = new respuestas;

// Verificar si es una solicitud POST
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    // Verificar si se envió un token
    if (!isset($_POST['token'])) {
        $datosArray = $_respuestas->error_401();
        header("Content-Type: application/json");
        echo json_encode($datosArray);
        exit;
    }
    
    // Validar el token (reutilizando código de la clase tnoticia)
    $token = $_POST['token'];
    $conexion = new conexion();
    $query = "SELECT id_token, estado FROM usuario_token WHERE token = '$token' AND estado = 'Activo'";
    $datos = $conexion->obtenerDatos($query);
    
    if (!$datos) {
        $datosArray = $_respuestas->error_401("El Token que envió es inválido o ha caducado");
        header("Content-Type: application/json");
        echo json_encode($datosArray);
        exit;
    }
    
    // Verificar si se envió un archivo
    if (!isset($_FILES['imagen']) || $_FILES['imagen']['error'] > 0) {
        $datosArray = $_respuestas->error_400("No se envió una imagen o hubo un error al subirla");
        header("Content-Type: application/json");
        echo json_encode($datosArray);
        exit;
    }
    
    // Configurar directorio destino
    $directorio = "../assets/img-projects/img-noticias/";
    
    // Crear el directorio si no existe
    if (!file_exists($directorio)) {
        mkdir($directorio, 0777, true);
    }
    
    // Generar un nombre único para el archivo
    $nombreOriginal = $_FILES['imagen']['name'];
    $extension = pathinfo($nombreOriginal, PATHINFO_EXTENSION);
    $nombreArchivo = uniqid() . '.' . $extension;
    $rutaCompleta = $directorio . $nombreArchivo;
    
    // Subir el archivo
    if (move_uploaded_file($_FILES['imagen']['tmp_name'], $rutaCompleta)) {
        // La ruta que guardaremos en la base de datos
        $rutaDB = "assets/img-projects/img-noticias/" . $nombreArchivo;
        
        // Respuesta exitosa
        $respuesta = $_respuestas->response;
        $respuesta["result"] = array(
            "ruta" => $rutaDB,
            "mensaje" => "Imagen subida correctamente"
        );
        
        header("Content-Type: application/json");
        echo json_encode($respuesta);
    } else {
        $datosArray = $_respuestas->error_500("Error al subir la imagen");
        header("Content-Type: application/json");
        echo json_encode($datosArray);
    }
} else {
    header('Content-Type: application/json');
    $datosArray = $_respuestas->error_405();
    echo json_encode($datosArray);
}
?>
<?php
require_once 'public/respuestas.class.php';
require_once 'public/archivo_entidad_aliada.class.php';

$_respuestas = new respuestas;
$_archivo_entidad_aliada = new archivo_entidad_aliada;

if($_SERVER['REQUEST_METHOD'] == "GET"){
    //echo "hola get";

    if (isset($_GET["page"]) && isset($_GET["id_entidad_aliada"])) {
        $_id_entidad_aliada = $_GET['id_entidad_aliada'];
        $pagina = $_GET["page"];
        $listaarchivo_entidad_aliada = $_archivo_entidad_aliada ->listaArchivoEntidadAliada($pagina,$_id_entidad_aliada);
        header("Content-Type: application/json");
        echo json_encode($listaarchivo_entidad_aliada);
        http_response_code(200);
    }else if(isset($_GET['id'])){
        $_id = $_GET['id'];
        $datos_archivo_entidad_aliada = $_archivo_entidad_aliada->obtenerArchivoEntidadAliada($_id);
        header("Content-Type: application/json");
        echo json_encode($datos_archivo_entidad_aliada);
        http_response_code(200);
    }
    // else if(isset($_GET['codtramite'])){
    //     $_codtramite = $_GET['codtramite'];
    //     $datos_archivo_entidad_aliada = $_archivo_entidad_aliada->buscarTramite($_codtramite);
    //     header("Content-Type: application/json");
    //     echo json_encode($datos_archivo_entidad_aliada);
    //     http_response_code(200);
    // }

}else 
// Manejador de peticiones HTTP POST
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    // Determinar el tipo de solicitud (multipart/form-data o application/json)
    $contentType = isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : '';
    
    if (strpos($contentType, 'multipart/form-data') !== false) {
        // Es una petición con formulario (archivos)
        if (!isset($_POST['token'])) {
            $datosArray = $_respuestas->error_401();
            header("Content-Type: application/json");
            echo json_encode($datosArray);
            http_response_code(401);
            exit;
        }
        
        // Crear un objeto con los datos del formulario
        $postData = [
            'nombre_archivo' => $_POST['nombre_archivo'] ?? '',
            'descripcion' => $_POST['descripcion'] ?? '',
            'fecha_registro' => $_POST['fecha_registro'] ?? '',
            'id_entidad_aliada' => $_POST['id_entidad_aliada'] ?? '',
            'token' => $_POST['token']
        ];
        
        // Enviar los datos al manejador (el archivo se maneja dentro de post())
        $datosArray = $_archivo_entidad_aliada->post(json_encode($postData));
    } else {
        // Es una petición JSON estándar
        $postBody = file_get_contents("php://input");
        $datosArray = $_archivo_entidad_aliada->post($postBody);
    }
    
    // Devolver la respuesta
    header("Content-Type: application/json");
    if (isset($datosArray["result"]["error_id"])) {
        $responseCode = $datosArray["result"]["error_id"];
        http_response_code($responseCode);
    } else {
        http_response_code(200);
    }
    echo json_encode($datosArray);
}
else if($_SERVER['REQUEST_METHOD'] == "PUT"){
    //echo "hola put";
    //recibimos los datos enviados
    $postBody = file_get_contents("php://input");
    //enviamos datos al manejador
    $datosArray = $_archivo_entidad_aliada->put($postBody);
    //print_r($postBody);
    //devolvemos la respuesta
    header("Content-Type: application/json");
    if(isset($datosArray["result"]["error_id"])){
        $responseCode = $datosArray["result"]["error_id"];
        http_response_code($responseCode);
    }else{
        http_response_code(200);
    }
    echo json_encode($datosArray);

}else if($_SERVER['REQUEST_METHOD'] == "DELETE"){


    // //recibimos los datos enviados
    // $postBody = file_get_contents("php://input");
    // //enviamos datos al manejador
    // $datosArray = $_persona->delete($postBody);
    // //print_r($postBody);
    // //devolvemos la respuesta
    // header("Content-Type: application/json");
    // if(isset($datosArray["result"]["error_id"])){
    //     $responseCode = $datosArray["result"]["error_id"];
    //     http_response_code($responseCode);
    // }else{
    //     http_response_code(200);
    // }
    // echo json_encode($datosArray);

    //MODIFICACION
    if(isset($_GET['id'])){
        $_id = $_GET['id'];
        $datosArray = $_archivo_entidad_aliada->delete($_id);
        header("Content-Type: application/json");
        echo json_encode($datosArray);
        http_response_code(200);
    } else {
        $datosArray = $_respuestas->error_400();
        header("Content-Type: application/json");
        echo json_encode($datosArray);
        http_response_code(400);
    }
}else{
    // header('Content-Type: application/json');
    // $datosArray = $_respuestas->error_405();
    // echo json_decode($datosArray);
}


?>
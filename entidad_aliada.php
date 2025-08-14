<?php
require_once 'public/respuestas.class.php';
require_once 'public/entidad_aliada.class.php';

$_respuestas = new respuestas;
$_entidad_aliada = new entidad_aliada;

if($_SERVER['REQUEST_METHOD'] == "GET"){
    //echo "hola get";

    if(isset($_GET["page"])){
        $pagina = $_GET["page"];
        $listaentidad_aliada = $_entidad_aliada ->listaEntidadAliada($pagina);
        header("Content-Type: application/json");
        echo json_encode($listaentidad_aliada);
        http_response_code(200);
    }else if(isset($_GET['id'])){
        $_id = $_GET['id'];
        $datos_entidad_aliada = $_entidad_aliada->obtenerEntidadAliada($_id);
        header("Content-Type: application/json");
        echo json_encode($datos_entidad_aliada);
        http_response_code(200);
    }
    // else if(isset($_GET['codtramite'])){
    //     $_codtramite = $_GET['codtramite'];
    //     $datos_entidad_aliada = $_entidad_aliada->buscarTramite($_codtramite);
    //     header("Content-Type: application/json");
    //     echo json_encode($datos_entidad_aliada);
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
            'nombre' => $_POST['nombre'] ?? '',
            'descripcion' => $_POST['descripcion'] ?? '',
            'fecha_registro' => $_POST['fecha_registro'] ?? '',
            'token' => $_POST['token']
        ];
        
        // Enviar los datos al manejador (el archivo se maneja dentro de post())
        $datosArray = $_entidad_aliada->post(json_encode($postData));
    } else {
        // Es una petición JSON estándar
        $postBody = file_get_contents("php://input");
        $datosArray = $_entidad_aliada->post($postBody);
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
    $datosArray = $_entidad_aliada->put($postBody);
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
        $datosArray = $_entidad_aliada->delete($_id);
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
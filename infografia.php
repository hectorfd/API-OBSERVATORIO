<?php
require_once 'public/respuestas.class.php';
require_once 'public/infografia.class.php';

$_respuestas = new respuestas;
$_infografia = new infografia;

if($_SERVER['REQUEST_METHOD'] == "GET"){
    //echo "hola get";

    if(isset($_GET["page"])){
        $pagina = $_GET["page"];
        $listainfografia = $_infografia ->listainfografia($pagina);
        header("Content-Type: application/json");
        echo json_encode($listainfografia);
        http_response_code(200);
    }else if(isset($_GET['id'])){
        $_id = $_GET['id'];
        $datos_infografia = $_infografia->obtenerInfografia($_id);
        header("Content-Type: application/json");
        echo json_encode($datos_infografia);
        http_response_code(200);
    }
    // else if(isset($_GET['codtramite'])){
    //     $_codtramite = $_GET['codtramite'];
    //     $datos_infografia = $_infografia->buscarTramite($_codtramite);
    //     header("Content-Type: application/json");
    //     echo json_encode($datos_infografia);
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
            'titulo' => $_POST['titulo'] ?? '',
            'descripcion' => $_POST['descripcion'] ?? '',
            'fecha' => $_POST['fecha'] ?? '',
            'lugar' => $_POST['lugar'] ?? '',
            'token' => $_POST['token']
        ];
        
        // Enviar los datos al manejador (el archivo se maneja dentro de post())
        $datosArray = $_infografia->post(json_encode($postData));
    } else {
        // Es una petición JSON estándar
        $postBody = file_get_contents("php://input");
        $datosArray = $_infografia->post($postBody);
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
    $datosArray = $_infografia->put($postBody);
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
        $datosArray = $_infografia->delete($_id);
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
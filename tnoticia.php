<?php
require_once 'public/respuestas.class.php';
require_once 'public/tnoticia.class.php';

$_respuestas = new respuestas;
$_noticia = new tnoticia;

if($_SERVER['REQUEST_METHOD'] == "GET"){
    //echo "hola get";

    if(isset($_GET["page"])){
        $pagina = $_GET["page"];
        $listanoticia = $_noticia ->listatnoticia($pagina);
        header("Content-Type: application/json");
        echo json_encode($listanoticia);
        http_response_code(200);
    }else if(isset($_GET['id'])){
        $_id = $_GET['id'];
        $datos_noticia = $_noticia->obtenertnoticia($_id);
        header("Content-Type: application/json");
        echo json_encode($datos_noticia);
        http_response_code(200);
    }
    // else if(isset($_GET['codtramite'])){
    //     $_codtramite = $_GET['codtramite'];
    //     $datos_noticia = $_noticia->buscarTramite($_codtramite);
    //     header("Content-Type: application/json");
    //     echo json_encode($datos_noticia);
    //     http_response_code(200);
    // }

}else 
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Create a JSON object with the POST data
    $postData = [
        'titulo' => $_POST['titulo'],
        'descripcion' => $_POST['descripcion'],
        'fecha' => $_POST['fecha'],
        'lugar' => $_POST['lugar'],
        'token' => $_POST['token']
    ];
    
    // If there's an image, handle it
    if (isset($_FILES['portada']) && $_FILES['portada']['error'] == 0) {
        // Let the post method handle the file upload
        $datosArray = $_noticia->post(json_encode($postData));
        
        header("Content-Type: application/json");
        if(isset($datosArray["result"]["error_id"])){
            $responseCode = $datosArray["result"]["error_id"];
            http_response_code($responseCode);
        } else {
            http_response_code(200);
        }
        echo json_encode($datosArray);
    } else {
        // No image was received
        $datosArray = $_respuestas->error_400("No se recibió ninguna imagen");
        header("Content-Type: application/json");
        echo json_encode($datosArray);
        http_response_code(400);
    }
}


else if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
    if (isset($_GET['id'])) {
        $id = $_GET['id'];
        
        // Manejar datos de formulario multipart (incluyendo archivos)
        $datos = $_POST;
        
        // Si no hay datos en $_POST, intentar leer el cuerpo JSON
        if (empty($_POST) && !empty(file_get_contents("php://input"))) {
            $postBody = file_get_contents("php://input");
            $datos = json_decode($postBody, true);
        }
        
        $datos['id'] = $id;
        
        // Si hay archivo, agregarlo a los datos
        if (isset($_FILES['portada']) && $_FILES['portada']['error'] == 0) {
            $datos['portada_file'] = $_FILES['portada'];
        }
        
        $datosArray = $_noticia->put(json_encode($datos));
        
        header("Content-Type: application/json");
        if (isset($datosArray["result"]["error_id"])) {
            http_response_code($datosArray["result"]["error_id"]);
        } else {
            http_response_code(200);
        }
        echo json_encode($datosArray);
    } else {
        $datosArray = $_respuestas->error_400("Se requiere un ID para actualizar");
        header("Content-Type: application/json");
        echo json_encode($datosArray);
        http_response_code(400);
    }
}





else if($_SERVER['REQUEST_METHOD'] == "DELETE"){


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
        $datosArray = $_noticia->delete($_id);
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
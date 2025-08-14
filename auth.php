<?php
require_once 'public/auth.class.php';
require_once 'public/respuestas.class.php';

$_auth = new auth;
$_respuestas = new respuestas;

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    // Recibir datos
    $postBody = file_get_contents("php://input");

    // Enviamos los datos al manejador 
    $datosArray = $_auth->login($postBody);
    
    // Devolvemos una respuesta
    header('Content-Type: application/json');
    if (isset($datosArray["result"]["error_id"])) {
        $responseCode = $datosArray["result"]["error_id"];
        http_response_code($responseCode);
    } else {
        http_response_code(200);
    }
    echo json_encode($datosArray);
} else {
    header('Content-Type: application/json');
    $datosArray = $_respuestas->error_405();
    echo json_encode($datosArray);
}
?>

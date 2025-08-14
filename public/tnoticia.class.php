<?php

require_once 'conexion/conexion.php';
require_once 'respuestas.class.php';

class tnoticia extends conexion{
    //tabla
    private $table = "tnoticia";
    private $titulo	= '';
    private $descripcion	= '';
    private $fecha	= '';
    private $lugar	= '';
    private $portada	= '';
    

    private $token = "";
    //459808482bf351709ff42d6911b440b1

    //lista todas las noticias con paginacion de 1 a 10
    public function listatnoticia($pagina = 1){
        $cantidad = 100000000;
        $inicio = ($pagina - 1) * $cantidad;

        $query = "SELECT * FROM " . $this->table . " ORDER BY id DESC LIMIT $inicio, $cantidad";
        $datos = parent::obtenerDatos($query);
        
        return $datos;
        //
    }

    //muestra los datos de una sola noticia
    public function obtenertnoticia($id){
        $query = "SELECT * FROM " . $this->table . " WHERE id = '$id'";
        //print_r($query);
        //$datos = parent::obtenerDatos($query);
        return parent::obtenerDatos($query);
    }

    //muestra los datos de una solo tramite, para busquedas
    // public function buscarAnioTrabajo($codtramite){
    //     $query = "SELECT * FROM " . $this->table . " WHERE codtramite = '$codtramite'";
    //     //print_r($query);
    //     //$datos = parent::obtenerDatos($query);
    //     return parent::obtenerDatos($query);
    //}

    //
    public function post($json) {
        $_respuestas = new respuestas;
        $datos = json_decode($json, true);
    
        if (!isset($datos['token'])) {
            return $_respuestas->error_401();
        } else {
            $this->token = $datos['token'];
            $arrayToken = $this->buscarToken();
            if ($arrayToken) {
                if (!isset($datos['titulo']) || !isset($datos['descripcion']) || !isset($datos['fecha']) || !isset($datos['lugar'])) {
                    return $_respuestas->error_400();
                } else {
                    $this->titulo = $datos['titulo'];
                    $this->descripcion = $datos['descripcion'];
                    $this->fecha = $datos['fecha'];
                    $this->lugar = $datos['lugar'];
    
                    // Manejo de la imagen (si se envía como archivo)
                    if (isset($_FILES['portada'])) {
                        $directorio = "assets/img/img-noticias/"; // Cambia esto
                        $nombreArchivo = uniqid() . "-" . basename($_FILES['portada']['name']);
                        $rutaCompleta = $directorio . $nombreArchivo;
    
                        if (move_uploaded_file($_FILES['portada']['tmp_name'], $rutaCompleta)) {
                            $this->portada = "assets/img/img-noticias/" . $nombreArchivo; // Ruta relativa para Angular
                        } else {
                            return $_respuestas->error_500("Error al subir la imagen");
                        }
                    } else {
                        $this->portada = $datos['portada'] ?? ''; // Si no hay imagen, usa un valor por defecto
                    }
    
                    $resp = $this->insertartnoticia();
                    if ($resp) {
                        $respuesta = $_respuestas->response;
                        $respuesta["result"] = array(
                            "id" => $resp,
                            "portada" => $this->portada // Devuelve la ruta de la imagen
                        );
                        return $respuesta;
                    } else {
                        return $_respuestas->error_500();
                    }
                }
            } else {
                return $_respuestas->error_401("Token inválido o caducado");
            }
        }
    }

    ///para insertar tramite
    private function insertartnoticia(){
        $query = "INSERT INTO " . $this->table . " (titulo, descripcion, fecha, lugar, portada)
        values
        ('" . $this->titulo . "','" . $this->descripcion . "','" . $this->fecha . "','" . $this->lugar . "','" . $this->portada . "')";
        //print_r($query);
        $resp = parent::nonQueryId($query);
        if($resp){
            return $resp;
        }else{
            return 0;
        }
    }

    public function put($json){
        $_respuestas = new respuestas;
        $datos = json_decode($json, true);
    
        if (!isset($datos['token'])) {
            return $_respuestas->error_401();
        } else {
            $this->token = $datos['token'];
            $arrayToken = $this->buscarToken();
            if ($arrayToken) {
                if (!isset($datos['id'])) {
                    return $_respuestas->error_400();
                } else {
                    $this->id = $datos['id'];
                    
                    // Validamos si la noticia existe
                    $noticiaExistente = $this->obtenertnoticia($this->id);
                    if (empty($noticiaExistente)) {
                        return $_respuestas->error_400("La noticia no existe");
                    }
                    
                    // Asignamos solo los campos que vengan en la solicitud
                    $this->titulo = $datos['titulo'] ?? $noticiaExistente[0]['titulo'];
                    $this->descripcion = $datos['descripcion'] ?? $noticiaExistente[0]['descripcion'];
                    $this->fecha = $datos['fecha'] ?? $noticiaExistente[0]['fecha'];
                    $this->lugar = $datos['lugar'] ?? $noticiaExistente[0]['lugar'];
                    
                    // Mantener la imagen anterior por defecto
                    $this->portada = $noticiaExistente[0]['portada'];
                    
                    // Procesar la nueva imagen si existe
                    if (isset($_FILES['portada']) && $_FILES['portada']['error'] == 0) {
                        $directorio = "assets/img/img-noticias/";
                        $nombreArchivo = uniqid() . "-" . basename($_FILES['portada']['name']);
                        $rutaCompleta = $directorio . $nombreArchivo;
                        
                        if (move_uploaded_file($_FILES['portada']['tmp_name'], $rutaCompleta)) {
                            // Eliminar la imagen anterior si existe y no es la predeterminada
                            $rutaAnterior = $noticiaExistente[0]['portada'];
                            if (!empty($rutaAnterior) && file_exists($rutaAnterior) && $rutaAnterior != "assets/img/img-noticias/default.jpg") {
                                unlink($rutaAnterior);
                            }
                            
                            // Actualizar la ruta de la nueva imagen
                            $this->portada = "assets/img/img-noticias/" . $nombreArchivo;
                        } else {
                            return $_respuestas->error_500("Error al subir la nueva imagen");
                        }
                    }
                    
                    $resp = $this->modificartnoticia();
                    if ($resp) {
                        $respuesta = $_respuestas->response;
                        $respuesta["result"] = array(
                            "id" => $this->id,
                            "portada" => $this->portada
                        );
                        return $respuesta;
                    } else {
                        return $_respuestas->error_500();
                    }
                }
            } else {
                return $_respuestas->error_401("Token inválido o caducado");
            }
        }
    }

// public function put($json){
//     $_respuestas = new respuestas;
//     $datos = json_decode($json, true);

//     if (!isset($datos['token'])) {
//         return $_respuestas->error_401();
//     } else {
//         $this->token = $datos['token'];
//         $arrayToken = $this->buscarToken();
//         if ($arrayToken) {
//             if (!isset($datos['id'])) {
//                 return $_respuestas->error_400();
//             } else {
//                 $this->id = $datos['id'];
                
//                 // Validamos si la noticia existe
//                 $noticiaExistente = $this->obtenertnoticia($this->id);
//                 if (empty($noticiaExistente)) {
//                     return $_respuestas->error_400("La noticia no existe");
//                 }
                
//                 // Asignamos solo los campos que vengan en la solicitud
//                 $this->titulo = $datos['titulo'] ?? $noticiaExistente[0]['titulo'];
//                 $this->descripcion = $datos['descripcion'] ?? $noticiaExistente[0]['descripcion'];
//                 $this->fecha = $datos['fecha'] ?? $noticiaExistente[0]['fecha'];
//                 $this->lugar = $datos['lugar'] ?? $noticiaExistente[0]['lugar'];

//                 // Manejo de la imagen (si se envía como archivo)
//                 if (isset($datos['portada_file'])) {
//                     $directorio = "assets/img/img-noticias/";
//                     $nombreArchivo = uniqid() . "-" . basename($datos['portada_file']['name']);
//                     $rutaCompleta = $directorio . $nombreArchivo;
                    
//                     if (move_uploaded_file($datos['portada_file']['tmp_name'], $rutaCompleta)) {
//                         $this->portada = "assets/img/img-noticias/" . $nombreArchivo;
                        
//                         // Eliminar la imagen anterior si existe
//                         if (!empty($noticiaExistente[0]['portada']) {
//                             $rutaAnterior = $noticiaExistente[0]['portada'];
//                             if (file_exists($rutaAnterior)) {
//                                 unlink($rutaAnterior);
//                             }
//                         }
//                     } else {
//                         return $_respuestas->error_500("Error al subir la imagen");
//                     }
//                 } else {
//                     $this->portada = $noticiaExistente[0]['portada']; // Mantener la imagen actual
//                 }

//                 $resp = $this->modificartnoticia();
//                 if ($resp) {
//                     $respuesta = $_respuestas->response;
//                     $respuesta["result"] = array(
//                         "id" => $this->id,
//                         "portada" => $this->portada
//                     );
//                     return $respuesta;
//                 } else {
//                     return $_respuestas->error_500();
//                 }
//             }
//         } else {
//             return $_respuestas->error_401("Token inválido o caducado");
//         }
//     }
// }

    ///para modificar noticia
    private function modificartnoticia(){
        $query = "UPDATE " . $this->table . " SET titulo = '" . $this->titulo . "',
        descripcion = '" . $this->descripcion . "',
        fecha = '" . $this->fecha . "',
        lugar = '" . $this->lugar . "',
        portada = '" . $this->portada . "'
        WHERE id = '" . $this->id . "'";

                
        //print_r($query);
        $resp = parent::nonQuery($query);
        if($resp>=1){
            return $resp;
        }else{
            return 0;
        }
    }

    

    //AQUI VA LA eliminacion
    ///para eliminar tnoticia
    public function delete($id){
        $_respuestas = new respuestas;

        if(!isset($id)){
            return $_respuestas->error_400();
        } else {
            $this->id = $id;
            
            $resp = $this->eliminartnoticia();
            if($resp){
                $respuesta = $_respuestas->response;
                $respuesta["result"] = array(
                    "id" => $this->id
                );
                return $respuesta;
            } else {
                return $_respuestas->error_500();
            }
        }
    }

    private function eliminartnoticia(){
        $query = "DELETE FROM " . $this->table . " WHERE id = '" . $this->id . "'";
        $resp = parent::nonQuery($query);
        if($resp >= 1){
            return $resp;
        } else {
            return 0;
        }
    }


    //para el token
    private function buscarToken(){
        $query = "SELECT id_token, id_token, estado from usuario_token WHERE token = '" . $this->token . "' AND estado = 'Activo'";
        $resp = parent::obtenerDatos($query);
        if($resp){
            return $resp;
        }else{
            return 0;
        }
    }
    

    private function actualizarToken($tokenid){
        date_default_timezone_set('America/Lima');
        $date = date("Y-m-d H:i");
        $query = "UPDATE usuarios_token SET fecha = '$date' WHERE TokenId = '$tokenid' ";
        if($resp >= 1){
            return $resp;
        }else{
            return 0;
        }
    }

}


?>
<?php

require_once 'conexion/conexion.php';
require_once 'respuestas.class.php';

class infografia extends conexion{
    //tabla
    private $table = "infografia";
    
	private $id_infografia	= "";
    private $titulo	= "";
    private $descripcion	= "";
    private $fecha	= "";
    private $lugar	= "";
    private $portada	= "";


    private $token = "";
    //459808482bf351709ff42d6911b440b1

    //lista todas las personas con paginacion de 1 a 10
    public function listainfografia($pagina = 1){
        $cantidad = 100000;
        $inicio = ($pagina - 1) * $cantidad;

        $query = "SELECT * FROM " . $this->table . " ORDER BY id_infografia DESC LIMIT $inicio, $cantidad";
        $datos = parent::obtenerDatos($query);
        
        return $datos;
        //
    }

    //muestra los datos de una sola persona
    public function obtenerInfografia($id){
        $query = "SELECT * FROM " . $this->table . " WHERE id_infografia = '$id'";
        //print_r($query);
        //$datos = parent::obtenerDatos($query);
        return parent::obtenerDatos($query);
    }

    //muestra los datos de un solo infografia, para busquedas
    public function buscarinfografia($codinfografia){
        $query = "SELECT * FROM " . $this->table . " WHERE codinfografia = '$codinfografia'";
        //print_r($query);
        //$datos = parent::obtenerDatos($query);
        return parent::obtenerDatos($query);
    }

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
                
                // Manejo de la imagen
                if (isset($_FILES['portada'])) {
                    $directorio = "assets/img/img-infografias/"; // Directorio para infografías
                    
                    // Crear el directorio si no existe
                    if (!file_exists($directorio)) {
                        mkdir($directorio, 0777, true);
                    }
                    
                    $nombreArchivo = uniqid() . "-" . basename($_FILES['portada']['name']);
                    $rutaCompleta = $directorio . $nombreArchivo;
                    
                    if (move_uploaded_file($_FILES['portada']['tmp_name'], $rutaCompleta)) {
                        $this->portada = $directorio . $nombreArchivo; // Ruta relativa para la base de datos
                    } else {
                        return $_respuestas->error_500("Error al subir la imagen");
                    }
                } else if (isset($datos['portada'])) {
                    // Si no hay archivo pero sí hay datos de imagen en base64
                    $this->portada = $datos['portada'];
                } else {
                    return $_respuestas->error_400("No se recibió la imagen");
                }
                
                $resp = $this->insertarinfografia();
                if ($resp) {
                    $respuesta = $_respuestas->response;
                    $respuesta["result"] = array(
                        "id_infografia" => $resp,
                        "portada" => $this->portada // Devuelve la ruta de la imagen
                    );
                    return $respuesta;
                } else {
                    return $_respuestas->error_500();
                }
            }
        } else {
            return $_respuestas->error_401("El Token que envio es invalido o ha caducado");
        }
    }
}

    ///para insertar tramite
    private function insertarinfografia(){
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

    //para el metodo put
    //para el metodo put
    public function put($json){
        $_respuestas = new respuestas;
        $datos = json_decode($json,true);
        //para la seguridad
        if(!isset($datos['token'])){
            return $_respuestas->error_401();
        }else{
            $this->token = $datos['token'];
            $arrayToken = $this->buscarToken();
            if($arrayToken){
                if(!isset($datos['id_infografia'])){
                    return $_respuestas->error_400();
                }else{
                    $this->id_infografia = $datos['id_infografia'];
                    if(isset($datos['titulo'])){$this->titulo=$datos['titulo'];}      
                    if(isset($datos['descripcion'])){$this->descripcion=$datos['descripcion'];}
                    if(isset($datos['fecha'])){$this->fecha=$datos['fecha'];}
                    if(isset($datos['lugar'])){$this->lugar=$datos['lugar'];}
                    if(isset($datos['portada'])){$this->portada=$datos['portada'];}     
                    
                    $resp = $this->modificartnoticia();
                    if($resp){
                        $respuesta = $_respuestas->response;
                        $respuesta["result"] = array(
                            "id_infografia" => $this->id_infografia
                        );
                        return $respuesta;
                    }else{
                        return $_respuestas->error_500();
                    }
                }
            }else{
                return $_respuestas->error_401("El Token que envio es invalido o ha caducado");

            }
        }
    }

    

    ///para insertar persona
    ///para modificar noticia
    private function modificartnoticia(){
        $query = "UPDATE " . $this->table . " SET titulo = '" . $this->titulo . "',
        descripcion = '" . $this->descripcion . "',
        fecha = '" . $this->fecha . "',
        lugar = '" . $this->lugar . "',
        portada = '" . $this->portada . "'
        WHERE id_infografia = '" . $this->id_infografia . "'";

                
        //print_r($query);
        $resp = parent::nonQuery($query);
        if($resp>=1){
            return $resp;
        }else{
            return 0;
        }
    }

    ///para eliminar persona
    // public function delete($json){
    //     $_respuestas = new respuestas;
    //     $datos = json_decode($json,true);
    //     if(!isset($datos['ID'])){
    //         return $_respuestas->error_400();
    //     }else{
    //         $this->personaid = $datos['ID'];
            
    //         $resp = $this->elinimarPersona();
    //         if($resp){
    //             $respuesta = $_respuestas->response;
    //             $respuesta["result"] = array(
    //                 "personaid" => $this->personaid
    //             );
    //             return $respuesta;
    //         }else{
    //             return $_respuestas->error_500();
    //         }
    //     }
    // }

    // private function elinimarPersona(){
    //     $query = "DELETE FROM " . $this->table . " WHERE ID= '" . $this->personaid . "'";
    //     $resp = parent::nonQuery($query);
    //     if($resp >= 1){
    //         return $resp;
    //     }else{
    //         return 0;
    //     }
    // }


    //AQUI VA LA MODIFICACION
    ///para eliminar persona
    public function delete($id_infografia){
        $_respuestas = new respuestas;

        // if(!isset($datos['token'])){
        //     return $_respuestas->error_401();
        // }else{
        //     $this->token = $datos['token'];
        //     $arrayToken = $this->buscarToken();
        //     if($arrayToken){

        //     }else{
        //         return $_respuestas->error_401("El Token que envio es invalido o ha caducado");

        //     }
        // }

        if(!isset($id_infografia)){
            return $_respuestas->error_400();
        } else {
            $this->id_infografia = $id_infografia;
            
            $resp = $this->eliminarinfografia();
            if($resp){
                $respuesta = $_respuestas->response;
                $respuesta["result"] = array(
                    "id_infografia" => $this->id_infografia
                );
                return $respuesta;
            } else {
                return $_respuestas->error_500();
            }
        }
    }

    private function eliminarinfografia(){
        $query = "DELETE FROM " . $this->table . " WHERE id_infografia = '" . $this->id_infografia . "'";
        $resp = parent::nonQuery($query);
        if($resp >= 1){
            return $resp;
        } else {
            return 0;
        }
    }


    //para el token
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
        $query = "UPDATE usuario_token SET fecha = '$date' WHERE TokenId = '$tokenid' ";
        if($resp >= 1){
            return $resp;
        }else{
            return 0;
        }
    }

}

?>
<?php

require_once 'conexion/conexion.php';
require_once 'respuestas.class.php';

class entidad_aliada extends conexion{
    //tabla
    private $table = "entidad_aliada";
    
	private $id_entidad_aliada= "";
    private $nombre	= "";
    private $descripcion	= "";
    private $portada	= "";
    private $fecha_registro	= "";


    private $token = "";
    //459808482bf351709ff42d6911b440b1

    //lista todas las personas con paginacion de 1 a 10
    public function listaEntidadAliada($pagina = 1){
        $cantidad = 100000;
        $inicio = ($pagina - 1) * $cantidad;

        $query = "SELECT * FROM " . $this->table . " ORDER BY id_entidad_aliada DESC LIMIT $inicio, $cantidad";
        $datos = parent::obtenerDatos($query);
        
        return $datos;
        //
    }

    //muestra los datos de una sola persona
    public function obtenerEntidadAliada($id){
        $query = "SELECT * FROM " . $this->table . " WHERE id_entidad_aliada = '$id'";
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
            if (!isset($datos['nombre']) || !isset($datos['descripcion']) || !isset($datos['fecha_registro'])) {
                return $_respuestas->error_400();
            } else {
                $this->nombre = $datos['nombre'];
                $this->descripcion = $datos['descripcion'];
                $this->fecha_registro = $datos['fecha_registro'];
                
                // Manejo de la imagen
                if (isset($_FILES['portada'])) {
                    $directorio = "assets/img/portada-entidad-aliada/"; // Directorio para infografías
                    
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
                
                $resp = $this->insertarEntidadAliada();
                if ($resp) {
                    $respuesta = $_respuestas->response;
                    $respuesta["result"] = array(
                        "id_entidad_aliada" => $resp,
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
    private function insertarEntidadAliada(){
        $query = "INSERT INTO " . $this->table . " (nombre, descripcion, portada, fecha_registro)
        values
        ('" . $this->nombre . "','" . $this->descripcion . "','" . $this->portada . "','" . $this->fecha_registro .  "')";
        //print_r($query);
        $resp = parent::nonQueryId($query);
        if($resp){
            return $resp;
        }else{
            return 0;
        }
    }

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
                if(!isset($datos['id_entidad_aliada'])){
                    return $_respuestas->error_400();
                }else{
                    $this->id_entidad_aliada = $datos['id_entidad_aliada'];
                    if(isset($datos['nombre'])){$this->nombre=$datos['nombre'];}      
                    if(isset($datos['descripcion'])){$this->descripcion=$datos['descripcion'];}
                    if(isset($datos['portada'])){$this->portada=$datos['portada'];}     
                    if(isset($datos['fecha_registro'])){$this->fecha_registro=$datos['fecha_registro'];}
                    
                    $resp = $this->modificarEntidadAliada();
                    if($resp){
                        $respuesta = $_respuestas->response;
                        $respuesta["result"] = array(
                            "id_entidad_aliada" => $this->id_entidad_aliada
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
    private function modificarEntidadAliada(){
        $query = "UPDATE " . $this->table . " SET nombre = '" . $this->nombre . "',
        descripcion = '" . $this->descripcion . "',
        portada = '" . $this->portada . "',
        fecha_registro = '" . $this->fecha_registro . "'
        WHERE id_entidad_aliada = '" . $this->id_entidad_aliada . "'";

                
        //print_r($query);
        $resp = parent::nonQuery($query);
        if($resp>=1){
            return $resp;
        }else{
            return 0;
        }
    }

   

    //AQUI VA LA MODIFICACION
    ///para eliminar persona
    public function delete($id_entidad_aliada){
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

        if(!isset($id_entidad_aliada)){
            return $_respuestas->error_400();
        } else {
            $this->id_entidad_aliada = $id_entidad_aliada;
            
            $resp = $this->eliminarEntidadAliada();
            if($resp){
                $respuesta = $_respuestas->response;
                $respuesta["result"] = array(
                    "id_entidad_aliada" => $this->id_entidad_aliada
                );
                return $respuesta;
            } else {
                return $_respuestas->error_500();
            }
        }
    }

    private function eliminarEntidadAliada(){
        $query = "DELETE FROM " . $this->table . " WHERE id_entidad_aliada = '" . $this->id_entidad_aliada . "'";
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
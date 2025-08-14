<?php

require_once 'conexion/conexion.php';
require_once 'respuestas.class.php';

class archivo_entidad_aliada extends conexion{
    //tabla
    private $table = "archivo_entidad_aliada";
    
	private $id_archivo_entidad_aliada= "";
    private $nombre_archivo	= "";
    private $descripcion	= "";
    private $fecha_registro	= "";
    private $ruta_archivo	= "";
    private $id_entidad_aliada	= "";


    private $token = "";
    //459808482bf351709ff42d6911b440b1

    //lista todas las personas con paginacion de 1 a 10
    public function listaArchivoEntidadAliada($pagina = 1, $id){
        $cantidad = 100000;
        $inicio = ($pagina - 1) * $cantidad;

        $query = "SELECT * FROM " . $this->table . "  WHERE id_entidad_aliada = '$id' ORDER BY id_archivo_entidad_aliada DESC LIMIT $inicio, $cantidad";
        $datos = parent::obtenerDatos($query);
        
        return $datos;
        //
    }

    //muestra los datos de una sola persona
    public function obtenerArchivoEntidadAliada($id){
        $query = "SELECT * FROM " . $this->table . " WHERE id_archivo_entidad_aliada = '$id'";
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
            if (!isset($datos['nombre_archivo']) || !isset($datos['descripcion']) || !isset($datos['fecha_registro']) || !isset($datos['id_entidad_aliada']) ) {
                return $_respuestas->error_400();
            } else {
                $this->nombre_archivo = $datos['nombre_archivo'];
                $this->descripcion = $datos['descripcion'];
                $this->fecha_registro = $datos['fecha_registro'];
                $this->id_entidad_aliada = $datos['id_entidad_aliada'];
                
                // Manejo de la imagen
                if (isset($_FILES['ruta_archivo'])) {
                    $directorio = "assets/archivos-entidad-aliada/"; // Directorio para infografías
                    
                    // Crear el directorio si no existe
                    if (!file_exists($directorio)) {
                        mkdir($directorio, 0777, true);
                    }
                    
                    $nombre_archivoArchivo = uniqid() . "-" . basename($_FILES['ruta_archivo']['name']);
                    $rutaCompleta = $directorio . $nombre_archivoArchivo;
                    
                    if (move_uploaded_file($_FILES['ruta_archivo']['tmp_name'], $rutaCompleta)) {
                        $this->ruta_archivo = $directorio . $nombre_archivoArchivo; // Ruta relativa para la base de datos
                    } else {
                        return $_respuestas->error_500("Error al subir archivo");
                    }
                } else if (isset($datos['ruta_archivo'])) {
                    // Si no hay archivo pero sí hay datos de imagen en base64
                    $this->ruta_archivo = $datos['ruta_archivo'];
                } else {
                    return $_respuestas->error_400("No se recibió archivo");
                }
                
                $resp = $this->insertarArchivoEntidadAliada();
                if ($resp) {
                    $respuesta = $_respuestas->response;
                    $respuesta["result"] = array(
                        "id_archivo_entidad_aliada" => $resp,
                        "ruta_archivo" => $this->ruta_archivo // Devuelve la ruta de la imagen
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
    private function insertarArchivoEntidadAliada(){
        $query = "INSERT INTO " . $this->table . " (nombre_archivo, descripcion, fecha_registro, ruta_archivo, id_entidad_aliada)
        values
        ('" . $this->nombre_archivo . "','" . $this->descripcion . "','" . $this->fecha_registro . "','" . $this->ruta_archivo . "','" . $this->id_entidad_aliada .  "')";
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
                if(!isset($datos['id_archivo_entidad_aliada'])){
                    return $_respuestas->error_400();
                }else{
                    $this->id_archivo_entidad_aliada = $datos['id_archivo_entidad_aliada'];
                    if(isset($datos['nombre_archivo'])){$this->nombre_archivo=$datos['nombre_archivo'];}      
                    if(isset($datos['descripcion'])){$this->descripcion=$datos['descripcion'];}
                    if(isset($datos['fecha_registro'])){$this->fecha_registro=$datos['fecha_registro'];}
                    if(isset($datos['ruta_archivo'])){$this->ruta_archivo=$datos['ruta_archivo'];}     
                    
                    $resp = $this->modificarArchivoEntidadAliada();
                    if($resp){
                        $respuesta = $_respuestas->response;
                        $respuesta["result"] = array(
                            "id_archivo_entidad_aliada" => $this->id_archivo_entidad_aliada
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
    private function modificarArchivoEntidadAliada(){
        $query = "UPDATE " . $this->table . " SET nombre_archivo = '" . $this->nombre_archivo . "',
        descripcion = '" . $this->descripcion . "',
        fecha_registro = '" . $this->fecha_registro . "',
        ruta_archivo = '" . $this->ruta_archivo . "'
        WHERE id_archivo_entidad_aliada = '" . $this->id_archivo_entidad_aliada . "'";

                
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
    public function delete($id_archivo_entidad_aliada){
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

        if(!isset($id_archivo_entidad_aliada)){
            return $_respuestas->error_400();
        } else {
            $this->id_archivo_entidad_aliada = $id_archivo_entidad_aliada;
            
            $resp = $this->eliminarEntidadAliada();
            if($resp){
                $respuesta = $_respuestas->response;
                $respuesta["result"] = array(
                    "id_archivo_entidad_aliada" => $this->id_archivo_entidad_aliada
                );
                return $respuesta;
            } else {
                return $_respuestas->error_500();
            }
        }
    }

    private function eliminarEntidadAliada(){
        $query = "DELETE FROM " . $this->table . " WHERE id_archivo_entidad_aliada = '" . $this->id_archivo_entidad_aliada . "'";
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
<?php

require_once 'conexion/conexion.php';
require_once 'respuestas.class.php';

class tusuario extends conexion{
    //tabla
    private $table = "tusuario";
    
    private $id_usuario = "";
    private $nombre = "";
    private $apellido = "";
    private $nombre_usuario = "";
    private $contrasena = "";
    private $fecha_registro = "";
    private $estado = "";
    private $foto_ruta = "";

    private $token = "";
    //459808482bf351709ff42d6911b440b1

    //lista todas las personas con paginacion de 1 a 10
    public function listatusuario($pagina = 1){
        $cantidad = 100000;
        $inicio = ($pagina - 1) * $cantidad;

        $query = "SELECT * FROM " . $this->table . " ORDER BY id_usuario DESC LIMIT $inicio, $cantidad";
        $datos = parent::obtenerDatos($query);
        
        return $datos;
        //
    }

    //muestra los datos de una sola persona
    public function obtenerUsuario($id_usuario){
        $query = "SELECT * FROM " . $this->table . " WHERE id_usuario = '$id_usuario'";
        //print_r($query);
        //$datos = parent::obtenerDatos($query);
        return parent::obtenerDatos($query);
    }

    //muestra los datos de un solo usuario, para busquedas
    public function buscarUsuario($codusuario){
        $query = "SELECT * FROM " . $this->table . " WHERE codusuario = '$codusuario'";
        //print_r($query);
        //$datos = parent::obtenerDatos($query);
        return parent::obtenerDatos($query);
    }

    //
    public function post($json){
        $_respuestas = new respuestas;
        $datos = json_decode($json,true);

        if(!isset($datos['token'])){
            return $_respuestas->error_401();
        }else{
            $this->token = $datos['token'];
            $arrayToken = $this->buscarToken();
            if($arrayToken){
                if( !isset($datos['nombre']) || !isset($datos['apellido']) || !isset($datos['nombre_usuario']) || !isset($datos['contrasena']) || !isset($datos['estado']) || !isset($datos['fecha_registro']) ){
                    return $_respuestas->error_400();
                }else{
                    $this->nombre=$datos['nombre'];
                    $this->apellido=$datos['apellido'];
                    $this->nombre_usuario=$datos['nombre_usuario'];
                    $this->contrasena=$datos['contrasena'];
                    $this->estado=$datos['estado'];
                    $this->fecha_registro=$datos['fecha_registro'];
                    
                    $resp = $this->insertarUsuario();
                    if($resp){
                        $respuesta = $_respuestas->response;
                        $respuesta["result"] = array(
                            "id_usuario" => $resp
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

    ///para insertar tramite
    private function insertarUsuario(){
        $query = "INSERT INTO " . $this->table . " (nombre, apellido, nombre_usuario, contrasena, estado, fecha_registro)
        VALUES ('" . $this->nombre . "','" . $this->apellido . "','" . $this->nombre_usuario . "','" . $this->contrasena . "','" . $this->estado . "','" . $this->fecha_registro . "')";
        //print_r($query);
        $resp = parent::nonQueryId($query);
        if($resp){
            return $resp;
        }else{
            return 0;
        }
    }

    //para el metodo put
// para el metodo put
public function put($json){
    $_respuestas = new respuestas;
    $datos = json_decode($json, true);

    // Verificar si se proporciona el token
    if (!isset($datos['token'])) {
        return $_respuestas->error_401();
    } else {
        $this->token = $datos['token'];
        $arrayToken = $this->buscarToken();

        if ($arrayToken) {
            if (!isset($datos['id_usuario'])) {
                return $_respuestas->error_400();
            } else {
                $this->id_usuario = $datos['id_usuario'];

                // Verificar si se proporciona una nueva contrasena
                if (isset($datos['nuevacontrasena'])) {
                    $this->contrasena = $datos['nuevacontrasena'];
                } else {
                    // Si no se proporciona una nueva contrasena, conserva la contrasena existente
                    $usuarioActual = $this->obtenerUsuario($this->id_usuario);
                    if ($usuarioActual && isset($usuarioActual[0]['contrasena'])) {
                        $this->contrasena = $usuarioActual[0]['contrasena'];
                    }
                }

                // Otras actualizaciones de campos, como usuario, estado, rol, etc.
                if (isset($datos['nombre'])) {
                    $this->nombre = $datos['nombre'];
                }
                // Otras actualizaciones de campos, como usuario, estado, rol, etc.
                if (isset($datos['apellido'])) {
                    $this->apellido = $datos['apellido'];
                }
                // Otras actualizaciones de campos, como usuario, estado, rol, etc.
                if (isset($datos['nombre_usuario'])) {
                    $this->nombre_usuario = $datos['nombre_usuario'];
                }

                if (isset($datos['fecha_registro'])) {
                    $this->fecha_registro = $datos['fecha_registro'];
                }

                if (isset($datos['contrasena'])) {
                    $this->contrasena = $datos['contrasena'];
                }
                if (isset($datos['estado'])) {
                    $this->estado = $datos['estado'];
                }

            
                

                // Agrega aquí otros campos que deseas actualizar

                $resp = $this->modificarUsuario();

                if ($resp) {
                    $respuesta = $_respuestas->response;
                    $respuesta["result"] = array(
                        "id_usuario" => $this->id_usuario
                    );
                    return $respuesta;
                } else {
                    return $_respuestas->error_500();
                }
            }
        } else {
            return $_respuestas->error_401("El Token que envió es inválido o ha caducado");
        }
    }
}

    

    ///para insertar persona
    private function modificarUsuario(){
        $query = "UPDATE " . $this->table . " SET nombre = '" . $this->nombre . "' , apellido = '"  . $this->apellido . "' , nombre_usuario = '"  . $this->nombre_usuario . "' , contrasena = '" . $this->contrasena . "' , estado = '" . $this->estado . "' , fecha_registro = '" . $this->fecha_registro .  
        "'WHERE id_usuario = '" . $this->id_usuario . "'";
                
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
    public function delete($id_usuario){
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

        if(!isset($id_usuario)){
            return $_respuestas->error_400();
        } else {
            $this->id_usuario = $id_usuario;
            
            $resp = $this->eliminarUsuario();
            if($resp){
                $respuesta = $_respuestas->response;
                $respuesta["result"] = array(
                    "id_usuario" => $this->id_usuario
                );
                return $respuesta;
            } else {
                return $_respuestas->error_500();
            }
        }
    }

    private function eliminarUsuario(){
        $query = "DELETE FROM " . $this->table . " WHERE id_usuario = '" . $this->id_usuario . "'";
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
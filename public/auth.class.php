<?php
require_once 'conexion/conexion.php';
require_once 'respuestas.class.php';

class auth extends conexion {
    public function login($json) {
        $_respuestas = new respuestas;
        $datos = json_decode($json, true);

        if (!isset($datos['usuario']) || !isset($datos["password"])) {
            // Error con los campos
            return $_respuestas->error_400();
        } else {
            // Todo está bien
            $usuario = $datos['usuario'];
            $password = $datos['password'];
            $datosUsuario = $this->obtenerDatosUsuario($usuario);

            if ($datosUsuario) {
                // Si existe usuario
                // Verificar si la contraseña es igual
                if ($password == $datosUsuario[0]['contrasena']) {
                    if (isset($datosUsuario[0]['estado']) && $datosUsuario[0]['estado'] == "Activo") {
                        // Verificar si el usuario ya tiene un token
                        $token = $this->obtenerToken($datosUsuario[0]['id_usuario']);

                        if (!$token) {
                            // Si el usuario no tiene un token, crear uno nuevo
                            $token = $this->insertarToken($datosUsuario[0]['id_usuario']);
                        }

                        if ($token) {
                            // Si se guardó el token correctamente
                            $result = $_respuestas->response;
                            $result["result"] = array(
                                "token" => $token,
                            );
                            return $result;
                        } else {
                            // Error al guardar o obtener el token
                            return $_respuestas->error_500("Error interno, No hemos podido obtener/guardar el token");
                        }
                    } else {
                        // El usuario está inactivo
                        return $_respuestas->error_200("el usuario está inactivo");
                    }
                } else {
                    // La contraseña no es igual
                    return $_respuestas->error_200("el password es inválido");
                }
            } else {
                // No existe el usuario
                return $_respuestas->error_200("El usuario $usuario no existe ");
            }
        }
    }

    // Para obtener los usuarios
    private function obtenerDatosUsuario($correo) {
        $query = "SELECT * FROM tusuario WHERE nombre_usuario = '$correo' ";
        $datos = parent::obtenerDatos($query);
        if (isset($datos[0]["id_usuario"])) {
            return $datos;
        } else {
            return 0;
        }
    }

    private function insertarToken($id_usuario) {
        $val = true;
        $token = bin2hex(openssl_random_pseudo_bytes(16, $val));
        $estado = "Activo";
        $query = "INSERT INTO usuario_token (id_usuario, token, estado) VALUES('$id_usuario','$token', '$estado')";
        $verifica = parent::nonQuery($query);
        if ($verifica) {
            return $token;
        } else {
            return 0;
        }
    }

    private function obtenerToken($id_usuario) {
        $query = "SELECT token FROM usuario_token WHERE id_usuario = '$id_usuario' AND estado = 'Activo'";
        $datos = parent::obtenerDatos($query);
        if (isset($datos[0]["token"])) {
            return $datos[0]["token"];
        } else {
            return false;
        }
    }
}
?>

<?php
    //para la conexion con la base de datos
    class conexion{
        private $server;
        private $user;
        private $password;
        private $database;
        private $port;
        private $conexion;

        //
        function __construct(){
            $listadatos = $this->datosConexion();
            foreach($listadatos as $key => $value){
                $this->server =  $value["server"];
                $this->user = $value["user"];
                $this->password = $value["password"];
                $this->database = $value["database"];
                $this->port = $value["port"];
            }
            $this->conexion = new mysqli($this->server,$this->user,$this->password,$this->database,$this->port);
            if($this->conexion->connect_errno){
                echo "algo va mal con la conexion";
                die();
            }

        }

        //funcion
        private function datosConexion(){
            $direccion = dirname(__FILE__);//funcion de php
            $jsondata = file_get_contents($direccion . "/" . "config");//guardar todo el contenido para despues devolverlo
            return json_decode($jsondata, true);//convertir los datos a un array
        }

        //funciones necesarias para la manipulacion de datos
        private function convertirUTF8($array){
            array_walk_recursive($array,function(&$item,$key){
                if(!mb_detect_encoding($item,'utf-8',true)){
                    $item = utf8_encode($item);
                }
            });
            return $array;
        }

        //FUNCION PARA OBTENER DATOS
        public function obtenerDatos($sqlstr){
            $results = $this->conexion->query($sqlstr);
            $resultsArray = array();
            foreach ($results as $key) {
                $resultsArray[] = $key;
            }
            return $this->convertirUTF8($resultsArray);
        }

        //FUNCION PARA GUARDAR DATOS
        public function nonQuery($sqlstr){
            $results = $this->conexion->query($sqlstr);
            return $this->conexion->affected_rows;
        }

        //FUNCION PARA GUARDAR DATOS y devuelva el id
        public function nonQueryId($sqlstr){
            $results = $this->conexion->query($sqlstr);
            $filas = $this->conexion->affected_rows;
            if($filas >= 1){
                return $this->conexion->insert_id;
            }else{
                return 0;
            }
        }

        //para encriotar la contraseña
        protected function encriptar($string){
            return md5($string);
        }


    }

?>

<?php
  
use model\ClienteModel;
require_once __DIR__ . '/../model/ClienteModel.php';
require_once __DIR__ . '/../config/funciones.php';


class AuthController {

    public function index(){
        require __DIR__ . '/../views/auth/index.php';
    }

    public function registro() {
    // Get alerts from the model
    $alertas = [];
    $usuario = new ClienteModel();

    if($_SERVER['REQUEST_METHOD'] === 'POST'){
        if(!empty($_FILES["FOTOGRAFIA_CLIENTE"]["tmp_name"])){
            $carpeta_imagenes = __DIR__ . "/../public/imgCliente";

            if(!is_dir($carpeta_imagenes)){
               mkdir($carpeta_imagenes, 0777, true);
            }
            $nombre_imagen = md5(uniqid(rand(), true));
            $extension = pathinfo($_FILES["FOTOGRAFIA_CLIENTE"]["name"], PATHINFO_EXTENSION);
            $ruta_imagen = $carpeta_imagenes . "/" . $nombre_imagen . "." . $extension;
            $_POST['FOTOGRAFIA_CLIENTE'] = $nombre_imagen.$extension;
        }

        $usuario->sincronizar($_POST);
        $alertas = $usuario->validar_cuenta();
        if(empty($alertas)){
            $existeEmail = ClienteModel::where('EMAIL', $usuario->EMAIL);
            if($existeEmail){
                 ClienteModel::setAlerta('error', 'El Email ya esta registrado');
                $alertas = ClienteModel::getAlertas();
            }else{
                if(!empty($ruta_imagen)){
                if(!move_uploaded_file($_FILES["FOTOGRAFIA_CLIENTE"]["tmp_name"], $ruta_imagen)){
                    ClienteModel::setAlerta('error', 'No se pudo guardar la imagen');
                }
                }
                $usuario->hashPassword();
                unset($usuario->PASSWORD);
                $usuario->crearToken();
                $usuario->getfechaactual();

                $resultado = $usuario->guardar();
                if($resultado){
                    header('Location:/mensaje');
                    exit;
                }
            }
        }
    }
    $alertas = ClienteModel::getAlertas(); 
    $usuario = $usuario;

    require __DIR__ . '/../views/auth/registro.php';
    }

    public function mensaje() {

     require __DIR__ . '/../views/auth/mensaje.php';
    }

}

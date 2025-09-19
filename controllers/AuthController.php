<?php
  
use model\ClienteModel;
require_once __DIR__ . '/../model/ClienteModel.php';
require_once __DIR__ . '/../config/funciones.php';
use Classes\Email;
use Model\Usuario;

require_once __DIR__ . '/../classes/Email.php';


class AuthController {

    public function index(){
        $alertas = [];

        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            $usuario = new ClienteModel($_POST);
            $alertas = $usuario->validarLogin();
            if(empty($alertas)){
               $usuario = ClienteModel::where('USUARIO_PAGINA', $usuario->USUARIO_PAGINA);
               if(!$usuario || !$usuario->CONFIRMADO){
                  ClienteModel::setAlerta('error', 'El Usuario No Existe o no esta confirmado');
               }else{
                if(password_verify($_POST['PASSWORD_PAGINA'], $usuario->PASSWORD_PAGINA) ) {
                   session_start();
                   $_SESSION['id'] = $usuario->ID;
                   $_SESSION['nombre'] = $usuario->NOMBRE_COMPLETO;
                   $_SESSION['usuario'] = $usuario->USUARIO_PAGINA;

                   header('Location: /autenticado');
                }else{
                    ClienteModel::setAlerta('error', 'Password Incorrecto');   
                }

               }
            }
        }

        $alertas = ClienteModel::getAlertas();
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
            $_POST['FOTOGRAFIA_CLIENTE'] = $nombre_imagen.".".$extension;
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

                $email = new Email($usuario->EMAIL, $usuario->NOMBRE_COMPLETO, $usuario->TOKEN);
                $email->enviarConfirmacion();

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

    public function confirmar(){
        $alertas = [];

        $token = s($_GET['token']);

        if(!$token) header('Location: /');

        $usuario = ClienteModel::where('TOKEN', $token);
        if(empty($usuario)){
            ClienteModel::setAlerta('error', 'Token No válido');
        }else{
            $usuario->CONFIRMADO = 1;
            $usuario->TOKEN = '';
            unset($usuario->PASSWORD);
            $usuario->guardar();

            ClienteModel::setAlerta('exito', 'Cuenta Comprobada Correctamente');
        }

         $alertas = ClienteModel::getAlertas(); 
        require __DIR__ . '/../views/auth/confirmar.php';
    }

}

<?php
  
use model\ClienteModel;
require_once __DIR__ . '/../model/ClienteModel.php';
require_once __DIR__ . '/../config/funciones.php';
use Classes\Email;

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
                   $_SESSION['ID'] = $usuario->ID;
                   $_SESSION['NOMBRE'] = $usuario->NOMBRE_COMPLETO;
                   $_SESSION['EMAIL'] = $usuario->EMAIL;
                   $_SESSION['USUARIO'] = $usuario->USUARIO_PAGINA;

                  header('Location: https://gtis.tech/Global-client/');
                  
                }else{
                    ClienteModel::setAlerta('error', 'Password Incorrecto');   
                }

               }
            }
        }

        $alertas = ClienteModel::getAlertas();
        require __DIR__ . '/../views/auth/index.php';
    }

    public function logout(){
         if($_SERVER['REQUEST_METHOD'] === 'POST') {
            session_start();
            $_SESSION = [];
            header('Location: https://gtis.tech/Global-client/');
        }
    }

    public function registro() {
    // Get alerts from the model
    $alertas = [];
    $usuario = new ClienteModel();

    if($_SERVER['REQUEST_METHOD'] === 'POST'){

        $usuario->sincronizar($_POST);
        $alertas = $usuario->validar_cuenta();
        if(empty($alertas)){
            $existeEmail = ClienteModel::where('EMAIL', $usuario->EMAIL);
            if($existeEmail){
                 ClienteModel::setAlerta('error', 'El Email ya esta registrado');
                $alertas = ClienteModel::getAlertas();
            }else{
                $usuario->hashPassword();
                unset($usuario->PASSWORD);
                $usuario->crearToken();
                $usuario->getfechaactual();

                $resultado = $usuario->guardar();

                $email = new Email($usuario->EMAIL, $usuario->NOMBRE_COMPLETO, $usuario->TOKEN);
                $email->enviarConfirmacion();

                if($resultado){
                    header('Location: https://gtis.tech/Global-client/mensaje');
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

        if(!$token) header('Location: https://gtis.tech/Global-client/');

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

     public function olvide(){
        $alertas = [];

        if($_SERVER['REQUEST_METHOD'] === 'POST'){
           $usuario = new ClienteModel($_POST);
           $alertas = $usuario->validarEmail();
           if(empty($alertas)){
             // Buscar el usuario
              $usuario = ClienteModel::where('EMAIL', $usuario->EMAIL);
             
              if($usuario && $usuario->CONFIRMADO){
                //Generar un nuevo token
                $usuario->crearToken();
                //Actualizar el usuario
                unset($usuario->PASSWORD);
                $usuario->guardar();
                //Enviar el email
                $email = new Email( $usuario->EMAIL, $usuario->NOMBRE_COMPLETO, $usuario->TOKEN);
                $email->enviarInstrucciones();

                //Imprimir alerta
                $alertas['exito'][] = 'Hemos enviado las instrucciones a tu email';
              }else{
                $alertas['error'][] = 'El Usuario no existe o no esta confirmado'; 
              }
           }
        }
        $alertas = $alertas;

        require __DIR__ . '/../views/auth/olvide.php';
    }
public function reestablecer(){
    $alertas = [];
    
    $token = s($_GET['token']);
    $token_valido = true;

    if(!$token) header('Location: https://gtis.tech/Global-client/');


    // Buscar usuario por token
    $usuario = ClienteModel::where('TOKEN', $token);

    // Si no existe el usuario -> token inválido
    if(!$usuario){
        ClienteModel::setAlerta('error', 'Token No válido, intenta de nuevo');
        $token_valido = false;
    }

    // Si existe usuario y llega un POST -> actualizar contraseña
    if($usuario && $_SERVER['REQUEST_METHOD'] === 'POST'){
        $usuario->sincronizar($_POST);
        $alertas = $usuario->validarPassword();
        if(empty($alertas)){
            $usuario->hashPassword();
            $usuario->TOKEN = null;

            $resultado = $usuario->guardar();
            if($resultado){
                    header('Location: https://gtis.tech/Global-client/login');
            }
        }
    }

    $alertas = $alertas;
    $alertas = ClienteModel::getAlertas();
    $token_valido = $token_valido;
    require __DIR__ . '/../views/auth/reestablecer.php';
}

}

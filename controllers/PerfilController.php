<?php

use model\ClienteModel;
require_once __DIR__ . '/../model/ClienteModel.php';
require_once __DIR__ . '/../config/funciones.php';

  class PerfilController{
    public function datospersonales(){
         session_start();
         if(!isauth()){
           header('Location: https://gtis.tech/Global-client/');
         }
         $alertas = [];

         $id = $_SESSION['ID'];
         $usuario = ClienteModel::find($id);

         if($_SERVER['REQUEST_METHOD'] === 'POST'){
          $usuario->sincronizar($_POST);
          $alertas = $usuario->validardatosp();
          if(empty($alertas)){
             $resultado = $usuario->guardar();
             if($resultado){
               ClienteModel::setAlerta('exito', 'Datos Actualizados');
              $alertas = ClienteModel::getAlertas();
              }
          }
         }

         $alertas = $alertas;
         $usuario = $usuario;
        require __DIR__ . '/../views/perfil/index.php';
    }

     public function nit(){
         session_start();
         if(!isauth()){
           header('Location: https://gtis.tech/Global-client/');
         }
         $alertas = [];   
         
         $id = $_SESSION['ID'];
         $cliente = ClienteModel::find($id);

          if($_SERVER['REQUEST_METHOD'] === 'POST'){
          $cliente->NIT = $_POST['NIT'];
          $alertas = $cliente->validarnit();
          if(empty($alertas)){
            $resultado = $cliente->guardar();
            if($resultado){
               ClienteModel::setAlerta('exito', 'NIT Actualizado');
              $alertas = ClienteModel::getAlertas();
            }
          }
          }

         $alertas = $alertas;
         $cliente = $cliente;
        require __DIR__ . '/../views/perfil/nit.php';
    }
     

    public function foto(){
       session_start();
         if(!isauth()){
           header('Location: https://gtis.tech/Global-client/');
         }
         $alertas = []; 

         $id = $_SESSION['ID'];
         
         $cliente = ClienteModel::where('ID', $id);

      if($_SERVER['REQUEST_METHOD'] === 'POST'){
         if(!empty($_FILES["FOTOGRAFIA"]["tmp_name"])){
          
          $carpeta_imagenes = __DIR__ . "/../public/imgCliente";

          if(!is_dir($carpeta_imagenes)){
            mkdir($carpeta_imagenes, 0777, true);
          }
        $extension = strtolower(pathinfo($_FILES['FOTOGRAFIA']['name'], PATHINFO_EXTENSION));

        $nombre_imagen = md5(uniqid(rand(), true)) . '.' . $extension;
        
        $ruta_destino = $carpeta_imagenes . '/' . $nombre_imagen;

        if($cliente->FOTOGRAFIA_CLIENTE){
        $ruta_vieja = __DIR__ . "/../public/imgCliente/" . $cliente->FOTOGRAFIA_CLIENTE;

         if(file_exists($ruta_vieja)) {
            unlink($ruta_vieja);
       }

        }
        
        if (move_uploaded_file($_FILES['FOTOGRAFIA']['tmp_name'], $ruta_destino)) {
                $cliente->FOTOGRAFIA_CLIENTE = $nombre_imagen; 
                $cliente->guardar();
                ClienteModel::setAlerta('exito', 'Imagen guardado');
             } else {
              ClienteModel::setAlerta('error', 'Error al guardar');
            }
     
         }else{
            ClienteModel::setAlerta('error', 'Inserte una imagen');
         }
        }
        $alertas = ClienteModel::getAlertas();        
     
        $alertas = $alertas;
        $cliente = $cliente ?? '';
        require __DIR__ . '/../views/perfil/foto.php';
    }
  }
?> 
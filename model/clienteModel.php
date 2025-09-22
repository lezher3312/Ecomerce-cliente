<?php
   namespace Model;
    
   use model\ActiveRecord;
use PDO;

   require_once __DIR__ . '/ActiveRecord.php';

     class ClienteModel extends ActiveRecord
     {     
       protected static $tabla = 'cliente';
       protected static $columnasDB = ['ID', 'NOMBRE_COMPLETO', 'TELEFONO', 'DIRECCION', 
       'UBICACION', 'LONGITUD', 'LATITUD', 'EMAIL', 'USUARIO_PAGINA', 'PASSWORD_PAGINA', 'NIT',
     'DIRECCION_ENTREGA', 'FOTOGRAFIA_CLIENTE', 'FECHA_CREACION', 'ESTADO', 'TOKEN', 'CONFIRMADO'];

     public $ID;
     public $NOMBRE_COMPLETO;
     public $TELEFONO;
     public $DIRECCION;
     public $UBICACION;
     public $LONGITUD;
     public $LATITUD;
     public $EMAIL;
     public $USUARIO_PAGINA;
     PUBLIC $PASSWORD;
     public $PASSWORD_PAGINA;
     public $NIT;
     public $DIRECCION_ENTREGA;
     public $FOTOGRAFIA_CLIENTE;
     public $FECHA_CREACION;
     public $ESTADO;
     public $TOKEN;
     public $CONFIRMADO;

     public function __construct($args = [])
    {
        $this->ID = $args['id'] ?? null;
        $this->NOMBRE_COMPLETO = $args['NOMBRE_COMPLETO'] ?? '';
        $this->TELEFONO = $args['TELEFONO'] ?? '';
        $this->DIRECCION = $args['DIRECCION'] ?? '';
        $this->UBICACION = $args['UBICACION'] ?? '';
        $this->LONGITUD = 'LONGITUD';
        $this->LATITUD = 'LATITUD';
        $this->EMAIL = $args['EMAIL'] ?? '';
        $this->USUARIO_PAGINA = $args['USUARIO_PAGINA'] ?? '';
        $this->PASSWORD_PAGINA = $args['PASSWORD_PAGINA'] ?? '';
        $this->PASSWORD = $args['PASSWORD'] ?? '';
        $this->NIT = $args['NIT'] ?? '';
        $this->DIRECCION_ENTREGA = $args['DIRECCION_ENTREGA'] ?? '';
        $this->FOTOGRAFIA_CLIENTE = $args['FOTOGRAFIA_CLIENTE'] ?? '';
        $this->FECHA_CREACION = $args['FECHA_CREACION'] ?? '';
        $this->ESTADO = $args['ESTADO'] ?? 1;
        $this->TOKEN = $args['TOKEN'] ?? '';
        $this->CONFIRMADO = $args['CONFIRMADO'] ?? 0;
    }
     public function validarLogin() {
        if(!$this->USUARIO_PAGINA) {
            self::$alertas['error'][] = 'El Usuario es Obligatorio';
        }
  
        if(!$this->PASSWORD_PAGINA) {
            self::$alertas['error'][] = 'El Password no puede ir vacio';
        }
        return self::$alertas;

    }
    // Validación para cuentas nuevas
    public function validar_cuenta() {
        if(!$this->NOMBRE_COMPLETO) {
            self::$alertas['error'][] = 'El Nombre Completo es Obligatorio';
        }
        if(!$this->TELEFONO) {
            self::$alertas['error'][] = 'El Telefono es Obligatorio';
        }
         if(!$this->EMAIL) {
            self::$alertas['error'][] = 'El Email es Obligatorio';
        }
         if(!$this->USUARIO_PAGINA) {
            self::$alertas['error'][] = 'El Usuario es Obligatorio';
        }
         if(!$this->PASSWORD_PAGINA) {
            self::$alertas['error'][] = 'La Contraseña es Obligatoria es Obligatorio';
        }
         if(!$this->PASSWORD) {
            self::$alertas['error'][] = 'La Contraseña es Obligatoria';
        }
        if(strlen($this->PASSWORD) < 6 || strlen($this->PASSWORD_PAGINA) < 6) {
            self::$alertas['error'][] = 'El password debe contener al menos 6 caracteres';
        }
        if($this->PASSWORD !== $this->PASSWORD_PAGINA) {
            self::$alertas['error'][] = 'Los password son diferentes';
        }
        
        return self::$alertas;
    }

    public function hashPassword(): void{
        $this->PASSWORD_PAGINA = password_hash($this->PASSWORD_PAGINA, PASSWORD_BCRYPT);
    }

    public function crearToken(): void{
        $this->TOKEN = uniqid();
    }

    public function getfechaactual(): void{
        $this->FECHA_CREACION = date('Y-m-d');
    }

     // Valida un email
    public function validarEmail() {
        if(!$this->EMAIL) {
            self::$alertas['error'][] = 'El Email es Obligatorio';
        }
        if(!filter_var($this->EMAIL, FILTER_VALIDATE_EMAIL)) {
            self::$alertas['error'][] = 'Email no válido';
        }
        return self::$alertas;
    }

     }

?>
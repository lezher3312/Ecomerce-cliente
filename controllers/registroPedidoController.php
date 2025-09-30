<?php
// controllers/registroPedidoController.php
declare(strict_types=1);

class RegistroPedidoController
{
    private string $viewsPath;
    private string $basePath;
    private PDO $pdo;
    private RegistroPedidoModel $model;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        $root = dirname(__DIR__);
        $this->viewsPath = $root . '/views';

        $this->basePath = defined('BASE_PATH')
            ? rtrim(BASE_PATH, '/')
            : rtrim(dirname($_SERVER['SCRIPT_NAME']) ?: '', '/');

        require_once $root . '/config/conexion.php';
        require_once $root . '/model/registroPedidoModel.php';
        require_once $root . '/model/CarritoModel.php';

        $this->pdo   = Conexion::getConexion();
        $this->model = new RegistroPedidoModel($this->pdo);
    }

    // GET /registro/pedido
    public function index(): void
    {
        if ($this->resolverIdCliente()) {
            $this->redirect($this->basePath . '/cotizacion');
        }

        $basePath  = $this->basePath;
        $pageTitle = 'Registrarse para continuar';
        require $this->viewsPath . '/paginas/registroPedido.php';
    }

    // POST /registro/pedido
    public function registrar(): void
    {
        if ($this->resolverIdCliente()) {
            $this->redirect($this->basePath . '/cotizacion');
        }

        $nombre     = trim((string)($_POST['nombre_completo'] ?? ''));
        $email      = trim((string)($_POST['email'] ?? ''));
        $telefono   = trim((string)($_POST['telefono'] ?? ''));
        $usuario    = trim((string)($_POST['usuario'] ?? ''));
        $pass       = (string)($_POST['password'] ?? '');
        $direccion  = trim((string)($_POST['direccion'] ?? ''));

        $errores = [];
        if ($nombre === '')  $errores[] = 'El nombre es obligatorio.';
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errores[] = 'Email inválido.';
        if ($pass === '' || strlen($pass) < 6) $errores[] = 'La contraseña debe tener al menos 6 caracteres.';
        if ($usuario === '') $usuario = strstr($email, '@', true) ?: $email;

        if ($this->model->existeEmail($email))     $errores[] = 'El email ya está registrado.';
        if ($this->model->existeUsuario($usuario)) $errores[] = 'El usuario ya está en uso.';

        if ($errores) {
            $_SESSION['flash_registro_error'] = implode(' ', $errores);
            $_SESSION['form_registro_old'] = $_POST;
            $this->redirect($this->basePath . '/registro/pedido');
        }

        // Crear cliente
        $idCliente = $this->model->crearCliente([
            'nombre'         => $nombre,
            'telefono'       => $telefono,
            'email'          => $email,
            'usuario'        => $usuario,
            'passwordPlano'  => $pass,
            'direccion'      => $direccion,
        ]);

        if (!$idCliente) {
            $_SESSION['flash_registro_error'] = 'No se pudo crear la cuenta. Intenta de nuevo.';
            $_SESSION['form_registro_old'] = $_POST;
            $this->redirect($this->basePath . '/registro/pedido');
        }

        // Autenticar sesión
        $_SESSION['ID'] = (int)$idCliente;
        $_SESSION['cliente'] = [
            'ID'              => (int)$idCliente,
            'NOMBRE_COMPLETO' => $nombre,
            'EMAIL'           => $email,
            'TELEFONO'        => $telefono,
            'DIRECCION'       => $direccion,
        ];

        // Fusionar carrito invitado → BD
        if (!empty($_SESSION['cart']) && is_array($_SESSION['cart'])) {
            $carritoModel = new CarritoModel($this->pdo);
            $carritoModel->mergeGuestCartToDb((int)$idCliente, $_SESSION['cart']);
            $_SESSION['cart'] = [];
        }

        // Ir a COTIZACIÓN
        $this->redirect($this->basePath . '/cotizacion');
    }

    private function resolverIdCliente(): ?int
    {
        if (!empty($_SESSION['cliente']['ID'])) return (int)$_SESSION['cliente']['ID'];
        if (!empty($_SESSION['usuarios']['id'])) return (int)$_SESSION['usuarios']['id'];
        if (!empty($_SESSION['ID']))             return (int)$_SESSION['ID'];
        return null;
    }

    private function redirect(string $to, int $code = 302): void
    {
        header("Location: {$to}", true, $code);
        exit;
    }
}

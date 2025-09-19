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
        // Si ya hay sesión, saltar registro
        if ($this->resolverIdCliente()) {
            $this->redirect($this->basePath . '/envio');
        }

        $basePath  = $this->basePath;
        $pageTitle = 'Registrarse para continuar';
        require $this->viewsPath . '/paginas/registroPedido.php';
    }

    // POST /registro/pedido
    public function registrar(): void
    {
        if ($this->resolverIdCliente()) {
            $this->redirect($this->basePath . '/envio');
        }

        $nombre     = trim((string)($_POST['nombre_completo'] ?? ''));
        $email      = trim((string)($_POST['email'] ?? ''));
        $telefono   = trim((string)($_POST['telefono'] ?? ''));
        $usuario    = trim((string)($_POST['usuario'] ?? ''));
        $pass       = (string)($_POST['password'] ?? '');
        $nit        = trim((string)($_POST['nit'] ?? ''));
        $direccion  = trim((string)($_POST['direccion'] ?? '')); // 👈 ahora pedimos DIRECCIÓN (no dirección de entrega)

        $errores = [];
        if ($nombre === '')  $errores[] = 'El nombre es obligatorio.';
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errores[] = 'Email inválido.';
        if ($pass === '' || strlen($pass) < 6) $errores[] = 'La contraseña debe tener al menos 6 caracteres.';
        if ($usuario === '') $usuario = strstr($email, '@', true) ?: $email;

        if ($this->model->existeEmail($email))     $errores[] = 'El email ya está registrado.';
        if ($this->model->existeUsuario($usuario)) $errores[] = 'El usuario ya está en uso.';

        // Procesar foto (opcional) — acepta cualquier image/*
        $fotoRelPath = '';
        if (!empty($_FILES['foto']) && is_uploaded_file($_FILES['foto']['tmp_name'])) {
            $file  = $_FILES['foto'];
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime  = $finfo->file($file['tmp_name']); // ej: image/jpeg, image/png, image/heic, image/svg+xml
            if (strpos($mime, 'image/') !== 0) {
                $errores[] = 'El archivo subido no es una imagen válida.';
            } else {
                $map = [
                    'image/jpeg'     => 'jpg',
                    'image/pjpeg'    => 'jpg',
                    'image/png'      => 'png',
                    'image/gif'      => 'gif',
                    'image/webp'     => 'webp',
                    'image/bmp'      => 'bmp',
                    'image/svg+xml'  => 'svg',
                    'image/x-icon'   => 'ico',
                    'image/tiff'     => 'tif',
                    'image/heic'     => 'heic',
                    'image/heif'     => 'heif',
                ];
                $ext = $map[$mime] ?? strtolower(pathinfo($file['name'], PATHINFO_EXTENSION) ?: 'img');

                $root      = dirname(__DIR__);
                $uploadDir = $root . '/public/imgCliente';
                if (!is_dir($uploadDir)) {
                    @mkdir($uploadDir, 0775, true);
                }

                $fileName = 'cli_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . preg_replace('/[^a-z0-9.]+/i', '', $ext);
                $destAbs  = $uploadDir . '/' . $fileName;

                if (move_uploaded_file($file['tmp_name'], $destAbs)) {
                    $fotoRelPath = '/public/imgCliente/' . $fileName; // ruta pública
                }
            }
        }

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
            'nit'            => $nit,
            'direccion'      => $direccion,
            'foto'           => $fotoRelPath,
        ]);

        if (!$idCliente) {
            $_SESSION['flash_registro_error'] = 'No se pudo crear la cuenta. Intenta de nuevo.';
            $_SESSION['form_registro_old'] = $_POST;
            $this->redirect($this->basePath . '/registro/pedido');
        }

        // Autenticar sesión (guardar con clave 'ID')
        $_SESSION['ID'] = (int)$idCliente;
        $_SESSION['cliente'] = [
            'ID'                 => (int)$idCliente,          // 👈 ahora 'ID'
            'NOMBRE_COMPLETO'    => $nombre,
            'EMAIL'              => $email,
            'NIT'                => $nit,
            'DIRECCION'          => $direccion,
            'FOTOGRAFIA_CLIENTE' => $fotoRelPath,
        ];

        // Fusionar carrito invitado → BD
        if (!empty($_SESSION['cart']) && is_array($_SESSION['cart'])) {
            $carritoModel = new CarritoModel($this->pdo);
            $carritoModel->mergeGuestCartToDb((int)$idCliente, $_SESSION['cart']);
            $_SESSION['cart'] = [];
        }

        // Continuar a /envio
        $this->redirect($this->basePath . '/envio');
    }

    private function resolverIdCliente(): ?int
    {
        // Corregido: usar 'ID' y devolver ese mismo campo
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

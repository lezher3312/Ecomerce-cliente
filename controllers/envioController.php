<?php
// controllers/envioController.php
declare(strict_types=1);

class EnvioController
{
    private string $viewsPath;
    private string $basePath;
    private PDO $pdo;
    private EnvioModel $model;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        $root = dirname(__DIR__);
        $this->viewsPath = $root . '/views';

        $this->basePath = defined('BASE_PATH')
            ? rtrim(BASE_PATH, '/')
            : rtrim(dirname($_SERVER['SCRIPT_NAME']) ?: '', '/');

        require_once $root . '/config/conexion.php';
        require_once $root . '/model/envioModel.php';
        require_once $root . '/model/CarritoModel.php';

        $this->pdo   = Conexion::getConexion();
        $this->model = new EnvioModel($this->pdo);
    }

    // GET /envio
    public function index(): void
    {
        $idCliente = $this->resolverIdCliente();
        if (!$idCliente) {
            $this->redirect($this->basePath . '/registro/pedido');
        }

        // Datos del cliente
        $cliente = $this->model->getCliente((int)$idCliente);

        // Parsear UBICACION "PAIS - REGION"
        $paisActual = 'Guatemala';
        $regionActual = '';
        if (!empty($cliente['UBICACION'])) {
            $parts = explode(' - ', (string)$cliente['UBICACION'], 2);
            $paisActual   = trim($parts[0] ?? 'Guatemala');
            $regionActual = trim($parts[1] ?? '');
        }

        // Resumen carrito
        $carritoModel = new CarritoModel($this->pdo);
        $idCot = $carritoModel->getCotizacionAbierta((int)$idCliente);
        $items = $idCot ? $carritoModel->getItems($idCot) : [];
        $count = array_sum(array_map(fn($x) => (int)$x['cantidad'], $items));
        $subtotal = array_sum(array_map(fn($x) => (float)$x['subtotal'], $items));
        $total = $subtotal;
        $cartCount = $count;

        $basePath  = $this->basePath;
        $pageTitle = 'Datos de envío';
        require $this->viewsPath . '/paginas/envio.php';
    }

    // POST /envio
    public function guardar(): void
    {
        $idCliente = $this->resolverIdCliente();
        if (!$idCliente) {
            $this->redirect($this->basePath . '/registro/pedido');
        }

        $nombre     = trim((string)($_POST['nombre_completo'] ?? ''));
        $direccion  = trim((string)($_POST['direccion'] ?? ''));
        $telefono   = trim((string)($_POST['telefono'] ?? ''));  // NUEVO
        $nit        = trim((string)($_POST['nit'] ?? ''));

        $pais       = trim((string)($_POST['pais'] ?? 'Guatemala'));
        $region     = trim((string)($_POST['region'] ?? ''));
        $dirEntrega = trim((string)($_POST['direccion_entrega'] ?? ''));

        $lat        = isset($_POST['latitud'])  ? (string)$_POST['latitud']  : '';
        $lon        = isset($_POST['longitud']) ? (string)$_POST['longitud'] : '';

        $errores = [];
        if ($nombre === '')    $errores[] = 'El nombre es obligatorio.';
        if ($direccion === '') $errores[] = 'La dirección es obligatoria.';
        if ($pais === '')      $errores[] = 'Selecciona un país.';

        if ($errores) {
            $_SESSION['flash_envio_error'] = implode(' ', $errores);
            $_SESSION['form_envio_old'] = $_POST;
            $this->redirect($this->basePath . '/envio');
        }

        $ubicacion = $pais . ' - ' . $region;

        $ok = $this->model->updateClienteEnvio((int)$idCliente, [
            'nombre'            => $nombre,
            'direccion'         => $direccion,
            'telefono'          => $telefono,       // NUEVO
            'nit'               => $nit,
            'direccion_entrega' => $dirEntrega,
            'ubicacion'         => $ubicacion,
            'latitud'           => $lat !== '' ? (float)$lat : null,
            'longitud'          => $lon !== '' ? (float)$lon : null,
        ]);

        if (!$ok) {
            $_SESSION['flash_envio_error'] = 'No se pudo guardar la información de envío.';
            $_SESSION['form_envio_old']    = $_POST;
        } else {
            $_SESSION['flash_envio_ok']    = 'Información de envío guardada correctamente.';
        }

        $this->redirect($this->basePath . '/envio');
    }

    private function resolverIdCliente(): ?int
    {
        // Usar siempre 'ID' cuando provenga del cliente
        if (!empty($_SESSION['cliente']['ID'])) return (int)$_SESSION['cliente']['ID'];
        if (!empty($_SESSION['usuarios']['id'])) return (int)$_SESSION['usuarios']['id'];
        if (!empty($_SESSION['ID']))            return (int)$_SESSION['ID'];
        return null;
    }

    private function redirect(string $to, int $code = 302): void
    {
        header("Location: {$to}", true, $code);
        exit;
    }
}

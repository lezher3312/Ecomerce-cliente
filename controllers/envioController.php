<?php
// controllers/EnvioController.php
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
            ? rtrim((string)BASE_PATH, '/')
            : rtrim((string)(dirname($_SERVER['SCRIPT_NAME']) ?: ''), '/');
        if ($this->basePath !== '' && $this->basePath[0] !== '/') {
            $this->basePath = '/' . $this->basePath;
        }

        require_once $root . '/config/conexion.php';
        require_once $root . '/model/envioModel.php';
        require_once $root . '/model/CarritoModel.php';

        $this->pdo   = Conexion::getConexion();
        $this->model = new EnvioModel($this->pdo);
    }

    public function index()
    {
        $idCliente = $this->resolverIdCliente();
        if (!$idCliente) {
            $this->redirect($this->basePath . '/registro/pedido');
        }

        $cliente = $this->model->getCliente((int)$idCliente) ?: [];

        $paisActual = 'Guatemala';
        $regionActual = '';
        if (!empty($cliente['UBICACION'])) {
            $parts = explode(' - ', (string)$cliente['UBICACION'], 2);
            $paisActual   = trim($parts[0] ?? 'Guatemala');
            $regionActual = trim($parts[1] ?? '');
        }

        $carritoModel = new CarritoModel($this->pdo);
        $idCot    = $carritoModel->getCotizacionAbierta((int)$idCliente);
        $items    = $idCot ? $carritoModel->getItems($idCot) : [];
        $count    = array_sum(array_map(fn($x) => (int)($x['cantidad'] ?? 0), $items));
        $subtotal = array_sum(array_map(fn($x) => (float)($x['subtotal'] ?? 0.0), $items));
        $total    = $subtotal;
        $cartCount= $count;

        $basePath  = $this->basePath;
        $pageTitle = 'Datos de envío';

        require $this->viewsPath . '/paginas/envio.php';
    }

    public function guardar()
    {
        $idCliente = $this->resolverIdCliente();
        if (!$idCliente) {
            $this->fail('Debes iniciar sesión para continuar.', $this->basePath . '/registro/pedido');
        }

        $nombre     = trim((string)($_POST['nombre_completo'] ?? ''));
        $direccion  = trim((string)($_POST['direccion'] ?? ''));
        $telefono   = trim((string)($_POST['telefono'] ?? ''));
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
            $_SESSION['form_envio_old']    = $_POST;

            if ($this->isAjax()) {
                $this->json(['ok' => false, 'message' => $_SESSION['flash_envio_error']]);
            }
            $this->redirect($this->basePath . '/envio');
        }

        $ubicacion = $pais . ' - ' . $region;

        try {
            $ok = $this->model->updateClienteEnvio((int)$idCliente, [
                'nombre'            => $nombre,
                'direccion'         => $direccion,
                'telefono'          => $telefono,
                'nit'               => $nit,
                'direccion_entrega' => $dirEntrega,
                'ubicacion'         => $ubicacion,
                'latitud'           => ($lat !== '' ? (float)$lat : null),
                'longitud'          => ($lon !== '' ? (float)$lon : null),
            ]);
        } catch (Throwable $e) {
            $ok = false;
            error_log('Error updateClienteEnvio: ' . $e->getMessage());
        }

        if (!$ok) {
            $_SESSION['flash_envio_error'] = 'No se pudo guardar la información de envío.';
            $_SESSION['form_envio_old']    = $_POST;

            if ($this->isAjax()) {
                $this->json(['ok' => false, 'message' => $_SESSION['flash_envio_error']]);
            }
            $this->redirect($this->basePath . '/envio');
        }

        // OK -> pasar cotización a estado 2 (Falta de impuesto)
        $carritoModel = new CarritoModel($this->pdo);
        $idCot = $carritoModel->getOrCreateCotizacionAbierta((int)$idCliente);
        if ($idCot) {
            $carritoModel->setEstado($idCot, 2);
        }

        $_SESSION['flash_envio_ok'] = 'Información de envío guardada correctamente.';

        if ($this->isAjax()) {
            $this->json([
                'ok'      => true,
                'message' => $_SESSION['flash_envio_ok'],
                'estado'  => 2,
            ]);
        }

        $this->redirect($this->basePath . '/cotizacion');
    }

    /* ================= Helpers ================= */

    private function resolverIdCliente(): ?int
    {
        if (!empty($_SESSION['cliente']['ID']))  return (int)$_SESSION['cliente']['ID'];
        if (!empty($_SESSION['usuarios']['id'])) return (int)$_SESSION['usuarios']['id'];
        if (!empty($_SESSION['ID']))             return (int)$_SESSION['ID'];
        return null;
    }

    private function isAjax(): bool
    {
        if (isset($_POST['ajax']) && $_POST['ajax'] === '1') return true;
        $hdr = $_SERVER['HTTP_X_REQUESTED_WITH'] ?? '';
        return (strtolower($hdr) === 'xmlhttprequest');
    }

    private function json(array $payload)
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload);
        exit;
    }

    private function fail(string $msg, string $redirectTo = '')
    {
        if ($this->isAjax()) {
            $this->json(['ok' => false, 'message' => $msg]);
        }
        $_SESSION['flash_envio_error'] = $msg;
        $this->redirect($redirectTo ?: ($this->basePath . '/envio'));
    }

    private function redirect(string $to, int $code = 302)
    {
        header("Location: {$to}", true, $code);
        exit;
    }
}

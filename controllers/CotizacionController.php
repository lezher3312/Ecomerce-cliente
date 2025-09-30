<?php
// controllers/CotizacionController.php
declare(strict_types=1);

class CotizacionController
{
    private string $viewsPath;
    private string $basePath;
    private PDO $pdo;
    private CarritoModel $carrito;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        $root = dirname(__DIR__);
        $this->viewsPath = $root . '/views';
        $this->basePath  = defined('BASE_PATH') ? rtrim(BASE_PATH, '/') : rtrim(dirname($_SERVER['SCRIPT_NAME']) ?: '', '/');

        require_once $root . '/config/conexion.php';
        require_once $root . '/model/CarritoModel.php';

        $this->pdo     = Conexion::getConexion();
        $this->carrito = new CarritoModel($this->pdo);
    }

    private function requireClienteId(): int
    {
        $id = null;
        if (!empty($_SESSION['cliente']['ID'])) $id = (int)$_SESSION['cliente']['ID'];
        elseif (!empty($_SESSION['usuarios']['id'])) $id = (int)$_SESSION['usuarios']['id'];
        elseif (!empty($_SESSION['ID'])) $id = (int)$_SESSION['ID'];

        if (!$id) {
            header('Location: ' . $this->basePath . '/login?next=/cotizacion');
            exit;
        }
        return $id;
    }

    // GET /cotizacion
    public function index(): void
    {
        $idCliente = $this->requireClienteId();
        $idCot     = $this->carrito->getOrCreateCotizacionAbierta($idCliente);

        $meta  = $this->carrito->getCotizacionMeta($idCot);
        $items = $this->carrito->getItems($idCot);

        $totalBase = (float)$meta['TOTAL_COTIZACION'];
        $estado    = (int)$meta['ESTADO'];
        $tipo      = (int)$meta['TIPO_COTIZACION'];

        $totalConImpuestos = (float)($meta['TOTAL_CON_IMPUESTOS'] ?? 0);
        $impuestosYOtros   = ($estado >= 3 && $totalConImpuestos > 0)
            ? max(0, $totalConImpuestos - $totalBase)
            : 0.0;

        $basePath  = $this->basePath;
        $pageTitle = 'Resumen de cotización';

        require $this->viewsPath . '/paginas/cotizacion.php';
    }

    // POST /cotizacion/tipo
    public function actualizarTipo(): void
    {
        $idCliente = $this->requireClienteId();
        $idCot     = $this->carrito->getOrCreateCotizacionAbierta($idCliente);

        $tipo = (int)($_POST['tipo_cotizacion'] ?? 1);
        $this->carrito->setTipoCotizacion($idCot, $tipo);

        header('Location: ' . $this->basePath . '/cotizacion');
        exit;
    }

    // POST /cotizacion/pedir -> 2 (falta impuesto)
    public function pedir(): void
    {
        $idCliente = $this->requireClienteId();
        $idCot     = $this->carrito->getOrCreateCotizacionAbierta($idCliente);

        $this->carrito->setEstado($idCot, 2);
        header('Location: ' . $this->basePath . '/cotizacion');
        exit;
    }

    // POST /cotizacion/procesar -> 3 (procesado)
    public function procesar(): void
    {
        $idCliente = $this->requireClienteId();
        $idCot     = $this->carrito->getOrCreateCotizacionAbierta($idCliente);

        $totalConImpuestos = (float)($_POST['total_con_impuestos'] ?? 0);
        if ($totalConImpuestos > 0) {
            $this->carrito->setTotalConImpuestos($idCot, $totalConImpuestos);
        }
        $this->carrito->setEstado($idCot, 3);

        header('Location: ' . $this->basePath . '/cotizacion');
        exit;
    }

    // POST /cotizacion/confirmar -> 4 (confirmado) → /envio
    public function confirmar(): void
    {
        $idCliente = $this->requireClienteId();
        $idCot     = $this->carrito->getOrCreateCotizacionAbierta($idCliente);

        $this->carrito->setEstado($idCot, 4);
        header('Location: ' . $this->basePath . '/envio');
        exit;
    }

    // POST /cotizacion/anular -> 5 (anulado) → /carrito
    public function anular(): void
    {
        $idCliente = $this->requireClienteId();
        $idCot     = $this->carrito->getOrCreateCotizacionAbierta($idCliente);

        $this->carrito->setEstado($idCot, 5);
        header('Location: ' . $this->basePath . '/carrito');
        exit;
    }
}

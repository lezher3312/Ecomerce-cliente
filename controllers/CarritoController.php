<?php
// controllers/CarritoController.php
class CarritoController
{
    private string $viewsPath;
    private string $publicPath;
    private string $basePath;
    private PDO $pdo;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        $root = dirname(__DIR__);
        $this->viewsPath  = $root . '/views';
        $this->publicPath = $root . '/public';

        $this->basePath = defined('BASE_PATH')
            ? rtrim(BASE_PATH, '/')
            : rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');

        require_once $root . '/config/conexion.php';
        require_once $root . '/model/CarritoModel.php';

        $this->pdo = Conexion::getConexion();
    }

    // GET /carrito
    public function index(): void
    {
        $model     = new CarritoModel($this->pdo);
        $idCliente = $this->resolverIdCliente();

        // Si NO hay sesión de cliente: mostrar carrito vacío (sin redirigir)
        if (!$idCliente) {
            $items    = [];
            $count    = 0;
            $subtotal = 0.0;
            $total    = 0.0;

            // Header / layout
            $cartCount = 0;
            $basePath  = $this->basePath;
            $pageTitle = 'Mi carrito';
            $cssExtra  = $this->basePath . '/public/css/carrito.css';

            require $this->viewsPath . '/layouts/head.php';
            require $this->viewsPath . '/layouts/header.php';
            require $this->viewsPath . '/paginas/carrito.php'; // tu vista ya muestra el mensaje/vínculos
            require $this->viewsPath . '/layouts/footer.php';
            return;
        }

        // Con cliente: cargar (o no) su cotización abierta y pintar items
        $idCot    = $model->getCotizacionAbierta($idCliente); // puede ser null
        $items    = $idCot ? $model->getItems($idCot) : [];
        $count    = array_sum(array_map(fn($x) => (int)$x['cantidad'], $items));
        $subtotal = array_sum(array_map(fn($x) => (float)$x['subtotal'], $items));
        $total    = $subtotal;

        // Header / layout
        $cartCount = $count;
        $basePath  = $this->basePath;
        $pageTitle = 'Mi carrito';
        $cssExtra  = $this->basePath . '/public/css/carrito.css';

        require $this->viewsPath . '/layouts/head.php';
        require $this->viewsPath . '/layouts/header.php';
        require $this->viewsPath . '/paginas/carrito.php';
        require $this->viewsPath . '/layouts/footer.php';
    }

    // POST /carrito  => agregar/actualizar línea en det_cotizacion_producto
    public function agregar(): void
    {
        $idProducto = filter_input(INPUT_POST, 'id_producto', FILTER_VALIDATE_INT);
        $cantidad   = filter_input(INPUT_POST, 'cantidad', FILTER_VALIDATE_INT);
        $cantidad   = ($cantidad && $cantidad > 0) ? $cantidad : 1;

        // Cliente obligatorio para agregar
        $idCliente = $this->resolverIdCliente();
        if (!$idCliente) {
            $_SESSION['flash_cart'] = 'Debes iniciar sesión para agregar al carrito.';
            $this->redirect($this->basePath . '/login?next=/catalogo');
        }

        if (!$idProducto) {
            $_SESSION['flash_cart'] = 'Producto inválido.';
            $this->redirect($this->basePath . '/catalogo?err=producto');
        }

        $model = new CarritoModel($this->pdo);

        // Obtener o crear cotización abierta
        $idCot = $model->getOrCreateCotizacionAbierta($idCliente);

        // Agregar/actualizar detalle
        $ok = $model->addOrUpdateItem($idCot, $idProducto, $cantidad);

        if ($ok) {
            $model->recalcularTotal($idCot);
            $_SESSION['flash_cart'] = 'Producto agregado al carrito.';
            $this->redirect($this->basePath . '/carrito', 303);
        } else {
            $_SESSION['flash_cart'] = 'No se pudo agregar el producto.';
            $this->redirect($this->basePath . '/catalogo?err=agregar');
        }
    }

    private function resolverIdCliente(): ?int
    {
        if (!empty($_SESSION['cliente']['ID_CLIENTE'])) {
            return (int)$_SESSION['cliente']['ID_CLIENTE'];
        }
        if (!empty($_SESSION['usuarios']['id_cliente'])) {
            return (int)$_SESSION['usuarios']['id_cliente'];
        }
        if (!empty($_SESSION['ID_CLIENTE'])) {
            return (int)$_SESSION['ID_CLIENTE'];
        }
        return null;
    }

    private function redirect(string $to, int $code = 302): void
    {
        header("Location: {$to}", true, $code);
        exit;
    }
}

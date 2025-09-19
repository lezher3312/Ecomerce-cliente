<?php
// controllers/CarritoController.php
declare(strict_types=1);

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
            : rtrim(dirname($_SERVER['SCRIPT_NAME']) ?: '', '/');

        require_once $root . '/config/conexion.php';
        require_once $root . '/model/CarritoModel.php';

        $this->pdo = Conexion::getConexion();
    }

    // GET /carrito
    public function index(): void
    {
        $model     = new CarritoModel($this->pdo);
        $idCliente = $this->resolverIdCliente();

        // Si hay sesión y existía carrito de invitado, fusión a BD y limpiar sesión
        if ($idCliente && !empty($_SESSION['cart']) && is_array($_SESSION['cart'])) {
            $model->mergeGuestCartToDb((int)$idCliente, $_SESSION['cart']);
            $_SESSION['cart'] = [];
        }

        if (!$idCliente) {
            // Invitado: pintar desde sesión
            $guestCart = (!empty($_SESSION['cart']) && is_array($_SESSION['cart'])) ? $_SESSION['cart'] : [];
            if ($guestCart) {
                [$items, $count, $subtotal, $total] = $model->sessionCartToItems($guestCart);
            } else {
                $items = []; $count = 0; $subtotal = 0.0; $total = 0.0;
            }
        } else {
            // Autenticado: leer desde BD (cotización WEB abierta)
            $idCot    = $model->getCotizacionAbierta((int)$idCliente); // puede ser null
            $items    = $idCot ? $model->getItems($idCot) : [];
            $count    = array_sum(array_map(fn($x) => (int)$x['cantidad'], $items));
            $subtotal = array_sum(array_map(fn($x) => (float)$x['subtotal'], $items));
            $total    = $subtotal;
        }

        // Header / layout
        $cartCount = $count ?? 0;
        $basePath  = $this->basePath;
        $pageTitle = 'Mi carrito';
        $cssExtra  = $this->basePath . '/public/css/carrito.css';

        require $this->viewsPath . '/layouts/head.php';
        require $this->viewsPath . '/layouts/header.php';
        require $this->viewsPath . '/paginas/carrito.php';
        require $this->viewsPath . '/layouts/footer.php';
    }

    // POST /carrito/agregar  (agregar/actualizar línea)
    public function agregar(): void
    {
        $idProducto = filter_input(INPUT_POST, 'id_producto', FILTER_VALIDATE_INT);
        $cantidad   = filter_input(INPUT_POST, 'cantidad', FILTER_VALIDATE_INT);
        $cantidad   = ($cantidad && $cantidad > 0) ? (int)$cantidad : 1;

        if (!$idProducto) {
            $_SESSION['flash_cart'] = 'Producto inválido.';
            $this->redirect($this->basePath . '/catalogo?err=producto');
        }

        $model     = new CarritoModel($this->pdo);
        $idCliente = $this->resolverIdCliente();

        if (!$idCliente) {
            // Invitado → guardar en sesión
            if (empty($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
                $_SESSION['cart'] = [];
            }
            $_SESSION['cart'][(int)$idProducto] = (int)(($_SESSION['cart'][(int)$idProducto] ?? 0) + $cantidad);

            $_SESSION['flash_cart'] = 'Producto agregado al carrito.';
            $this->redirect($this->basePath . '/carrito', 303);
        }

        // Autenticado → guardar en BD
        $idCot = $model->getOrCreateCotizacionAbierta((int)$idCliente);
        $ok    = $model->addOrUpdateItem($idCot, (int)$idProducto, $cantidad);

        if ($ok) {
            $model->recalcularTotal($idCot);
            $_SESSION['flash_cart'] = 'Producto agregado al carrito.';
            $this->redirect($this->basePath . '/carrito', 303);
        } else {
            $_SESSION['flash_cart'] = 'No se pudo agregar el producto.';
            $this->redirect($this->basePath . '/catalogo?err=agregar');
        }
    }

    // POST /carrito/actualizar  (cambiar cantidad)
    public function actualizar(): void
    {
        $idProducto = filter_input(INPUT_POST, 'id_producto', FILTER_VALIDATE_INT);
        $cantidad   = filter_input(INPUT_POST, 'cantidad', FILTER_VALIDATE_INT);
        $cantidad   = ($cantidad && $cantidad > 0) ? (int)$cantidad : 1;

        if (!$idProducto) {
            $_SESSION['flash_cart'] = 'Producto inválido.';
            $this->redirect($this->basePath . '/carrito');
        }

        $model     = new CarritoModel($this->pdo);
        $idCliente = $this->resolverIdCliente();

        if ($idCliente) {
            $idCot = $model->getOrCreateCotizacionAbierta((int)$idCliente);
            $ok    = $model->updateQty($idCot, (int)$idProducto, $cantidad);
            if ($ok) {
                $model->recalcularTotal($idCot);
            }
        } else {
            $model->sessionUpdateQty((int)$idProducto, $cantidad);
        }

        $_SESSION['flash_cart'] = 'Cantidad actualizada.';
        $this->redirect($this->basePath . '/carrito', 303);
    }

    // POST /carrito/eliminar  (eliminar línea)
    public function eliminar(): void
    {
        $idProducto = filter_input(INPUT_POST, 'id_producto', FILTER_VALIDATE_INT);

        if (!$idProducto) {
            $_SESSION['flash_cart'] = 'Producto inválido.';
            $this->redirect($this->basePath . '/carrito');
        }

        $model     = new CarritoModel($this->pdo);
        $idCliente = $this->resolverIdCliente();

        if ($idCliente) {
            $idCot = $model->getCotizacionAbierta((int)$idCliente);
            if ($idCot) {
                $model->removeItem($idCot, (int)$idProducto);
                $model->recalcularTotal($idCot);
            }
        } else {
            $model->sessionRemoveItem((int)$idProducto);
        }

        $_SESSION['flash_cart'] = 'Producto eliminado del carrito.';
        $this->redirect($this->basePath . '/carrito', 303);
    }

    private function resolverIdCliente(): ?int
    {
        // Ahora buscamos siempre 'ID' cuando viene de la tabla/objeto cliente
        if (!empty($_SESSION['cliente']['ID'])) {
            return (int)$_SESSION['cliente']['ID'];
        }
        // Si guardas el ID del cliente dentro de usuarios, usa 'id'
        if (!empty($_SESSION['usuarios']['id'])) {
            return (int)$_SESSION['usuarios']['id'];
        }
        // Fallback genérico a $_SESSION['ID']
        if (!empty($_SESSION['ID'])) {
            return (int)$_SESSION['ID'];
        }
        return null;
    }

    private function redirect(string $to, int $code = 302): void
    {
        header("Location: {$to}", true, $code);
        exit;
    }
}

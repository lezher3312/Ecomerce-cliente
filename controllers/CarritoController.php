<?php
// controllers/CarritoController.php
declare(strict_types=1);

class CarritoController
{
    private string $viewsPath;
    private string $basePath;
    private PDO $pdo;
    private CarritoModel $carritoModel;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        $root = dirname(__DIR__);
        $this->viewsPath = $root . '/views';
        $this->basePath  = defined('BASE_PATH') ? rtrim(BASE_PATH, '/') : rtrim(dirname($_SERVER['SCRIPT_NAME']) ?: '', '/');

        require_once $root . '/config/conexion.php';
        require_once $root . '/model/CarritoModel.php';

        $this->pdo = Conexion::getConexion();
        $this->carritoModel = new CarritoModel($this->pdo);
    }

    // GET /carrito
    public function index(): void
    {
        $guestCart = $_SESSION['cart'] ?? [];
        [$items, $count, $subtotal, $total] = $this->carritoModel->sessionCartToItems($guestCart);

        $basePath  = $this->basePath;
        $cartCount = $count;
        $pageTitle = 'Mi carrito';

        require $this->viewsPath . '/paginas/carrito.php';
    }

    // POST /carrito/agregar
    public function agregar(): void
    {
        $idProducto = (int)($_POST['id_producto'] ?? 0);
        $cantidad   = max(1, (int)($_POST['cantidad'] ?? 1));

        if ($idProducto <= 0) {
            $_SESSION['flash_cart'] = 'Producto inválido.';
            header('Location: ' . $this->basePath . '/carrito');
            exit;
        }

        // Añadir/actualizar en sesión
        if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        if (isset($_SESSION['cart'][$idProducto])) {
            // Soporta ambos formatos
            if (is_array($_SESSION['cart'][$idProducto])) {
                $_SESSION['cart'][$idProducto]['cantidad'] = (int)($_SESSION['cart'][$idProducto]['cantidad'] ?? 0) + $cantidad;
            } else {
                $_SESSION['cart'][$idProducto] = (int)$_SESSION['cart'][$idProducto] + $cantidad;
            }
        } else {
            // Guarda también info útil para el render
            $p = $this->fetchProductoSimple($idProducto);
            if ($p) {
                $_SESSION['cart'][$idProducto] = [
                    'cantidad' => $cantidad,
                    'precio'   => (float)$p['precio'],
                    'nombre'   => (string)$p['nombre'],
                    'imagen'   => $p['imagen'],
                ];
            } else {
                // fallback mínimo
                $_SESSION['cart'][$idProducto] = $cantidad;
            }
        }

        $_SESSION['flash_cart'] = 'Producto agregado al carrito.';
        header('Location: ' . $this->basePath . '/carrito');
        exit;
    }

    private function fetchProductoSimple(int $idProducto): ?array
    {
        $st = $this->pdo->prepare(
            "SELECT ID_PRODUCTO, NOMBRE_PRODUCTO, PRECIO, FOTOGRAFIA_PRODUCTO
               FROM producto WHERE ID_PRODUCTO = :p LIMIT 1"
        );
        $st->execute([':p' => $idProducto]);
        $r = $st->fetch(PDO::FETCH_ASSOC);
        if (!$r) return null;
        return [
            'id'     => (int)$r['ID_PRODUCTO'],
            'nombre' => (string)$r['NOMBRE_PRODUCTO'],
            'precio' => (float)$r['PRECIO'],
            'imagen' => $r['FOTOGRAFIA_PRODUCTO'] ?: null,
        ];
    }
}

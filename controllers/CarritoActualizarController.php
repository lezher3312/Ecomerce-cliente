<?php
// controllers/CarritoActualizarController.php
declare(strict_types=1);

class CarritoActualizarController
{
    private string $basePath;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $this->basePath = defined('BASE_PATH') ? rtrim(BASE_PATH, '/') : rtrim(dirname($_SERVER['SCRIPT_NAME']) ?: '', '/');
    }

    // POST /carrito/actualizar
    public function actualizar(): void
    {
        $idProducto = (int)($_POST['id_producto'] ?? 0);
        $cantidad   = max(1, (int)($_POST['cantidad'] ?? 1));

        if ($idProducto > 0 && !empty($_SESSION['cart'])) {
            if (isset($_SESSION['cart'][$idProducto])) {
                if (is_array($_SESSION['cart'][$idProducto])) {
                    $_SESSION['cart'][$idProducto]['cantidad'] = $cantidad;
                } else {
                    $_SESSION['cart'][$idProducto] = $cantidad;
                }
                $_SESSION['flash_cart'] = 'Cantidad actualizada.';
            }
        }

        header('Location: ' . $this->basePath . '/carrito');
        exit;
    }

    // POST /carrito/eliminar
    public function eliminar(): void
    {
        $idProducto = (int)($_POST['id_producto'] ?? 0);
        if ($idProducto > 0 && !empty($_SESSION['cart'][$idProducto])) {
            unset($_SESSION['cart'][$idProducto]);
            $_SESSION['flash_cart'] = 'Producto eliminado del carrito.';
        }

        header('Location: ' . $this->basePath . '/carrito');
        exit;
    }
}

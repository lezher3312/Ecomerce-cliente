<?php
// controllers/CarritoActualizarController.php
declare(strict_types=1);

class CarritoActualizarController
{
    private string $viewsPath;
    private string $basePath;
    private PDO $pdo;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $root = dirname(__DIR__);
        $this->viewsPath = $root . '/views';

        $this->basePath = defined('BASE_PATH')
            ? rtrim(BASE_PATH, '/')
            : rtrim(dirname($_SERVER['SCRIPT_NAME']) ?: '', '/');

        require_once $root . '/config/conexion.php';
        require_once $root . '/model/CarritoModel.php';

        $this->pdo = Conexion::getConexion();
    }

    /**
     * POST /carrito/actualizar  (cambiar cantidad de un producto)
     */
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
            $idCot = $model->getOrCreateCotizacionAbierta($idCliente);
            $ok    = $model->updateQty($idCot, (int)$idProducto, $cantidad);
            if ($ok) {
                $model->recalcularTotal($idCot);
            }
        } else {
            // Invitado (SESSION)
            $model->sessionUpdateQty((int)$idProducto, $cantidad);
        }

        $_SESSION['flash_cart'] = 'Cantidad actualizada.';
        $this->redirect($this->basePath . '/carrito', 303);
    }

    /**
     * POST /carrito/eliminar  (eliminar producto del carrito)
     */
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
            $idCot = $model->getCotizacionAbierta($idCliente);
            if ($idCot) {
                $model->removeItem($idCot, (int)$idProducto);
                $model->recalcularTotal($idCot);
            }
        } else {
            // Invitado (SESSION)
            $model->sessionRemoveItem((int)$idProducto);
        }

        $_SESSION['flash_cart'] = 'Producto eliminado del carrito.';
        $this->redirect($this->basePath . '/carrito', 303);
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

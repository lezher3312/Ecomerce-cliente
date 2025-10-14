<?php
// controllers/CotizacionController.php
declare(strict_types=1);

class CotizacionController
{
    private string $viewsPath;
    private string $basePath;
    private PDO $pdo;
    private CarritoModel $carrito;

    // Tu API Key de FastForex (solo preview)
    private const FASTFOREX_API_KEY = "e0fa6cb60a-97bab1afaf-t3hp21";

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

    /* ===================== helpers sesión ===================== */

    private function getClienteIdOrNull(): ?int
    {
        if (!empty($_SESSION['cliente']['ID'])) return (int)$_SESSION['cliente']['ID'];
        if (!empty($_SESSION['usuarios']['id'])) return (int)$_SESSION['usuarios']['id'];
        if (!empty($_SESSION['ID'])) return (int)$_SESSION['ID'];
        return null;
    }

    private function requireClienteId(): int
    {
        $id = $this->getClienteIdOrNull();
        if (!$id) {
            header('Location: ' . $this->basePath . '/login?next=/cotizacion');
            exit;
        }
        return $id;
    }

    /* ===================== FX simple ===================== */

    private function fetchTcFastForexSimple(): array
    {
        $apiKey = self::FASTFOREX_API_KEY;
        $url = "https://api.fastforex.io/fetch-one?from=USD&to=GTQ&api_key={$apiKey}";
        $json = @file_get_contents($url);
        if ($json === false) return [false, 0.0, null, null];

        $data = json_decode($json, true);
        if (!$data || !isset($data['result']['GTQ'])) return [false, 0.0, null, $data];

        $tc  = (float)$data['result']['GTQ'];
        $upd = $data['updated'] ?? null;
        return [true, $tc, $upd, $data];
    }

    /* ========================= GET /cotizacion ========================= */

    public function index(): void
    {
        $idCliente = $this->getClienteIdOrNull();
        $basePath  = $this->basePath;
        $pageTitle = 'Resumen de cotización';

        // tipo cotización
        $tipo = (int)($_SESSION['cotz_tipo'] ?? 1);
        if (!in_array($tipo, [1,2], true)) $tipo = 1;

        if ($idCliente) {
            // migrar carrito invitado
            $guestCart = $_SESSION['cart'] ?? ($_SESSION['carrito'] ?? []);
            if (!empty($guestCart)) {
                $this->carrito->mergeGuestCartToDb($idCliente, $guestCart);
                unset($_SESSION['cart'], $_SESSION['carrito']);
                $_SESSION['flash'] = 'Productos del carrito migrados a la cotización.';
            }

            $idCot = $this->carrito->getOrCreateCotizacionAbierta($idCliente);
            $this->carrito->setTipoCotizacion($idCot, $tipo);

            $meta   = $this->carrito->getCotizacionMeta($idCot);
            $items  = $this->carrito->getItems($idCot);

            $tipo              = (int)($meta['TIPO_COTIZACION'] ?? $tipo);
            $totalBase         = (float)($meta['TOTAL_COTIZACION'] ?? 0);
            $estado            = (int)($meta['ESTADO'] ?? 1);

            // AHORA: impuestos/otros como viene de BD
            $impuestosYOtros   = (float)($meta['TOTAL_CON_IMPUESTOS'] ?? 0);

            // ====== NUEVO: obtener desglose de tablas ======
            $impuestosDet      = $this->carrito->getImpuestosByCotizacion($idCot); // array o []
            $recargosDet       = $this->carrito->getRecargosByCotizacion($idCot);  // lista

            $sumaImpuestos = (float)($impuestosDet['ENVIO'] ?? 0)
                            + (float)($impuestosDet['ARANCEL'] ?? 0)
                            + (float)($impuestosDet['DESADUANAJE'] ?? 0)
                            + (float)($impuestosDet['FLETE'] ?? 0);

            $sumaRecargos  = 0.0;
            foreach ($recargosDet as $r) {
                $sumaRecargos += (float)($r['VALOR_RECARGO'] ?? 0);
            }
            // ===============================================

            $tipoCambioGuard   = (float)($meta['TIPO_DE_CAMBIO'] ?? 0);
            $totalVentaEnQ     = (float)($meta['TOTAL_VENTA_EN_Q'] ?? 0);

            $tipoCambioPreview = isset($_SESSION['tc_preview']) ? (float)$_SESSION['tc_preview'] : 0.0;
            $tcUpdatedAt       = $_SESSION['tc_updated_at'] ?? null;

            $tipoCambio        = $tipoCambioPreview ?: $tipoCambioGuard;
        } else {
            $guestCart = $_SESSION['cart'] ?? ($_SESSION['carrito'] ?? []);
            [$items, $totalBase] = $this->carrito->buildItemsFromGuestCart($guestCart, $tipo);

            $estado            = 1;
            $impuestosYOtros   = 0.0;

            // sin desglose para invitado
            $impuestosDet = [];
            $recargosDet  = [];
            $sumaImpuestos = 0.0;
            $sumaRecargos  = 0.0;

            $tipoCambioGuard   = 0.0;
            $totalVentaEnQ     = 0.0;

            $tipoCambioPreview = isset($_SESSION['tc_preview']) ? (float)$_SESSION['tc_preview'] : 0.0;
            $tcUpdatedAt       = $_SESSION['tc_updated_at'] ?? null;
            $tipoCambio        = $tipoCambioPreview;
        }

        require $this->viewsPath . '/paginas/cotizacion.php';
    }

    /* ========================= POST acciones ========================= */

    public function actualizarTipo(): void
    {
        $tipo = (int)$_POST['tipo_cotizacion'] ?? 1;
        $_SESSION['cotz_tipo'] = in_array($tipo, [1,2], true) ? $tipo : 1;

        $idCliente = $this->getClienteIdOrNull();
        if ($idCliente) {
            $idCot = $this->carrito->getOrCreateCotizacionAbierta($idCliente);
            $this->carrito->setTipoCotizacion($idCot, $_SESSION['cotz_tipo']);
        }

        header('Location: ' . $this->basePath . '/cotizacion');
        exit;
    }

    public function pedir(): void
    {
        $idCliente = $this->requireClienteId();
        $idCot     = $this->carrito->getOrCreateCotizacionAbierta($idCliente);

        $this->carrito->setEstado($idCot, 2);
        header('Location: ' . $this->basePath . '/cotizacion');
        exit;
    }

    public function procesar(): void
    {
        $idCliente = $this->requireClienteId();
        $idCot     = $this->carrito->getOrCreateCotizacionAbierta($idCliente);

        $totalConImpuestos = (float)($_POST['total_con_impuestos'] ?? 0);
        if ($totalConImpuestos >= 0) {
            $this->carrito->setTotalConImpuestos($idCot, $totalConImpuestos);
        }
        $this->carrito->setEstado($idCot, 3);

        header('Location: ' . $this->basePath . '/cotizacion');
        exit;
    }

 // controllers/CotizacionController.php (solo el método confirmar)
public function confirmar(): void
{
    $idCliente = $this->requireClienteId();
    $idCot     = $this->carrito->getOrCreateCotizacionAbierta($idCliente);

    // estado 4 = Confirmado
    $this->carrito->setEstado($idCot, 4);

    // (opcional) limpiar preview de TC
    unset($_SESSION['tc_preview'], $_SESSION['tc_updated_at']);

    // <-- AQUÍ la redirección correcta
    header('Location: ' . $this->basePath . '/pago');
    exit;
}



    public function anular(): void
    {
        $idCliente = $this->requireClienteId();
        $idCot     = $this->carrito->getOrCreateCotizacionAbierta($idCliente);

        $this->carrito->setEstado($idCot, 5);
        header('Location: ' . $this->basePath . '/carrito');
        exit;
    }

    /* ===== Tipo de cambio ===== */

    public function actualizarTipoCambio(): void
    {
        [$ok, $tc, $upd] = $this->fetchTcFastForexSimple();

        if ($ok && $tc > 0) {
            $_SESSION['tc_preview']    = $tc;
            $_SESSION['tc_updated_at'] = $upd ?? date('c');
            $_SESSION['flash'] = 'Tipo de cambio consultado: 1 USD = Q ' . number_format($tc, 2) . ' (preview).';
        } else {
            $_SESSION['flash'] = 'No se pudo consultar el tipo de cambio.';
        }

        header('Location: ' . $this->basePath . '/cotizacion');
        exit;
    }

    public function verQuetzales(): void
    {
        $idCliente = $this->requireClienteId();
        $idCot  = $this->carrito->getOrCreateCotizacionAbierta($idCliente);
        $meta   = $this->carrito->getCotizacionMeta($idCot);

        $totalBase       = (float)($meta['TOTAL_COTIZACION'] ?? 0);
        $impuestosYOtros = (float)($meta['TOTAL_CON_IMPUESTOS'] ?? 0);
        $totalFinalUSD   = $totalBase + $impuestosYOtros;

        $tc = isset($_SESSION['tc_preview']) ? (float)$_SESSION['tc_preview'] : 0.0;
        if ($tc <= 0) {
            [$ok, $tcNow] = $this->fetchTcFastForexSimple();
            $tc = $ok ? (float)$tcNow : 0.0;
        }

        if ($tc <= 0) {
            $_SESSION['flash'] = 'No hay tipo de cambio válido. Presiona “Actualizar tipo de cambio” primero.';
            header('Location: ' . $this->basePath . '/cotizacion');
            exit;
        }

        $this->carrito->setTipoCambio($idCot, $tc);
        $totalQ = round($totalFinalUSD * $tc, 2);
        $this->carrito->setTotalVentaEnQ($idCot, $totalQ);

        unset($_SESSION['tc_preview'], $_SESSION['tc_updated_at']);

        $_SESSION['flash'] = 'Guardado: 1 USD = Q ' . number_format($tc, 2) .
                             ' · Total en quetzales: Q ' . number_format($totalQ, 2);
        header('Location: ' . $this->basePath . '/cotizacion');
        exit;
    }
}

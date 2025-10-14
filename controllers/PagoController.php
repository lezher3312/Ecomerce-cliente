<?php
// controllers/PagoController.php
declare(strict_types=1);

class PagoController
{
    private string $viewsPath;
    private string $basePath;
    private PDO $pdo;
    private CarritoModel $carrito;
    private PagoModel $pago;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        $root = dirname(__DIR__);
        $this->viewsPath = $root . '/views';
        $this->basePath  = defined('BASE_PATH')
            ? rtrim((string)BASE_PATH, '/')
            : rtrim((string)(dirname($_SERVER['SCRIPT_NAME']) ?: ''), '/');
        if ($this->basePath !== '' && $this->basePath[0] !== '/') $this->basePath = '/' . $this->basePath;

        require_once $root . '/config/conexion.php';
        require_once $root . '/model/CarritoModel.php';
        require_once $root . '/model/PagoModel.php';

        $this->pdo     = Conexion::getConexion();
        $this->carrito = new CarritoModel($this->pdo);
        $this->pago    = new PagoModel($this->pdo);
    }

    /* ================= Helpers ================= */

    private function requireClienteId(): int
    {
        if (!empty($_SESSION['cliente']['ID']))  return (int)$_SESSION['cliente']['ID'];
        if (!empty($_SESSION['usuarios']['id'])) return (int)$_SESSION['usuarios']['id'];
        if (!empty($_SESSION['ID']))             return (int)$_SESSION['ID'];
        header('Location: ' . $this->basePath . '/login?next=/pago');
        exit;
    }

    /** Debe existir en CarritoModel: getUltimaCotizacionConfirmada($idCliente) con ESTADO=4 */
    private function getCotizacionConfirmadaOrRedirect(int $idCliente): array
    {
        $cot = $this->carrito->getUltimaCotizacionConfirmada($idCliente);
        if (!$cot || (int)$cot['ESTADO'] !== 4) {
            $_SESSION['flash'] = 'Debes confirmar la cotización antes de pagar.';
            header('Location: ' . $this->basePath . '/cotizacion');
            exit;
        }
        return $cot;
    }

    /* ================= GET /pago ================= */

    public function index(): void
    {
        $idCliente = $this->requireClienteId();

        // Última confirmada
        $meta   = $this->getCotizacionConfirmadaOrRedirect($idCliente);
        $idCot  = (int)$meta['ID_COTIZACION'];

        // Totales en GTQ
        $totalQ = (float)($meta['TOTAL_VENTA_EN_Q'] ?? 0);
        if ($totalQ <= 0) {
            // Fallback: TOTAL_COTIZACION + TOTAL_CON_IMPUESTOS en Q si lo guardas separado
            $totalQ = (float)(($meta['TOTAL_COTIZACION_Q'] ?? 0) + ($meta['TOTAL_IMP_Q'] ?? 0));
        }

        // Items si deseas
        $items = $this->carrito->getItems($idCot);

        // Impuestos/recargos (en Q si así guardas)
        [$impuestos, $recargos] = $this->pago->getImpuestosYRecargos($idCot);

        // Pagos previos y saldo (en GTQ)
        $pagadoQ         = $this->pago->sumPagado($idCot);
        $saldoPendienteQ = max($totalQ - $pagadoQ, 0);

        // Métodos
        $formasPago = [
            'tarjeta'        => 'Tarjeta (Visa / Mastercard)',
            'transferencia'  => 'Transferencia bancaria',
            'deposito'       => 'Depósito en agencia',
            'contraentrega'  => 'Pago contra entrega (COD)',
        ];

        // Listado de pagos
        $pagosPrevios = $this->pago->listarPagos($idCot);

        $pageTitle = 'Pago';
        $basePath  = $this->basePath;

        require $this->viewsPath . '/paginas/pago.php';
    }

    /* ================= POST /pago/crear ================= */

    public function crear(): void
    {
        $idCliente = $this->requireClienteId();

        $meta   = $this->getCotizacionConfirmadaOrRedirect($idCliente);
        $idCot  = (int)$meta['ID_COTIZACION'];

        // Total en GTQ
        $totalQ = (float)($meta['TOTAL_VENTA_EN_Q'] ?? 0);
        if ($totalQ <= 0) {
            $totalQ = (float)(($meta['TOTAL_COTIZACION_Q'] ?? 0) + ($meta['TOTAL_IMP_Q'] ?? 0));
        }

        // Saldo real
        $pagadoQ         = $this->pago->sumPagado($idCot);
        $saldoPendienteQ = max($totalQ - $pagadoQ, 0);
        if ($saldoPendienteQ <= 0) {
            $_SESSION['flash_pago_ok'] = 'Esta cotización ya no tiene saldo pendiente.';
            header('Location: ' . $this->basePath . '/pago');
            exit;
        }

        // Monto solicitado (GTQ)
        $monto = isset($_POST['monto_q']) ? (float)$_POST['monto_q'] : 0.0;
        if ($monto <= 0) $monto = $saldoPendienteQ;
        if ($monto > $saldoPendienteQ) $monto = $saldoPendienteQ;

        // Método
        $metodo = isset($_POST['metodo']) ? (string)$_POST['metodo'] : '';
        $permitidos = ['tarjeta','transferencia','deposito','contraentrega'];
        if (!in_array($metodo, $permitidos, true)) {
            $_SESSION['flash_pago_error'] = 'Selecciona un método de pago válido.';
            header('Location: ' . $this->basePath . '/pago');
            exit;
        }

        // Cuotas (opcional, informativo)
        $cuotas = isset($_POST['cuotas']) ? (int)$_POST['cuotas'] : 1;
        if ($cuotas < 1) $cuotas = 1;
        if ($cuotas > 4) $cuotas = 4;

        // Datos extra
        $desc = 'Pago de cotización #' . $idCot . ' (' . strtoupper($metodo) . ')';
        if ($cuotas > 1) $desc .= " - plan {$cuotas} cuotas";
        $ref  = isset($_POST['referencia']) ? trim((string)$_POST['referencia']) : '';

        $tarjeta = null;
        $transf  = null;

        if ($metodo === 'tarjeta') {
            $tarjeta = [
                'tipo_tarjeta' => trim((string)($_POST['tipo_tarjeta'] ?? '')),
                'codigo'       => trim((string)($_POST['numero4'] ?? '')), // últimos 4
                'exp_month'    => (int)($_POST['exp_month'] ?? 0),
                'exp_year'     => (int)($_POST['exp_year'] ?? 0),
                'nombre'       => trim((string)($_POST['titular'] ?? '')),
            ];
        }

        // Manejo de imagen de comprobante (transferencia/depósito)
        $imgPath = '';
        if ($metodo === 'transferencia' || $metodo === 'deposito') {
            $idBanco     = (int)($_POST['id_banco'] ?? 0);
            $comprobante = trim((string)($_POST['comprobante'] ?? $ref));

            // Upload
            if (!empty($_FILES['img_comprobante']['name']) && $_FILES['img_comprobante']['error'] === UPLOAD_ERR_OK) {
                $tmp  = $_FILES['img_comprobante']['tmp_name'];
                $name = $_FILES['img_comprobante']['name'];
                $ext  = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                $allow= ['jpg','jpeg','png'];
                if (in_array($ext, $allow, true)) {
                    // carpeta destino (asegúrate de crearla con permisos)
                    $destDir = dirname(__DIR__).'/public/uploads/comprobantes';
                    if (!is_dir($destDir)) @mkdir($destDir, 0775, true);
                    $fname   = 'comp_'.$idCot.'_'.date('Ymd_His').'_'.bin2hex(random_bytes(3)).'.'.$ext;
                    $destAbs = $destDir.'/'.$fname;
                    if (@move_uploaded_file($tmp, $destAbs)) {
                        // ruta pública
                        $imgPath = 'public/uploads/comprobantes/'.$fname;
                    }
                }
            }

            $transf = [
                'id_banco'    => $idBanco,
                'comprobante' => $comprobante,
                'img_path'    => $imgPath,
            ];
        }

        // Crear pago en GTQ (modelo guarda ESTADO=1)
        $ok = $this->pago->crearPago(
            $idCot,
            $idCliente,
            $desc,
            $monto,
            $totalQ,
            $metodo,
            $tarjeta,
            $transf
        );

        if ($ok) {
            $_SESSION['flash_pago_ok'] = 'Pago registrado correctamente.';
            header('Location: ' . $this->basePath . '/pago');
            exit;
        }

        $_SESSION['flash_pago_error'] = 'No se pudo registrar el pago. Intenta de nuevo.';
        header('Location: ' . $this->basePath . '/pago');
        exit;
    }

    /* ================= POST /pago/anular ================= */

    public function anular(): void
    {
        $idCliente = $this->requireClienteId(); // por seguridad básica de sesión
        $idPago = isset($_POST['id_pago']) ? (int)$_POST['id_pago'] : 0;
        if ($idPago <= 0) {
            $_SESSION['flash_pago_error'] = 'Pago inválido.';
            header('Location: ' . $this->basePath . '/pago');
            exit;
        }

        $ok = $this->pago->anularPago($idPago);
        if ($ok) {
            $_SESSION['flash_pago_ok'] = 'Pago anulado correctamente.';
        } else {
            $_SESSION['flash_pago_error'] = 'No se pudo anular el pago.';
        }

        header('Location: ' . $this->basePath . '/pago');
        exit;
    }

    /* ================= (Opcional) Gracias ================= */

    public function gracias(): void
    {
        $basePath  = $this->basePath;
        $pageTitle = 'Pago recibido';
        require $this->viewsPath . '/layouts/head.php';
        require $this->viewsPath . '/layouts/header.php';
        ?>
        <main class="container" style="max-width:900px;margin:24px auto;">
          <h1>¡Gracias!</h1>
          <?php if (!empty($_SESSION['flash_pago_ok'])): ?>
            <div class="alert" style="background:#ecfdf5;border-color:#bbf7d0;color:#065f46;">
              <?= htmlspecialchars($_SESSION['flash_pago_ok']); unset($_SESSION['flash_pago_ok']); ?>
            </div>
          <?php endif; ?>
          <p>Registramos tu pago. Si elegiste transferencia/depósito, será validado por un asesor.</p>
          <a class="btn btn-primary" href="<?= htmlspecialchars($basePath) ?>/cotizacion">Volver al resumen</a>
        </main>
        <?php
        require $this->viewsPath . '/layouts/footer.php';
    }
}

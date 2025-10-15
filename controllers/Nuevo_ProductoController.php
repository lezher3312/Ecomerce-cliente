<?php
// controllers/Nuevo_ProductoController.php
declare(strict_types=1);

class Nuevo_ProductoController
{
    private PDO $pdo;
    private string $viewsPath;
    private string $basePath;
    private CarritoModel $carrito;
    private Nuevo_ProductoModel $model;

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
        require_once $root . '/model/Nuevo_ProductoModel.php';

        $this->pdo     = Conexion::getConexion();
        $this->carrito = new CarritoModel($this->pdo);
        $this->model   = new Nuevo_ProductoModel($this->pdo);
    }

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

    public function form(): void
    {
        $categorias  = $this->model->getCategorias();   // [ ['id'=>..,'nombre'=>..], ... ]
        $proveedores = $this->model->getProveedores();  // [ ['id'=>..,'nombre'=>..], ... ]

        $tipo = (int)($_SESSION['cotz_tipo'] ?? 1);
        if (!in_array($tipo, [1, 2], true)) $tipo = 1;

        $basePath = $this->basePath;
        require $this->viewsPath . '/paginas/nuevo_producto.php';
    }

    public function guardar(): void
    {
        $idCliente = $this->requireClienteId();
        $idCot     = $this->carrito->getOrCreateCotizacionAbierta($idCliente);

        $nombre       = trim((string)($_POST['nombre'] ?? ''));
        $idCategoria  = (int)($_POST['id_categoria'] ?? 0);
        $idProveedor  = (int)($_POST['id_proveedor'] ?? 0);
        $link         = trim((string)($_POST['link'] ?? ''));
        $precio       = (float)($_POST['precio'] ?? 0);
        $cantidad     = max(1, (int)($_POST['cantidad'] ?? 1));
        $tipoCtz      = (int)($_POST['tipo_cotizacion'] ?? ($_SESSION['cotz_tipo'] ?? 1));
        $embed        = !empty($_POST['embed']);

        // Validación básica
        if ($nombre === '' || $precio <= 0) {
            $this->fail('Nombre o precio inválidos.', $embed);
            return; // cortar ejecución (fail hace exit, esto es por claridad)
        }

        // Subida de boleta SOLO si tipo = 2 (solo transporte)
        $boletaUrl = null;
        if ($tipoCtz === 2 && !empty($_FILES['boleta']['name'])) {
            $boletaUrl = $this->subirBoleta($_FILES['boleta']);
            if ($boletaUrl === null) {
                $this->fail('Archivo de boleta no permitido o error al subir.', $embed);
                return;
            }
        }

        $this->pdo->beginTransaction();
        try {
            // 1) Insert producto (en tabla "producto")
            $idProducto = $this->model->insertProducto([
                'ID_CATPRODUCTO'  => $idCategoria ?: null,
                'ID_PROVEEDOR'    => $idProveedor ?: null,
                'NOMBRE_PRODUCTO' => $nombre,
                'DESCRIPCION'     => 'Producto agregado desde cotización',
                'PRECIO'          => $precio, // USD
                'LINK'            => $link ?: null,
                'ESTADO'          => 1,       // activo
            ]);

            // 2) Ajustar tipo de cotización si cambió
            if (in_array($tipoCtz, [1, 2], true)) {
                $this->carrito->setTipoCotizacion($idCot, $tipoCtz);
                $_SESSION['cotz_tipo'] = $tipoCtz;
            }

            // 3) Agregar detalle
            $ok = $this->carrito->addOrUpdateItem($idCot, $idProducto, $cantidad);
            if (!$ok) {
                throw new RuntimeException('No se pudo agregar el ítem al detalle.');
            }

            // 4) Guardar boleta (si corresponde) — tabla opcional
            if ($boletaUrl) {
                try {
                    $st = $this->pdo->prepare("
                        INSERT INTO documentos_cotizacion (ID_COTIZACION, TIPO, URL, FECHA_CREACION)
                        VALUES (:c, 'BOLETA_PAGO', :u, NOW())
                    ");
                    $st->execute([':c' => $idCot, ':u' => $boletaUrl]);
                } catch (Throwable $e) {
                    // si no existe la tabla u otro detalle, se ignora silenciosamente
                }
            }

            $this->pdo->commit();
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            $this->fail('Error al guardar: ' . $e->getMessage(), $embed);
            return;
        }

        if ($embed) {
            echo "<script>window.parent.postMessage({type:'nuevoProductoAdded'}, '*');</script>";
            echo "<p>Guardado correctamente. Puedes cerrar esta ventana.</p>";
            return;
        }

        header('Location: ' . $this->basePath . '/cotizacion');
        exit;
    }

    private function subirBoleta(array $f): ?string
    {
        $err = (int)($f['error'] ?? UPLOAD_ERR_NO_FILE);
        if ($err !== UPLOAD_ERR_OK) return null;

        $tmp  = $f['tmp_name'] ?? null;
        if (!$tmp || !is_uploaded_file($tmp)) return null;

        $allowed = [
            'image/jpeg'      => 'jpg',
            'image/png'       => 'png',
            'image/gif'       => 'gif',
            'image/webp'      => 'webp',
            'image/bmp'       => 'bmp',
            'image/tiff'      => 'tif',
            'image/svg+xml'   => 'svg',
            'application/pdf' => 'pdf',
        ];

        $mime = @mime_content_type($tmp) ?: ($f['type'] ?? '');
        $ext  = $allowed[$mime] ?? null;

        // Fallback por extensión del nombre
        if ($ext === null) {
            $nameExt = strtolower(pathinfo($f['name'] ?? '', PATHINFO_EXTENSION));
            $ext = ($nameExt !== '') ? $nameExt : null;
        }
        if ($ext === null) return null;

        $dir = dirname(__DIR__) . '/public/uploads/boletas';
        if (!is_dir($dir)) @mkdir($dir, 0775, true);

        $name = 'boleta_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $path = $dir . '/' . $name;

        if (!move_uploaded_file($tmp, $path)) return null;

        // URL pública relativa al proyecto
        return $this->basePath . '/public/uploads/boletas/' . $name;
    }

    private function fail(string $msg, bool $embed): void
    {
        http_response_code(400);
        echo '<p style="color:#b91c1c">' . htmlspecialchars($msg) . '</p>';
        if ($embed) {
            echo "<script>window.parent.postMessage({type:'nuevoProductoError', msg: " . json_encode($msg) . "}, '*');</script>";
        }
        exit;
    }
}

<?php
// model/CarritoModel.php
declare(strict_types=1);

class CarritoModel
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        if (session_status() === PHP_SESSION_NONE) session_start();
    }

    public function getCotizacionAbierta(int $idCliente): ?int
    {
        $st = $this->pdo->prepare(
            "SELECT ID_COTIZACION
               FROM cotizacion
              WHERE ID_CLIENTE = :cli
                AND TIPO_COTIZACION = 'WEB'
                AND ESTADO IN ('BORRADOR','PENDIENTE')
           ORDER BY ID_COTIZACION DESC
              LIMIT 1"
        );
        $st->execute([':cli' => $idCliente]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        return $row ? (int)$row['ID_COTIZACION'] : null;
    }

    public function getOrCreateCotizacionAbierta(int $idCliente): int
{
    $id = $this->getCotizacionAbierta($idCliente);
    if ($id) return $id;

    // Fechas base
    $fechaCot  = date('Y-m-d H:i:s');                           // ahora
    $fechaVenc = date('Y-m-d H:i:s', strtotime('+15 days'));    // vence en 15 días
    $fechaEnt  = date('Y-m-d H:i:s', strtotime('+3 days'));     // entrega estimada en 3 días (ajústalo a tu negocio)

    $st = $this->pdo->prepare(
        "INSERT INTO cotizacion
           (ID_CLIENTE, FECHA_COTIZACION, FECHA_VENCIMIENTO, FECHA_ENTREGA,
            TOTAL_COTIZACION, TIPO_COTIZACION, ESTADO, ANTICIPO)
         VALUES
           (:cli, :fc, :fv, :fe,
            0.00, 'WEB', 'BORRADOR', 0.00)"
    );
    $st->execute([
        ':cli' => $idCliente,
        ':fc'  => $fechaCot,
        ':fv'  => $fechaVenc,
        ':fe'  => $fechaEnt,
    ]);

    return (int)$this->pdo->lastInsertId();
}


    // Para pintar fichas (incluye DESCRIPCION)
    public function getProductCardData(int $idProducto): ?array
    {
        $st = $this->pdo->prepare(
            "SELECT ID_PRODUCTO, NOMBRE_PRODUCTO, DESCRIPCION, PRECIO, FOTOGRAFIA_PRODUCTO, EXISTENCIA, ESTADO
               FROM producto
              WHERE ID_PRODUCTO = :p
              LIMIT 1"
        );
        $st->execute([':p' => $idProducto]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        if (!$row) return null;

        return [
            'id'          => (int)$row['ID_PRODUCTO'],
            'nombre'      => (string)$row['NOMBRE_PRODUCTO'],
            'descripcion' => (string)($row['DESCRIPCION'] ?? ''),
            'precio'      => (float)$row['PRECIO'],
            'imagen'      => $row['FOTOGRAFIA_PRODUCTO'] ?: null,
        ];
    }

    // Agregar o sumar en BD
    public function addOrUpdateItem(int $idCotizacion, int $idProducto, int $cantidad): bool
    {
        // precio actual
        $stP = $this->pdo->prepare(
            "SELECT PRECIO FROM producto WHERE ID_PRODUCTO = :p LIMIT 1"
        );
        $stP->execute([':p' => $idProducto]);
        $precio = (float)($stP->fetchColumn() ?: 0.0);
        if ($precio <= 0) return false;

        // existe línea?
        $stC = $this->pdo->prepare(
            "SELECT ID_DETCOTIZACION_PRODUCTO, CANTIDAD
               FROM det_cotizacion_producto
              WHERE ID_COTIZACION = :c AND ID_PRODUCTO = :p
              LIMIT 1"
        );
        $stC->execute([':c' => $idCotizacion, ':p' => $idProducto]);
        $line = $stC->fetch(PDO::FETCH_ASSOC);

        if ($line) {
            $newQty = (int)$line['CANTIDAD'] + $cantidad;
            $newSub = $newQty * $precio;
            $stU = $this->pdo->prepare(
                "UPDATE det_cotizacion_producto
                    SET CANTIDAD = :q, PRECIO = :pr, SUBTOTAL = :st
                  WHERE ID_DETCOTIZACION_PRODUCTO = :id"
            );
            return $stU->execute([
                ':q'  => $newQty,
                ':pr' => $precio,
                ':st' => $newSub,
                ':id' => (int)$line['ID_DETCOTIZACION_PRODUCTO'],
            ]);
        }

        $sub  = $cantidad * $precio;
        $stI = $this->pdo->prepare(
            "INSERT INTO det_cotizacion_producto
               (ID_COTIZACION, ID_PRODUCTO, CANTIDAD, PRECIO, SUBTOTAL)
             VALUES (:c, :p, :q, :pr, :st)"
        );
        return $stI->execute([
            ':c'  => $idCotizacion,
            ':p'  => $idProducto,
            ':q'  => $cantidad,
            ':pr' => $precio,
            ':st' => $sub,
        ]);
    }

    // Actualizar cantidad en BD
    public function updateQty(int $idCotizacion, int $idProducto, int $cantidad): bool
    {
        $cantidad = max(1, (int)$cantidad);

        // Obtener precio actual de la línea (o del producto si no existiera)
        $stLine = $this->pdo->prepare(
            "SELECT ID_DETCOTIZACION_PRODUCTO, PRECIO
               FROM det_cotizacion_producto
              WHERE ID_COTIZACION = :c AND ID_PRODUCTO = :p
              LIMIT 1"
        );
        $stLine->execute([':c' => $idCotizacion, ':p' => $idProducto]);
        $line = $stLine->fetch(PDO::FETCH_ASSOC);
        if (!$line) return false;

        $precio = (float)$line['PRECIO'];
        if ($precio <= 0) {
            $stP = $this->pdo->prepare("SELECT PRECIO FROM producto WHERE ID_PRODUCTO = :p LIMIT 1");
            $stP->execute([':p' => $idProducto]);
            $precio = (float)($stP->fetchColumn() ?: 0.0);
        }
        if ($precio <= 0) return false;

        $sub = $cantidad * $precio;

        $stU = $this->pdo->prepare(
            "UPDATE det_cotizacion_producto
                SET CANTIDAD = :q, SUBTOTAL = :st, PRECIO = :pr
              WHERE ID_DETCOTIZACION_PRODUCTO = :id"
        );
        return $stU->execute([
            ':q'  => $cantidad,
            ':st' => $sub,
            ':pr' => $precio,
            ':id' => (int)$line['ID_DETCOTIZACION_PRODUCTO'],
        ]);
    }

    // Eliminar línea en BD
    public function removeItem(int $idCotizacion, int $idProducto): void
    {
        $st = $this->pdo->prepare(
            "DELETE FROM det_cotizacion_producto
              WHERE ID_COTIZACION = :c AND ID_PRODUCTO = :p
              LIMIT 1"
        );
        $st->execute([':c' => $idCotizacion, ':p' => $idProducto]);
    }

    // Items desde BD (incluye DESCRIPCION)
    public function getItems(int $idCotizacion): array
    {
        $st = $this->pdo->prepare(
            "SELECT d.ID_PRODUCTO, d.CANTIDAD, d.PRECIO, d.SUBTOTAL,
                    p.NOMBRE_PRODUCTO, p.DESCRIPCION, p.FOTOGRAFIA_PRODUCTO
               FROM det_cotizacion_producto d
               JOIN producto p ON p.ID_PRODUCTO = d.ID_PRODUCTO
              WHERE d.ID_COTIZACION = :c
           ORDER BY d.ID_DETCOTIZACION_PRODUCTO DESC"
        );
        $st->execute([':c' => $idCotizacion]);
        $rows = $st->fetchAll(PDO::FETCH_ASSOC);

        $items = [];
        foreach ($rows as $r) {
            $items[] = [
                'id'          => (int)$r['ID_PRODUCTO'],
                'nombre'      => (string)$r['NOMBRE_PRODUCTO'],
                'descripcion' => (string)($r['DESCRIPCION'] ?? ''),
                'precio'      => (float)$r['PRECIO'],
                'cantidad'    => (int)$r['CANTIDAD'],
                'subtotal'    => (float)$r['SUBTOTAL'],
                'imagen'      => $r['FOTOGRAFIA_PRODUCTO'] ?: null,
            ];
        }
        return $items;
    }

    // ====== Modo invitado (sesión) ======

    public function sessionCartToItems(array $guestCart): array
    {
        $items = [];
        $count = 0;
        $subtotal = 0.0;

        foreach ($guestCart as $idProducto => $qty) {
            $idProducto = (int)$idProducto;
            $qty = max(1, (int)$qty);

            $prod = $this->getProductCardData($idProducto);
            if (!$prod) continue;

            $rowTotal = $qty * (float)$prod['precio'];
            $items[] = [
                'id'          => $prod['id'],
                'nombre'      => $prod['nombre'],
                'descripcion' => $prod['descripcion'],
                'precio'      => (float)$prod['precio'],
                'cantidad'    => $qty,
                'subtotal'    => $rowTotal,
                'imagen'      => $prod['imagen'],
            ];
            $count    += $qty;
            $subtotal += $rowTotal;
        }

        $total = $subtotal;
        return [$items, $count, $subtotal, $total];
    }

    public function sessionUpdateQty(int $idProducto, int $cantidad): void
    {
        if (empty($_SESSION['cart']) || !is_array($_SESSION['cart'])) return;
        $cantidad = max(1, (int)$cantidad);
        if (isset($_SESSION['cart'][$idProducto])) {
            $_SESSION['cart'][$idProducto] = $cantidad;
        }
    }

    public function sessionRemoveItem(int $idProducto): void
    {
        if (!empty($_SESSION['cart'][$idProducto])) {
            unset($_SESSION['cart'][$idProducto]);
        }
    }

    // Fusionar carrito de invitado a BD del cliente
    public function mergeGuestCartToDb(int $idCliente, array $guestCart): void
    {
        if (!$guestCart) return;
        $idCot = $this->getOrCreateCotizacionAbierta($idCliente);

        foreach ($guestCart as $idProducto => $qty) {
            $idProducto = (int)$idProducto;
            $qty        = max(1, (int)$qty);
            $this->addOrUpdateItem($idCot, $idProducto, $qty);
        }
        $this->recalcularTotal($idCot);
    }

    // Recalcular total de cotización
    public function recalcularTotal(int $idCotizacion): void
    {
        $st = $this->pdo->prepare(
            "SELECT COALESCE(SUM(SUBTOTAL), 0) AS total
               FROM det_cotizacion_producto
              WHERE ID_COTIZACION = :c"
        );
        $st->execute([':c' => $idCotizacion]);
        $total = (float)($st->fetchColumn() ?: 0.0);

        $up = $this->pdo->prepare(
            "UPDATE cotizacion
                SET TOTAL_COTIZACION = :t
              WHERE ID_COTIZACION = :c"
        );
        $up->execute([':t' => $total, ':c' => $idCotizacion]);
    }
}

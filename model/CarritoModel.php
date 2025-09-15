<?php
// model/CarritoModel.php
class CarritoModel
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        if (session_status() === PHP_SESSION_NONE) session_start();
    }

    /**
     * Devuelve la ID_COTIZACION de la cotización "abierta" del cliente, si existe.
     * Criterio: ESTADO IN ('BORRADOR','PENDIENTE') y TIPO_COTIZACION = 'WEB'
     */
    public function getCotizacionAbierta(int $idCliente): ?int
    {
        $sql = "SELECT ID_COTIZACION
                FROM cotizacion
                WHERE ID_CLIENTE = :cli
                  AND TIPO_COTIZACION = 'WEB'
                  AND ESTADO IN ('BORRADOR','PENDIENTE')
                ORDER BY ID_COTIZACION DESC
                LIMIT 1";
        $st = $this->pdo->prepare($sql);
        $st->execute([':cli' => $idCliente]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        return $row ? (int)$row['ID_COTIZACION'] : null;
    }

    /**
     * Obtiene o crea la cotización abierta del cliente.
     */
    public function getOrCreateCotizacionAbierta(int $idCliente): int
    {
        $id = $this->getCotizacionAbierta($idCliente);
        if ($id) return $id;

        $sql = "INSERT INTO cotizacion
                  (ID_CLIENTE, FECHA_COTIZACION, FECHA_VENCIMIENTO, FECHA_ENTREGA,
                   TOTAL_COTIZACION, TIPO_COTIZACION, ESTADO, ANTICIPO)
                VALUES
                  (:cli, NOW(), DATE_ADD(NOW(), INTERVAL 15 DAY), NULL,
                   0.00, 'WEB', 'BORRADOR', 0.00)";
        $st = $this->pdo->prepare($sql);
        $st->execute([':cli' => $idCliente]);
        return (int)$this->pdo->lastInsertId();
    }

    /**
     * Agrega o actualiza una línea en det_cotizacion_producto para un producto.
     * - Si ya existe el producto en la misma cotización: suma la cantidad.
     * - PRECIO se toma de producto.PRECIO
     */
    public function addOrUpdateItem(int $idCotizacion, int $idProducto, int $cantidad): bool
    {
        // 1) Obtener precio actual del producto (y validar existencia/estado)
        $sqlP = "SELECT ID_PRODUCTO, NOMBRE_PRODUCTO, PRECIO, FOTOGRAFIA_PRODUCTO, EXISTENCIA, ESTADO
                 FROM producto
                 WHERE ID_PRODUCTO = :p LIMIT 1";
        $stP = $this->pdo->prepare($sqlP);
        $stP->execute([':p' => $idProducto]);
        $prod = $stP->fetch(PDO::FETCH_ASSOC);
        if (!$prod) return false;

        $precio = (float)$prod['PRECIO'];

        // 2) ¿Existe ya una línea para ese producto?
        $sqlCheck = "SELECT ID_DETCOTIZACION_PRODUCTO, CANTIDAD, PRECIO
                     FROM det_cotizacion_producto
                     WHERE ID_COTIZACION = :c AND ID_PRODUCTO = :p
                     LIMIT 1";
        $stC = $this->pdo->prepare($sqlCheck);
        $stC->execute([':c' => $idCotizacion, ':p' => $idProducto]);
        $line = $stC->fetch(PDO::FETCH_ASSOC);

        if ($line) {
            $newQty   = (int)$line['CANTIDAD'] + $cantidad;
            $newSub   = $newQty * $precio;
            $sqlUpd = "UPDATE det_cotizacion_producto
                       SET CANTIDAD = :q, PRECIO = :pr, SUBTOTAL = :st
                       WHERE ID_DETCOTIZACION_PRODUCTO = :id";
            $stU = $this->pdo->prepare($sqlUpd);
            return $stU->execute([
                ':q'  => $newQty,
                ':pr' => $precio,
                ':st' => $newSub,
                ':id' => (int)$line['ID_DETCOTIZACION_PRODUCTO'],
            ]);
        } else {
            $sub = $cantidad * $precio;
            $sqlIns = "INSERT INTO det_cotizacion_producto
                       (ID_COTIZACION, ID_PRODUCTO, CANTIDAD, PRECIO, SUBTOTAL)
                       VALUES (:c, :p, :q, :pr, :st)";
            $stI = $this->pdo->prepare($sqlIns);
            return $stI->execute([
                ':c'  => $idCotizacion,
                ':p'  => $idProducto,
                ':q'  => $cantidad,
                ':pr' => $precio,
                ':st' => $sub,
            ]);
        }
    }

    /**
     * Recalcula y actualiza TOTAL_COTIZACION en la cotización.
     */
    public function recalcularTotal(int $idCotizacion): void
    {
        $sql = "SELECT COALESCE(SUM(SUBTOTAL), 0) AS total
                FROM det_cotizacion_producto
                WHERE ID_COTIZACION = :c";
        $st = $this->pdo->prepare($sql);
        $st->execute([':c' => $idCotizacion]);
        $total = (float)($st->fetchColumn() ?: 0);

        $up = $this->pdo->prepare("UPDATE cotizacion
                                   SET TOTAL_COTIZACION = :t
                                   WHERE ID_COTIZACION = :c");
        $up->execute([':t' => $total, ':c' => $idCotizacion]);
    }

    /**
     * Devuelve los ítems del carrito para una cotización,
     * mapeados a lo que tu vista espera: id, nombre, precio, cantidad, subtotal, imagen
     */
    public function getItems(int $idCotizacion): array
    {
        $sql = "SELECT d.ID_PRODUCTO,
                       d.CANTIDAD,
                       d.PRECIO,
                       d.SUBTOTAL,
                       p.NOMBRE_PRODUCTO,
                       p.FOTOGRAFIA_PRODUCTO
                FROM det_cotizacion_producto d
                JOIN producto p ON p.ID_PRODUCTO = d.ID_PRODUCTO
                WHERE d.ID_COTIZACION = :c
                ORDER BY d.ID_DETCOTIZACION_PRODUCTO DESC";
        $st = $this->pdo->prepare($sql);
        $st->execute([':c' => $idCotizacion]);
        $rows = $st->fetchAll(PDO::FETCH_ASSOC);

        $items = [];
        foreach ($rows as $r) {
            $items[] = [
                'id'       => (int)$r['ID_PRODUCTO'],
                'nombre'   => $r['NOMBRE_PRODUCTO'],
                'precio'   => (float)$r['PRECIO'],
                'cantidad' => (int)$r['CANTIDAD'],
                'subtotal' => (float)$r['SUBTOTAL'],
                'imagen'   => $r['FOTOGRAFIA_PRODUCTO'] ?: null,
            ];
        }
        return $items;
    }
}

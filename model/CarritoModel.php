<?php
// model/CarritoModel.php
declare(strict_types=1);

class CarritoModel
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        if (session_status() === PHP_SESSION_NONE) session_start();
    }

    /* ====== COTIZACIÓN CABECERA ====== */

    public function getCotizacionAbierta(int $idCliente): ?int
    {
        $st = $this->pdo->prepare(
            "SELECT ID_COTIZACION
               FROM cotizacion
              WHERE ID_CLIENTE = :cli
                AND TIPO_COTIZACION IN (1,2)
                AND ESTADO IN (0,1,2,3)
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

        $now     = date('Y-m-d H:i:s');
        $venc    = date('Y-m-d H:i:s', strtotime('+15 days'));
        $entrega = date('Y-m-d H:i:s', strtotime('+10 days'));

        $st = $this->pdo->prepare(
            "INSERT INTO cotizacion
               (ID_CLIENTE, FECHA_COTIZACION, FECHA_VENCIMIENTO, FECHA_ENTREGA,
                TOTAL_COTIZACION, TOTAL_CON_IMPUESTOS, TIPO_DE_CAMBIO,
                TIPO_COTIZACION, ESTADO, ANTICIPO, TOTAL_VENTA_EN_Q)
             VALUES
               (:cli, :fc, :fv, :fe,
                0.00, 0.00, 0.00,
                1, 1, 0.00, 0.00)"
        );
        $st->execute([':cli' => $idCliente, ':fc' => $now, ':fv' => $venc, ':fe' => $entrega]);
        return (int)$this->pdo->lastInsertId();
    }

    public function getCotizacionMeta(int $idCot): array
    {
        $st = $this->pdo->prepare("SELECT * FROM cotizacion WHERE ID_COTIZACION = :c LIMIT 1");
        $st->execute([':c' => $idCot]);
        return $st->fetch(PDO::FETCH_ASSOC) ?: [];
    }

    public function setTipoCotizacion(int $idCot, int $tipo): void
    {
        $tipo = in_array($tipo, [1,2], true) ? $tipo : 1;
        $st = $this->pdo->prepare("UPDATE cotizacion SET TIPO_COTIZACION = :t WHERE ID_COTIZACION = :c");
        $st->execute([':t' => $tipo, ':c' => $idCot]);
        $this->recalcularLineasPorTipo($idCot, $tipo);
        $this->recalcularTotal($idCot);
    }

    public function setEstado(int $idCot, int $estado): void
    {
        if (!in_array($estado, [0,1,2,3,4,5], true)) return;
        $st = $this->pdo->prepare("UPDATE cotizacion SET ESTADO = :e WHERE ID_COTIZACION = :c");
        $st->execute([':e' => $estado, ':c' => $idCot]);
    }

    public function setTotalConImpuestos(int $idCot, float $totalConImpuestos): void
    {
        $st = $this->pdo->prepare("UPDATE cotizacion SET TOTAL_CON_IMPUESTOS = :t WHERE ID_COTIZACION = :c");
        $st->execute([':t' => $totalConImpuestos, ':c' => $idCot]);
    }

    public function setTipoCambio(int $idCot, float $tc): void
    {
        $st = $this->pdo->prepare("UPDATE cotizacion SET TIPO_DE_CAMBIO = :tc WHERE ID_COTIZACION = :c");
        $st->execute([':tc' => $tc, ':c' => $idCot]);
    }

    public function setTotalVentaEnQ(int $idCot, float $totalQ): void
    {
        $st = $this->pdo->prepare("UPDATE cotizacion SET TOTAL_VENTA_EN_Q = :q WHERE ID_COTIZACION = :c");
        $st->execute([':q' => $totalQ, ':c' => $idCot]);
    }

    public function contarItemsCarrito(int $idCliente): int
    {
        $st = $this->pdo->prepare(
            "SELECT COALESCE(SUM(d.CANTIDAD), 0)
               FROM cotizacion c
          LEFT JOIN det_cotizacion_producto d ON d.ID_COTIZACION = c.ID_COTIZACION
              WHERE c.ID_CLIENTE = :cli
                AND c.ESTADO IN (0,1,2,3)"
        );
        $st->execute([':cli' => $idCliente]);
        return (int)$st->fetchColumn();
    }

    /* ====== PRODUCTO Y CÁLCULOS ====== */

    private function fetchProducto(int $idProducto): ?array
    {
        $st = $this->pdo->prepare(
            "SELECT ID_PRODUCTO, NOMBRE_PRODUCTO, DESCRIPCION, PRECIO,
                    OFERTA, PORCENTAJE_OFERTA, INICIO_OFERTA, FIN_OFERTA,
                    FOTOGRAFIA_PRODUCTO,
                    HAZMAT_OPTION, HAZMAT_PRECIO,
                    PESO_OPTION, PESO_CANTIDAD, PESO_PRECIO
               FROM producto
              WHERE ID_PRODUCTO = :p LIMIT 1"
        );
        $st->execute([':p' => $idProducto]);
        return $st->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    private function precioConOferta(array $p): array
    {
        $precio = (float)$p['PRECIO'];
        $activa = false;
        $ofertaFlag = (int)($p['OFERTA'] ?? 0);
        $porc       = (float)($p['PORCENTAJE_OFERTA'] ?? 0);
        $hoy        = date('Y-m-d');

        if ($ofertaFlag === 1 && $porc > 0) {
            $ini = !empty($p['INICIO_OFERTA']) ? substr($p['INICIO_OFERTA'], 0, 10) : null;
            $fin = !empty($p['FIN_OFERTA'])    ? substr($p['FIN_OFERTA'], 0, 10)    : null;
            if ((!$ini || $ini <= $hoy) && (!$fin || $fin >= $hoy)) {
                $precio = round($precio * (1 - $porc / 100), 2);
                $activa = true;
            }
        }
        return [$precio, $activa, $porc];
    }

    private function cargoUnitario(array $p): float
    {
        $cargo = 0.0;
        if ((int)($p['HAZMAT_OPTION'] ?? 0) === 1) {
            $cargo += (float)($p['HAZMAT_PRECIO'] ?? 0);
        }
        if ((int)($p['PESO_OPTION'] ?? 0) === 1) {
            $cargo += (float)($p['PESO_PRECIO'] ?? 0);
        }
        return round($cargo, 2);
    }

    /* ====== DETALLE: CRUD + RECÁLCULOS ====== */

    public function addOrUpdateItem(int $idCotizacion, int $idProducto, int $cantidad): bool
    {
        $cantidad = max(1, (int)$cantidad);
        $p = $this->fetchProducto($idProducto);
        if (!$p) return false;

        [$precioUnit] = $this->precioConOferta($p);
        $cargoUnit = $this->cargoUnitario($p);

        $tipo = (int)($this->getCotizacionMeta($idCotizacion)['TIPO_COTIZACION'] ?? 1);
        $lineBase = ($tipo === 1 ? ($precioUnit + $cargoUnit) : $cargoUnit);
        $subtotal = round($lineBase * $cantidad, 2);

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
            $subtotal2 = round($lineBase * $newQty, 2);

            $stU = $this->pdo->prepare(
                "UPDATE det_cotizacion_producto
                    SET CANTIDAD = :q, PRECIO = :pr, CARGO_ADICIONAL = :ca, SUBTOTAL = :st
                  WHERE ID_DETCOTIZACION_PRODUCTO = :id"
            );
            $ok = $stU->execute([
                ':q'  => $newQty,
                ':pr' => $precioUnit,
                ':ca' => $cargoUnit,
                ':st' => $subtotal2,
                ':id' => (int)$line['ID_DETCOTIZACION_PRODUCTO'],
            ]);
        } else {
            $stI = $this->pdo->prepare(
                "INSERT INTO det_cotizacion_producto
                   (ID_COTIZACION, ID_PRODUCTO, CANTIDAD, PRECIO, CARGO_ADICIONAL, SUBTOTAL)
                 VALUES (:c, :p, :q, :pr, :ca, :st)"
            );
            $ok = $stI->execute([
                ':c'  => $idCotizacion,
                ':p'  => $idProducto,
                ':q'  => $cantidad,
                ':pr' => $precioUnit,
                ':ca' => $cargoUnit,
                ':st' => $subtotal,
            ]);
        }

        if ($ok ?? false) $this->recalcularTotal($idCotizacion);
        return (bool)($ok ?? false);
    }

    public function updateQty(int $idCotizacion, int $idProducto, int $cantidad): bool
    {
        $cantidad = max(1, (int)$cantidad);

        $p = $this->fetchProducto($idProducto);
        if (!$p) return false;

        [$precioUnit] = $this->precioConOferta($p);
        $cargoUnit = $this->cargoUnitario($p);

        $tipo = (int)($this->getCotizacionMeta($idCotizacion)['TIPO_COTIZACION'] ?? 1);
        $lineBase = ($tipo === 1 ? ($precioUnit + $cargoUnit) : $cargoUnit);
        $subtotal = round($lineBase * $cantidad, 2);

        $stU = $this->pdo->prepare(
            "UPDATE det_cotizacion_producto
                SET CANTIDAD = :q, PRECIO = :pr, CARGO_ADICIONAL = :ca, SUBTOTAL = :st
              WHERE ID_COTIZACION = :c AND ID_PRODUCTO = :p"
        );
        $ok = $stU->execute([
            ':q'  => $cantidad,
            ':pr' => $precioUnit,
            ':ca' => $cargoUnit,
            ':st' => $subtotal,
            ':c'  => $idCotizacion,
            ':p'  => $idProducto,
        ]);

        if ($ok) $this->recalcularTotal($idCotizacion);
        return (bool)$ok;
    }

    public function removeItem(int $idCotizacion, int $idProducto): void
    {
        $st = $this->pdo->prepare(
            "DELETE FROM det_cotizacion_producto
              WHERE ID_COTIZACION = :c AND ID_PRODUCTO = :p
              LIMIT 1"
        );
        $st->execute([':c' => $idCotizacion, ':p' => $idProducto]);
        $this->recalcularTotal($idCotizacion);
    }

    private function recalcularLineasPorTipo(int $idCot, int $tipo): void
    {
        $st = $this->pdo->prepare(
            "SELECT d.ID_DETCOTIZACION_PRODUCTO, d.ID_PRODUCTO, d.CANTIDAD
               FROM det_cotizacion_producto d
              WHERE d.ID_COTIZACION = :c"
        );
        $st->execute([':c' => $idCot]);
        foreach ($st->fetchAll(PDO::FETCH_ASSOC) as $r) {
            $p = $this->fetchProducto((int)$r['ID_PRODUCTO']);
            if (!$p) continue;

            [$precioUnit] = $this->precioConOferta($p);
            $cargoUnit    = $this->cargoUnitario($p);
            $qty          = (int)$r['CANTIDAD'];

            $lineBase = ($tipo === 1 ? ($precioUnit + $cargoUnit) : $cargoUnit);
            $subtotal = round($lineBase * $qty, 2);

            $up = $this->pdo->prepare(
                "UPDATE det_cotizacion_producto
                    SET PRECIO = :pr, CARGO_ADICIONAL = :ca, SUBTOTAL = :st
                  WHERE ID_DETCOTIZACION_PRODUCTO = :id"
            );
            $up->execute([
                ':pr' => $precioUnit,
                ':ca' => $cargoUnit,
                ':st' => $subtotal,
                ':id' => (int)$r['ID_DETCOTIZACION_PRODUCTO'],
            ]);
        }
    }

    public function getItems(int $idCot): array
    {
        $st = $this->pdo->prepare(
            "SELECT d.ID_PRODUCTO, d.CANTIDAD, d.PRECIO, d.CARGO_ADICIONAL, d.SUBTOTAL,
                    p.NOMBRE_PRODUCTO, p.DESCRIPCION, p.FOTOGRAFIA_PRODUCTO,
                    p.OFERTA, p.PORCENTAJE_OFERTA, p.INICIO_OFERTA, p.FIN_OFERTA
               FROM det_cotizacion_producto d
               JOIN producto p ON p.ID_PRODUCTO = d.ID_PRODUCTO
              WHERE d.ID_COTIZACION = :c
           ORDER BY d.ID_DETCOTIZACION_PRODUCTO DESC"
        );
        $st->execute([':c' => $idCot]);
        $items = [];
        foreach ($st->fetchAll(PDO::FETCH_ASSOC) as $r) {
            $ofertaActiva = false;
            $porc = (float)($r['PORCENTAJE_OFERTA'] ?? 0);
            if ((int)$r['OFERTA'] === 1 && $porc > 0) {
                $hoy = date('Y-m-d');
                $ini = !empty($r['INICIO_OFERTA']) ? substr($r['INICIO_OFERTA'],0,10) : null;
                $fin = !empty($r['FIN_OFERTA'])    ? substr($r['FIN_OFERTA'],0,10)    : null;
                if ((!$ini || $ini <= $hoy) && (!$fin || $fin >= $hoy)) $ofertaActiva = true;
            }

            $items[] = [
                'id'             => (int)$r['ID_PRODUCTO'],
                'nombre'         => (string)$r['NOMBRE_PRODUCTO'],
                'descripcion'    => (string)($r['DESCRIPCION'] ?? ''),
                'precio_unit'    => (float)$r['PRECIO'],
                'cantidad'       => (int)$r['CANTIDAD'],
                'cargo_unit'     => (float)$r['CARGO_ADICIONAL'],
                'cargo_total'    => round(((float)$r['CARGO_ADICIONAL']) * (int)$r['CANTIDAD'], 2),
                'oferta_activa'  => $ofertaActiva,
                'oferta_porc'    => $ofertaActiva ? $porc : 0,
                'subtotal'       => (float)$r['SUBTOTAL'],
                'imagen'         => $r['FOTOGRAFIA_PRODUCTO'] ?: null,
            ];
        }
        return $items;
    }

    public function recalcularTotal(int $idCot): void
    {
        $st = $this->pdo->prepare(
            "SELECT COALESCE(SUM(SUBTOTAL), 0)
               FROM det_cotizacion_producto
              WHERE ID_COTIZACION = :c"
        );
        $st->execute([':c' => $idCot]);
        $total = (float)$st->fetchColumn();

        $up = $this->pdo->prepare(
            "UPDATE cotizacion SET TOTAL_COTIZACION = :t WHERE ID_COTIZACION = :c"
        );
        $up->execute([':t' => $total, ':c' => $idCot]);
    }

    /* ====== Sesión ↔ BD ====== */

    public function mergeGuestCartToDb(int $idCliente, array $guestCart): void
    {
        if (!$guestCart) return;
        $idCot = $this->getOrCreateCotizacionAbierta($idCliente);
        foreach ($guestCart as $idProducto => $data) {
            $qty = is_array($data) ? (int)($data['cantidad'] ?? 1) : (int)$data;
            $this->addOrUpdateItem($idCot, (int)$idProducto, max(1, $qty));
        }
        $this->recalcularTotal($idCot);
    }

    public function sessionCartToItems(array $guestCart): array
    {
        $items = [];
        $count = 0;
        $subtotal = 0.0;

        foreach ($guestCart as $idProducto => $data) {
            $idProducto = (int)$idProducto;

            if (is_array($data)) {
                $qty     = max(1, (int)($data['cantidad'] ?? 1));
                $nombreV = isset($data['nombre']) ? (string)$data['nombre'] : null;
                $precioV = isset($data['precio']) ? (float)$data['precio'] : null;
                $imgV    = isset($data['imagen']) ? (string)$data['imagen'] : null;
            } else {
                $qty = max(1, (int)$data);
                $nombreV = $imgV = null;
                $precioV = null;
            }

            $p = $this->fetchProducto($idProducto);
            if (!$p) continue;

            [$unitPrice] = $this->precioConOferta($p);
            if ($precioV !== null && $precioV > 0) $unitPrice = (float)$precioV;

            $rowTotal = $unitPrice * $qty;

            $items[] = [
                'id'          => (int)$p['ID_PRODUCTO'],
                'nombre'      => $nombreV ?? (string)$p['NOMBRE_PRODUCTO'],
                'descripcion' => (string)($p['DESCRIPCION'] ?? ''),
                'precio'      => (float)$unitPrice,
                'cantidad'    => (int)$qty,
                'subtotal'    => (float)$rowTotal,
                'imagen'      => $imgV ?? ($p['FOTOGRAFIA_PRODUCTO'] ?: null),
            ];

            $count    += $qty;
            $subtotal += $rowTotal;
        }

        $total = $subtotal;
        return [$items, $count, $subtotal, $total];
    }

    public function buildItemsFromGuestCart(array $guestCart, int $tipo): array
    {
        $items = [];
        $totalBase = 0.0;

        foreach ($guestCart as $idProducto => $data) {
            $qty = is_array($data) ? (int)($data['cantidad'] ?? 1) : (int)$data;
            $qty = max(1, $qty);
            $p = $this->fetchProducto((int)$idProducto);
            if (!$p) continue;

            [$precioUnit] = $this->precioConOferta($p);
            $cargoUnit = $this->cargoUnitario($p);
            $lineBase = ($tipo === 1 ? ($precioUnit + $cargoUnit) : $cargoUnit);
            $subtotal = round($lineBase * $qty, 2);

            $items[] = [
                'id'          => (int)$p['ID_PRODUCTO'],
                'nombre'      => (string)$p['NOMBRE_PRODUCTO'],
                'descripcion' => (string)($p['DESCRIPCION'] ?? ''),
                'precio_unit' => (float)$precioUnit,
                'cantidad'    => (int)$qty,
                'cargo_unit'  => (float)$cargoUnit,
                'subtotal'    => (float)$subtotal,
                'imagen'      => $p['FOTOGRAFIA_PRODUCTO'] ?: null,
            ];
            $totalBase += $subtotal;
        }
        return [$items, round($totalBase, 2)];
    }

    /* ====== NUEVO: Impuestos y Recargos (para desglose en la vista) ====== */

    /**
     * Devuelve el último registro de la tabla `impuestos` para la cotización dada.
     * Estructura prevista: ID_IMPUESTOS, ID_COTIZACION, ENVIO, ARANCEL, DESADUANAJE, FLETE, FECHA_CREACION.
     */
    public function getImpuestosByCotizacion(int $idCot): array
    {
        $st = $this->pdo->prepare(
            "SELECT ENVIO, ARANCEL, DESADUANAJE, FLETE
               FROM impuestos
              WHERE ID_COTIZACION = :c
           ORDER BY ID_IMPUESTOS DESC
              LIMIT 1"
        );
        $st->execute([':c' => $idCot]);
        $row = $st->fetch(PDO::FETCH_ASSOC) ?: [];
        return [
            'ENVIO'        => (float)($row['ENVIO'] ?? 0),
            'ARANCEL'      => (float)($row['ARANCEL'] ?? 0),
            'DESADUANAJE'  => (float)($row['DESADUANAJE'] ?? 0),
            'FLETE'        => (float)($row['FLETE'] ?? 0),
        ];
    }

    /**
     * Devuelve la lista de recargos (activos) para la cotización.
     * Estructura prevista: ID_RECARGOS, ID_COTIZACION, NOMBRE_RERCARGO, VALOR_RECARGO, ESTADO, FECHA_CREACION.
     */
    public function getRecargosByCotizacion(int $idCot): array
    {
        $st = $this->pdo->prepare(
            "SELECT NOMBRE_RERCARGO, VALOR_RECARGO
               FROM recargos
              WHERE ID_COTIZACION = :c
                AND (ESTADO = 1 OR ESTADO IS NULL)
           ORDER BY FECHA_CREACION ASC, ID_RECARGOS ASC"
        );
        $st->execute([':c' => $idCot]);
        return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /** Calcula la suma de impuestos + recargos (útil para comparar con TOTAL_CON_IMPUESTOS). */
    public function calcularSumaImpuestosYRecargos(int $idCot): float
    {
        $imp = $this->getImpuestosByCotizacion($idCot);
        $sum = (float)($imp['ENVIO'] ?? 0)
             + (float)($imp['ARANCEL'] ?? 0)
             + (float)($imp['DESADUANAJE'] ?? 0)
             + (float)($imp['FLETE'] ?? 0);

        foreach ($this->getRecargosByCotizacion($idCot) as $r) {
            $sum += (float)($r['VALOR_RECARGO'] ?? 0);
        }
        return round($sum, 2);
    }

    /** Opcional: sincroniza TOTAL_CON_IMPUESTOS con el detalle actual (si lo deseas). */
    public function refreshTotalConImpuestosDesdeDetalle(int $idCot): void
    {
        $sum = $this->calcularSumaImpuestosYRecargos($idCot);
        $this->setTotalConImpuestos($idCot, $sum);
    }

    // CarritoModel.php
public function getUltimaCotizacionConfirmada(int $idCliente): ?array {
    $st = $this->pdo->prepare(
        "SELECT *
           FROM cotizacion
          WHERE ID_CLIENTE = :cli AND ESTADO = 4
       ORDER BY ID_COTIZACION DESC
          LIMIT 1"
    );
    $st->execute([':cli' => $idCliente]);
    $row = $st->fetch(PDO::FETCH_ASSOC);
    return $row ?: null;
}

}

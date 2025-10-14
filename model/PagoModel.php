<?php
// model/PagoModel.php
declare(strict_types=1);

class PagoModel
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        if (session_status() === PHP_SESSION_NONE) session_start();
    }

    /** Desglose de impuestos y recargos (en GTQ si tus tablas ya guardan GTQ). */
    public function getImpuestosYRecargos(int $idCot): array
    {
        $impuestos = [];
        try {
            $st = $this->pdo->prepare(
                "SELECT ENVIO, ARANCEL, DESADUANAJE, FLETE
                   FROM impuestos
                  WHERE ID_COTIZACION = :c
               ORDER BY ID_IMPUESTOS DESC LIMIT 1"
            );
            $st->execute([':c' => $idCot]);
            if ($row = $st->fetch(PDO::FETCH_ASSOC)) {
                $impuestos = [
                    'ENVIO'       => (float)($row['ENVIO'] ?? 0),
                    'ARANCEL'     => (float)($row['ARANCEL'] ?? 0),
                    'DESADUANAJE' => (float)($row['DESADUANAJE'] ?? 0),
                    'FLETE'       => (float)($row['FLETE'] ?? 0),
                ];
            }
        } catch (Throwable $e) {
            $impuestos = [];
        }

        $recargos = [];
        try {
            $st = $this->pdo->prepare(
                "SELECT NOMBRE_RERCARGO, VALOR_RECARGO
                   FROM recargos
                  WHERE ID_COTIZACION = :c
               ORDER BY ID_RECARGOS ASC"
            );
            $st->execute([':c' => $idCot]);
            foreach ($st->fetchAll(PDO::FETCH_ASSOC) as $r) {
                $recargos[] = [
                    'nombre' => (string)($r['NOMBRE_RERCARGO'] ?? ''),
                    'valor'  => (float)($r['VALOR_RECARGO'] ?? 0),
                ];
            }
        } catch (Throwable $e) {
            $recargos = [];
        }

        return [$impuestos, $recargos];
    }

    /** Suma de pagos vigentes (ESTADO <> 0) para una cotización. MONTO en GTQ. */
    public function sumPagado(int $idCot): float
    {
        try {
            $st = $this->pdo->prepare(
                "SELECT COALESCE(SUM(MONTO), 0)
                   FROM pago
                  WHERE ID_COTIZACION = :c
                    AND (ESTADO IS NULL OR ESTADO <> '0')"
            );
            $st->execute([':c' => $idCot]);
            return (float)$st->fetchColumn();
        } catch (Throwable $e) {
            return 0.0;
        }
    }

    /** Lista pagos de la cotización. */
    public function listarPagos(int $idCot): array
    {
        $st = $this->pdo->prepare(
            "SELECT ID_PAGO, ID_COTIZACION, DESCRIPCION, MONTO, SALDO, FECHA_PAGO, ESTADO, FORMA_PAGO
               FROM pago
              WHERE ID_COTIZACION = :c
              ORDER BY ID_PAGO DESC"
        );
        $st->execute([':c' => $idCot]);
        return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /** Anula un pago (ESTADO=0) y setea FECHA_PAGO_ANULADO. */
    public function anularPago(int $idPago): bool
    {
        try {
            $st = $this->pdo->prepare(
                "UPDATE pago
                    SET ESTADO = '0',
                        FECHA_PAGO_ANULADO = :f
                  WHERE ID_PAGO = :id"
            );
            return $st->execute([
                ':f'  => date('Y-m-d H:i:s'),
                ':id' => $idPago,
            ]);
        } catch (Throwable $e) {
            return false;
        }
    }

    /**
     * Crea un pago en GTQ.
     * - Calcula SALDO: totalQ - (pagadoPrevio + montoActual).
     * - ESTADO inicial: 1 (vigente).
     * - Si transferencia/depósito, guarda imagen del comprobante si se provee.
     */
    public function crearPago(
        int $idCot,
        int $idCliente,
        string $descripcion,
        float $montoQ,
        float $totalQ,
        string $formaPago,           // tarjeta | transferencia | deposito | contraentrega
        ?array $tarjeta = null,      // ['tipo_tarjeta','codigo','exp_month','exp_year','nombre']
        ?array $transfer = null      // ['id_banco','comprobante','img_path']
    ): bool {
        try {
            $this->pdo->beginTransaction();

            // Sumas previas (GTQ)
            $sumPrev = $this->sumPagado($idCot);
            $saldo = max($totalQ - ($sumPrev + $montoQ), 0);

            // Insert en pago (ESTADO=1)
            $st = $this->pdo->prepare(
                "INSERT INTO pago
                    (ID_COTIZACION, DESCRIPCION, MONTO, SALDO, FECHA_PAGO, ESTADO, FORMA_PAGO, ID_ADMIN, ID_ADMIN_ANULO, FECHA_PAGO_ANULADO)
                 VALUES
                    (:cot, :des, :monto, :saldo, :fp, :est, :forma, NULL, NULL, NULL)"
            );
            $ok = $st->execute([
                ':cot'   => $idCot,
                ':des'   => $descripcion,
                ':monto' => $montoQ,
                ':saldo' => $saldo,
                ':fp'    => date('Y-m-d H:i:s'),
                ':est'   => '1',                 // <- vigente
                ':forma' => $formaPago,
            ]);
            if (!$ok) { $this->pdo->rollBack(); return false; }

            $idPago = (int)$this->pdo->lastInsertId();

            // Tarjeta
            if ($formaPago === 'tarjeta' && $tarjeta) {
                $st2 = $this->pdo->prepare(
                    "INSERT INTO tarjeta
                        (ID_PAGO, TIPO_TARJETA, CODIGO, EXPIRACION_MONTH, EXPIRACION_YEAR, CVV, NOMBRE)
                     VALUES
                        (:pago, :tipo, :cod, :mm, :yy, NULL, :nom)"
                );
                $st2->execute([
                    ':pago' => $idPago,
                    ':tipo' => (string)($tarjeta['tipo_tarjeta'] ?? ''),
                    ':cod'  => (string)($tarjeta['codigo'] ?? ''),      // últimos 4
                    ':mm'   => (int)($tarjeta['exp_month'] ?? 0),
                    ':yy'   => (int)($tarjeta['exp_year'] ?? 0),
                    ':nom'  => (string)($tarjeta['nombre'] ?? ''),
                ]);
            }

            // Transferencia / Depósito
            if (($formaPago === 'transferencia' || $formaPago === 'deposito') && $transfer) {
                $st3 = $this->pdo->prepare(
                    "INSERT INTO transferencia
                        (ID_PAGO, ID_BANCO, COMPROBANTE, IMAGEN_COMPROBANTE)
                     VALUES
                        (:pago, :banco, :comp, :img)"
                );
                $st3->execute([
                    ':pago' => $idPago,
                    ':banco'=> (int)($transfer['id_banco'] ?? 0),
                    ':comp' => (string)($transfer['comprobante'] ?? ''),
                    ':img'  => (string)($transfer['img_path'] ?? ''), // puede ser vacío si no se subió
                ]);
            }

            $this->pdo->commit();
            return true;
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) $this->pdo->rollBack();
            return false;
        }
    }
}

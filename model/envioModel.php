<?php
// model/envioModel.php
declare(strict_types=1);

class EnvioModel
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        if (session_status() === PHP_SESSION_NONE) session_start();
    }

    public function getCliente(int $idCliente): array
    {
        $st = $this->pdo->prepare(
            "SELECT ID, NOMBRE_COMPLETO, TELEFONO, DIRECCION, UBICACION,
                    LONGITUD, LATITUD, EMAIL, NIT, DIRECCION_ENTREGA, FOTOGRAFIA_CLIENTE
             FROM cliente
             WHERE ID = :id
             LIMIT 1"
        );
        $st->execute([':id' => $idCliente]);
        return $st->fetch(PDO::FETCH_ASSOC) ?: [];
    }

    public function updateClienteEnvio(int $idCliente, array $data): bool
    {
        // OJO: usamos WHERE ID = :id (coincide con getCliente)
        $sql = "UPDATE cliente
                   SET NOMBRE_COMPLETO   = :nom,
                       DIRECCION         = :dir,
                       TELEFONO          = :tel,
                       NIT               = :nit,
                       DIRECCION_ENTREGA = :dir_ent,
                       UBICACION         = :ubi,
                       LATITUD           = :lat,
                       LONGITUD          = :lon
                 WHERE ID = :id";

        try {
            $st = $this->pdo->prepare($sql);
            return $st->execute([
                ':nom'     => $data['nombre'],
                ':dir'     => $data['direccion'],
                ':tel'     => $data['telefono'] ?: null,
                ':nit'     => $data['nit'] ?: null,
                ':dir_ent' => $data['direccion_entrega'] ?: null,
                ':ubi'     => $data['ubicacion'] ?: null,
                ':lat'     => $data['latitud'],
                ':lon'     => $data['longitud'],
                ':id'      => $idCliente,
            ]);
        } catch (Throwable $e) {
            error_log('SQL updateClienteEnvio: ' . $e->getMessage());
            return false;
        }
    }
}

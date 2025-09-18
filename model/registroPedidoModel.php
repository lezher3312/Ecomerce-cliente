<?php
// model/registroPedidoModel.php
declare(strict_types=1);

class RegistroPedidoModel
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        if (session_status() === PHP_SESSION_NONE) session_start();
    }

    public function existeEmail(string $email): bool
    {
        $st = $this->pdo->prepare("SELECT 1 FROM cliente WHERE EMAIL = :e LIMIT 1");
        $st->execute([':e' => $email]);
        return (bool)$st->fetchColumn();
    }

    public function existeUsuario(string $usuario): bool
    {
        $st = $this->pdo->prepare("SELECT 1 FROM cliente WHERE USUARIO_PAGINA = :u LIMIT 1");
        $st->execute([':u' => $usuario]);
        return (bool)$st->fetchColumn();
    }

    /**
     * Crea el cliente y devuelve ID_CLIENTE (int) o 0 en error.
     * Campos usados de `cliente`:
     * NOMBRE_COMPLETO, TELEFONO, EMAIL, USUARIO_PAGINA, PASSWORD_PAGINA (hash),
     * NIT, DIRECCION, FOTOGRAFIA_CLIENTE, FECHA_CREACION, ESTADO, CONFIRMADO.
     */
    public function crearCliente(array $data): int
    {
        $hash = password_hash($data['passwordPlano'], PASSWORD_DEFAULT);

        $sql = "INSERT INTO cliente
                   (NOMBRE_COMPLETO, TELEFONO, EMAIL,
                    USUARIO_PAGINA, PASSWORD_PAGINA,
                    NIT, DIRECCION, FOTOGRAFIA_CLIENTE,
                    FECHA_CREACION, ESTADO, CONFIRMADO)
                VALUES
                   (:nom, :tel, :email,
                    :usr, :pwd,
                    :nit, :dir, :foto,
                    NOW(), 1, 1)";
        $st = $this->pdo->prepare($sql);
        $ok = $st->execute([
            ':nom'  => $data['nombre'],
            ':tel'  => $data['telefono'] ?: null,
            ':email'=> $data['email'],
            ':usr'  => $data['usuario'],
            ':pwd'  => $hash,
            ':nit'  => $data['nit'] ?: null,
            ':dir'  => $data['direccion'] ?: null,  // 👈 DIRECCION (no DIRECCION_ENTREGA)
            ':foto' => ($data['foto'] ?? '') !== '' ? $data['foto'] : '', // evita NOT NULL
        ]);

        return $ok ? (int)$this->pdo->lastInsertId() : 0;
    }
}

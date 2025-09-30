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
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function existeEmail(string $email): bool
    {
        $st = $this->pdo->prepare("SELECT 1 FROM cliente WHERE LOWER(EMAIL) = LOWER(:e) LIMIT 1");
        $st->execute([':e' => trim($email)]);
        return (bool)$st->fetchColumn();
    }

    public function existeUsuario(string $usuario): bool
    {
        $st = $this->pdo->prepare("SELECT 1 FROM cliente WHERE LOWER(USUARIO_PAGINA) = LOWER(:u) LIMIT 1");
        $st->execute([':u' => trim($usuario)]);
        return (bool)$st->fetchColumn();
    }

    public function crearCliente(array $data): int
    {
        $hash = password_hash($data['passwordPlano'], PASSWORD_DEFAULT);

        $sql = "INSERT INTO cliente
                   (NOMBRE_COMPLETO, TELEFONO, EMAIL,
                    USUARIO_PAGINA, PASSWORD_PAGINA,
                    DIRECCION, FECHA_CREACION, ESTADO, CONFIRMADO)
                VALUES
                   (:nom, :tel, :email,
                    :usr, :pwd,
                    :dir, NOW(), 1, 1)";
        $st = $this->pdo->prepare($sql);
        $ok = $st->execute([
            ':nom'   => $data['nombre'],
            ':tel'   => $data['telefono'] ?: null,
            ':email' => $data['email'],
            ':usr'   => $data['usuario'],
            ':pwd'   => $hash,
            ':dir'   => $data['direccion'] ?: null,
        ]);

        return $ok ? (int)$this->pdo->lastInsertId() : 0;
    }
}

<?php
require_once __DIR__ . '/../config/conexion.php';

class UsuarioModel {
    /** @var PDO */
    private $db;

    public function __construct() {
        // obtenemos la conexión desde la clase Conexion
        $this->db = Conexion::getConexion();
    }

    // 🔹 Listar todos los usuarios
    public function listarUsuarios(): array {
        $sql = "SELECT id_usuario, nombre, email, id_rol, fecha_registro 
                  FROM usuarios 
              ORDER BY fecha_registro DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    // 🔹 Obtener un usuario por ID
    public function obtenerUsuario(int $id): ?array {
        $sql = "SELECT id_usuario, nombre, email, id_rol, fecha_registro 
                  FROM usuarios 
                 WHERE id_usuario = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        $usuario = $stmt->fetch();
        return $usuario ?: null; // retorna null si no existe
    }

    // 🔹 Registrar un nuevo usuario
    public function registrarUsuario(string $nombre, string $email, string $contrasena, int $id_rol = 6): bool {
        $sql = "INSERT INTO usuarios (nombre, email, contrasena, id_rol, fecha_registro) 
                VALUES (?, ?, ?, ?, NOW())";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$nombre, $email, $contrasena, $id_rol]);
    }

    // 🔹 Actualizar datos de usuario
    public function actualizarUsuario(int $id, string $nombre, string $email, int $id_rol): bool {
        $sql = "UPDATE usuarios 
                   SET nombre = ?, email = ?, id_rol = ? 
                 WHERE id_usuario = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$nombre, $email, $id_rol, $id]);
    }

    // 🔹 Eliminar usuario
    public function eliminarUsuario(int $id): bool {
        $sql = "DELETE FROM usuarios WHERE id_usuario = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }
}

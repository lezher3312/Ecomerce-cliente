<?php
require_once __DIR__ . '/../config/conexion.php';

class ProductoModel {
    private $db;

    public function __construct() {
        $this->db = Conexion::getConexion();
    }

    // Obtener categorías
    public function obtenerCategorias() {
        $sql = "SELECT ID_CATPRODUCTO AS id_categoria, NOMBRE AS nombre
                  FROM cat_producto
                 WHERE ESTADO = 1";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    // Obtener productos filtrados
    public function obtenerProductos($categoria = null, $min = null, $max = null, $orden = 'recientes') {
        $sql = "SELECT p.ID_PRODUCTO AS id_producto,
                       p.NOMBRE_PRODUCTO AS nombre,
                       p.DESCRIPCION AS descripcion,
                       p.PRECIO AS precio,
                       p.FOTOGRAFIA_PRODUCTO AS imagen_principal,
                       c.NOMBRE AS categoria
                  FROM producto p
             LEFT JOIN cat_producto c ON c.ID_CATPRODUCTO = p.ID_CATPRODUCTO
                 WHERE p.ESTADO = 1";

        $params = [];
        // filtros dinámicos
        if ($categoria) {
            $sql .= " AND p.ID_CATPRODUCTO = ?";
            $params[] = $categoria;
        }
        if ($min !== null && $min !== '') {
            $sql .= " AND p.PRECIO >= ?";
            $params[] = $min;
        }
        if ($max !== null && $max !== '') {
            $sql .= " AND p.PRECIO <= ?";
            $params[] = $max;
        }

        // ordenamiento
        switch ($orden) {
            case 'precio_asc':
                $sql .= " ORDER BY p.PRECIO ASC";
                break;
            case 'precio_desc':
                $sql .= " ORDER BY p.PRECIO DESC";
                break;
            default:
                $sql .= " ORDER BY p.FECHA_CREACION DESC";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
 
    public function obtenerPorId($id) {
    $sql = "SELECT p.ID_PRODUCTO AS id_producto,
                   p.NOMBRE_PRODUCTO AS nombre,
                   p.DESCRIPCION AS descripcion,
                   p.PRECIO AS precio,
                   p.FOTOGRAFIA_PRODUCTO AS imagen_principal,
                   c.NOMBRE AS categoria,
                   p.EXISTENCIA AS existencia,
                   p.FECHA_CREACION,
                   p.ESTADO
              FROM producto p
         LEFT JOIN cat_producto c ON c.ID_CATPRODUCTO = p.ID_CATPRODUCTO
             WHERE p.ID_PRODUCTO = ? AND p.ESTADO = 1";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([$id]);
    return $stmt->fetch();
}


}

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
public function obtenerNovedades() {
    $sql = "SELECT ID_PRODUCTO AS id_producto, 
                   NOMBRE_PRODUCTO AS nombre,
                   DESCRIPCION AS descripcion,
                   PRECIO AS precio,
                   FOTOGRAFIA_PRODUCTO AS imagen_principal,
                   FECHA_CREACION
              FROM producto
             WHERE ESTADO = 1
               AND FECHA_CREACION >= NOW() - INTERVAL 5 DAY
          ORDER BY FECHA_CREACION DESC";
    return $this->db->query($sql)->fetchAll();
}

public function obtenerMasVendidos() {
    // Simulación: menor existencia = más vendido
    $sql = "SELECT ID_PRODUCTO AS id_producto, 
                   NOMBRE_PRODUCTO AS nombre,
                   DESCRIPCION AS descripcion,
                   PRECIO AS precio,
                   FOTOGRAFIA_PRODUCTO AS imagen_principal,
                   EXISTENCIA
              FROM producto
             WHERE ESTADO = 1
          ORDER BY EXISTENCIA ASC
             LIMIT 10";
    return $this->db->query($sql)->fetchAll();
}

public function obtenerOfertas($limit = 50) {
    $sql = "SELECT p.ID_PRODUCTO AS id_producto,
                   p.NOMBRE_PRODUCTO AS nombre,
                   p.DESCRIPCION AS descripcion,
                   p.PRECIO AS precio,
                   p.FOTOGRAFIA_PRODUCTO AS imagen_principal,
                   c.NOMBRE AS categoria,
                   p.OFERTA,
                   p.PORCENTAJE_OFERTA,
                   p.INICIO_OFERTA,
                   p.FIN_OFERTA
              FROM producto p
         LEFT JOIN cat_producto c ON c.ID_CATPRODUCTO = p.ID_CATPRODUCTO
             WHERE p.ESTADO = 1
               AND p.OFERTA IN (1,2) -- 1=activa, 2=futura
          ORDER BY p.INICIO_OFERTA DESC
             LIMIT ?";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([$limit]);
    $productos = $stmt->fetchAll();

    foreach ($productos as &$p) {
        $ahora = date('Y-m-d');
        if ($p['OFERTA'] == 1 &&
            $p['INICIO_OFERTA'] <= $ahora &&
            $p['FIN_OFERTA'] >= $ahora) {
            // calcular precio con descuento
            $descuento = ($p['PORCENTAJE_OFERTA'] / 100) * $p['precio'];
            $p['precio_oferta'] = $p['precio'] - $descuento;
        } else {
            $p['precio_oferta'] = null; // futura o no válida aún
        }
    }
    return $productos;
}



}

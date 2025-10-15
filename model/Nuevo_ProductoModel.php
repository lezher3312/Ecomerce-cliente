<?php
// model/Nuevo_ProductoModel.php
declare(strict_types=1);

class Nuevo_ProductoModel
{
    private PDO $pdo;

    // Tablas y columnas reales
    private const TAB_CATEGORIA = 'cat_producto';
    private const COL_CAT_ID    = 'ID_CATPRODUCTO';
    private const COL_CAT_NOM   = 'NOMBRE';

    private const TAB_PROVEEDOR = 'proveedor';
    private const COL_PROV_ID   = 'ID_PROVEEDOR';
    private const COL_PROV_NOM  = 'NOMBRE';

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    /** Devuelve: [ ['id'=>int, 'nombre'=>string], ... ] */
    public function getCategorias(): array
    {
        $sql = "SELECT " . self::COL_CAT_ID . " AS id, " . self::COL_CAT_NOM . " AS nombre
                  FROM " . self::TAB_CATEGORIA . "
                 ORDER BY " . self::COL_CAT_NOM . " ASC";
        try {
            $st = $this->pdo->query($sql);
            return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Throwable $e) {
            return [];
        }
    }

    /** Devuelve: [ ['id'=>int, 'nombre'=>string], ... ] */
    public function getProveedores(): array
    {
        $sql = "SELECT " . self::COL_PROV_ID . " AS id, " . self::COL_PROV_NOM . " AS nombre
                  FROM " . self::TAB_PROVEEDOR . "
                 ORDER BY " . self::COL_PROV_NOM . " ASC";
        try {
            $st = $this->pdo->query($sql);
            return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Throwable $e) {
            return [];
        }
    }

    /**
     * Inserta en 'producto' y retorna el ID_PRODUCTO generado.
     * Espera keys: ID_CATPRODUCTO, ID_PROVEEDOR, NOMBRE_PRODUCTO, DESCRIPCION, PRECIO, LINK, ESTADO
     */
    public function insertProducto(array $data): int
    {
        $sql = "
            INSERT INTO producto
                (ID_CATPRODUCTO, ID_PROVEEDOR, NOMBRE_PRODUCTO, DESCRIPCION, PRECIO, LINK, ESTADO, FECHA_CREACION)
            VALUES
                (:cat, :prov, :nom, :des, :pre, :lnk, :est, NOW())
        ";
        $st = $this->pdo->prepare($sql);
        $st->execute([
            ':cat' => $data['ID_CATPRODUCTO'] ?? null,
            ':prov'=> $data['ID_PROVEEDOR'] ?? null,
            ':nom' => (string)($data['NOMBRE_PRODUCTO'] ?? ''),
            ':des' => $data['DESCRIPCION'] ?? null,
            ':pre' => (float)($data['PRECIO'] ?? 0),
            ':lnk' => $data['LINK'] ?? null,
            ':est' => (int)($data['ESTADO'] ?? 1),
        ]);
        return (int)$this->pdo->lastInsertId();
    }
}

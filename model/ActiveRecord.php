<?php
namespace Model;

require_once __DIR__ . '/../config/conexion.php';

use PDO;

class ActiveRecord {

    protected static $db; 
    protected static $tabla = '';
    protected static $columnasDB = [];
    protected static $alertas = [];

    // Initialize DB automatically using Conexion singleton
    public static function setDB($database = null) {
        if ($database) {
            self::$db = $database;
        } elseif (!self::$db) {
            self::$db = \Conexion::getConexion();
        }
    }

    // Alerts
    public static function setAlerta($tipo, $mensaje) {
        static::$alertas[$tipo][] = $mensaje;
    }

    public static function getAlertas() {
        return static::$alertas;
    }

    public function validar() {
        static::$alertas = [];
        return static::$alertas;
    }

    // Execute SELECT queries
    public static function consultarSQL($query, $params = []) {
        self::setDB(); // ensure DB is initialized
        $stmt = self::$db->prepare($query);
        $stmt->execute($params);
        $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $array = [];
        foreach ($registros as $registro) {
            $array[] = static::crearObjeto($registro);
        }
        return $array;
    }

    // Convert DB row to object
    protected static function crearObjeto($registro) {
        $objeto = new static;
        foreach ($registro as $key => $value) {
            if (property_exists($objeto, $key)) {
                $objeto->$key = $value;
            }
        }
        return $objeto;
    }

    public function atributos() {
        $atributos = [];
        foreach (static::$columnasDB as $columna) {
            if ($columna === 'ID') continue;
            $atributos[$columna] = $this->$columna ?? null;
        }
        return $atributos;
    }

    public function sincronizar($args = []) {
        foreach ($args as $key => $value) {
            if (property_exists($this, $key) && !is_null($value)) {
                $this->$key = $value;
            }
        }
    }

    public function guardar() {
        return $this->ID ? $this->actualizar() : $this->crear();
    }

    public static function all($orden = 'DESC') {
        $query = "SELECT * FROM " . static::$tabla . " ORDER BY ID {$orden}";
        return self::consultarSQL($query);
    }

    public static function find($ID) {
        self::setDB();
        $query = "SELECT * FROM " . static::$tabla . " WHERE ID = :ID LIMIT 1";
        $stmt = self::$db->prepare($query);
        $stmt->execute(['ID' => $ID]);
        $registro = $stmt->fetch(PDO::FETCH_ASSOC);
        return $registro ? static::crearObjeto($registro) : null;
    }

    public static function get($limite) {
        self::setDB();
        $query = "SELECT * FROM " . static::$tabla . " ORDER BY ID DESC LIMIT :limite";
        $stmt = self::$db->prepare($query);
        $stmt->bindValue(':limite', (int)$limite, PDO::PARAM_INT);
        $stmt->execute();
        $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return array_map([static::class, 'crearObjeto'], $registros);
    }

    public function crear() {
        self::setDB();
        $atributos = $this->atributos();
        $columnas = array_keys($atributos);
        $placeholders = array_map(fn($col) => ":$col", $columnas);

        $query = "INSERT INTO " . static::$tabla . " (" 
               . join(', ', $columnas) 
               . ") VALUES (" . join(', ', $placeholders) . ")";

        $stmt = self::$db->prepare($query);
        $resultado = $stmt->execute($atributos);

        $this->ID = self::$db->lastInsertId();
        return $resultado;
    }

    public function actualizar() {
        self::setDB();
        $atributos = $this->atributos();
        $valores = [];
        foreach ($atributos as $key => $value) {
            $valores[] = "{$key} = :{$key}";
        }
        $query = "UPDATE " . static::$tabla 
               . " SET " . join(', ', $valores)
               . " WHERE ID = :ID LIMIT 1";
        $atributos['ID'] = $this->ID;
        $stmt = self::$db->prepare($query);
        return $stmt->execute($atributos);
    }

    public function eliminar() {
        self::setDB();
        $query = "DELETE FROM " . static::$tabla . " WHERE ID = :ID LIMIT 1";
        $stmt = self::$db->prepare($query);
        return $stmt->execute(['ID' => $this->ID]);
    }

    public static function where($columna, $valor) {
        self::setDB();
        $query = "SELECT * FROM " . static::$tabla . " WHERE {$columna} = :valor LIMIT 1";
        $stmt = self::$db->prepare($query);
        $stmt->execute(['valor' => $valor]);
        $registro = $stmt->fetch(PDO::FETCH_ASSOC);
        return $registro ? static::crearObjeto($registro) : null;
    }

    public static function whereArray($array = []) {
        self::setDB();
        $valores = [];
        foreach ($array as $key => $value) {
            $valores[] = "{$key} = :{$key}";
        }
        $query = "SELECT * FROM " . static::$tabla . " WHERE " . join(' AND ', $valores) . " LIMIT 1";
        $stmt = self::$db->prepare($query);
        $stmt->execute($array);
        $registro = $stmt->fetch(PDO::FETCH_ASSOC);
        return $registro ? static::crearObjeto($registro) : null;
    }

    public static function total($columna = '', $valor = '') {
        self::setDB();
        $query = "SELECT COUNT(*) FROM " . static::$tabla;
        $params = [];
        if ($columna) {
            $query .= " WHERE {$columna} = :valor";
            $params['valor'] = $valor;
        }
        $stmt = self::$db->prepare($query);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    public static function totalArray($array = []) {
        self::setDB();
        $query = "SELECT COUNT(*) FROM " . static::$tabla;
        $params = [];
        if (!empty($array)) {
            $valores = [];
            foreach ($array as $key => $value) {
                $valores[] = "{$key} = :{$key}";
                $params[$key] = $value;
            }
            $query .= " WHERE " . join(' AND ', $valores);
        }
        $stmt = self::$db->prepare($query);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }
}

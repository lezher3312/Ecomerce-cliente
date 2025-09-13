<?php
// config/conexion.php
date_default_timezone_set('America/Guatemala');

class Conexion {
    private static $host = '82.197.82.45';
    private static $db   = 'u474044222_gtis';
    private static $user = 'u474044222_gtis';
    private static $pass = 'Gtis2025/';
    private static $charset = 'utf8mb4';

    private static $pdo = null;

    public static function getConexion() {
        if (self::$pdo === null) {
            $dsn = "mysql:host=" . self::$host . ";dbname=" . self::$db . ";charset=" . self::$charset;

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Manejo de errores con excepciones
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Devuelve arrays asociativos
                PDO::ATTR_EMULATE_PREPARES   => false,                  // Prepared Statements nativos
            ];

            try {
                self::$pdo = new PDO($dsn, self::$user, self::$pass, $options);
            } catch (PDOException $e) {
                // Registrar error en log (no mostrar datos sensibles al usuario final)
                error_log("Error de conexión: " . $e->getMessage());
                die("No se pudo conectar a la base de datos, intente más tarde.");
            }
        }
        return self::$pdo;
    }
}

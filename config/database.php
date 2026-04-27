<?php
namespace Config;

use PDO;
use PDOException;

class Database {
    private static $host = '127.0.0.1';
    private static $dbname = 'torque_erp';
    private static $user = 'root';
    private static $password = '';
    private static $conn = null;

    public static function getConnection() {
        if (self::$conn === null) {
            try {
                $dsn = "mysql:host=" . self::$host . ";dbname=" . self::$dbname . ";charset=utf8mb4";
                self::$conn = new PDO($dsn, self::$user, self::$password);
                self::$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                // Forzar UTF-8 en la conexión
                self::$conn->exec("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");
                self::$conn->exec("SET CHARACTER SET utf8mb4");
            } catch (PDOException $e) {
                die("Connection error: " . $e->getMessage());
            }
        }
        return self::$conn;
    }
}

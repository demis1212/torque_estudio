<?php
/**
 * Configuración de Base de Datos para cPanel
 * 
 * INSTRUCCIONES:
 * 1. Crear BD en cPanel → MySQL Database Wizard
 * 2. Anotar: nombre BD, usuario, password
 * 3. Actualizar las variables de abajo
 * 4. Renombrar este archivo a database.php (o copiar contenido)
 */

namespace App\Config;

class Database {
    // ============================================
    // CONFIGURACIÓN CPANEL - ACTUALIZAR ESTO
    // ============================================
    
    // Host: Generalmente 'localhost' en cPanel compartido
    // Si no funciona, consulta tu proveedor de hosting
    private static $host = 'localhost';
    
    // Base de datos: El nombre completo que cPanel te da
    // Ejemplo: 'tucpanel_torque' (incluye el prefijo de tu cuenta)
    private static $dbname = 'TUCPANEL_PREFIJO_torque';
    
    // Usuario: El nombre completo del usuario de BD
    // Ejemplo: 'tucpanel_torqueuser'
    private static $user = 'TUCPANEL_PREFIJO_torqueuser';
    
    // Password: El password que generaste para el usuario
    private static $password = 'TU_PASSWORD_SEGURO_AQUI';
    
    // ============================================
    
    /**
     * Obtener conexión PDO a la base de datos
     * @return PDO
     * @throws Exception
     */
    public static function getConnection() {
        try {
            $dsn = "mysql:host=" . self::$host . ";dbname=" . self::$dbname . ";charset=utf8mb4";
            
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
            ];
            
            $pdo = new PDO($dsn, self::$user, self::$password, $options);
            
            return $pdo;
            
        } catch (PDOException $e) {
            // Log del error para debugging
            error_log("[DATABASE ERROR] " . date('Y-m-d H:i:s') . ": " . $e->getMessage());
            
            // Mensaje amigable para producción
            throw new \Exception("Error de conexión a la base de datos. Por favor, verifica la configuración en app/config/database.php");
        }
    }
    
    /**
     * Verificar conexión a la base de datos
     * @return bool
     */
    public static function testConnection() {
        try {
            $conn = self::getConnection();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Obtener información de configuración (sin password)
     * @return array
     */
    public static function getConfig() {
        return [
            'host' => self::$host,
            'database' => self::$dbname,
            'user' => self::$user,
            'charset' => 'utf8mb4'
        ];
    }
}

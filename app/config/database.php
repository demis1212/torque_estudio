<?php
/**
 * Configuración de Base de Datos para InfinityFree
 * 
 * CUENTA: if0_41764302
 * URL Panel: https://dash.infinityfree.com/accounts/if0_41764302
 */

namespace App\Config;

class Database {
    // ============================================
    // CONFIGURACIÓN INFINITYFREE - ACTUALIZAR ESTO
    // ============================================
    
    // Host: Generalmente 'localhost' en InfinityFree
    // O el servidor SQL específico que muestre cPanel (ej: sqlXXX.epizy.com)
    private static $host = 'localhost';
    
    // Base de datos: El nombre completo que aparece en cPanel
    // Ejemplo: 'if0_41764302_torque'
    private static $dbname = 'if0_41764302_torque';
    
    // Usuario: El nombre completo del usuario MySQL
    // Ejemplo: 'if0_41764302_torqueuser'
    private static $user = 'if0_41764302_torqueuser';
    
    // Password: El password que generaste al crear el usuario
    private static $password = 'cocodrilo1AA';
    
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
            // Log del error
            error_log("[INFINITYFREE DB ERROR] " . date('Y-m-d H:i:s') . ": " . $e->getMessage());
            
            // Mensaje seguro para producción
            throw new \Exception("Error de conexión a la base de datos. Verifica la configuración.");
        }
    }
    
    /**
     * Verificar conexión
     * @return bool
     */
    public static function testConnection() {
        try {
            self::getConnection();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}

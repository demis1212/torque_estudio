<?php
/**
 * Configuración General de la Aplicación
 * Torque Studio ERP
 */

namespace App\Config;

class Config {
    
    // ============================================
    // CONFIGURACIÓN DE LA APLICACIÓN
    // ============================================
    
    // Modo de la aplicación: 'development', 'production'
    const APP_ENV = 'production';
    
    // URL base de la aplicación (sin barra al final)
    // Ejemplo para InfinityFree: 'https://tu-dominio.epizy.com'
    const APP_URL = 'https://tu-dominio.epizy.com';
    
    // Nombre de la aplicación
    const APP_NAME = 'Torque Studio ERP';
    
    // Versión
    const APP_VERSION = '1.0.0';
    
    // Zona horaria
    const TIMEZONE = 'America/Caracas';
    
    // ============================================
    // CONFIGURACIÓN DE SESIÓN
    // ============================================
    
    // Tiempo de expiración de sesión (en segundos) - 2 horas
    const SESSION_LIFETIME = 7200;
    
    // Nombre de la cookie de sesión
    const SESSION_NAME = 'torque_session';
    
    // ============================================
    // CONFIGURACIÓN DE SEGURIDAD
    // ============================================
    
    // Clave secreta para tokens CSRF (cambiar en producción)
    const CSRF_SECRET = 'tu-clave-secreta-aqui-cambiar-en-produccion';
    
    // Intentos máximos de login antes de bloqueo
    const MAX_LOGIN_ATTEMPTS = 5;
    
    // Tiempo de bloqueo por intentos fallidos (en segundos) - 15 minutos
    const LOCKOUT_TIME = 900;
    
    // ============================================
    // CONFIGURACIÓN DE BASE DE DATOS
    // (Se usa database.php para la conexión PDO)
    // ============================================
    
    // Prefijo de tablas (opcional)
    const DB_PREFIX = '';
    
    // ============================================
    // CONFIGURACIÓN DE UPLOADS
    // ============================================
    
    // Tamaño máximo de archivo (en MB)
    const MAX_UPLOAD_SIZE = 10;
    
    // Tipos de archivo permitidos
    const ALLOWED_UPLOAD_TYPES = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
    
    // Directorio de uploads (relativo a public/)
    const UPLOAD_DIR = 'uploads/';
    
    // ============================================
    // CONFIGURACIÓN DE EMAIL (SMTP)
    // ============================================
    
    const SMTP_HOST = 'smtp.tudominio.com';
    const SMTP_PORT = 587;
    const SMTP_USER = 'noreply@tudominio.com';
    const SMTP_PASS = 'tu-password-smtp';
    const SMTP_FROM = 'noreply@tudominio.com';
    const SMTP_FROM_NAME = 'Torque Studio ERP';
    
    // ============================================
    // MÉTODOS ÚTILES
    // ============================================
    
    /**
     * Obtener URL base de la aplicación
     * @return string
     */
    public static function getBaseUrl() {
        return self::APP_URL;
    }
    
    /**
     * Verificar si estamos en modo desarrollo
     * @return bool
     */
    public static function isDevelopment() {
        return self::APP_ENV === 'development';
    }
    
    /**
     * Verificar si estamos en modo producción
     * @return bool
     */
    public static function isProduction() {
        return self::APP_ENV === 'production';
    }
    
    /**
     * Obtener ruta absoluta de uploads
     * @return string
     */
    public static function getUploadPath() {
        return __DIR__ . '/../../public/' . self::UPLOAD_DIR;
    }
}

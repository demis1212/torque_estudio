<?php
// =====================================================
// CONFIGURACIÓN UTF-8 FORZADA
// =====================================================
// Forzar codificación UTF-8 en todo el output
header('Content-Type: text/html; charset=utf-8');

// Configurar PHP para UTF-8
ini_set('default_charset', 'utf-8');
mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');
mb_regex_encoding('UTF-8');

// Asegurar que la conexión PDO también use UTF-8
ini_set('pdo_mysql.default_charset', 'utf8mb4');

session_start();

// Simple Autoloader
spl_autoload_register(function ($class) {
    // Expected: App\Controllers\UserController, App\Models\User, Config\Database
    $parts = explode('\\', $class);
    $className = array_pop($parts);
    $path = strtolower(implode(DIRECTORY_SEPARATOR, $parts));
    
    // File path
    $file = dirname(__DIR__) . DIRECTORY_SEPARATOR . $path . DIRECTORY_SEPARATOR . $className . '.php';
    
    // Also try lowercase for the file name in case of config/database.php
    $fileLower = dirname(__DIR__) . DIRECTORY_SEPARATOR . $path . DIRECTORY_SEPARATOR . strtolower($className) . '.php';

    if (file_exists($file)) {
        require_once $file;
    } elseif (file_exists($fileLower)) {
        require_once $fileLower;
    }
});

// Helper functions (e.g. for views and CSRF)
require_once dirname(__DIR__) . '/app/helpers.php';

// Load routes
require_once dirname(__DIR__) . '/routes/web.php';

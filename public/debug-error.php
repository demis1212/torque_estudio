<?php
/**
 * DEBUG ERROR - Capturar error exacto de la página
 * Torque Studio ERP
 */

// Mostrar todos los errores
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔍 Debug de Error</h1>";

try {
    echo "<h2>1. Intentando cargar configuración...</h2>";
    require_once dirname(__DIR__) . '/config/database.php';
    echo "<p>✅ Configuración cargada</p>";
    
    echo "<h2>2. Intentando conectar a BD...</h2>";
    $db = Config\Database::getConnection();
    echo "<p>✅ Base de datos conectada</p>";
    
    echo "<h2>3. Verificando sesión...</h2>";
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    echo "<p>✅ Sesión iniciada</p>";
    echo "<p>Sesión actual: " . print_r($_SESSION, true) . "</p>";
    
    echo "<h2>4. Intentando cargar helpers...</h2>";
    require_once dirname(__DIR__) . '/app/helpers.php';
    echo "<p>✅ Helpers cargados</p>";
    
    echo "<h2>5. Intentando cargar rutas...</h2>";
    require_once dirname(__DIR__) . '/routes/web.php';
    echo "<p>✅ Rutas cargadas</p>";
    
} catch (Exception $e) {
    echo "<h2 style='color: red;'>❌ ERROR CAPTURADO:</h2>";
    echo "<pre style='background: #fee; padding: 15px; border-radius: 8px;'>";
    echo "Mensaje: " . $e->getMessage() . "\n\n";
    echo "Archivo: " . $e->getFile() . "\n";
    echo "Línea: " . $e->getLine() . "\n\n";
    echo "Stack Trace:\n" . $e->getTraceAsString();
    echo "</pre>";
} catch (Error $e) {
    echo "<h2 style='color: red;'>❌ ERROR FATAL CAPTURADO:</h2>";
    echo "<pre style='background: #fee; padding: 15px; border-radius: 8px;'>";
    echo "Mensaje: " . $e->getMessage() . "\n\n";
    echo "Archivo: " . $e->getFile() . "\n";
    echo "Línea: " . $e->getLine() . "\n\n";
    echo "Stack Trace:\n" . $e->getTraceAsString();
    echo "</pre>";
}

echo "<h2>6. Información del servidor:</h2>";
echo "<pre>";
echo "PHP Version: " . PHP_VERSION . "\n";
echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "\n";
echo "Request URI: " . ($_SERVER['REQUEST_URI'] ?? 'N/A') . "\n";
echo "Script Name: " . ($_SERVER['SCRIPT_NAME'] ?? 'N/A') . "\n";
echo "</pre>";

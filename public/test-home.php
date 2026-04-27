<?php
/**
 * TEST HOME - Probar la ruta raíz específicamente
 * Torque Studio ERP
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🧪 Test de Ruta Raíz (/)</h1>";

echo "<h2>Información de Request:</h2>";
echo "<pre>";
echo "REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? 'NO SET') . "\n";
echo "SCRIPT_NAME: " . ($_SERVER['SCRIPT_NAME'] ?? 'NO SET') . "\n";
echo "PHP_SELF: " . ($_SERVER['PHP_SELF'] ?? 'NO SET') . "\n";
echo "DOCUMENT_ROOT: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'NO SET') . "\n";
echo "</pre>";

// Simular lo que hace el router
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$basePath = dirname($_SERVER['SCRIPT_NAME']);
if ($basePath === '/' || $basePath === '\\') {
    $basePath = '';
}

// Quitar base path de uri
if (strpos($uri, $basePath) === 0) {
    $uri = substr($uri, strlen($basePath));
}
if ($uri === '') $uri = '/';

echo "<h2>Variables del Router:</h2>";
echo "<pre>";
echo "uri procesado: '{$uri}'\n";
echo "basePath: '{$basePath}'\n";
echo "uri === '/': " . ($uri === '/' ? 'TRUE' : 'FALSE') . "\n";
echo "</pre>";

// Verificar sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

echo "<h2>Sesión:</h2>";
echo "<pre>";
print_r($_SESSION);
echo "user_id set: " . (isset($_SESSION['user_id']) ? 'YES' : 'NO') . "\n";
echo "</pre>";

// Probar carga de archivos
echo "<h2>Test de includes:</h2>";

try {
    require_once dirname(__DIR__) . '/config/database.php';
    echo "✅ config/database.php cargado<br>";
    
    require_once dirname(__DIR__) . '/app/helpers.php';
    echo "✅ app/helpers.php cargado<br>";
    
    $db = Config\Database::getConnection();
    echo "✅ Conexión BD exitosa<br>";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}

// Simular la lógica del router para "/"
echo "<h2>Simulación de Ruta /:</h2>";

if ($uri === '/') {
    echo "✅ URI detectado como '/'<br>";
    
    if (isset($_SESSION['user_id'])) {
        echo "→ Redirigiría a: {$basePath}/dashboard<br>";
    } else {
        echo "→ Redirigiría a: {$basePath}/login<br>";
        
        // Verificar si existe el controlador de login
        $loginFile = dirname(__DIR__) . '/app/controllers/AuthController.php';
        if (file_exists($loginFile)) {
            echo "✅ AuthController.php existe<br>";
            
            try {
                require_once $loginFile;
                echo "✅ AuthController cargado<br>";
                
                $controller = new App\Controllers\AuthController();
                echo "✅ Instancia de AuthController creada<br>";
                
                echo "→ Intentando mostrar login...<br>";
                $controller->showLogin();
                
            } catch (Exception $e) {
                echo "❌ Error: " . $e->getMessage() . "<br>";
            }
            
        } else {
            echo "❌ AuthController.php NO existe en: {$loginFile}<br>";
        }
    }
} else {
    echo "URI no es '/', es: '{$uri}'<br>";
}

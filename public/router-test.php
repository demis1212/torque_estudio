<?php
/**
 * ROUTER TEST - Probar específicamente la ruta /
 * Torque Studio ERP
 */

// Simular exactamente lo que pasa cuando vas a /
$_SERVER['REQUEST_URI'] = '/torque/';
$_SERVER['SCRIPT_NAME'] = '/torque/index.php';

echo "<h1>🔧 Router Test - Simulando /torque/</h1>";

echo "<h2>Variables simuladas:</h2>";
echo "<pre>";
echo "REQUEST_URI: {$_SERVER['REQUEST_URI']}\n";
echo "SCRIPT_NAME: {$_SERVER['SCRIPT_NAME']}\n";
echo "</pre>";

// Procesar como hace el router
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$basePath = dirname($_SERVER['SCRIPT_NAME']);

echo "<h2>Paso a paso:</h2>";
echo "<pre>";
echo "1. uri original: '{$uri}'\n";
echo "2. basePath (dirname de SCRIPT_NAME): '{$basePath}'\n";

if ($basePath === '/' || $basePath === '\\') {
    $basePath = '';
    echo "3. basePath ajustado a: '{$basePath}'\n";
}

// Quitar base path de uri
if (strpos($uri, $basePath) === 0) {
    $uri = substr($uri, strlen($basePath));
    echo "4. uri después de quitar basePath: '{$uri}'\n";
}

if ($uri === '') {
    $uri = '/';
    echo "5. uri ajustado a: '{$uri}'\n";
}

echo "\nRESULTADO FINAL:\n";
echo "uri = '{$uri}'\n";
echo "uri === '/': " . ($uri === '/' ? 'TRUE ✓' : 'FALSE ✗') . "\n";
echo "</pre>";

// Ahora probar con el valor REAL actual
echo "<h2>Valores ACTUALES del servidor:</h2>";
echo "<pre>";
echo "REAL REQUEST_URI: " . ($_SERVER['REQUEST_URI']) . "\n";
echo "REAL SCRIPT_NAME: " . ($_SERVER['SCRIPT_NAME']) . "\n";
echo "</pre>";

// Procesar los valores reales
$realUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$realBasePath = dirname($_SERVER['SCRIPT_NAME']);
if ($realBasePath === '/' || $realBasePath === '\\') $realBasePath = '';
if (strpos($realUri, $realBasePath) === 0) {
    $realUri = substr($realUri, strlen($realBasePath));
}
if ($realUri === '') $realUri = '/';

echo "<h2>Procesamiento REAL:</h2>";
echo "<pre>";
echo "uri procesado: '{$realUri}'\n";
echo "uri === '/': " . ($realUri === '/' ? 'TRUE ✓' : 'FALSE ✗') . "\n";
echo "</pre>";

// Mostrar qué debería pasar
echo "<h2>¿Qué debería pasar?</h2>";
if ($realUri === '/') {
    echo "<p>✅ Debería redirigir a login (porque no hay sesión)</p>";
} else {
    echo "<p>❌ El router no detectó '/', detectó: '{$realUri}'</p>";
    echo "<p>Esto explica el 404</p>";
}

// Sugerir fix
echo "<h2>Solución:</h2>";
echo "<p>El problema es que el router está procesando mal el URI.</p>";
echo "<p>Prueba acceder directamente al login:</p>";
echo "<ul>";
echo "<li><a href='/torque/login'>/torque/login</a></li>";
echo "<li><a href='/torque/index.php'>/torque/index.php</a></li>";
echo "</ul>";

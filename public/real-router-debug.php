<?php
/**
 * REAL ROUTER DEBUG - Ver exactamente qué pasa en /
 * Torque Studio ERP
 */

// Guardar todas las variables de servidor
echo "<h1>🕵️ REAL ROUTER DEBUG</h1>";
echo "<p>Accede a ESTA URL y luego a /torque/ y compara</p>";

echo "<h2>Variables \$_SERVER:</h2>";
echo "<pre style='background: #f5f5f5; padding: 15px;'>";
$vars = [
    'REQUEST_URI',
    'SCRIPT_NAME', 
    'PHP_SELF',
    'QUERY_STRING',
    'SCRIPT_FILENAME',
    'PATH_INFO',
    'ORIG_PATH_INFO'
];

foreach ($vars as $var) {
    $val = $_SERVER[$var] ?? 'NO SET';
    echo sprintf("%-20s: %s\n", $var, $val);
}
echo "</pre>";

// Simular el procesamiento del router
echo "<h2>Procesamiento del Router:</h2>";

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$scriptName = $_SERVER['SCRIPT_NAME'];
$basePath = dirname($scriptName);

echo "<pre>";
echo "1. parse_url(REQUEST_URI, PATH): {$uri}\n";
echo "2. dirname(SCRIPT_NAME): {$basePath}\n";

// Ajustar basePath
if ($basePath === '/' || $basePath === '\\') {
    $basePath = '';
    echo "3. basePath ajustado: '{$basePath}' (era / o \\)\n";
}

// Quitar basePath de URI
echo "4. Verificando: strpos('{$uri}', '{$basePath}') === 0\n";
if (strpos($uri, $basePath) === 0) {
    $newUri = substr($uri, strlen($basePath));
    echo "5. substr('{$uri}', strlen('{$basePath}')) = '{$newUri}'\n";
    $uri = $newUri;
} else {
    echo "5. NO coincide el strpos!\n";
}

// Ajustar URI vacío
if ($uri === '' || $uri === '/') {
    $oldUri = $uri;
    $uri = '/';
    echo "6. URI ajustado a '/' (era: '{$oldUri}')\n";
}

echo "\n<strong>RESULTADO:</strong>\n";
echo "uri final: '{$uri}'\n";
echo "uri === '/': " . ($uri === '/' ? 'TRUE ✓' : 'FALSE ✗') . "\n";
echo "</pre>";

// Diagnóstico
echo "<h2>Diagnóstico:</h2>";
if ($uri === '/') {
    echo "<p style='color: green;'>✅ El router detecta '/' correctamente</p>";
    echo "<p>El problema está en otra parte (controlador, vista, etc.)</p>";
} else {
    echo "<p style='color: red;'>❌ El router NO detecta '/' como raíz</p>";
    echo "<p>Detectó: '{$uri}' en lugar de '/'</p>";
    
    echo "<h3>Posible solución:</h3>";
    echo "<p>Modificar el router en routes/web.php para que maneje mejor el basePath.</p>";
    echo "<pre style='background: #f5f5f5; padding: 10px;'>";
    echo "// En lugar de:\n";
    echo "\$basePath = dirname(\$_SERVER['SCRIPT_NAME']);\n\n";
    echo "// Usar:\n";
    echo "\$basePath = '/torque'; // Hardcodeado temporalmente\n";
    echo "</pre>";
}

echo "<h2>Links de prueba:</h2>";
echo "<ul>";
echo "<li><a href='/torque/'>/torque/ (raíz)</a></li>";
echo "<li><a href='/torque/login'>/torque/login</a></li>";
echo "<li><a href='/torque/real-router-debug.php'>Recargar esta página</a></li>";
echo "</ul>";

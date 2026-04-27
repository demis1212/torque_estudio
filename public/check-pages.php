<?php
/**
 * CHECK PAGES - Verificar estructura HTML de todas las páginas
 * Torque Studio ERP
 */

header('Content-Type: text/html; charset=utf-8');

$pages = [
    ['/', 'Dashboard'],
    ['/clients', 'Clientes'],
    ['/services', 'Servicios'],
    ['/parts', 'Inventario'],
    ['/tools', 'Herramientas'],
    ['/work-orders', 'Órdenes'],
];

echo "<!DOCTYPE html><html><head><title>Check Pages</title></head><body style='font-family: monospace; padding: 20px;'>";
echo "<h1>Verificando estructura HTML...</h1>";

$baseUrl = 'http://localhost/torque';

foreach ($pages as $page) {
    $url = $baseUrl . $page[0];
    echo "<h2>{$page[1]} ({$page[0]})</h2>";
    
    $html = @file_get_contents($url);
    
    if ($html === false) {
        echo "<p style='color: red;'>❌ No se pudo cargar</p>";
        continue;
    }
    
    // Buscar etiquetas
    $hasDocType = stripos($html, '<!DOCTYPE') !== false;
    $hasHtml = stripos($html, '<html') !== false;
    $hasBody = stripos($html, '<body') !== false;
    $hasCloseHtml = stripos($html, '</html>') !== false;
    $hasCloseBody = stripos($html, '</body>') !== false;
    
    echo "<ul>";
    echo "<li>" . ($hasDocType ? "✅" : "❌") . " DOCTYPE</li>";
    echo "<li>" . ($hasHtml ? "✅" : "❌") . " &lt;html&gt;</li>";
    echo "<li>" . ($hasBody ? "✅" : "❌") . " &lt;body&gt;</li>";
    echo "<li>" . ($hasCloseBody ? "✅" : "❌") . " &lt;/body&gt;</li>";
    echo "<li>" . ($hasCloseHtml ? "✅" : "❌") . " &lt;/html&gt;</li>";
    echo "</ul>";
    
    // Mostrar errores PHP
    if (preg_match('/Fatal error.*<\/b>/i', $html, $matches)) {
        echo "<p style='color: red; background: #fee; padding: 10px;'>";
        echo "<strong>❌ ERROR PHP:</strong><br>";
        echo strip_tags($matches[0]);
        echo "</p>";
    }
    
    // Mostrar primeros 500 caracteres
    echo "<pre style='background: #f5f5f5; padding: 10px; overflow: auto; max-height: 200px; font-size: 12px;'>";
    echo htmlspecialchars(substr($html, 0, 500));
    echo "</pre>";
    
    // Mostrar últimos 200 caracteres
    echo "<p><strong>Últimos 200 caracteres:</strong></p>";
    echo "<pre style='background: #f5f5f5; padding: 10px; overflow: auto; font-size: 12px;'>";
    echo htmlspecialchars(substr($html, -200));
    echo "</pre>";
}

echo "</body></html>";

<?php
/**
 * RAW CHECK - Ver contenido crudo de páginas
 * Torque Studio ERP
 */

header('Content-Type: text/html; charset=utf-8');

$pages = [
    ['/', 'Dashboard'],
    ['/clients', 'Clientes'],
    ['/services', 'Servicios'],
];

$baseUrl = 'http://localhost/torque';

echo "<h1>🔍 Verificación de Páginas</h1>";

foreach ($pages as $page) {
    $url = $baseUrl . $page[0];
    echo "<h2>{$page[1]} - {$url}</h2>";
    
    $html = @file_get_contents($url);
    
    if ($html === false) {
        echo "<p style='color: red;'>❌ No se pudo cargar</p>";
        continue;
    }
    
    // Mostrar headers HTTP
    echo "<h3>Headers:</h3>";
    echo "<pre>";
    print_r($http_response_header ?? ['No headers']);
    echo "</pre>";
    
    // Mostrar primeros 1000 caracteres
    echo "<h3>Primeros 1000 caracteres:</h3>";
    echo "<pre style='background: #f5f5f5; padding: 10px; overflow: auto; max-height: 300px;'>";
    echo htmlspecialchars(substr($html, 0, 1000));
    echo "</pre>";
    
    // Verificar estructura
    echo "<h3>Verificación:</h3>";
    echo "<ul>";
    echo "<li>Longitud: " . strlen($html) . " caracteres</li>";
    echo "<li>DOCTYPE: " . (preg_match('/<!DOCTYPE/i', $html) ? '✅ Sí' : '❌ No') . "</li>";
    echo "<li>&lt;html&gt;: " . (preg_match('/<html/i', $html) ? '✅ Sí' : '❌ No') . "</li>";
    echo "<li>&lt;/html&gt;: " . (preg_match('/<\/html>/i', $html) ? '✅ Sí' : '❌ No') . "</li>";
    
    // Buscar errores PHP
    if (preg_match('/<b>(Fatal error|Parse error|Warning)<\/b>/i', $html, $matches)) {
        echo "<li style='color: red;'>❌ ERROR PHP: {$matches[1]}</li>";
    }
    echo "</ul>";
    
    echo "<hr>";
}

echo "<h2>Prueba completada</h2>";

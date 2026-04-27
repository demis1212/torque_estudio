<?php
/**
 * TEST URLS - Probar diferentes URLs para encontrar una que funcione
 * Torque Studio ERP
 */

header('Content-Type: text/html; charset=utf-8');

echo "<h1>🧪 Probando URLs</h1>";

$urls = [
    'http://localhost/',
    'http://localhost/index.php',
    'http://127.0.0.1/',
    'http://127.0.0.1/index.php',
    'http://localhost/torque/',
    'http://localhost/torque/index.php',
    'http://127.0.0.1/torque/',
    'http://127.0.0.1/torque/index.php',
];

echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
echo "<tr><th>URL</th><th>Código HTTP</th><th>Estado</th><th>Acción</th></tr>";

foreach ($urls as $url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $status = ($httpCode == 200) ? '✅ OK' : "❌ Error {$httpCode}";
    $color = ($httpCode == 200) ? 'green' : 'red';
    $link = ($httpCode == 200) ? "<a href='{$url}' target='_blank'>Abrir →</a>" : '-';
    
    echo "<tr>";
    echo "<td><code>{$url}</code></td>";
    echo "<td>{$httpCode}</td>";
    echo "<td style='color: {$color};'>{$status}</td>";
    echo "<td>{$link}</td>";
    echo "</tr>";
}

echo "</table>";

echo "<h2>Si ninguna URL da 200, el problema es:</h2>";
echo "<ol>";
echo "<li>Apache no está escuchando en el puerto 80</li>";
echo "<li>El VirtualHost está mal configurado</li>";
echo "<li>El .htaccess está causando problemas</li>";
echo "</ol>";

echo "<h2>Solución temporal:</h2>";
echo "<p>Edita <code>C:\xampp\apache\conf\httpd.conf</code></p>";
echo "<p>Busca <code>DocumentRoot</code> y cámbialo a:</p>";
echo "<pre>DocumentRoot "C:/xampp/htdocs/torque/public"
&lt;Directory "C:/xampp/htdocs/torque/public"&gt;
    Options Indexes FollowSymLinks
    AllowOverride All
    Require all granted
&lt;/Directory&gt;</pre>";
echo "<p>Luego reinicia Apache y prueba <code>http://localhost/</code></p>";

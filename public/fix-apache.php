<?php
/**
 * FIX APACHE - Diagnosticar y solucionar problemas de routing
 * Torque Studio ERP
 */

header('Content-Type: text/html; charset=utf-8');

echo "<h1>🔧 Diagnóstico de Apache</h1>";

// 1. Verificar si .htaccess existe
$htaccessPath = __DIR__ . '/.htaccess';
echo "<h2>1. Verificando .htaccess</h2>";
if (file_exists($htaccessPath)) {
    echo "<p>✅ .htaccess existe</p>";
    echo "<pre style='background: #f5f5f5; padding: 10px;'>";
    echo htmlspecialchars(file_get_contents($htaccessPath));
    echo "</pre>";
} else {
    echo "<p>❌ .htaccess NO EXISTE - Creando...</p>";
    
    $htaccess = <<<HTACCESS
RewriteEngine On
RewriteBase /torque/

# Si es un archivo o directorio real, no hacer rewrite
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d

# Reescribir todo al index.php
RewriteRule ^(.*)$ index.php [QSA,L]
HTACCESS;
    
    file_put_contents($htaccessPath, $htaccess);
    echo "<p>✅ .htaccess creado</p>";
}

// 2. Verificar mod_rewrite
echo "<h2>2. Verificando mod_rewrite</h2>";
if (in_array('mod_rewrite', apache_get_modules())) {
    echo "<p>✅ mod_rewrite está habilitado</p>";
} else {
    echo "<p>❌ mod_rewrite NO está habilitado</p>";
    echo "<p><strong>Solución:</strong></p>";
    echo "<ol>";
    echo "<li>Abre C:\xampp\apache\conf\httpd.conf</li>";
    echo "<li>Busca: <code>#LoadModule rewrite_module modules/mod_rewrite.so</code></li>";
    echo "<li>Quita el # al inicio</li>";
    echo "<li>Reinicia Apache</li>";
    echo "</ol>";
}

// 3. Verificar AllowOverride
echo "<h2>3. Verificando AllowOverride</h2>";
echo "<p>Para que .htaccess funcione, el directorio debe tener <code>AllowOverride All</code></p>";
echo "<p>Verifica en httpd.conf o httpd-vhosts.conf:</p>";
echo "<pre style='background: #f5f5f5; padding: 10px;'>
&lt;Directory " . __DIR__ . "&gt;
    AllowOverride All
    Require all granted
&lt;/Directory&gt;
</pre>";

// 4. Test de routing
echo "<h2>4. Test de Routing</h2>";

$testUrls = [
    '/torque/',
    '/torque/index.php',
    '/torque/clients',
    '/torque/services',
];

echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
echo "<tr><th>URL</th><th>Estado</th><th>Código HTTP</th></tr>";

foreach ($testUrls as $url) {
    $fullUrl = 'http://localhost' . $url;
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $fullUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $status = ($httpCode == 200) ? '✅ OK' : "❌ Error {$httpCode}";
    $color = ($httpCode == 200) ? 'green' : 'red';
    
    echo "<tr>";
    echo "<td>{$url}</td>";
    echo "<td style='color: {$color};'>{$status}</td>";
    echo "<td>{$httpCode}</td>";
    echo "</tr>";
}

echo "</table>";

// 5. Instrucciones finales
echo "<h2>5. Instrucciones para corregir</h2>";
echo "<ol>";
echo "<li><strong>Habilitar mod_rewrite:</strong>";
echo "<ul>";
echo "<li>Abre: <code>C:\xampp\apache\conf\httpd.conf</code></li>";
echo "<li>Busca: <code>#LoadModule rewrite_module</code></li>";
echo "<li>Quita el # (descomenta)</li>";
echo "<li>Guarda</li>";
echo "</ul></li>";
echo "<li><strong>Verificar AllowOverride:</strong>";
echo "<ul>";
echo "<li>En httpd.conf o httpd-vhosts.conf</li>";
echo "<li>Asegúrate que el directorio tenga <code>AllowOverride All</code></li>";
echo "</ul></li>";
echo "<li><strong>Reiniciar Apache</strong></li>";
echo "</ol>";

echo "<p><a href='/torque/' style='padding: 10px 20px; background: #4d8eff; color: white; text-decoration: none; border-radius: 8px;'>Probar de nuevo →</a></p>";

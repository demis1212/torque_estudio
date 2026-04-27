<?php
/**
 * FIX APACHE - Solución Simple: Mover a htdocs root
 * Torque Studio ERP
 */

header('Content-Type: text/html; charset=utf-8');

echo "<h1>🔧 Solución Simple para Apache</h1>";

$source = 'C:/xampp/htdocs/torque/public';
$dest = 'C:/xampp/htdocs';

// Verificar que existe source
if (!is_dir($source)) {
    echo "<p>❌ No existe: {$source}</p>";
    exit;
}

// Listar archivos en source
$files = glob($source . '/*');
echo "<p>Archivos encontrados en {$source}: " . count($files) . "</p>";
echo "<ul>";
foreach ($files as $file) {
    echo "<li>" . basename($file) . "</li>";
}
echo "</ul>";

// OPCIÓN 1: Copiar todo a htdocs
echo "<h2>Opción 1: Copiar archivos a htdocs</h2>";
echo "<p>Esto copiará los archivos de <code>{$source}</code> a <code>{$dest}</code></p>";

// Crear backup del htdocs original
$backupDir = 'C:/xampp/htdocs_backup_' . date('Ymd_His');
if (!is_dir($backupDir)) {
    mkdir($backupDir);
}

// Mover archivos antiguos de htdocs a backup (excepto xampp, dashboard, etc.)
$htdocsFiles = glob($dest . '/*');
$excluded = ['xampp', 'dashboard', 'img', 'webalizer', 'applications.html'];

foreach ($htdocsFiles as $file) {
    $name = basename($file);
    if (!in_array($name, $excluded) && is_file($file)) {
        rename($file, $backupDir . '/' . $name);
    }
}

// Copiar archivos del proyecto
$projectFiles = ['index.php', '.htaccess', 'assets', 'css'];
foreach ($projectFiles as $item) {
    $src = $source . '/' . $item;
    $dst = $dest . '/' . $item;
    
    if (is_file($src)) {
        copy($src, $dst);
        echo "<p>✅ Copiado: {$item}</p>";
    } elseif (is_dir($src)) {
        // Para directorios, necesitaríamos recursive copy
        echo "<p>📁 Directorio: {$item} (copiar manualmente)</p>";
    }
}

// Crear un index.php que redirija o incluya el del proyecto
$indexContent = file_get_contents($source . '/index.php');
file_put_contents($dest . '/index.php', $indexContent);

echo "<h2>✅ Archivos copiados</h2>";
echo "<p>Ahora prueba acceder a:</p>";
echo "<ul>";
echo "<li><a href='http://localhost/'>http://localhost/</a></li>";
echo "<li><a href='http://localhost/index.php'>http://localhost/index.php</a></li>";
echo "</ul>";

echo "<h2>Si no funciona, prueba esta URL:</h2>";
echo "<p><a href='http://127.0.0.1/torque/'>http://127.0.0.1/torque/</a></p>";
echo "<p><a href='http://127.0.0.1/torque/index.php'>http://127.0.0.1/torque/index.php</a></p>";

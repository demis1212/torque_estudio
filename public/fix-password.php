<?php
// Script para actualizar contraseña correctamente
require_once __DIR__ . '/../config/database.php';

echo "<h2>Actualizar Contraseña</h2>";

try {
    $db = \Config\Database::getConnection();
    
    // Generar hash correcto
    $newHash = password_hash('admin123', PASSWORD_DEFAULT);
    echo "<p>Nuevo hash generado: $newHash</p>";
    
    // Actualizar en BD
    $stmt = $db->prepare("UPDATE users SET password = ? WHERE email = 'admin@torque.com'");
    $stmt->execute([$newHash]);
    
    echo "<p style='color:green;'>✓ Contraseña actualizada para admin@torque.com</p>";
    echo "<p>Nuevo valor en BD: $newHash</p>";
    
    // Verificar
    $stmt = $db->prepare("SELECT password FROM users WHERE email = 'admin@torque.com'");
    $stmt->execute();
    $storedHash = $stmt->fetchColumn();
    
    echo "<p>Hash almacenado: $storedHash</p>";
    
    // Verificar que funciona
    $verify = password_verify('admin123', $storedHash);
    echo "<p>Verificación: " . ($verify ? "<b style='color:green'>CORRECTO</b>" : "<b style='color:red'>FALLO</b>") . "</p>";
    
} catch (Exception $e) {
    echo "<p style='color:red'>Error: " . $e->getMessage() . "</p>";
}

echo "<p><a href='test-login.php' style='color:#8ab4f8'>← Probar login</a></p>";

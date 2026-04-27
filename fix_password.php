<?php
require_once "c:/Users/victuspc/Desktop/Nueva carpeta/config/database.php";
$hash = password_hash("admin123", PASSWORD_DEFAULT);
$db = Config\Database::getConnection();
$stmt = $db->prepare("UPDATE users SET password = ? WHERE id IN (1,2,3)");
$stmt->execute([$hash]);
echo "Hash actualizado: " . $hash . "\n";
echo "Filas afectadas: " . $stmt->rowCount() . "\n";
?>

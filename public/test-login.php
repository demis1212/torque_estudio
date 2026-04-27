<?php
// Script para probar el login manualmente
require_once __DIR__ . '/../config/database.php';

session_start();

echo "<h2>Test de Login</h2>";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    
    echo "<p>Email ingresado: $email</p>";
    echo "<p>Password ingresado: $password</p>";
    
    try {
        $db = \Config\Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            echo "<p style='color:green'>✓ Usuario encontrado: {$user['name']}</p>";
            echo "<p>Hash en BD: {$user['password']}</p>";
            
            $verify = password_verify($password, $user['password']);
            echo "<p>Password verify: " . ($verify ? "<b style='color:green'>CORRECTO</b>" : "<b style='color:red'>INCORRECTO</b>") . "</p>";
            
            if ($verify) {
                echo "<p style='color:green;font-size:20px;'>🎉 LOGIN EXITOSO</p>";
            } else {
                echo "<p style='color:red;'>❌ Contraseña incorrecta</p>";
            }
        } else {
            echo "<p style='color:red'>✗ Usuario no encontrado</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color:red'>Error: " . $e->getMessage() . "</p>";
    }
}
?>

<form method="POST" style="margin-top:20px;">
    <label>Email: <input type="email" name="email" value="admin@torque.com"></label><br><br>
    <label>Password: <input type="password" name="password" value="admin123"></label><br><br>
    <button type="submit">Probar Login</button>
</form>

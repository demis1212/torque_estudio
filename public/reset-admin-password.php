<?php
header('Content-Type: text/html; charset=utf-8');

try {
    require_once dirname(__DIR__) . '/config/database.php';
    $db = Config\Database::getConnection();

    $email = 'admin@torque.com';
    $newPassword = 'admin123';
    $hash = password_hash($newPassword, PASSWORD_DEFAULT);

    $stmt = $db->prepare("SELECT id, name, email FROM users WHERE email = :email LIMIT 1");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch();

    echo '<h2>🔐 Reset Admin Password</h2>';

    if (!$user) {
        echo '<p style="color:red;">❌ No existe usuario admin@torque.com</p>';
        exit;
    }

    $upd = $db->prepare("UPDATE users SET password = :password WHERE id = :id");
    $upd->execute([
        'password' => $hash,
        'id' => $user['id'],
    ]);

    $check = password_verify($newPassword, $hash);

    echo '<p>✅ Usuario encontrado: <strong>' . htmlspecialchars($user['name']) . '</strong></p>';
    echo '<p>✅ Password actualizado</p>';
    echo '<p>✅ Verificación hash: ' . ($check ? 'OK' : 'FAIL') . '</p>';

    echo '<hr>';
    echo '<p><strong>Credenciales:</strong></p>';
    echo '<p>Email: admin@torque.com<br>Password: admin123</p>';

    echo '<p><a href="/torque/login" style="display:inline-block;padding:10px 14px;background:#4d8eff;color:white;text-decoration:none;border-radius:8px;">Ir a Login</a></p>';

} catch (Throwable $e) {
    echo '<p style="color:red;">❌ Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
}

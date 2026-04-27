<?php
namespace App\Controllers;

use App\Models\User;

class AuthController {
    
    public function showLogin() {
        if (isset($_SESSION['user_id'])) {
            $basePath = dirname($_SERVER['SCRIPT_NAME']);
            if ($basePath === '/' || $basePath === '\\') $basePath = '';
            redirect($basePath . '/dashboard');
        }
        view('login');
    }

    public function login() {
        $email = trim(strtolower($_POST['email'] ?? ''));
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            $error = "Por favor ingrese email y contraseña.";
            view('login', ['error' => $error]);
            return;
        }

        // Rate limiting básico - prevenir fuerza bruta
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $attemptKey = 'login_attempts_' . $ip;
        $lockoutKey = 'login_lockout_' . $ip;
        
        if (isset($_SESSION[$lockoutKey]) && $_SESSION[$lockoutKey] > time()) {
            $error = "Demasiados intentos fallidos. Intente nuevamente en 15 minutos.";
            view('login', ['error' => $error]);
            return;
        }

        $userModel = new User();
        $user = $userModel->findByEmail($email);

        if ($user && password_verify($password, $user['password'])) {
            // Resetear contador de intentos fallidos
            unset($_SESSION[$attemptKey]);
            unset($_SESSION[$lockoutKey]);
            
            // Setup session securely
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_role'] = $user['role_id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['login_time'] = time(); // Para timeout de sesión
            $_SESSION['ip_address'] = $ip; // Vincular sesión a IP

            $basePath = dirname($_SERVER['SCRIPT_NAME']);
            if ($basePath === '/' || $basePath === '\\')            redirect($basePath . '/dashboard');
        } else {
            // Incrementar contador de intentos fallidos
            $_SESSION[$attemptKey] = ($_SESSION[$attemptKey] ?? 0) + 1;
            
            // Bloquear después de 5 intentos fallidos
            if ($_SESSION[$attemptKey] >= 5) {
                $_SESSION[$lockoutKey] = time() + (15 * 60); // 15 minutos
                $error = "Demasiados intentos fallidos. Cuenta bloqueada por 15 minutos.";
            } else {
                $error = "Credenciales incorrectas.";
            }
            view('login', ['error' => $error]);
        }
    }

    public function logout() {
        // Limpiar variables de sesión antes de destruir (buena práctica de seguridad)
        $_SESSION = [];
        
        // Invalidar cookie de sesión
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', [
                'expires' => time() - 3600,
                'path' => '/',
                'secure' => isset($_SERVER['HTTPS']),
                'httponly' => true,
                'samesite' => 'Strict'
            ]);
        }
        
        session_destroy();
        
        $basePath = dirname($_SERVER['SCRIPT_NAME']);
        if ($basePath === '/' || $basePath === '\\') $basePath = '';
        redirect($basePath . '/login');
    }
}

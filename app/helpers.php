<?php

function view($name, $data = []) {
    extract($data);
    $viewPath = dirname(__DIR__) . '/app/views/' . $name . '.php';
    if (file_exists($viewPath)) {
        require $viewPath;
    } else {
        die("View not found: " . $name);
    }
}

function redirect($url) {
    if (!empty($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/torque/') === 0) {
        if (strpos($url, '/') === 0 && strpos($url, '/torque/') !== 0 && $url !== '/torque') {
            $url = '/torque' . $url;
        }
    }
    header("Location: $url");
    exit();
}

function esc($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            die("CSRF Token validation failed.");
        }
    }
}

function getUserRole() {
    // Check both user_role (new) and role_id (old sessions)
    return $_SESSION['user_role'] ?? $_SESSION['role_id'] ?? 0;
}

/**
 * Requiere autenticación para acceder a una página protegida
 * Redirige a login si el usuario no está autenticado
 */
function require_auth() {
    if (!isset($_SESSION['user_id'])) {
        $basePath = dirname($_SERVER['SCRIPT_NAME']);
        if ($basePath === '/' || $basePath === '\\') $basePath = '';
        redirect($basePath . '/login');
        exit;
    }
}

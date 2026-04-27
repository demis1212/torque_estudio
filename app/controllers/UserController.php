<?php
namespace App\Controllers;

use App\Models\User;
use App\Models\Role;

class UserController {
    
    private function checkAuth() {
        if (!isset($_SESSION['user_id'])) {
            $basePath = dirname($_SERVER['SCRIPT_NAME']);
            if ($basePath === '/' || $basePath === '\\') $basePath = '';
            redirect($basePath . '/login');
            exit;
        }
        // Solo Admin puede gestionar usuarios
        if (getUserRole() != 1) {
            die("Acceso denegado. Solo administradores pueden gestionar usuarios.");
        }
    }

    public function index() {
        $this->checkAuth();
        $userModel = new User();
        $users = $userModel->getAllWithRoles();
        view('users/index', ['users' => $users]);
    }

    public function create() {
        $this->checkAuth();
        $roleModel = new Role();
        $roles = $roleModel->all();
        view('users/create', ['roles' => $roles]);
    }

    public function store() {
        $this->checkAuth();
        
        // Validar que las contraseñas coincidan
        if ($_POST['password'] !== $_POST['password_confirm']) {
            $error = "Las contraseñas no coinciden.";
            $roleModel = new Role();
            $roles = $roleModel->all();
            view('users/create', ['error' => $error, 'roles' => $roles]);
            return;
        }
        
        $userModel = new User();
        
        // Verificar email único
        if ($userModel->findByEmail($_POST['email'])) {
            $error = "El email ya está registrado.";
            $roleModel = new Role();
            $roles = $roleModel->all();
            view('users/create', ['error' => $error, 'roles' => $roles]);
            return;
        }
        
        $data = $_POST;
        $data['password'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $userModel->create($data);
        
        $basePath = dirname($_SERVER['SCRIPT_NAME']);
        if ($basePath === '/' || $basePath === '\\') $basePath = '';
        redirect($basePath . '/users');
    }

    public function edit($id) {
        $this->checkAuth();
        $userModel = new User();
        $user = $userModel->find($id);
        
        if (!$user) {
            die("Usuario no encontrado.");
        }

        $roleModel = new Role();
        $roles = $roleModel->all();
        view('users/edit', ['user' => $user, 'roles' => $roles]);
    }

    public function update($id) {
        $this->checkAuth();
        $userModel = new User();
        $data = $_POST;
        
        // Si se proporciona nueva contraseña, hashearla
        if (!empty($data['password'])) {
            if ($data['password'] !== $data['password_confirm']) {
                $error = "Las contraseñas no coinciden.";
                $user = $userModel->find($id);
                $roleModel = new Role();
                $roles = $roleModel->all();
                view('users/edit', ['error' => $error, 'user' => $user, 'roles' => $roles]);
                return;
            }
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        } else {
            unset($data['password']);
        }
        
        $userModel->update($id, $data);
        
        $basePath = dirname($_SERVER['SCRIPT_NAME']);
        if ($basePath === '/' || $basePath === '\\') $basePath = '';
        redirect($basePath . '/users');
    }

    public function delete($id) {
        $this->checkAuth();
        
        // No permitir eliminar el propio usuario
        if ($id == $_SESSION['user_id']) {
            die("No puedes eliminar tu propio usuario.");
        }
        
        $userModel = new User();
        $userModel->delete($id);
        
        $basePath = dirname($_SERVER['SCRIPT_NAME']);
        if ($basePath === '/' || $basePath === '\\') $basePath = '';
        redirect($basePath . '/users');
    }
}

<?php
namespace App\Controllers;

use App\Models\Setting;
use App\Models\ActivityLog;

class SettingsController {
    
    private function checkAuth() {
        if (!isset($_SESSION['user_id'])) {
            $basePath = dirname($_SERVER['SCRIPT_NAME']);
            if ($basePath === '/' || $basePath === '\\') $basePath = '';
            redirect($basePath . '/login');
        }
        // Only admin can access settings
        if (getUserRole() != 1) {
            $basePath = dirname($_SERVER['SCRIPT_NAME']);
            if ($basePath === '/' || $basePath === '\\') $basePath = '';
            redirect($basePath . '/dashboard');
        }
    }

    public function index() {
        $this->checkAuth();
        
        $settingModel = new Setting();
        $settings = $settingModel->getAll();
        
        // Group settings by group
        $groupedSettings = [];
        foreach ($settings as $setting) {
            $group = $setting['group'] ?? 'general';
            $groupedSettings[$group][] = $setting;
        }
        
        view('settings/index', [
            'grouped_settings' => $groupedSettings,
            'settings' => $settings
        ]);
    }

    public function update() {
        $this->checkAuth();
        
        $settingModel = new Setting();
        
        foreach ($_POST['settings'] as $key => $value) {
            $settingModel->update($key, $value);
        }
        
        // Log activity
        $log = new ActivityLog();
        $log->log('update', 'settings', null, 'Configuraciones del sistema actualizadas');
        
        $basePath = dirname($_SERVER['SCRIPT_NAME']);
        if ($basePath === '/' || $basePath === '\\') $basePath = '';
        redirect($basePath . '/settings');
    }

    public function create() {
        $this->checkAuth();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $settingModel = new Setting();
            $settingModel->set(
                $_POST['key'],
                $_POST['value'],
                $_POST['group'] ?? 'general',
                $_POST['description'] ?? null
            );
            
            $basePath = dirname($_SERVER['SCRIPT_NAME']);
            if ($basePath === '/' || $basePath === '\\') $basePath = '';
            redirect($basePath . '/settings');
        }
        
        view('settings/create');
    }

    public function delete($key) {
        $this->checkAuth();
        
        $settingModel = new Setting();
        $settingModel->delete($key);
        
        $basePath = dirname($_SERVER['SCRIPT_NAME']);
        if ($basePath === '/' || $basePath === '\\') $basePath = '';
        redirect($basePath . '/settings');
    }
}

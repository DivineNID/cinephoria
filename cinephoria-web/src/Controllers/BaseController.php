<?php

namespace App\Controllers;

class BaseController {
    protected function render($view, $data = []) {
        extract($data);
        
        ob_start();
        include VIEW_PATH . "/$view.php";
        $content = ob_get_clean();
        
        include VIEW_PATH . '/layout/main.php';
    }
    
    protected function redirect($path) {
        header("Location: " . BASE_URL . "/$path");
        exit();
    }
    
    protected function isAuthenticated() {
        return isset($_SESSION['user_id']);
    }
    
    protected function requireAuth() {
        if (!$this->isAuthenticated()) {
            $this->redirect('login');
        }
    }
}
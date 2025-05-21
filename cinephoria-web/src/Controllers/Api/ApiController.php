<?php
namespace App\Controllers\Api;

class ApiController {
    protected function json($data, $status = 200) {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
    
    protected function requireAuth() {
        $headers = getallheaders();
        $token = $headers['Authorization'] ?? null;
        
        if (!$token || !$this->validateToken($token)) {
            $this->json(['error' => 'Unauthorized'], 401);
        }
    }
    
    private function validateToken($token) {
        // Validation du JWT
        try {
            $decoded = JWT::decode($token, JWT_SECRET_KEY, ['HS256']);
            return $decoded->user_id;
        } catch (\Exception $e) {
            return false;
        }
    }
}




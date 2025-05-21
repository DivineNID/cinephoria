<?php
namespace App\Controllers\Api;

use App\Models\User;

class AuthApiController extends ApiController {
    public function login() {
        $data = json_decode(file_get_contents('php://input'), true);
        
        $user = User::findByEmail($data['email'] ?? '');
        if (!$user || !password_verify($data['password'] ?? '', $user->getPassword())) {
            return $this->json(['error' => 'Invalid credentials'], 401);
        }
        
        $token = JWT::encode([
            'user_id' => $user->getId(),
            'exp' => time() + (60 * 60 * 24) // 24 heures
        ], JWT_SECRET_KEY);
        
        return $this->json(['token' => $token]);
    }
}
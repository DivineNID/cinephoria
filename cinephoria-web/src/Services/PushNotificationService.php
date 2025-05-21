<?php
namespace App\Services;

class PushNotificationService {
    public function sendToUser($userId, $title, $message, $data = []) {
        // Exemple avec OneSignal ou Firebase
        // $user = User::findById($userId);
        // $token = $user->getPushToken();
        // ... envoi via API
    }
}
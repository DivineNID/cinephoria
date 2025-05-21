<?php
namespace App\Services;

class AuthorizationService {
    public static function can($user, $action, $resource = null) {
        $role = $user->getRole();
        $permissions = [
            'admin' => ['*'],
            'employee' => ['moderate_reviews', 'manage_incidents', 'manage_sessions'],
            'user' => ['book', 'review', 'profile']
        ];
        if (in_array('*', $permissions[$role])) return true;
        return in_array($action, $permissions[$role]);
    }
}
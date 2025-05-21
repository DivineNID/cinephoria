<?php
namespace App\Middleware;

class SecurityMiddleware {
    public function handle($request, $next) {
        // En-têtes de sécurité
        header('X-Frame-Options: DENY');
        header('X-Content-Type-Options: nosniff');
        header('X-XSS-Protection: 1; mode=block');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('Content-Security-Policy: default-src \'self\'; script-src \'self\' \'unsafe-inline\' \'unsafe-eval\' https://cdn.jsdelivr.net; style-src \'self\' \'unsafe-inline\' https://cdn.jsdelivr.net; img-src \'self\' data: https:; font-src \'self\' https://cdn.jsdelivr.net');
        
        // Protection CSRF
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                throw new \Exception('Invalid CSRF token');
            }
        }
        
        // Rate limiting
        $rateLimiter = new RateLimiter();
        if (!$rateLimiter->isAllowed($_SERVER['REMOTE_ADDR'])) {
            header('HTTP/1.1 429 Too Many Requests');
            exit('Too many requests');
        }
        
        return $next($request);
    }
}


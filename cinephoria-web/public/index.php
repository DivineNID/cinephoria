<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/config.php';

session_start();

// Router simple
$route = $_GET['route'] ?? 'home';

// Sécurité basique
$route = filter_var($route, FILTER_SANITIZE_URL);
$route = strtolower($route);

// Mapping des routes vers les contrôleurs
$routes = [
    'home' => ['controller' => 'HomeController', 'action' => 'index'],
    'films' => ['controller' => 'FilmController', 'action' => 'index'],
    'reservation' => ['controller' => 'ReservationController', 'action' => 'index'],
    'login' => ['controller' => 'AuthController', 'action' => 'login'],
    'register' => ['controller' => 'AuthController', 'action' => 'register'],
    // Ajoutez d'autres routes ici
];

// Vérification si la route existe
if (!isset($routes[$route])) {
    header("HTTP/1.0 404 Not Found");
    include VIEW_PATH . '/errors/404.php';
    exit();
}

// Chargement du contrôleur approprié
$controllerName = "App\\Controllers\\" . $routes[$route]['controller'];
$actionName = $routes[$route]['action'];

try {
    $controller = new $controllerName();
    $controller->$actionName();
} catch (Exception $e) {
    // Log l'erreur
    error_log($e->getMessage());
    // Afficher une page d'erreur
    include VIEW_PATH . '/errors/500.php';
}
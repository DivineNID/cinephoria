<?php

$cfg['Servers'][$i]['controluser'] = 'controluser';
$cfg['Servers'][$i]['controlpass'] = 'MOT_DE_PASSE_CONTROLUSER';

define('DB_HOST', 'localhost');
define('DB_NAME', 'ecoride');
define('DB_USER', 'root');
define('DB_PASSWORD', 'password');

define('BASE_URL', 'http://localhost:8000');
define('SITE_NAME', 'Cinéphoria');

// Configuration des chemins
define('ROOT_PATH', dirname(__DIR__));
define('VIEW_PATH', ROOT_PATH . '/src/Views');
define('CONTROLLER_PATH', ROOT_PATH . '/src/Controllers');
define('MODEL_PATH', ROOT_PATH . '/src/Models');

// Configuration des emails
define('MAIL_HOST', 'smtp.example.com');
define('MAIL_USERNAME', 'your-email@example.com');
define('MAIL_PASSWORD', 'your-password');
define('MAIL_PORT', 587);

// Configuration de sécurité
define('HASH_SALT', 'votre-salt-secret');
define('SESSION_LIFETIME', 3600); // 1 heure
<?php

return [
    'mysql' => [
        'host' => 'localhost',
        'database' => 'cinephoria',
        'username' => 'root',
        'password' => 'NIdivine219##',
        'charset' => 'utf8mb4',
        'options' => [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ],
    ],
    'mongodb' => [
        'uri' => 'mongodb://localhost:27017',
        'database' => 'cinephoria_stats'
    ]
];
<?php
// src/Services/Database.php

namespace App\Services;

use PDO;
use MongoDB\Client;

class Database {
    private static $pdo = null;
    private static $mongodb = null;
    
    public static function getMysqlConnection(): PDO {
        if (self::$pdo === null) {
            $config = require_once ROOT_PATH . '/config/database.php';
            $dsn = "mysql:host={$config['mysql']['host']};dbname={$config['mysql']['database']};charset={$config['mysql']['charset']}";
            
            try {
                self::$pdo = new PDO(
                    $dsn,
                    $config['mysql']['username'],
                    $config['mysql']['password'],
                    $config['mysql']['options']
                );
            } catch (\PDOException $e) {
                throw new \Exception("Erreur de connexion à la base de données : " . $e->getMessage());
            }
        }
        return self::$pdo;
    }
    
    public static function getMongoConnection() {
        if (self::$mongodb === null) {
            $config = require_once ROOT_PATH . '/config/database.php';
            try {
                $client = new Client($config['mongodb']['uri']);
                self::$mongodb = $client->selectDatabase($config['mongodb']['database']);
            } catch (\Exception $e) {
                throw new \Exception("Erreur de connexion à MongoDB : " . $e->getMessage());
            }
        }
        return self::$mongodb;
    }
}
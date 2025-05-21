<?php

namespace App\Models;

use App\Services\Database;
use PDO;

class Film {
    private $id;
    private $titre;
    private $description;
    private $ageMinimum;
    private $coupDeCoeur;
    private $dateAjout;
    
    public static function findAll() {
        $db = Database::getMysqlConnection();
        $stmt = $db->query("SELECT * FROM films ORDER BY dateAjout DESC");
        return $stmt->fetchAll(PDO::FETCH_CLASS, self::class);
    }
    
    public static function findLastWednesday() {
        $db = Database::getMysqlConnection();
        $stmt = $db->prepare("
            SELECT * FROM films 
            WHERE DATE(dateAjout) = (
                SELECT MAX(DATE(dateAjout)) 
                FROM films 
                WHERE DAYOFWEEK(dateAjout) = 4
            )
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_CLASS, self::class);
    }
    
    // Getters et Setters
    public function getId() { return $this->id; }
    public function getTitre() { return $this->titre; }
    public function getDescription() { return $this->description; }
    public function getAgeMinimum() { return $this->ageMinimum; }
    public function getCoupDeCoeur() { return $this->coupDeCoeur; }
    public function getDateAjout() { return $this->dateAjout; }
}
<?php
namespace App\Services;

use Redis;

class CacheService {
    private $redis;
    private static $instance = null;
    
    private function __construct() {
        $this->redis = new Redis();
        $this->redis->connect(REDIS_HOST, REDIS_PORT);
        if (REDIS_PASSWORD) {
            $this->redis->auth(REDIS_PASSWORD);
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getFilmSchedule($filmId, $date) {
        $key = "film_schedule:{$filmId}:{$date}";
        $cached = $this->redis->get($key);
        
        if ($cached) {
            return json_decode($cached, true);
        }
        
        return null;
    }
    
    public function setFilmSchedule($filmId, $date, $schedule) {
        $key = "film_schedule:{$filmId}:{$date}";
        $this->redis->setex($key, 3600, json_encode($schedule));
    }
    
    public function invalidateFilmSchedule($filmId) {
        $pattern = "film_schedule:{$filmId}:*";
        $keys = $this->redis->keys($pattern);
        if (!empty($keys)) {
            $this->redis->del($keys);
        }
    }
}
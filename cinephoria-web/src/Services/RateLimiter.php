<?php
namespace App\Services;

class RateLimiter {
    private $redis;
    private $maxRequests = 100;
    private $window = 3600; // 1 heure
    
    public function __construct() {
        $this->redis = new Redis();
        $this->redis->connect(REDIS_HOST, REDIS_PORT);
    }
    
    public function isAllowed($ip) {
        $key = "rate_limit:$ip";
        $current = $this->redis->get($key);
        
        if (!$current) {
            $this->redis->setex($key, $this->window, 1);
            return true;
        }
        
        if ($current >= $this->maxRequests) {
            return false;
        }
        
        $this->redis->incr($key);
        return true;
    }
}
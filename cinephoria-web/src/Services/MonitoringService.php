<?php
namespace App\Services;

use Prometheus\CollectorRegistry;
use Prometheus\Storage\Redis;

class MonitoringService {
    private $registry;
    private static $instance = null;
    
    private function __construct() {
        $this->registry = new CollectorRegistry(new Redis([
            'host' => REDIS_HOST,
            'port' => REDIS_PORT
        ]));
        
        $this->initializeMetrics();
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function initializeMetrics() {
        // Compteur de réservations
        $this->registry->getOrRegisterCounter('cinephoria', 'bookings_total', 
            'Total number of bookings', ['status']);
        
        // Histogramme des temps de réponse
        $this->registry->getOrRegisterHistogram('cinephoria', 'request_duration_seconds',
            'Request duration in seconds', ['endpoint'], [0.1, 0.3, 0.5, 0.7, 1, 2, 5]);
        
        // Jauge pour les sessions actives
        $this->registry->getOrRegisterGauge('cinephoria', 'active_sessions',
            'Number of active sessions');
    }
    
    public function recordBooking($status) {
        $counter = $this->registry->getOrRegisterCounter('cinephoria', 'bookings_total');
        $counter->inc(['status' => $status]);
    }
    
    public function recordRequestDuration($endpoint, $duration) {
        $histogram = $this->registry->getOrRegisterHistogram('cinephoria', 'request_duration_seconds');
        $histogram->observe($duration, ['endpoint' => $endpoint]);
    }
    
    public function updateActiveSessions($count) {
        $gauge = $this->registry->getOrRegisterGauge('cinephoria', 'active_sessions');
        $gauge->set($count);
    }
}
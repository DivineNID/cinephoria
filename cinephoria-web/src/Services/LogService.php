<?php
namespace App\Services;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\ElasticsearchHandler;
use Elasticsearch\ClientBuilder;

class LogService {
    private $logger;
    private static $instance = null;
    
    private function __construct() {
        $this->logger = new Logger('cinephoria');
        
        // Handler pour les fichiers
        $this->logger->pushHandler(new StreamHandler(
            ROOT_PATH . '/logs/app.log',
            Logger::DEBUG
        ));
        
        // Handler pour Elasticsearch
        if (defined('ELASTICSEARCH_HOST')) {
            $client = ClientBuilder::create()
                ->setHosts([ELASTICSEARCH_HOST])
                ->build();
            
            $this->logger->pushHandler(new ElasticsearchHandler($client));
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function logBooking($booking) {
        $this->logger->info('Nouvelle réservation', [
            'booking_id' => $booking->getId(),
            'user_id' => $booking->getUserId(),
            'amount' => $booking->getTotalPrice(),
            'session' => $booking->getSession()->toArray()
        ]);
    }
    
    public function logIncident($incident) {
        $this->logger->warning('Incident signalé', [
            'incident_id' => $incident->getId(),
            'room_id' => $incident->getRoomId(),
            'reported_by' => $incident->getReportedBy(),
            'description' => $incident->getDescription()
        ]);
    }
    
    public function logError(\Exception $e) {
        $this->logger->error($e->getMessage(), [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]);
    }
}
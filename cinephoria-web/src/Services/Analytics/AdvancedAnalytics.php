// src/Services/Analytics/AdvancedAnalytics.php
namespace App\Services\Analytics;

use MongoDB\Client;
use App\Services\CacheService;

class AdvancedAnalytics {
    private $mongodb;
    private $cache;
    
    public function __construct() {
        $this->mongodb = new Client(MONGODB_URI);
        $this->cache = CacheService::getInstance();
    }
    
    public function getComprehensiveStats($period = 'month') {
        $cacheKey = "stats:comprehensive:{$period}";
        $cached = $this->cache->get($cacheKey);
        
        if ($cached) {
            return json_decode($cached, true);
        }
        
        $stats = [
            'revenue' => $this->getRevenueStats($period),
            'occupancy' => $this->getOccupancyStats($period),
            'popular_movies' => $this->getPopularMovies($period),
            'peak_hours' => $this->getPeakHours($period),
            'customer_demographics' => $this->getCustomerDemographics($period)
        ];
        
        $this->cache->set($cacheKey, json_encode($stats), 3600);
        return $stats;
    }
    
    private function getRevenueStats($period) {
        $collection = $this->mongodb->cinephoria->bookings;
        
        $pipeline = [
            [
                '$match' => [
                    'created_at' => [
                        '$gte' => $this->getStartDate($period)
                    ]
                ]
            ],
            [
                '$group' => [
                    '_id' => [
                        'date' => ['$dateToString' => ['format' => '%Y-%m-%d', 'date' => '$created_at']],
                        'cinema' => '$cinema_id'
                    ],
                    'total_revenue' => ['$sum' => '$total_price'],
                    'ticket_count' => ['$sum' => '$seat_count']
                ]
            ],
            [
                '$sort' => ['_id.date' => 1]
            ]
        ];
        
        return $collection->aggregate($pipeline)->toArray();
    }
    
    private function getOccupancyStats($period) {
        $collection = $this->mongodb->cinephoria->sessions;
        
        $pipeline = [
            [
                '$match' => [
                    'date' => [
                        '$gte' => $this->getStartDate($period)
                    ]
                ]
            ],
            [
                '$lookup' => [
                    'from' => 'bookings',
                    'localField' => '_id',
                    'foreignField' => 'session_id',
                    'as' => 'bookings'
                ]
            ],
            [
                '$project' => [
                    'date' => 1,
                    'room_capacity' => 1,
                    'booked_seats' => ['$size' => '$bookings'],
                    'occupancy_rate' => [
                        '$multiply' => [
                            ['$divide' => [['$size' => '$bookings'], '$room_capacity']],
                            100
                        ]
                    ]
                ]
            ]
        ];
        
        return $collection->aggregate($pipeline)->toArray();
    }
}
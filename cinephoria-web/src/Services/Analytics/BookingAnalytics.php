// src/Services/Analytics/BookingAnalytics.php
namespace App\Services\Analytics;

use MongoDB\Client;

class BookingAnalytics {
    private $collection;
    
    public function __construct() {
        $client = new Client(MONGODB_URI);
        $this->collection = $client->cinephoria->bookings;
    }
    
    public function getStatistics($period) {
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
                    'count' => ['$sum' => 1],
                    'revenue' => ['$sum' => '$total_price']
                ]
            ],
            [
                '$sort' => ['_id.date' => 1]
            ]
        ];
        
        return $this->collection->aggregate($pipeline)->toArray();
    }
    
    private function getStartDate($period) {
        switch ($period) {
            case 'week':
                return new \MongoDB\BSON\UTCDateTime(strtotime('-7 days') * 1000);
            case 'month':
                return new \MongoDB\BSON\UTCDateTime(strtotime('-30 days') * 1000);
            case 'year':
                return new \MongoDB\BSON\UTCDateTime(strtotime('-365 days') * 1000);
            default:
                return new \MongoDB\BSON\UTCDateTime(strtotime('-7 days') * 1000);
        }
    }
}
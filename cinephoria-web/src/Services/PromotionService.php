<?php
namespace App\Services;

class PromotionService {
    private $redis;
    
    public function __construct() {
        $this->redis = new \Redis();
        $this->redis->connect(REDIS_HOST, REDIS_PORT);
    }
    
    public function createPromotion($data) {
        $promotion = new Promotion();
        $promotion->setCode($data['code'])
                 ->setType($data['type'])
                 ->setValue($data['value'])
                 ->setStartDate($data['start_date'])
                 ->setEndDate($data['end_date'])
                 ->setUsageLimit($data['usage_limit'])
                 ->save();
        
        // Cache la promotion
        $this->cachePromotion($promotion);
        
        return $promotion;
    }
    
    public function applyPromotion($code, $booking) {
        $promotion = $this->getPromotionFromCache($code);
        if (!$promotion) {
            $promotion = Promotion::findByCode($code);
            if ($promotion) {
                $this->cachePromotion($promotion);
            }
        }
        
        if (!$promotion || !$this->isPromotionValid($promotion)) {
            return false;
        }
        
        $discount = $this->calculateDiscount($promotion, $booking);
        $booking->setDiscount($discount);
        
        return true;
    }
    
    private function calculateDiscount($promotion, $booking) {
        switch ($promotion->getType()) {
            case 'percentage':
                return $booking->getTotalPrice() * ($promotion->getValue() / 100);
            case 'fixed':
                return min($promotion->getValue(), $booking->getTotalPrice());
            default:
                return 0;
        }
    }
    
    private function isPromotionValid($promotion) {
        $now = new \DateTime();
        
        if ($now < $promotion->getStartDate() || $now > $promotion->getEndDate()) {
            return false;
        }
        
        $usageCount = $this->redis->get("promotion:{$promotion->getCode()}:usage");
        if ($usageCount >= $promotion->getUsageLimit()) {
            return false;
        }
        
        return true;
    }
    
    private function cachePromotion($promotion) {
        $key = "promotion:{$promotion->getCode()}";
        $this->redis->setex($key, 3600, json_encode($promotion->toArray()));
    }
    
    private function getPromotionFromCache($code) {
        $key = "promotion:{$code}";
        $cached = $this->redis->get($key);
        
        return $cached ? Promotion::fromArray(json_decode($cached, true)) : null;
    }
}
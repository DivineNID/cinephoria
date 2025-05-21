<?php
namespace App\Services;

class RecommendationService {
    private $mongodb;
    
    public function __construct() {
        $this->mongodb = new \MongoDB\Client(MONGODB_URI);
    }
    
    public function getPersonalizedRecommendations($userId) {
        $userPreferences = $this->getUserPreferences($userId);
        $similarUsers = $this->findSimilarUsers($userPreferences);
        
        return $this->getRecommendedFilms($similarUsers, $userPreferences['watched_films']);
    }
    
    private function getUserPreferences($userId) {
        $bookings = Booking::findAllByUser($userId);
        $reviews = Review::findAllByUser($userId);
        
        $watchedFilms = [];
        $genres = [];
        $ratings = [];
        
        foreach ($bookings as $booking) {
            $film = $booking->getSession()->getFilm();
            $watchedFilms[] = $film->getId();
            
            foreach ($film->getGenres() as $genre) {
                $genres[$genre->getId()] = ($genres[$genre->getId()] ?? 0) + 1;
            }
        }
        
        foreach ($reviews as $review) {
            $ratings[$review->getFilmId()] = $review->getRating();
        }
        
        return [
            'watched_films' => $watchedFilms,
            'genre_preferences' => $genres,
            'ratings' => $ratings
        ];
    }
    
    private function findSimilarUsers($userPreferences) {
        $collection = $this->mongodb->cinephoria->user_preferences;
        
        $pipeline = [
            [
                '$match' => [
                    'genre_preferences' => [
                        '$elemMatch' => [
                            '$in' => array_keys($userPreferences['genre_preferences'])
                        ]
                    ]
                ]
            ],
            [
                '$project' => [
                    'user_id' => 1,
                    'similarity_score' => [
                        '$reduce' => [
                            'input' => '$genre_preferences',
                            'initialValue' => 0,
                            'in' => [
                                '$add' => [
                                    '$$value',
                                    [
                                        '$cond' => [
                                            'if' => [
                                                '$in' => [
                                                    '$$this',
                                                    array_keys($userPreferences['genre_preferences'])
                                                ]
                                            ],
                                            'then' => 1,
                                            'else' => 0
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            [
                '$sort' => ['similarity_score' => -1]
            ],
            [
                '$limit' => 10
            ]
        ];
        
        return $collection->aggregate($pipeline)->toArray();
    }
    
    private function getRecommendedFilms($similarUsers, $watchedFilms) {
        $userIds = array_map(fn($user) => $user['user_id'], $similarUsers);
        
        $collection = $this->mongodb->cinephoria->bookings;
        
        $pipeline = [
            [
                '$match' => [
                    'user_id' => ['$in' => $userIds],
                    'film_id' => ['$nin' => $watchedFilms]
                ]
            ],
            [
                '$group' => [
                    '_id' => '$film_id',
                    'count' => ['$sum' => 1]
                ]
            ],
            [
                '$sort' => ['count' => -1]
            ],
            [
                '$limit' => 5
            ]
        ];
        
        $recommendedFilmIds = array_map(
            fn($doc) => $doc['_id'],
            $collection->aggregate($pipeline)->toArray()
        );
        
        return Film::findByIds($recommendedFilmIds);
    }
}
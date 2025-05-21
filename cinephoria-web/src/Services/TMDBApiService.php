<?php
namespace App\Services;

use GuzzleHttp\Client;

class TMDBApiService {
    private $client;
    private $apiKey;
    
    public function __construct() {
        $this->client = new Client([
            'base_uri' => 'https://api.themoviedb.org/3/',
            'timeout' => 5.0
        ]);
        $this->apiKey = TMDB_API_KEY;
    }
    
    public function getMovieDetails($tmdbId) {
        $response = $this->client->get("movie/{$tmdbId}", [
            'query' => [
                'api_key' => $this->apiKey,
                'language' => 'fr-FR',
                'append_to_response' => 'credits,videos'
            ]
        ]);
        
        return json_decode($response->getBody(), true);
    }
    
    public function searchMovies($query) {
        $response = $this->client->get('search/movie', [
            'query' => [
                'api_key' => $this->apiKey,
                'language' => 'fr-FR',
                'query' => $query
            ]
        ]);
        
        return json_decode($response->getBody(), true);
    }
}
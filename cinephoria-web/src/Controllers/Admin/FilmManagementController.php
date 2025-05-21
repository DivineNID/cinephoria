<?php
namespace App\Controllers\Admin;

use App\Models\Film;
use App\Services\ImageService;
use App\Services\FilmMetadataService;
use App\Services\TMDBApiService;

class FilmManagementController extends AdminBaseController {
    private $imageService;
    private $metadataService;
    private $tmdbService;
    
    public function __construct() {
        parent::__construct();
        $this->imageService = new ImageService();
        $this->metadataService = new FilmMetadataService();
        $this->tmdbService = new TMDBApiService();
    }
    
    public function importFromTMDB() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $tmdbId = filter_input(INPUT_POST, 'tmdb_id', FILTER_SANITIZE_NUMBER_INT);
            
            try {
                $movieData = $this->tmdbService->getMovieDetails($tmdbId);
                $film = $this->createFilmFromTMDB($movieData);
                
                $this->setFlash('success', 'Film importé avec succès');
                $this->redirect("admin/films/edit/{$film->getId()}");
            } catch (\Exception $e) {
                $this->setFlash('danger', $e->getMessage());
            }
        }
        
        $this->render('admin/films/import', [
            'title' => 'Importer depuis TMDB'
        ]);
    }
    
    private function createFilmFromTMDB($movieData) {
        $film = new Film();
        $film->setTitle($movieData['title'])
             ->setDescription($movieData['overview'])
             ->setDuration($movieData['runtime'])
             ->setReleaseDate($movieData['release_date']);
        
        // Téléchargement et traitement de l'affiche
        $posterUrl = $this->tmdbService->getPosterUrl($movieData['poster_path']);
        $localPosterPath = $this->imageService->downloadAndProcessPoster($posterUrl);
        $film->setPosterUrl($localPosterPath);
        
        // Ajout des métadonnées
        $this->metadataService->addMetadata($film, [
            'original_title' => $movieData['original_title'],
            'imdb_id' => $movieData['imdb_id'],
            'vote_average' => $movieData['vote_average'],
            'genres' => array_map(fn($genre) => $genre['name'], $movieData['genres'])
        ]);
        
        $film->save();
        return $film;
    }
}


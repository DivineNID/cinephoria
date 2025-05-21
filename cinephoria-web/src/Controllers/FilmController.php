<?php
namespace App\Controllers;

use App\Models\Film;
use App\Models\Genre;
use App\Models\Cinema;
use App\Models\Session;

class FilmController extends BaseController {
    public function index() {
        $filters = [
            'cinema_id' => filter_input(INPUT_GET, 'cinema', FILTER_SANITIZE_NUMBER_INT),
            'genre_id' => filter_input(INPUT_GET, 'genre', FILTER_SANITIZE_NUMBER_INT),
            'date' => filter_input(INPUT_GET, 'date', FILTER_SANITIZE_STRING)
        ];
        
        $films = Film::findAllWithFilters($filters);
        $cinemas = Cinema::findAll();
        $genres = Genre::findAll();
        
        $this->render('films/index', [
            'title' => 'Nos films',
            'films' => $films,
            'cinemas' => $cinemas,
            'genres' => $genres,
            'filters' => $filters
        ]);
    }
    
    public function show($id) {
        $film = Film::findById($id);
        if (!$film) {
            $this->setFlash('danger', 'Film non trouvé');
            $this->redirect('films');
        }
        
        $sessions = Session::findByFilm($id);
        $reviews = $film->getApprovedReviews();
        
        $this->render('films/show', [
            'title' => $film->getTitle(),
            'film' => $film,
            'sessions' => $sessions,
            'reviews' => $reviews
        ]);
    }
    
    public function addReview() {
        $this->requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $filmId = filter_input(INPUT_POST, 'film_id', FILTER_SANITIZE_NUMBER_INT);
            $rating = filter_input(INPUT_POST, 'rating', FILTER_SANITIZE_NUMBER_INT);
            $comment = filter_input(INPUT_POST, 'comment', FILTER_SANITIZE_STRING);
            
            if ($rating < 1 || $rating > 5) {
                $this->setFlash('danger', 'Note invalide');
                $this->redirect("films/show/$filmId");
            }
            
            $review = new Review();
            $review->setUserId($_SESSION['user_id'])
                  ->setFilmId($filmId)
                  ->setRating($rating)
                  ->setComment($comment)
                  ->setStatus('pending')
                  ->save();
            
            $this->setFlash('success', 'Votre avis a été soumis et sera examiné par notre équipe');
            $this->redirect("films/show/$filmId");
        }
    }
}
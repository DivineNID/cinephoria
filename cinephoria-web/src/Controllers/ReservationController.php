<?php
namespace App\Controllers;

use App\Models\Session;
use App\Models\Booking;
use App\Models\Seat;
use App\Services\PaymentGateway;

class ReservationController extends BaseController {
    public function index() {
        $cinemaId = filter_input(INPUT_GET, 'cinema', FILTER_SANITIZE_NUMBER_INT);
        $filmId = filter_input(INPUT_GET, 'film', FILTER_SANITIZE_NUMBER_INT);
        $date = filter_input(INPUT_GET, 'date', FILTER_SANITIZE_STRING) ?: date('Y-m-d');
        
        $sessions = Session::findAvailable($cinemaId, $filmId, $date);
        $cinemas = Cinema::findAll();
        $films = Film::findAll();
        
        $this->render('reservation/index', [
            'title' => 'Réservation',
            'sessions' => $sessions,
            'cinemas' => $cinemas,
            'films' => $films,
            'selectedDate' => $date,
            'selectedCinema' => $cinemaId,
            'selectedFilm' => $filmId
        ]);
    }
    
    public function selectSeats($sessionId) {
        $this->requireAuth();
        
        $session = Session::findById($sessionId);
        if (!$session) {
            $this->setFlash('danger', 'Séance non trouvée');
            $this->redirect('reservation');
        }
        
        $availableSeats = $session->getAvailableSeats();
        
        $this->render('reservation/seats', [
            'title' => 'Sélection des sièges',
            'session' => $session,
            'availableSeats' => $availableSeats
        ]);
    }
    
    public function confirm() {
        $this->requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('reservation');
        }
        
        $sessionId = filter_input(INPUT_POST, 'session_id', FILTER_SANITIZE_NUMBER_INT);
        $seatIds = $_POST['seats'] ?? [];
        
        if (empty($seatIds)) {
            $this->setFlash('danger', 'Veuillez sélectionner au moins un siège');
            $this->redirect("reservation/seats/$sessionId");
        }
        
        try {
            $session = Session::findById($sessionId);
            $seats = Seat::findByIds($seatIds);
            
            // Calcul du prix total
            $totalPrice = $session->calculatePrice(count($seats));
            
            // Création de la réservation
            $booking = new Booking();
            $booking->setUserId($_SESSION['user_id'])
                   ->setSessionId($sessionId)
                   ->setTotalPrice($totalPrice)
                   ->setStatus('pending');
            
            // Transaction SQL
            $db = Database::getMysqlConnection();
            $db->beginTransaction();
            
            try {
                $booking->save();
                foreach ($seats as $seat) {
                    $booking->addSeat($seat->getId());
                }
                
                $db->commit();
                
                // Redirection vers le paiement
                $_SESSION['pending_booking_id'] = $booking->getId();
                $this->redirect("payment/process/{$booking->getId()}");
                
            } catch (\Exception $e) {
                $db->rollBack();
                throw $e;
            }
            
        } catch (\Exception $e) {
            $this->setFlash('danger', 'Une erreur est survenue lors de la réservation');
            $this->redirect("reservation/seats/$sessionId");
        }
    }
}
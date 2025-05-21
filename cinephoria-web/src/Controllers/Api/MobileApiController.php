<?php
namespace App\Controllers\Api;

use App\Models\User;
use App\Models\Booking;
use App\Services\QRCodeGenerator;
use App\Services\JWTService;

class MobileApiController extends ApiController {
    private $jwt;
    private $qrGenerator;
    
    public function __construct() {
        $this->jwt = new JWTService();
        $this->qrGenerator = new QRCodeGenerator();
    }
    
    public function getUserDashboard() {
        $userId = $this->authenticateRequest();
        
        $upcomingBookings = Booking::findUpcomingByUser($userId);
        $pastBookings = Booking::findPastByUser($userId);
        $favoriteMovies = User::findById($userId)->getFavoriteMovies();
        
        return $this->jsonResponse([
            'upcoming_bookings' => array_map([$this, 'formatBooking'], $upcomingBookings),
            'past_bookings' => array_map([$this, 'formatBooking'], $pastBookings),
            'favorite_movies' => array_map([$this, 'formatMovie'], $favoriteMovies)
        ]);
    }
    
    public function getBookingQRCode($bookingId) {
        $userId = $this->authenticateRequest();
        
        $booking = Booking::findById($bookingId);
        if (!$booking || $booking->getUserId() !== $userId) {
            return $this->errorResponse('Booking not found', 404);
        }
        
        $qrCode = $this->qrGenerator->generateForBooking($booking);
        
        return $this->jsonResponse([
            'qr_code' => $qrCode,
            'booking' => $this->formatBooking($booking)
        ]);
    }
    
    private function formatBooking($booking) {
        $session = $booking->getSession();
        $film = $session->getFilm();
        
        return [
            'id' => $booking->getId(),
            'film' => [
                'title' => $film->getTitle(),
                'poster_url' => $film->getPosterUrl()
            ],
            'session' => [
                'date' => $session->getFormattedDate(),
                'time' => $session->getFormattedTime(),
                'room' => $session->getRoom()->getName()
            ],
            'seats' => array_map(function($seat) {
                return [
                    'row' => $seat->getRowNumber(),
                    'number' => $seat->getSeatNumber()
                ];
            }, $booking->getSeats()),
            'total_price' => $booking->getTotalPrice(),
            'status' => $booking->getStatus()
        ];
    }
}
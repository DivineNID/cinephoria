<?php
namespace App\Controllers\Api;

use App\Models\Booking;
use App\Services\QRCodeGenerator;

class BookingApiController extends ApiController {
    public function getUserBookings($userId) {
        $this->requireAuth();
        
        $bookings = Booking::findByUser($userId);
        $formattedBookings = array_map(function($booking) {
            return [
                'id' => $booking->getId(),
                'film' => $booking->getSession()->getFilm()->getTitle(),
                'date' => $booking->getSession()->getFormattedStartTime(),
                'seats' => $booking->getSeats()->count(),
                'status' => $booking->getStatus()
            ];
        }, $bookings);
        
        return $this->json($formattedBookings);
    }
    
    public function getQRCode($bookingId) {
        $this->requireAuth();
        
        $booking = Booking::findById($bookingId);
        if (!$booking) {
            return $this->json(['error' => 'Booking not found'], 404);
        }
        
        $qrGenerator = new QRCodeGenerator();
        $qrCode = $qrGenerator->generateForBooking($booking);
        
        return $this->json(['qr_code' => $qrCode]);
    }
}
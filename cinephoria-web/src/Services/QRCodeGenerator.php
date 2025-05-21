<?php
namespace App\Services;

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use App\Models\Booking;

class QRCodeGenerator {
    public function generateForBooking(Booking $booking) {
        $data = json_encode([
            'booking_id' => $booking->getId(),
            'session_id' => $booking->getSessionId(),
            'user_id' => $booking->getUserId(),
            'seats' => $booking->getSeats()->map(fn($seat) => $seat->getId())->toArray(),
            'timestamp' => time()
        ]);
        
        $qrCode = QrCode::create($data)
            ->setSize(300)
            ->setMargin(10);
        
        $writer = new PngWriter();
        $result = $writer->write($qrCode);
        
        return $result->getDataUri();
    }
    
    public function verifyQRCode($qrData) {
        try {
            $data = json_decode($qrData, true);
            
            $booking = Booking::findById($data['booking_id']);
            if (!$booking) {
                return false;
            }
            
            return [
                'valid' => true,
                'booking' => $booking,
                'seats' => $data['seats']
            ];
        } catch (\Exception $e) {
            return false;
        }
    }
}
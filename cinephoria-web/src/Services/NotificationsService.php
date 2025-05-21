<?php
namespace App\Services;

use Pusher\Pusher;

class NotificationService {
    private $pusher;
    
    public function __construct() {
        $this->pusher = new Pusher(
            PUSHER_APP_KEY,
            PUSHER_APP_SECRET,
            PUSHER_APP_ID,
            [
                'cluster' => PUSHER_CLUSTER,
                'useTLS' => true
            ]
        );
    }
    
    public function notifyBookingConfirmation($userId, $bookingData) {
        $this->pusher->trigger('user-' . $userId, 'booking-confirmed', $bookingData);
    }
    
    public function notifyIncident($roomId, $incidentData) {
        $this->pusher->trigger('room-' . $roomId, 'incident-reported', $incidentData);
    }
}


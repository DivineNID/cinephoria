<?php
namespace App\Services;

use App\Models\Booking;
use Stripe\Stripe;
use Stripe\PaymentIntent;

class PaymentGateway {
    private $stripe;
    
    public function __construct() {
        Stripe::setApiKey(STRIPE_SECRET_KEY);
    }
    
    public function createPaymentIntent(Booking $booking) {
        try {
            $paymentIntent = PaymentIntent::create([
                'amount' => $booking->getTotalPrice() * 100, // Stripe utilise les centimes
                'currency' => 'eur',
                'metadata' => [
                    'booking_id' => $booking->getId(),
                    'user_id' => $booking->getUserId()
                ]
            ]);
            
            return $paymentIntent;
        } catch (\Exception $e) {
            error_log("Erreur Stripe : " . $e->getMessage());
            throw new \Exception("Erreur lors de l'initialisation du paiement");
        }
    }
}

// src/Controllers/PaymentController.php
namespace App\Controllers;

use App\Models\Booking;
use App\Services\PaymentGateway;
use App\Services\Mailer;

class PaymentController extends BaseController {
    public function process($bookingId) {
        $this->requireAuth();
        
        $booking = Booking::findById($bookingId);
        if (!$booking || $booking->getUserId() !== $_SESSION['user_id']) {
            $this->setFlash('danger', 'Réservation non trouvée');
            $this->redirect('reservation');
        }
        
        $gateway = new PaymentGateway();
        try {
            $paymentIntent = $gateway->createPaymentIntent($booking);
            
            $this->render('payment/process', [
                'title' => 'Paiement',
                'booking' => $booking,
                'clientSecret' => $paymentIntent->client_secret
            ]);
        } catch (\Exception $e) {
            $this->setFlash('danger', $e->getMessage());
            $this->redirect('reservation');
        }
    }
    
    public function confirm() {
        $this->requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('reservation');
        }
        
        $bookingId = $_SESSION['pending_booking_id'] ?? null;
        if (!$bookingId) {
            $this->setFlash('danger', 'Session de paiement expirée');
            $this->redirect('reservation');
        }
        
        $booking = Booking::findById($bookingId);
        $booking->setStatus('confirmed');
        $booking->save();
        
        // Envoi de la confirmation par email
        $mailer = new Mailer();
        $mailer->sendBookingConfirmation($booking);
        
        unset($_SESSION['pending_booking_id']);
        
        $this->setFlash('success', 'Paiement confirmé ! Un email de confirmation vous a été envoyé.');
        $this->redirect("booking/show/$bookingId");
    }
}
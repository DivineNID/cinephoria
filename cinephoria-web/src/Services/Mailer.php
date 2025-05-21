<?php
namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mailer {
    private $mailer;
    
    public function __construct() {
        $this->mailer = new PHPMailer(true);
        
        // Configuration
        $this->mailer->isSMTP();
        $this->mailer->Host = MAIL_HOST;
        $this->mailer->SMTPAuth = true;
        $this->mailer->Username = MAIL_USERNAME;
        $this->mailer->Password = MAIL_PASSWORD;
        $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->mailer->Port = MAIL_PORT;
        $this->mailer->CharSet = 'UTF-8';
        
        $this->mailer->setFrom(MAIL_USERNAME, SITE_NAME);
    }
    
    public function sendConfirmationEmail($user) {
        try {
            $this->mailer->addAddress($user->getEmail());
            $this->mailer->Subject = 'Bienvenue sur ' . SITE_NAME;
            
            $body = $this->getEmailTemplate('confirmation', [
                'username' => $user->getUsername(),
                'sitename' => SITE_NAME
            ]);
            
            $this->mailer->Body = $body;
            $this->mailer->AltBody = strip_tags($body);
            
            return $this->mailer->send();
        } catch (Exception $e) {
            error_log("Erreur d'envoi d'email : " . $e->getMessage());
            return false;
        }
    }
    
    public function sendBookingConfirmation($booking) {
        try {
            $user = $booking->getUser();
            $session = $booking->getSession();
            $film = $session->getFilm();
            
            $this->mailer->addAddress($user->getEmail());
            $this->mailer->Subject = 'Confirmation de votre réservation - ' . SITE_NAME;
            
            $body = $this->getEmailTemplate('booking_confirmation', [
                'username' => $user->getUsername(),
                'film_title' => $film->getTitle(),
                'session_date' => $session->getFormattedStartTime(),
                'booking_id' => $booking->getId(),
                'total_price' => $booking->getTotalPrice(),
                'qr_code' => $booking->generateQRCode()
            ]);
            
            $this->mailer->Body = $body;
            $this->mailer->AltBody = strip_tags($body);
            
            return $this->mailer->send();
        } catch (Exception $e) {
            error_log("Erreur d'envoi d'email : " . $e->getMessage());
            return false;
        }
    }
    
    private function getEmailTemplate($template, $data) {
        ob_start();
        extract($data);
        include VIEW_PATH . "/emails/$template.php";
        return ob_get_clean();
    }
}
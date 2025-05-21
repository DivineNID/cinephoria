<?php
namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Models\Booking;
use App\Models\Session;
use App\Models\User;

class BookingTest extends TestCase {
    private $booking;
    private $session;
    private $user;
    
    protected function setUp(): void {
        $this->user = $this->createMock(User::class);
        $this->session = $this->createMock(Session::class);
        $this->booking = new Booking();
    }
    
    public function testCalculateTotalPrice() {
        $this->session->method('getBasePrice')->willReturn(10.0);
        $this->session->method('getQualityMultiplier')->willReturn(1.5);
        
        $this->booking->setSession($this->session);
        $this->booking->setSeatsCount(2);
        
        $this->assertEquals(30.0, $this->booking->calculateTotalPrice());
    }
    
    public function testBookingValidation() {
        $this->session->method('getAvailableSeats')->willReturn(5);
        $this->booking->setSession($this->session);
        
        $this->assertTrue($this->booking->canBook(3));
        $this->assertFalse($this->booking->canBook(6));
    }
}

// tests/Functional/ReservationTest.php
namespace Tests\Functional;

use PHPUnit\Framework\TestCase;
use App\Controllers\ReservationController;
use App\Models\Session;
use App\Models\User;

class ReservationTest extends TestCase {
    private $controller;
    
    protected function setUp(): void {
        $this->controller = new ReservationController();
        // Simuler une session utilisateur
        $_SESSION['user_id'] = 1;
    }
    
    public function testReservationProcess() {
        $_POST = [
            'session_id' => 1,
            'seats' => [1, 2],
        ];
        
        $response = $this->controller->confirm();
        
        $this->assertInstanceOf(Booking::class, $response);
        $this->assertEquals('pending', $response->getStatus());
        $this->assertEquals(2, $response->getSeats()->count());
    }
}
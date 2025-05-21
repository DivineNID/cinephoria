<?php
namespace Tests\Functional;

use PHPUnit\Framework\TestCase;
use App\Models\User;

class UserRegistrationTest extends TestCase {
    public function testUserRegistration() {
        $user = new User();
        $user->setEmail('test@example.com')
             ->setPassword(password_hash('Test1234!', PASSWORD_DEFAULT))
             ->setFirstname('Test')
             ->setLastname('User')
             ->setUsername('testuser');
        $this->assertTrue($user->save());
        $this->assertNotNull(User::findByEmail('test@example.com'));
    }
}
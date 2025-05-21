<?php
namespace App\Controllers;

use App\Models\User;
use App\Services\Mailer;

class AuthController extends BaseController {
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
            $password = $_POST['password'] ?? '';
            
            $user = User::findByEmail($email);
            
            if ($user && password_verify($password, $user->getPassword())) {
                $_SESSION['user_id'] = $user->getId();
                $_SESSION['user'] = [
                    'id' => $user->getId(),
                    'email' => $user->getEmail(),
                    'username' => $user->getUsername(),
                    'role' => $user->getRole()
                ];
                
                $this->setFlash('success', 'Connexion réussie !');
                $this->redirect(isset($_SESSION['redirect_after_login']) 
                    ? $_SESSION['redirect_after_login'] 
                    : 'home');
            } else {
                $this->setFlash('danger', 'Identifiants incorrects');
            }
        }
        
        $this->render('auth/login', ['title' => 'Connexion']);
    }
    
    public function register() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
            $password = $_POST['password'] ?? '';
            $firstname = filter_input(INPUT_POST, 'firstname', FILTER_SANITIZE_STRING);
            $lastname = filter_input(INPUT_POST, 'lastname', FILTER_SANITIZE_STRING);
            $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
            
            // Validation
            $errors = [];
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = "Email invalide";
            }
            if (strlen($password) < 8) {
                $errors[] = "Le mot de passe doit faire au moins 8 caractères";
            }
            if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $password)) {
                $errors[] = "Le mot de passe doit contenir au moins une majuscule, une minuscule, un chiffre et un caractère spécial";
            }
            
            if (empty($errors)) {
                $user = new User();
                $user->setEmail($email)
                     ->setPassword(password_hash($password, PASSWORD_DEFAULT))
                     ->setFirstname($firstname)
                     ->setLastname($lastname)
                     ->setUsername($username)
                     ->setRole('user');
                
                if ($user->save()) {
                    // Envoi email de confirmation
                    $mailer = new Mailer();
                    $mailer->sendConfirmationEmail($user);
                    
                    $this->setFlash('success', 'Inscription réussie ! Un email de confirmation vous a été envoyé.');
                    $this->redirect('login');
                } else {
                    $this->setFlash('danger', 'Une erreur est survenue lors de l\'inscription');
                }
            } else {
                $this->setFlash('danger', implode('<br>', $errors));
            }
        }
        
        $this->render('auth/register', ['title' => 'Inscription']);
    }
    
    public function forgotPassword() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
            
            $user = User::findByEmail($email);
            if ($user) {
                $token = bin2hex(random_bytes(32));
                $user->setResetToken($token)
                     ->setResetTokenExpiry(date('Y-m-d H:i:s', strtotime('+1 hour')))
                     ->save();
                
                $mailer = new Mailer();
                $mailer->sendPasswordResetEmail($user, $token);
                
                $this->setFlash('success', 'Un email de réinitialisation vous a été envoyé');
                $this->redirect('login');
            } else {
                $this->setFlash('danger', 'Aucun compte trouvé avec cet email');
            }
        }
        
        $this->render('auth/forgot-password', ['title' => 'Mot de passe oublié']);
    }
    
    public function logout() {
        session_destroy();
        $this->redirect('home');
    }
}
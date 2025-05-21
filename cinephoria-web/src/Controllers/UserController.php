<?php
namespace App\Controllers;

use App\Models\User;
use App\Services\Mailer;

class UserController extends BaseController {
    public function profile() {
        $this->requireAuth();
        $user = User::findById($_SESSION['user_id']);
        $this->render('user/profile', ['title' => 'Mon profil', 'user' => $user]);
    }

    public function updateProfile() {
        $this->requireAuth();
        $user = User::findById($_SESSION['user_id']);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $user->setFirstname($_POST['firstname'])
                 ->setLastname($_POST['lastname'])
                 ->setUsername($_POST['username']);
            if (!empty($_POST['password'])) {
                $user->setPassword(password_hash($_POST['password'], PASSWORD_DEFAULT));
            }
            $user->save();
            $this->setFlash('success', 'Profil mis à jour');
            $this->redirect('profile');
        }
        $this->render('user/edit', ['title' => 'Modifier mon profil', 'user' => $user]);
    }

    public function preferences() {
        $this->requireAuth();
        $user = User::findById($_SESSION['user_id']);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $user->setPreferences(json_encode($_POST['preferences']));
            $user->save();
            $this->setFlash('success', 'Préférences enregistrées');
            $this->redirect('preferences');
        }
        $this->render('user/preferences', [
            'title' => 'Mes préférences',
            'user' => $user,
            'preferences' => json_decode($user->getPreferences(), true)
        ]);
    }

    public function exportData() {
        $this->requireAuth();
        $user = User::findById($_SESSION['user_id']);
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="mes_donnees_cinephoria.json"');
        echo json_encode($user->exportAllData());
        exit;
    }

    public function deleteAccount() {
        $this->requireAuth();
        $user = User::findById($_SESSION['user_id']);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $user->delete();
            session_destroy();
            $this->setFlash('success', 'Votre compte a été supprimé conformément à la RGPD.');
            $this->redirect('home');
        }
        $this->render('user/delete', ['title' => 'Supprimer mon compte']);
    }
}
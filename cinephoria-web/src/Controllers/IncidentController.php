<?php
namespace App\Controllers;

use App\Models\Incident;
use App\Models\Room;

class IncidentController extends BaseController {
    public function __construct() {
        $this->requireEmployee();
    }
    
    public function index() {
        $incidents = Incident::findAll();
        
        $this->render('incidents/index', [
            'title' => 'Gestion des incidents',
            'incidents' => $incidents
        ]);
    }
    
    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $roomId = filter_input(INPUT_POST, 'room_id', FILTER_SANITIZE_NUMBER_INT);
            $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);
            
            $incident = new Incident();
            $incident->setRoomId($roomId)
                    ->setReportedBy($_SESSION['user_id'])
                    ->setDescription($description)
                    ->setStatus('reported')
                    ->save();
            
            $this->setFlash('success', 'Incident signalé avec succès');
            $this->redirect('incidents');
        }
        
        $rooms = Room::findAll();
        $this->render('incidents/create', [
            'title' => 'Signaler un incident',
            'rooms' => $rooms
        ]);
    }
    
    public function update($id) {
        $incident = Incident::findById($id);
        if (!$incident) {
            $this->setFlash('danger', 'Incident non trouvé');
            $this->redirect('incidents');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING);
            
            $incident->setStatus($status);
            if ($status === 'resolved') {
                $incident->setResolvedAt(date('Y-m-d H:i:s'));
            }
            $incident->save();
            
            $this->setFlash('success', 'Statut de l\'incident mis à jour');
            $this->redirect('incidents');
        }
        
        $this->render('incidents/edit', [
            'title' => 'Modifier l\'incident',
            'incident' => $incident
        ]);
    }
}
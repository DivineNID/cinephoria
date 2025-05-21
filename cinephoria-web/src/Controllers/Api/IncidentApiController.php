<?php
namespace App\Controllers\Api;

use App\Models\Incident;

class IncidentApiController extends ApiController {
    public function report() {
        $userId = $this->authenticateRequest();
        $data = json_decode(file_get_contents('php://input'), true);
        $incident = new Incident();
        $incident->setRoomId($data['room_id'])
                 ->setReportedBy($userId)
                 ->setDescription($data['description'])
                 ->setStatus('reported')
                 ->save();
        $this->json(['success' => true, 'incident_id' => $incident->getId()]);
    }

    public function list() {
        $this->requireEmployee();
        $incidents = Incident::findAll();
        $this->json(['incidents' => array_map(fn($i) => $i->toArray(), $incidents)]);
    }
}

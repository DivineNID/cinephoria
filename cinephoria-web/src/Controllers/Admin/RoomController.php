<?php
namespace App\Controllers\Admin;

use App\Models\Room;
use App\Models\Cinema;
use App\Services\RoomConfigurationService;

class RoomController extends AdminBaseController {
    private $configService;
    
    public function __construct() {
        parent::__construct();
        $this->configService = new RoomConfigurationService();
    }
    
    public function index() {
        $cinemaId = filter_input(INPUT_GET, 'cinema', FILTER_SANITIZE_NUMBER_INT);
        $rooms = $cinemaId ? Room::findByCinema($cinemaId) : Room::findAll();
        $cinemas = Cinema::findAll();
        
        $this->render('admin/rooms/index', [
            'title' => 'Gestion des salles',
            'rooms' => $rooms,
            'cinemas' => $cinemas,
            'selectedCinema' => $cinemaId
        ]);
    }
    
    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $roomData = $this->validateRoomData($_POST);
                $seatConfig = json_decode($_POST['seat_configuration'], true);
                
                $room = new Room();
                $room->setCinemaId($roomData['cinema_id'])
                     ->setName($roomData['name'])
                     ->setCapacity($roomData['capacity'])
                     ->setIsHandicapAccessible($roomData['is_handicap_accessible']);
                
                $room->save();
                
                // Configuration des sièges
                $this->configService->configureSeatLayout($room->getId(), $seatConfig);
                
                $this->setFlash('success', 'Salle créée avec succès');
                $this->redirect('admin/rooms');
            } catch (\Exception $e) {
                $this->setFlash('danger', $e->getMessage());
            }
        }
        
        $cinemas = Cinema::findAll();
        $this->render('admin/rooms/create', [
            'title' => 'Nouvelle salle',
            'cinemas' => $cinemas
        ]);
    }
}


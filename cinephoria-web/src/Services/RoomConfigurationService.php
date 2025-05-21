<?php
namespace App\Services;

use App\Models\Room;
use App\Models\Seat;

class RoomConfigurationService {
    public function configureSeatLayout($roomId, array $configuration) {
        $db = Database::getMysqlConnection();
        $db->beginTransaction();
        
        try {
            // Suppression des sièges existants
            Seat::deleteByRoom($roomId);
            
            // Création des nouveaux sièges
            foreach ($configuration as $row => $seats) {
                foreach ($seats as $seatNumber => $type) {
                    $seat = new Seat();
                    $seat->setRoomId($roomId)
                         ->setRowNumber($row)
                         ->setSeatNumber($seatNumber)
                         ->setIsHandicap($type === 'handicap')
                         ->save();
                }
            }
            
            $db->commit();
            return true;
        } catch (\Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }
}
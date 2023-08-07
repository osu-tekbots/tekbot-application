<?php

namespace DataAccess;

use Model\Station;
use Model\StationContents;
use Model\Room;

/**
 * Handles logic related to queries on loading/editing Lab in the database
 * Labs are identified by their 
 */

class LabDao {
    /** @var DatabaseConnection */
    private $conn;

    /** @var \Util\Logger */
    private $logger;

    /**
     * Creates a new instance of the data access object for Lab data
     * Labs are in rooms and the rooms have stations in them and those stations have contents
    *
    * @param DatabaseConnection $conn the connection used to communicate to the database
    * @param \Util\Logger $logger the logger used to log details about the interaction with the database
    */
    public function __construct($connection, $logger) {
        $this->conn = $connection;
        $this->logger = $logger;
    }

    /**
     * Fetches all info from labs_rooms db
     * @return \Model\Station|array station contents info with specified Id
     */

     public function getRooms(){
        try {
            $sql = '
            SELECT *
            FROM `labs_rooms`
            ';
            $results = $this->conn->query($sql);
            $rooms = array();
            foreach ($results as $row) {
                $room = self::ExtractRoomFromRow($row);
                $rooms[] = $room;
            }
            return $rooms;
            //return \array_map('self::ExtractRoomFromRow', $results);
        } catch(\Exception $e) {
            $this->logger->error('Failed to get Room names: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Fetches station by labs_stations.id
     * @return \Model\Station|array station contents info with specified Id
     */
    
    public function getStationById($id) {
        try {
            $sql = '
            SELECT 
			labs_stations.*, 
			labs_rooms.id AS room_id, 
			labs_rooms.map AS map, 
			labs_rooms.name AS room_name
            FROM `labs_stations`
            INNER JOIN `labs_rooms`
            ON labs_stations.roomid = labs_rooms.id
            WHERE labs_stations.id = :id
            ';
            $params = array(':id' => $id);
            $results = $this->conn->query($sql, $params);
            $station = self::ExtractStationFromRow($results[0]);

            return $station;
        } catch(\Exception $e) {
            $this->logger->error('Failed to get Stations in room: ' . $e->getMessage());
            return false;
        }
    }
    /**
    * Fetches all info for stations model
    * @return \Model\Station|array station contents info with specified Id
    */
    public function getStations(){
        try {
            $sql = '
            SELECT 
			labs_stations.*, 
			labs_rooms.id AS room_id, 
			labs_rooms.map AS map, 
			labs_rooms.name AS room_name
            FROM `labs_stations`
            INNER JOIN `labs_rooms`
            ON labs_stations.roomid = labs_rooms.id
            ';
            $results = $this->conn->query($sql);
            return \array_map('self::ExtractStationFromRow', $results);
        } catch(\Exception $e) {
            $this->logger->error('Failed to get Room names: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Fetches all stations in room id
     * @return \Model\Lab|array station contents info with specified Id
     */

    public function getStationsFromRoom($roomid) {
        try {
            $sql = '
            SELECT 
			labs_stations.*, 
			labs_rooms.id AS room_id, 
			labs_rooms.map AS map, 
			labs_rooms.name AS room_name
            FROM `labs_stations`
            INNER JOIN `labs_rooms`
            ON labs_stations.roomid = :roomid
            WHERE labs_rooms.id = :roomid
            ';
            $params = array(':roomid' => $roomid);
            $results = $this->conn->query($sql, $params);
            return \array_map('self::ExtractStationFromRow', $results);
        } catch(\Exception $e) {
            $this->logger->error('Failed to get Stations in room: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Fetches the station contents by station Id
     * @return \Model\Lab|array station contents info with specified Id
     */

    public function getStationContents($stationid) {
        try {
            $sql = '
            SELECT *
            FROM `labs_stationcontents`
            WHERE labs_stationcontents.stationid = :stationid
            ';

            $params = array(':id' => $stationid);
            $results = $this->conn->query($sql, $params);
            //return \array_map('self::ExtractStationContentsFromRow', $results);
            $contents = self::ExtractStationFromRow($results[0]);

            return $contents;
        } catch(\Exception $e) {
            $this->logger->error('Failed to get Station contents by ID: ' . $e->getMessage());
            return false;
        }
    }

    public function getStationIdFromRoomAndBench($roomid, $benchid) {
        try {
            $sql = '
            SELECT id
            FROM `labs_stations`
            WHERE roomid=:roomid AND name=:benchid;';

            $params = array(':roomid' => $roomid, ':benchid' => $benchid);
            $results = $this->conn->query($sql, $params);
            //return \array_map('self::ExtractStationContentsFromRow', $results);
            $contents = self::ExtractStationFromRow($results[0]);
            return $contents->getId();
        } catch(\Exception $e) {
            $this->logger->error('Failed to get Station ID by Room ID and Bench ID: ' . $e->getMessage());
            return false;
        }
    }

    public static function ExtractStationFromRow($row) {
        //uses room as sub model
        $room = new Room();

        if(isset($row['room_name'])) {
            $room->setName($row['room_name']);
        }
        if(isset($row['room_id'])) {
            $room->setId($row['room_id']);
        }
        if(isset($row['map'])) {
            $room->setMap($row['map']);
        }

        $station = new Station($row['id']);
        
		$station->setRoom($room);
        
		if(isset($row['id'])) {
            $station->setId($row['id']);
        }

        if(isset($row['name'])) {
            $station->setName($row['name']);
        }

        if(isset($row['roomid'])) {
            $station->setRoomId($row['roomid']);
        }

        if(isset($row['image'])) {
            $station->setImage($row['image']);
        }
		
		return $station;
    }
    

    public static function ExtractStationContentsFromRow($row) {
        $stationContents = new StationContents($row['id']);

        if(isset($row['id'])) {
            $stationContents->setId($row['id']);
        }

        if(isset($row['stationid'])) {
            $stationContents->setStationId($row['stationid']);
        }

        if(isset($row['equipmentid'])) {
            $stationContents->setEquipmentId($row['equipmentid']);
        }

        if(isset($row['invid'])) {
            $stationContents->setInvid($row['invid']);
        }

        if(isset($row['status'])) {
            $stationContents->setStatus($row['status']);
        }

        if(isset($row['comment'])) {
            $stationContents->setComment($row['comment']);
        }
        return $stationContents;
    }

    public static function ExtractRoomFromRow($row) {
        $room = new Room($row['id']);

        if(isset($row['id'])) {
            $room->setId($row['id']);
        }
        if(isset($row['name'])) {
            $room->setName($row['name']);
        }
        if(isset($row['map'])) {
            $room->setMap($row['map']);
        }
        return $room;
    }

}

?>
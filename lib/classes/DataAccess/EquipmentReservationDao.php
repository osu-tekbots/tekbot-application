<?php
// Updated 11/5/2019
namespace DataAccess;

use Model\EquipmentReservation;
use Model\Contract;
use DataAccess\EquipmentDao;


/**
 * Handles all of the logic related to queries on capstone project resources in the database.
 */
class EquipmentReservationDao {

    /** @var DatabaseConnection */
    private $conn;

    /** @var \Util\Logger */
    private $logger;

    /**
     * Creates a new instance of the data access object for capstone project data.
     *
     * @param DatabaseConnection $connection the connection to use to communiate with the database
     * @param \Util\Logger $logger the logger to use to log details about the interactions with the database
     */
    public function __construct($connection, $logger) {
        $this->conn = $connection;
        $this->logger = $logger;
    }


    /**
     * Fetches reservations associated with a user.
     *
     * @param string $userID the ID of the user whose reservations to fetch
     * @return \Model\EquipmentReservation[]|boolean an array of reservations on success, false otherwise
     */
    public function getReservationsForUser($userID) {
        try {
            $sql = '
            SELECT * 
            FROM equipment_reservation, user, equipment, user_access_level
            WHERE equipment_reservation.equipment_id = equipment.equipment_id
                AND equipment_reservation.user_id = user.user_id
                AND user.access_level_id = user_access_level.user_access_level_id
                AND equipment_reservation.user_id = :uid
                
            ';
            $params = array(':uid' => $userID);
            $results = $this->conn->query($sql, $params);

            $reservations = array();
            foreach ($results as $row) {
                $reservation = self::ExtractReservationFromRow($row, true);
                $reservations[] = $reservation;
            }
           
            return $reservations;
        } catch (\Exception $e) {
            $this->logger->error("Failed to get reservations for user '$userID': " . $e->getMessage());
            return false;
        }
    }

    /**
     * Fetches the equipment reservation with the provided ID
     *
     * @param string $id
     * @return \Model\EquipmentReservation|boolean the equipment on success, false otherwise
     */
    public function getReservation($id) {
        try {
            $sql = '
            SELECT * 
            FROM equipment_reservation, user, equipment, user_access_level
            WHERE equipment_reservation.equipment_id = equipment.equipment_id
                AND equipment_reservation.user_id = user.user_id
                AND user.access_level_id = user_access_level.user_access_level_id
                AND equipment_reservation.eqreservation_id = :id
                
            ';
            $params = array(':id' => $id);
            $results = $this->conn->query($sql, $params);
            if (\count($results) == 0) {
                return false;
            }

            $reservation = self::ExtractReservationFromRow($results[0]);

            return $reservation;
        } catch (\Exception $e) {
            $this->logger->error("Failed to fetch reservation with id '$id': " . $e->getMessage());
            return false;
        }
    }

    /**
     * Fetches reserved equipment for admin.
     *
     * @return \Model\EquipmentReservation[]|boolean an array of projects on success, false otherwise
     */
    public function getReservationsForAdmin() {
        try {
            $sql = '
            SELECT * 
            FROM equipment_reservation, user, equipment, user_access_level
            WHERE equipment_reservation.equipment_id = equipment.equipment_id
                AND equipment_reservation.user_id = user.user_id
                AND user.access_level_id = user_access_level.user_access_level_id
                
            ';
            $results = $this->conn->query($sql);

            $reservations = array();
            foreach ($results as $row) {
                $reservation = self::ExtractReservationFromRow($row, true);
                $reservations[] = $reservation;
            }
           
            return $reservations;
        } catch (\Exception $e) {
            $this->logger->error("Failed to get admin reservations: " . $e->getMessage());
            return false;
        }
    }


    public function getEquipmentAvailableStatus($equipmentID){
        try {
            $sql = '
            SELECT equipment_id, equipment_name, instances
            FROM equipment
            WHERE equipment_id = :eid
                AND instances > (
                    SELECT (
                        SELECT COUNT(*) 
                        FROM equipment_reservation
                        WHERE equipment_id = :eid
                        AND is_active = 1
                    ) + ( 
                        SELECT COUNT(*)
                        FROM equipment_checkout
                        WHERE equipment_id = :eid
                        AND (checkout_status_id = 1 OR checkout_status_id = 2)
                    )
                )
            ';
            
            $params = array(
                ':eid' => $equipmentID
            );
            $results = $this->conn->query($sql, $params);

            if (\count($results) == 0) {
                // Return false if equipment is not available
                return false;
            } else {
                // Return true if equipment is available for checkout
                return true;
            }

        } catch (\Exception $e) {
            $this->logger->error('Failed to get any equipments: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Adds a new equipment reservation entry into the database.
     *
     * @param \Model\EquipmentReservation $reservation the equipment to add
     * @return boolean true if successful, false otherwise
     */
    public function addNewReservation($reservation) {
        try {
            $sql = '
            INSERT INTO equipment_reservation VALUES (
                :id,
                :equipmentid,
                :userid,
                :reserved,
                :expired,
                :isactive
            )
            ';
            $params = array(
                ':id' => $reservation->getReservationID(),
                ':equipmentid' => $reservation->getEquipmentID(),
                ':userid' => $reservation->getUserID(),
                ':reserved' => QueryUtils::FormatDate($reservation->getDatetimeReserved()),
                ':expired' => QueryUtils::FormatDate($reservation->getDatetimeExpired()),
                ':isactive' => $reservation->getIsActive()
            );
            $this->conn->execute($sql, $params);
            return true;
        } catch (\Exception $e) {
            $this->logger->error('Failed to add new reservation: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Updates an equipment reservation in the database.
     *
     * @param \Model\EquipmentReservation $reservation the reservation to add
     * @return boolean true if successful, false otherwise
     */
    public function updateReservation($reservation) {
        try {
            $sql = '
            UPDATE equipment_reservation SET
                equipment_id = :equipmentid,
                user_id = :userid,
                datetime_reserved = :reserved,
                datetime_expired = :expired,
                is_active = :isactive
            WHERE eqreservation_id = :id
            ';
            $params = array(
                ':id' => $reservation->getReservationID(),
                ':equipmentid' => $reservation->getEquipmentID(),
                ':userid' => $reservation->getUserID(),
                ':reserved' => $reservation->getDatetimeReserved(),
                ':expired' => $reservation->getDatetimeExpired(),
                ':isactive' => $reservation->getIsActive()
            );
            $this->conn->execute($sql, $params);
            return true;
        } catch (\Exception $e) {
            $id = $reservation->getReservationID();
            $this->logger->error("Failed to update reservation with id '$id': " . $e->getMessage());
            return false;
        }
    }


    /**
     * Extracts equipment reservation object using information from the database row
     *
     * @param mixed[] $row the row in the database from which information is to be extracted
     * @return \Model\EquipmentReservation
     */
    public static function ExtractReservationFromRow($row, $userInRow = false) {
        $reservation = new EquipmentReservation($row['eqreservation_id']);
        $reservation->setEquipmentID($row['equipment_id']);
        $reservation->setUserID($row['user_id']);
        $reservation->setDatetimeReserved($row['datetime_reserved']);
        $reservation->setDatetimeExpired($row['datetime_expired']);
        $reservation->setIsActive($row['is_active']);
        $reservation->setReservationID($row['eqreservation_id']);

        if ($userInRow) {
            $reservation->setUser(UsersDao::ExtractUserFromRow($row));
        }
        return $reservation;
    }


}



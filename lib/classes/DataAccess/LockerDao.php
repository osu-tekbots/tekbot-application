<?php

namespace DataAccess;

use Model\Locker;

/**
 * Handles all of the logic related to queries on capstone 3dprinter resources in the database.
 */
class LockerDao {

    /** @var DatabaseConnection */
    private $conn;

    /** @var \Util\Logger */
    private $logger;

    /**
     * Creates a new instance of the data access object for 3d printer data.
     *
     * @param DatabaseConnection $connection the connection to use to communiate with the database
     * @param \Util\Logger $logger the logger to use to log details about the interactions with the database
     */
    public function __construct($connection, $logger) {
        $this->conn = $connection;
        $this->logger = $logger;
    }

    /**
     * Fetches all Lockers.
     * @return an array of lockers on success, false otherwise
     */
    public function getLockers() {
        try {
            $sql = '
            SELECT * FROM `lockers_lockers` ORDER BY lockernumber ASC
            ';
            $results = $this->conn->query($sql);

            $lockers = array();
            foreach ($results as $row) {
                $locker = self::ExtractLockerFromRow($row);
                $lockers[] = $locker;
            }
            return $lockers;
        } catch (\Exception $e) {
            $this->logger->error('Failed to get any lockers: ' . $e->getMessage());
            return false;
        }
    }


    /**
     * Fetches all Print Jobs by ID.
     * @return \Model\PrinterJob[]|boolean an array of printers on success, false otherwise
     */
    public function getLockerByID($id) {
        try {
            $sql = '
            SELECT * 
            FROM `lockers_lockers`
            WHERE lockers_lockers.ID = :id
            ';
            $params = array(':id' => $id);
            $results = $this->conn->query($sql, $params);
			if (\count($results) == 0) {
                return false;
            } else {
				$locker = self::ExtractLockerFromRow($results[0]);
				return $locker;
			}
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to get locker with id '.$id.': ' . $e->getMessage());
            return false;
        }
    }

    public function updateLocker($locker) {
        try {
            $sql = '
            UPDATE lockers_lockers SET
                lockernumber = :lockernumber,
                lockerRoomId = :lockerRoomId,
                status = :status,
                location = :location,
                userId = :userId,
                free = :free,
                dueDate = :dueDate
            WHERE ID = :id
            ';
            $params = array(
                ':id' => $locker->getLockerId(),
                ':lockernumber' => $locker->getLockerNumber(),
                ':lockerRoomId' => $locker->getLockerRoomId(),
                ':status' => $locker->getStatus(),
                ':location' => $locker->getLocation(),
                ':userId' => $locker->getUserId(),
                ':free' => $locker->getFree(),
                ':dueDate' => QueryUtils::FormatDate($locker->getDueDate())
            );
            $this->conn->execute($sql, $params);
            return true;
        } catch (\Exception $e) {
            $this->logger->error('Failed to update locker: ' . $e->getMessage());
            return false;
        }
    }

    public function deleteLockerByID($lockerID) {
        try {
            $sql = '
            DELETE FROM lockers_lockers
            WHERE ID = :id
            ';
            $params = array(
                ':id' => $lockerID,
            );
            $this->conn->execute($sql, $params);

            return true;
        } catch (\Exception $e) {
            $this->logger->error('Failed to remove locker from DB: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Creates a new Equipment object using information from the database row
     *
     * @param mixed[] $row the row in the database from which information is to be extracted
     * @return \Model\Equipment
     */
    public static function ExtractLockerFromRow($row) {
        $locker = new Locker($row['ID']);

		if(isset($row['lockernumber'])){
			$locker->setLockerNumber($row['lockernumber']);
		}
		if(isset($row['lockerRoomId'])){
			$locker->setLockerRoomId($row['lockerRoomId']);
		}
		if(isset($row['status'])){
			$locker->setStatus($row['status']);
		}
		if(isset($row['location'])){
			$locker->setLocation($row['location']);
		}
		if(isset($row['free'])){
			$locker->setFree($row['free']);
		}
        if(isset($row['userId'])){
			$locker->setUserId($row['userId']);
		}
        if(isset($row['dueDate'])) {
            $locker->setDueDate(new \DateTime($row['dueDate']));
        }
       
        return $locker;
    }

   
}

?>
<?php

namespace DataAccess;

use Model\Box;

/**
 * Handles all of the logic related to queries on loading/editing messages in the database.
 */
class BoxDao {

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
     * Fetches all Parts.
     * @return an array of parts on success, false otherwise
     */
    public function getBoxes() {
        try {
            $sql = '
            SELECT tekbots_boxes.*
			FROM `tekbots_boxes`
			ORDER BY number ASC
			';
            $results = $this->conn->query($sql);

            $boxes = array();
            foreach ($results as $row) {
                $box = self::ExtractBoxFromRow($row);
                $boxes[] = $box;
            }
            return $boxes;
        } catch (\Exception $e) {
            $this->logger->error('Failed to get any boxes: ' . $e->getMessage());
            return false;
        }
    }


	/**
     * Fetches a box by box_key.
     * @return an array of parts on success, false otherwise
     */
    public function getBoxById($id) {
        try {
            $sql = '
            SELECT *
			FROM `tekbots_boxes`
			WHERE tekbots_boxes.box_key = :id
			 ';
            $params = array(':id' => $id);
            $results = $this->conn->query($sql, $params);
			foreach ($results as $row) {
                $box = self::ExtractBoxFromRow($row);
            }
            return $box;
        } catch (\Exception $e) {
            $this->logger->error('Failed to get any boxes: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Fetches a single part by StockNumber.
     * @return \Model\Part|boolean a part on success, false otherwise
     */
    public function boxStatus($id) {
        try {
            $sql = '
            SELECT tekbots_boxes.*
			FROM `tekbots_boxes`
			WHERE tekbots_boxes.box_key = :id AND locked = 0
			';
            $params = array(':id' => $id);
            $results = $this->conn->query($sql, $params);
			if (\count($results) == 0)
				return false;
			else
				return true;
        } catch (\Exception $e) {
            $this->logger->error('Failed to get state of box: ' . $e->getMessage());
            return false;
        }
    }
	
	/**
     * Fetches a single part by StockNumber.
     * @return \Model\Part|boolean a part on success, false otherwise
     */
    public function lockBox($id) {
        try {
            $sql = '
            UPDATE  tekbots_boxes 
			SET locked = 1
			WHERE tekbots_boxes.box_key = :id 
			';
            $params = array(':id' => $id);
            $results = $this->conn->execute($sql, $params);

			return true;
        } catch (\Exception $e) {
            $this->logger->error('Failed to get state of box: ' . $e->getMessage());
            return false;
        }
    }
	
	/**
     * Fetches a single part by StockNumber.
     * @return \Model\Part|boolean a part on success, false otherwise
     */
    public function unlockBox($id) {
        try {
            $sql = '
            UPDATE  tekbots_boxes 
			SET locked = 0  
			WHERE tekbots_boxes.box_key = :id
			';
            $params = array(':id' => $id);
            $results = $this->conn->execute($sql, $params);

			return true;
        } catch (\Exception $e) {
            $this->logger->error('Failed to get state of box: ' . $e->getMessage());
            return false;
        }
    }
	
	/**
     * Fetches a single part by StockNumber.
     * @return \Model\Part|boolean a part on success, false otherwise
     */
    public function pickupBox($id) {
        try {
            $sql = '
            UPDATE  tekbots_boxes 
			SET pickup_date = NOW() 
			WHERE tekbots_boxes.box_key = :id 
			';
            $params = array(':id' => $id);
            $results = $this->conn->execute($sql, $params);
          
            return (true);
        } catch (\Exception $e) {
            $this->logger->error('Failed to mark items picked up: ' . $e->getMessage());
            return false;
        }
    }
	
	public function resetBox($id) {
        try {
            $sql = '
            UPDATE tekbots_boxes 
			SET pickup_date = "0000-00-00 00:00:00", fill_date = "0000-00-00 00:00:00", user_id = "", locked = 1, fill_by = "", order_number = ""  
			WHERE tekbots_boxes.box_key = :id 
			';
            $params = array(':id' => $id);
            $this->conn->execute($sql, $params);
          
            return (true);
        } catch (\Exception $e) {
            $this->logger->error('Failed to reset box: ' . $e->getMessage());
            return false;
        }
    }

    public function updateBox($box) {
        try {
            $sql = '
            UPDATE tekbots_boxes SET
                number = :number,
				user_id = :user_id,
				order_number = :order_number,
				fill_date = :fill_date,
				fill_by = :fill_by,
				locked = :locked,
				pickup_date = :pickup_date
            WHERE box_key = :box_key
            ';
            $params = array(
				':box_key' => $box->getBoxKey(),
                ':number' => $box->getNumber(),
				':user_id' => $box->getUserId(),
                ':order_number' => $box->getOrderNumber(),
                ':fill_date' => $box->getFillDate(),
                ':fill_by' => $box->getFillBy(),
                ':locked' => $box->getLocked(),
                ':pickup_date' => $box->getPickupDate() 
            );
            $this->conn->execute($sql, $params);
			
            return true;
        } catch (\Exception $e) {
            $this->logger->error('Failed to update box: ' . $e->getMessage());
            return false;
        }
    }
	
	public function fillBoxById($id, $userId) {
        try {
            $sql = '
            UPDATE tekbots_boxes SET
                user_id = :user_id,
				fill_date = NOW(),
				locked = 1
            WHERE box_key = :id
            ';
            $params = array(
                ':user_id' => $userId,
                ':id' => $id 
            );
            $this->conn->execute($sql, $params);
			
            return true;
        } catch (\Exception $e) {
            $this->logger->error('Failed to update box: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Creates a new Equipment object using information from the database row
     *
     * @param mixed[] $row the row in the database from which information is to be extracted
     * @return \Model\Equipment
     */
    public static function ExtractBoxFromRow($row) {
        $box = new Box($row['box_key']);

		if(isset($row['box_key'])){
			$box->setBoxKey($row['box_key']);
		}
		if(isset($row['number'])){
			$box->setNumber($row['number']);
		}
		if(isset($row['user_id'])){
			$box->setUserId($row['user_id']);
		}
		if(isset($row['locked'])){
			$box->setLocked($row['locked']);
		}
		if(isset($row['fill_date'])){
			$box->setFillDate($row['fill_date']);
		}
		if(isset($row['fill_by'])){
			$box->setFillBy($row['fill_by']);
		}
		if(isset($row['pickup_date'])){
			$box->setPickupDate($row['pickup_date']);
		}
		if(isset($row['order_number'])){
			$box->setOrderNumber($row['order_number']);
		}
			
       
        return $box;
    }

   
}

?>
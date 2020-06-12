<?php

namespace DataAccess;

use Model\Part;

/**
 * Handles all of the logic related to queries on loading/editing messages in the database.
 */
class InventoryDao {

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
    public function getInventory() {
        try {
            $sql = '
            SELECT tekbots_parts.*, tekbots_types.Description AS type, tekbots_inventory.Quantity AS Quantity, tekbots_inventory.Location AS Location, tekbots_inventory.lastupdated AS lastcounted 
			FROM `tekbots_parts`
			INNER JOIN `tekbots_types` ON tekbots_types.ID = tekbots_parts.TypeID
			INNER JOIN `tekbots_inventory` ON tekbots_inventory.StockNumber = tekbots_parts.StockNumber
			ORDER BY tekbots_types.Description ASC, tekbots_parts.Name ASC
            ';
            $results = $this->conn->query($sql);

            $parts = array();
            foreach ($results as $row) {
                $part = self::ExtractPartFromRow($row);
                $parts[] = $part;
            }
            return $parts;
        } catch (\Exception $e) {
            $this->logger->error('Failed to get any parts: ' . $e->getMessage());
            return false;
        }
    }


	/**
     * Fetches all Parts by typeId.
     * @return an array of parts on success, false otherwise
     */
    public function getInventoryByTypeId($typeId) {
        try {
            $sql = '
            SELECT tekbots_parts.*, tekbots_types.Description AS type, tekbots_inventory.Quantity AS Quantity, tekbots_inventory.Location AS Location, tekbots_inventory.lastupdated AS lastcounted 
			FROM `tekbots_parts`
			INNER JOIN `tekbots_types` ON tekbots_types.ID = tekbots_parts.TypeID
			INNER JOIN `tekbots_inventory` ON tekbots_inventory.StockNumber = tekbots_parts.StockNumber
			WHERE tekbots_parts.TypeID = :typeid 
			ORDER BY tekbots_types.Description ASC, tekbots_parts.Name ASC
            ';
            $params = array(':typeid' => $typeId);
            $result = $this->conn->query($sql, $params);
            
            $parts = array();
            foreach ($results as $row) {
                $part = self::ExtractPartFromRow($row);
                $parts[] = $part;
            }
            return $parts;
        } catch (\Exception $e) {
            $this->logger->error('Failed to get any parts: ' . $e->getMessage());
            return false;
        }
    }
	
	/**
     * Fetches all Parts by typeId.
     * @return an array of rows on success, false otherwise
     */
    public function getTypes() {
        try {
            $sql = '
            SELECT tekbots_types.id AS typeId, tekbots_types.Description AS type
			FROM `tekbots_types`
			ORDER BY type ASC
            ';
            $result = $this->conn->query($sql);

            return $result;
        } catch (\Exception $e) {
            $this->logger->error('Failed to get any types: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Fetches a single part by StockNumber.
     * @return \Model\Part|boolean a part on success, false otherwise
     */
    public function getPartByStocknumber($stockNumber) {
        try {
            $sql = '
            SELECT tekbots_parts.*, tekbots_types.Description AS type, tekbots_inventory.Quantity AS Quantity, tekbots_inventory.Location AS Location, tekbots_inventory.lastupdated AS lastcounted 
			FROM `tekbots_parts`
			INNER JOIN `tekbots_types` ON tekbots_types.ID = tekbots_parts.TypeID
			INNER JOIN `tekbots_inventory` ON tekbots_inventory.StockNumber = tekbots_parts.StockNumber
			WHERE tekbots_parts.StockNumber = :stocknumber 
			ORDER BY tekbots_types.Description ASC, tekbots_parts.Name ASC
            ';
            $params = array(':stocknumber' => $stockNumber);
            $results = $this->conn->query($sql, $params);
          
            foreach ($results as $row) {
                $part = self::ExtractPartFromRow($row);
            }
            return $part;
        } catch (\Exception $e) {
            $this->logger->error('Failed to get part with StockNumber '.$stockNumber.': ' . $e->getMessage());
            return false;
        }
    }
	
	/**
     * Fetches a single part by StockNumber.
     * @return \Model\Part|boolean a part on success, false otherwise
     */
    public function getKitsUsedInByStocknumber($stockNumber) {
        try {
            $sql = '
            SELECT COUNT(*) AS kitsusedin 
			FROM tekbots_kitcontents 
			INNER JOIN tekbots_parts ON tekbots_parts.StockNumber = tekbots_kitcontents.ParentID 
			WHERE tekbots_kitcontents.ChildID = :stocknumber AND  tekbots_parts.archive = 0
			';
            $params = array(':stocknumber' => $stockNumber);
            $results = $this->conn->query($sql, $params);
          
            return ($results[0]['kitsusedin'] == 0 ? ' ' : $results[0]['kitsusedin']);
        } catch (\Exception $e) {
            $this->logger->error('Failed to identify kits with StockNumber '.$stockNumber.': ' . $e->getMessage());
            return false;
        }
    }

    public function updatePart($part) {
        try {
            $sql = '
            UPDATE tekbots_parts SET
                Name = :Name,
                touchnetid = :touchnetid,
				Image = :Image,
				OriginalImage = :OriginalImage,
				DataSheet = :DataSheet,
				LastPrice = :LastPrice,
				LastSupplier = :LastSupplier,
				TypeID = :TypeID,
				Manufacturer = :Manufacturer,
				ManufacturerNumber = :ManufacturerNumber,
				partMargin = :partMargin,
                stocked = :stocked,
                archive = :archive,
                MarketPrice = :MarketPrice,
                comment = :comment,
                lastupdated = NOW() 
            WHERE StockNumber = :stocknumber
            ';
            $params = array(
                ':stocknumber' => $part->getStocknumber(),
				':Name' => $part->getName(),
                ':touchnetid' => $part->getTouchnetId(),
                ':Image' => $part->getImage(),
                ':OriginalImage' => $part->getOriginalImage(),
                ':DataSheet' => $part->getDatasheet(),
                ':LastPrice' => $part->getLastPrice(),
                ':LastSupplier' => $part->getLastSupplier(),
                ':TypeID' => $part->getTypeId(),
                ':Manufacturer' => $part->getManufacturer(),
                ':ManufacturerNumber' => $part->getManufacturerNumber(),
                ':partMargin' => $part->getPartMargin(),
                ':stocked' => $part->getStocked(),
                ':archive' => $part->getArchive(),
                ':MarketPrice' => $part->getMarketPrice(),
                ':comment' => $part->getComment()  
            );
            $this->conn->execute($sql, $params);
			
			$sql = '
            UPDATE tekbots_inventory SET
                Quantity = :Quantity,
                Location = :Location,
                lastupdated = NOW() 
            WHERE StockNumber = :stocknumber 
            ';
            $params = array(
                ':stocknumber' => $part->getStocknumber(),
				':Quantity' => $part->getQuantity(),
                ':Location' => $part->getLocation() 
            );
            $this->conn->execute($sql, $params);
			
            return true;
        } catch (\Exception $e) {
            $this->logger->error('Failed to update part: ' . $e->getMessage());
            return false;
        }
    }

    public function deleteMessageByID($id) {
        try {
            $sql = '
            DELETE FROM messages
            WHERE message_id = :id
            ';
            $params = array(
                ':id' => $id,
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
    public static function ExtractPartFromRow($row) {
        $part = new Part($row['StockNumber']);

		if(isset($row['Name'])){
			$part->setName($row['Name']);
		}
		if(isset($row['touchnetid'])){
			$part->setTouchnetId($row['touchnetid']);
		}
		if(isset($row['Image'])){
			$part->setImage($row['Image']);
		}
		if(isset($row['OriginalImage'])){
			$part->setOriginalImage($row['OriginalImage']);
		}
		if(isset($row['DataSheet'])){
			$part->setDatasheet($row['DataSheet']);
		}
		if(isset($row['LastPrice'])){
			$part->setLastPrice($row['LastPrice']);
		}
		if(isset($row['LastSupplier'])){
			$part->setLastSupplier($row['LastSupplier']);
		}
		if(isset($row['TypeID'])){
			$part->setTypeId($row['TypeID']);
		}
		if(isset($row['Manufacturer'])){
			$part->setManufacturer($row['Manufacturer']);
		}
		if(isset($row['ManufacturerNumber'])){
			$part->setManufacturerNumber($row['ManufacturerNumber']);
		}
		if(isset($row['partMargin'])){
			$part->setPartMargin($row['partMargin']);
		}
		if(isset($row['stocked'])){
			$part->setStocked($row['stocked']);
		}
		if(isset($row['archive'])){
			$part->setArchive($row['archive']);
		}
		if(isset($row['MarketPrice'])){
			$part->setMarketPrice($row['MarketPrice']);
		}
		if(isset($row['comment'])){
			$part->setComment($row['comment']);
		}
		if(isset($row['lastupdated'])){
			$part->setLastUpdated($row['lastupdated']);
		}
		if(isset($row['type'])){
			$part->setType($row['type']);
		}
		if(isset($row['Quantity'])){
			$part->setQuantity($row['Quantity']);
		}
		if(isset($row['Location'])){
			$part->setLocation($row['Location']);
		}
		if(isset($row['lastcounted'])){
			$part->setLastCounted($row['lastcounted']);
		}		
       
        return $part;
    }

   
}

?>
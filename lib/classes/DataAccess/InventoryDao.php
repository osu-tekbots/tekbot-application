<?php

namespace DataAccess;

use Model\Part;
use Model\Kit;

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
    public function getInventory($archive = null) {
        try {
            if ($archive === null){
				$sql = '
				SELECT tekbots_parts.*, tekbots_types.Description AS type, tekbots_inventory.Quantity AS Quantity, tekbots_inventory.Location AS Location, tekbots_inventory.lastupdated AS lastcounted 
				FROM `tekbots_parts`
				INNER JOIN `tekbots_types` ON tekbots_types.ID = tekbots_parts.TypeID
				INNER JOIN `tekbots_inventory` ON tekbots_inventory.StockNumber = tekbots_parts.StockNumber
				ORDER BY tekbots_types.Description ASC, tekbots_parts.Name ASC
				';
				$results = $this->conn->query($sql);
			} else {
				$sql = '
				SELECT tekbots_parts.*, tekbots_types.Description AS type, tekbots_inventory.Quantity AS Quantity, tekbots_inventory.Location AS Location, tekbots_inventory.lastupdated AS lastcounted 
				FROM `tekbots_parts`
				INNER JOIN `tekbots_types` ON tekbots_types.ID = tekbots_parts.TypeID
				INNER JOIN `tekbots_inventory` ON tekbots_inventory.StockNumber = tekbots_parts.StockNumber
				WHERE tekbots_parts.archive = :archive 
				ORDER BY tekbots_types.Description ASC, tekbots_parts.Name ASC
				';
				$params = array(':archive' => $archive);
				$results = $this->conn->query($sql, $params);
            
			}

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
    public function getInventoryByTypeId($typeId, $archive = null) {
        try {
            if ($archive === null){
				$sql = '
				SELECT tekbots_parts.*, tekbots_types.Description AS type, tekbots_inventory.Quantity AS Quantity, tekbots_inventory.Location AS Location, tekbots_inventory.lastupdated AS lastcounted 
				FROM `tekbots_parts`
				INNER JOIN `tekbots_types` ON tekbots_types.ID = tekbots_parts.TypeID
				INNER JOIN `tekbots_inventory` ON tekbots_inventory.StockNumber = tekbots_parts.StockNumber
				WHERE tekbots_parts.TypeID = :typeid 
				ORDER BY tekbots_types.Description ASC, tekbots_parts.Name ASC
				';
				$params = array(':typeid' => $typeId);
				$results = $this->conn->query($sql, $params);
			} else {
				$sql = '
				SELECT tekbots_parts.*, tekbots_types.Description AS type, tekbots_inventory.Quantity AS Quantity, tekbots_inventory.Location AS Location, tekbots_inventory.lastupdated AS lastcounted 
				FROM `tekbots_parts`
				INNER JOIN `tekbots_types` ON tekbots_types.ID = tekbots_parts.TypeID
				INNER JOIN `tekbots_inventory` ON tekbots_inventory.StockNumber = tekbots_parts.StockNumber
				WHERE tekbots_parts.TypeID = :typeid AND tekbots_parts.archive = :archive
				ORDER BY tekbots_types.Description ASC, tekbots_parts.Name ASC
				';
				$params = array(':typeid' => $typeId, ':archive' => $archive);
				$results = $this->conn->query($sql, $params);
			}
            
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
    public function getKitContentsByStocknumber($stockNumber) {
        try {
			$sql = '
			SELECT tekbots_kitcontents.* 
			FROM `tekbots_kitcontents` 
			INNER JOIN tekbots_parts ON tekbots_parts.StockNumber = tekbots_kitcontents.ChildID 
			INNER JOIN tekbots_types ON tekbots_types.ID = tekbots_parts.TypeID 
			WHERE `ParentID` = :stocknumber 
			ORDER BY tekbots_types.Description ASC, tekbots_parts.Name ASC
			';
            $params = array(':stocknumber' => $stockNumber);
            $results = $this->conn->query($sql, $params);
          
            $contents = Array();
			foreach ($results as $row) {
				$contents[$row['ChildID']] = $row['Quantity'];
//				$contents[] = $row;
            }
			
            return $contents;
        } catch (\Exception $e) {
            $this->logger->error('Failed to get part with StockNumber '.$stockNumber.': ' . $e->getMessage());
            return false;
        }
    }
	
	/**
     * Fetches all Parts by typeId.
     * @return an array of parts on success, false otherwise
     */
    public function getSuppliersByStocknumber($stockNumber) {
        try {
			$sql = '
			SELECT tekbots_supplierparts.* , tekbots_supplier.SupplierName, tekbots_supplier.SupplierContact 
			FROM `tekbots_supplierparts`
			INNER JOIN tekbots_supplier ON tekbots_supplier.ID = tekbots_supplierparts.SupplierID
			WHERE `tekbots_supplierparts`.`StockNumber` = :stocknumber		
			ORDER BY tekbots_supplier.SupplierName ASC
			';
            $params = array(':stocknumber' => $stockNumber);
            $results = $this->conn->query($sql, $params);
			
            return $results;
        } catch (\Exception $e) {
            $this->logger->error('Failed to get part with StockNumber '.$stockNumber.': ' . $e->getMessage());
            return false;
        }
    }
	
	public function updateKitQuantity($parentid, $childid, $quantity) {
        try {
            $sql = '
			UPDATE tekbots_kitcontents
			SET `Quantity` = :quantity
			WHERE `ParentID` = :parentid 
			AND `ChildID` = :childid
			';
            $params = array(
                ':parentid' => $parentid,
				':childid' => $childid,
				':quantity' => $quantity
				
            );
            $this->conn->execute($sql, $params);

            return true;
        } catch (\Exception $e) {
            $this->logger->error('Failed to update quantity: ' . $e->getMessage());
            return false;
        }
    }
	
	public function addKitContents($parentid, $childid, $quantity) {
        try {
            $sql = '
			INSERT INTO tekbots_kitcontents
			(Quantity, ParentID, ChildID) VALUES
			(:quantity, :parentid, :childid)
			';
            $params = array(
                ':parentid' => $parentid,
				':childid' => $childid,
				':quantity' => $quantity
				
            );
            $this->conn->execute($sql, $params);

            return true;
        } catch (\Exception $e) {
            $this->logger->error('Failed to update quantity: ' . $e->getMessage());
            return false;
        }
    }
	
	public function addPartSupplier($stockNumber, $supplier, $partnumber, $htmllink) {
        try {
            $sql = '
			INSERT INTO tekbots_supplierparts
			(StockNumber, SupplierID, SupplierPart, link) VALUES
			(:stockNumber, :supplier, :partnumber, :htmllink)
			';
            $params = array(
                ':stockNumber' => $stockNumber,
				':supplier' => $supplier,
				':htmllink' => $htmllink,
				':partnumber' => $partnumber
				
            );
            $this->conn->execute($sql, $params);

            return true;
        } catch (\Exception $e) {
            $this->logger->error('Failed to add supplier: ' . $e->getMessage());
            return false;
        }
    }
	
	public function removePartSupplier($id) {
        try {
            $sql = '
			DELETE FROM tekbots_supplierparts
			WHERE ID = :id
			';
            $params = array(
                ':id' => $id				
            );
            $this->conn->execute($sql, $params);

            return true;
        } catch (\Exception $e) {
            $this->logger->error('Failed to add supplier: ' . $e->getMessage());
            return false;
        }
    }
	
	public function removeKitContents($parentid, $childid) {
        try {
            $sql = '
			DELETE FROM tekbots_kitcontents
			WHERE `ParentID` = :parentid 
			AND ChildID = :childid
			';
            $params = array(
                ':parentid' => $parentid,
				':childid' => $childid	
            );
            $this->conn->execute($sql, $params);

            return true;
        } catch (\Exception $e) {
            $this->logger->error('Failed to update quantity: ' . $e->getMessage());
            return false;
        }
    }
	
	/**
     * 
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
     * 
     * @return an array of rows on success, false otherwise
     */
    public function getSuppliers() {
        try {
            $sql = '
            SELECT tekbots_supplier.*
			FROM `tekbots_supplier`
			ORDER BY SupplierName ASC
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
     * Fetches the number of kits using this stocknumber.
     * @return an array on success failure on false
     */
    public function getKitsUsedInByStocknumber($stockNumber) {
        try {
            $sql = '
            SELECT DISTINCT Name AS kitNames 
			FROM tekbots_kitcontents 
			INNER JOIN tekbots_parts ON tekbots_parts.StockNumber = tekbots_kitcontents.ParentID 
			WHERE tekbots_kitcontents.ChildID = :stocknumber AND tekbots_parts.archive = 0
			';
            $params = array(':stocknumber' => $stockNumber);
            $results = $this->conn->query($sql, $params);
          
            //return ($results[0]['kitsusedin'] == 0 ? ' ' : $results[0]['kitsusedin']);
            return $results;
            //return \array_map('self::ExtractKitFromRow', $results);
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
                publicDescription = :publicdesc,
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
                ':comment' => $part->getComment(),
				':publicdesc' => $part->getPublicDescription()  
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
	
	public function addPart($part) {
        try {
            $sql = '
            INSERT INTO tekbots_parts 
			(StockNumber, TypeID, Name)
			VALUES
			(:stocknumber, :TypeID, :Name)
            ';
            $params = array(
                ':stocknumber' => $part->getStocknumber(),
				':Name' => $part->getName(),
                ':TypeID' => $part->getTypeId()  
            );
            $this->conn->execute($sql, $params);
			
			$sql = '
            INSERT INTO tekbots_inventory
            (StockNumber, Quantity, Location, lastupdated)
			VALUES (:stocknumber, :Quantity, :Location, NOW())
            ';
            $params = array(
                ':stocknumber' => $part->getStocknumber(),
				':Quantity' => 0,
                ':Location' => 'TBD' 
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

    //public function sendRecountEmail($id) {}

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
		if(isset($row['publicDescription'])){
			$part->setPublicDescription($row['publicDescription']);
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
	
	/**
     * Creates a new Equipment object using information from the database row
     *
     * @param mixed[] $row the row in the database from which information is to be extracted
     * @return \Model\Equipment
     */
    public static function ExtractKitFromRow($row) {
        $kit = new Kit($row['StockNumber']);

		if(isset($row['Name'])){
			$kit->setName($row['Name']);
		}
		if(isset($row['Image'])){
			$kit->setImage($row['Image']);
		}
		if(isset($row['TypeID'])){
			$kit->setTypeId($row['TypeID']);
		}
		if(isset($row['type'])){
			$kit->setType($row['type']);
		}		
       
        return $kit;
    }

   
}

?>
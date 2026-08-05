<?php
namespace DataAccess;

use DataAccess\EquipmentUnitDao;
use Model\EquipmentType;
use Model\EquipmentTypeCategory;
use Model\EquipmentTypeImage;

/**
 * Handles all of the logic related to queries on equipment types in the database.
 */
class EquipmentTypeDao {

    /** @var DatabaseConnection */
    private $conn;

    /** @var \Util\Logger */
    private $logger;

    /** @var \DataAccess\EquipmentUnitDao */
    private $unit_dao;

    /**
     * Creates a new instance of the data access object for equipment data.
     *
     * @param DatabaseConnection $connection the connection to use to communiate with the database
     * @param \Util\Logger $logger the logger to use to log details about the interactions with the database
     */
    public function __construct($connection, $logger) {
        $this->conn = $connection;
        $this->logger = $logger;

        $this->unit_dao = new EquipmentUnitDao($connection, $logger);
    }


    /**
     * Fetches all public equipment types along with their default image. Only the default image
     * will be present in the models' image list.
     *
     * @return \Model\EquipmentType[]|boolean an array of equipments on success, false otherwise
     */
    public function getBrowsableEquipment() {
        try {
            $sql = 'SELECT * FROM equipment_type
                    INNER JOIN equipment_type_category ON etc_id = et_etc_id
                    LEFT JOIN equipment_type_image ON eti_et_id = et_id AND eti_is_default
                    INNER JOIN equipment_unit ON eu_et_id = et_id
                WHERE eu_is_public AND NOT et_is_deleted AND NOT eu_is_deleted
                GROUP BY et_id;
            ';

            $results = $this->conn->query($sql);

            $equipment = array();
            foreach ($results as $row) {
                $e = self::ExtractEquipmentTypeFromRow($row);
                $e->setCategory(self::ExtractEquipmentTypeCategoryFromRow($row));
                $e->setImages([self::ExtractEquipmentTypeImageFromRow($row)]);
                $e->setUnits($this->unit_dao->getAllUnitsByEquipmentID($e->getEquipmentID()));

                $equipment[] = $e;
            }

            return $equipment;
        } catch (\Exception $e) {
            $this->logger->error('Failed to get browsable equipment: ' . $e->getMessage());
            return false;
        }
    }


    /**
     * Fetches all unarchived equipment for employee perusal along with their default image.
     *
     * @param boolean $includeDeleted Whether to include deleted equipment in the list
     * 
     * @return \Model\EquipmentType[]|boolean an array of equipments on success, false otherwise
     */
    public function getEmployeeEquipment($includeDeleted = false) {
        try {
            $sql = 'SELECT * FROM equipment_type
                    INNER JOIN equipment_type_category ON et_etc_id = etc_id
                    LEFT JOIN equipment_type_image ON eti_et_id = et_id AND eti_is_default
                WHERE (NOT et_is_deleted OR :include_deleted)
                ORDER BY et_name ASC
            ';
            $params = ['include_deleted' => $includeDeleted];
        
            $results = $this->conn->query($sql, $params);

            $equipment = array();
            foreach ($results as $row) {
                $e = self::ExtractEquipmentTypeFromRow($row);
                $e->setCategory(self::ExtractEquipmentTypeCategoryFromRow($row));
                if (isset($row['eti_id'])) {
                    $e->setImages([self::ExtractEquipmentTypeImageFromRow($row)]);
                }
                $e->setUnits($this->unit_dao->getAllUnitsByEquipmentID($e->getEquipmentID()));

                $equipment[] = $e;
            }

            return $equipment;
        } catch (\Exception $e) {
            $this->logger->error('Failed to get employee equipment: ' . $e->getMessage());
            return false;
        }
    }


    /**
     * Fetches the equipment type with the provided ID
     *
     * @param string $id
     * @param boolean $includeDeletedInstances Whether to include deleted instances in the instance list
     * @return \Model\EquipmentType|boolean the equipment on success, false otherwise
     */
    public function getEquipment($id, $includeDeletedInstances = false) {
        try {
            $sql = 'SELECT * FROM equipment_type WHERE et_id = :id;';
            $params = array(':id' => $id);

            $results = $this->conn->query($sql, $params);
            if (\count($results) == 0) {
                return false;
            }

            $equipment = self::ExtractEquipmentTypeFromRow($results[0]);
            $equipment->setImages($this->getEquipmentImages($id));
            $equipment->setUnits($this->unit_dao->getAllUnitsByEquipmentID($id, $includeDeletedInstances));

            return $equipment;
        } catch (\Exception $e) {
            $this->logger->error("Failed to fetch equipment with id '$id': " . $e->getMessage());
            return false;
        }
    }


    /**
     * Fetches the equipment type with the unit with the provided ID
     *
     * @param string $id
     * @return \Model\EquipmentType|boolean the equipment on success, false otherwise
     */
    public function getEquipmentByUnitID($id) {
        try {
            $sql = 'SELECT * FROM equipment_type JOIN equipment_unit ON eu_et_id = et_id WHERE eu_id = :id;';
            $params = array(':id' => $id);

            $results = $this->conn->query($sql, $params);
            if (\count($results) == 0) {
                return false;
            }

            $equipment = self::ExtractEquipmentTypeFromRow($results[0]);
            $equipment->setImages($this->getEquipmentImages($equipment->getEquipmentID()));
            $equipment->setUnits([$this->unit_dao->getUnit($id)]);

            return $equipment;
        } catch (\Exception $e) {
            $this->logger->error("Failed to fetch equipment with id '$id': " . $e->getMessage());
            return false;
        }
    }


    /**
     * Fetches all images for the given equipment
     *
     * @param string $id
     * @return \Model\EquipmentTypeImage[]|boolean the equipment images on success, false otherwise
     */
    public function getEquipmentImages($id) {
        try {
            $sql = 'SELECT * FROM equipment_type_image WHERE eti_et_id = :id ORDER BY eti_is_default DESC';
            $params = array(':id' => $id);
            
            $results = $this->conn->query($sql, $params);

            $images = array();
            foreach ($results as $row){
                $image = self::ExtractEquipmentTypeImageFromRow($row);
                $images[] = $image;
            }

            return $images;
        } catch (\Exception $e) {
            $this->logger->error("Failed to fetch equipment images for type '$id': " . $e->getMessage());
            return false;
        }
    }


    /**
     * Fetches the given equipment image
     *
     * @param string $id
     * @return \Model\EquipmentTypeImage|boolean the equipment image on success, false otherwise
     */
    public function getEquipmentImage($id) {
        try {
            $sql = 'SELECT * FROM equipment_type_image WHERE eti_id = :id';
            $params = array(':id' => $id);
            
            $results = $this->conn->query($sql, $params);

            return self::ExtractEquipmentTypeImageFromRow($results[0]);
        } catch (\Exception $e) {
            $this->logger->error("Failed to fetch equipment image '$id': " . $e->getMessage());
            return false;
        }
    }


    /**
     * Adds a new equipment type to the database.
     * 
     * @param \Model\EquipmentType $equipment The equipment to add
     * 
     * @return boolean Whether the insertion succeeded
     */
    public function addEquipment($equipment) {
        try {
            // NOTE: if implementing categories, don't hardcode `etc_id` to `1`
            $sql = 'INSERT INTO equipment_type(
                et_id, et_name, et_description, et_parts, et_replacement_cost,
                et_usage_instructions, et_notes, et_return_check, et_etc_id,
                et_date_created, et_date_updated
            ) VALUES (
                :id, :name, :description, :parts, :replacement_cost, :usage_instructions,
                :notes, :return_check, 1, :date_created, :date_updated
            );';
            $params = [
                'id' => $equipment->getEquipmentID(),
                'name' => $equipment->getName(),
                'description' => $equipment->getDescription(),
                'parts' => $equipment->getParts(),
                'replacement_cost' => $equipment->getReplacementCost(),
                'usage_instructions' => $equipment->getUsageInstructions(),
                'notes' => $equipment->getNotes(),
                'return_check' => $equipment->getReturnCheck(),
                'date_created' => QueryUtils::formatDate($equipment->getDateCreated()),
                'date_updated' => QueryUtils::formatDate($equipment->getDateUpdated())
            ];

            $this->conn->execute($sql, $params);

            return true;
        } catch (\Exception $e) {
            $this->logger->error("Failed to add equipment '$id': " . $e->getMessage());
            return false;
        }
    }


    /**
     * Adds a new equipment image to the database
     */
    public function addEquipmentImage($equipmentImage) {
        try {
            $sql = 'INSERT INTO equipment_type_image (eti_id, eti_et_id, eti_filename, eti_is_default)
                VALUES (:eti_id, :et_id, :filename, :is_default);';
            $params = [
                'eti_id' => $equipmentImage->getImageID(),
                'et_id' => $equipmentImage->getEquipmentID(),
                'filename' => $equipmentImage->getFilename(),
                'is_default' => $equipmentImage->getIsDefault()
            ];
    
            $this->conn->execute($sql, $params);
    
            return true;
        } catch (\Exception $e) {
            $this->logger->error("Failed to add equipment image: " . $e->getMessage());
            return false;
        }
    }


    /**
     * Updates the given equipment type in the database
     *
     * @param \Model\EquipmentType $equipment The new details for the equipment (will update the matching ID)
     *
     * @return boolean Whether the update succeeded
     */
    public function updateEquipment($equipment) {
        try {
            $sql = 'UPDATE equipment_type
                SET
                    et_name = :name,
                    et_description = :description,
                    et_usage_instructions = :usageInstructions,
                    et_notes = :notes,
                    et_return_check = :returnCheck,
                    et_parts = :parts,
                    et_replacement_cost = :replacementCost,
                    et_date_updated = :dateUpdated
                WHERE `et_id` = :id;
            ';
            $params = [
                ':name' => $equipment->getName(),
                ':description' => $equipment->getDescription(),
                ':usageInstructions' => $equipment->getUsageInstructions(),
                ':notes' => $equipment->getNotes(),
                ':returnCheck' => $equipment->getReturnCheck(),
                ':parts' => $equipment->getParts(),
                ':replacementCost' => $equipment->getReplacementCost(),
                ':dateUpdated' => QueryUtils::FormatDate($equipment->getDateUpdated()),
                ':id' => $equipment->getEquipmentID()
            ];
            
            $this->conn->execute($sql, $params);
    
            return true;
        } catch (\Exception $e) {
            $this->logger->error("Failed to update equipment type '{$equipment->getEquipmentID()}': " . $e->getMessage());
            return false;
        }
    }


    /**
     * Makes the image with the given ID the default image (shown first) for its corresponding equipment type.
     * 
     * @param string $id The image to make default
     * 
     * @return boolean Whether the update succeeded
     */
    public function updateDefaultEquipmentImage($id) {
        try {
            $sql = 'UPDATE equipment_type_image
                SET eti_is_default = (eti_id = :id)
                WHERE eti_et_id = (
                    SELECT eti_et_id FROM equipment_type_image WHERE eti_id = :id
                );
            ';
            $params = [ 'id' => $id ];
    
            $this->conn->execute($sql, $params);

            return true;
        } catch (\Exception $e) {
            $this->logger->error("Failed to set default image '$id': " . $e->getMessage());
            return false;
        }
    }


    /**
     * Soft-deletes the given equipment by setting `is_deleted = true`, making the
     * equipment no longer show up in searches. Soft-delete used instead of hard-delete
     * to safeguard against accidents.
     * 
     * @param string $id The equipment to soft-delete
     * 
     * @return boolean Whether the deletion succeeded
     */
    public function deleteEquipment($id) {
        try {
            $sql = 'UPDATE equipment_type SET et_is_deleted = TRUE WHERE et_id = :id;';
            $params = ['id' => $id];
    
            $this->conn->execute($sql, $params);

            return true;
        } catch (\Exception $e) {
            $this->logger->error("Failed to soft delete equipment '$id': " . $e->getMessage());
            return false;
        }
    }


    /**
     * Undoes soft-delete for the given equipment by setting `is_deleted = false`, making
     * the equipment show up in searches again.
     * 
     * @param string $id The equipment to restore
     * 
     * @return boolean Whether the restoration succeeded
     */
    public function restoreEquipment($id) {
        try {
            $sql = 'UPDATE equipment_type SET et_is_deleted = FALSE WHERE et_id = :id;';
            $params = ['id' => $id];
    
            $this->conn->execute($sql, $params);

            return true;
        } catch (\Exception $e) {
            $this->logger->error("Failed to restore equipment '$id': " . $e->getMessage());
            return false;
        }
    }


    /**
     * Deletes the given equipment image from the database.
     * 
     * @param string $id The image to delete
     * 
     * @return boolean Whether the deletion was successful
     */
    public function deleteEquipmentImage($id) {
        try {
            $sql = 'DELETE FROM equipment_type_image WHERE eti_id = :eti_id;';
            $params = ['eti_id' => $id];
    
            $this->conn->execute($sql, $params);
    
            return true;
        } catch (\Exception $e) {
            $this->logger->error("Failed to delete equipment unit '$id': " . $e->getMessage());
            return false;
        }
    }


    /**
     * Creates a new EquipmentType object using information from the database row
     *
     * @param mixed[] $row the row in the database from which information is to be extracted
     * @return \Model\EquipmentType
     */
    public static function ExtractEquipmentTypeFromRow($row) {
        $equipment = new EquipmentType($row['et_id']);
        $equipment->setName($row['et_name']);
        $equipment->setDescription($row['et_description']);
        $equipment->setUsageInstructions($row['et_usage_instructions']);
        $equipment->setNotes($row['et_notes']);
        $equipment->setReturnCheck($row['et_return_check']);
        $equipment->setParts($row['et_parts']);
        $equipment->setReplacementCost($row['et_replacement_cost']);
        $equipment->setIsDeleted($row['et_is_deleted']);
        $equipment->setDateCreated(new \DateTime(($row['et_date_created'] == '' ? 'now' : $row['et_date_created'])));
        $equipment->setDateUpdated(new \DateTime(($row['et_date_updated'] == '' ? 'now' : $row['et_date_updated'])));
        return $equipment;
    }


    /**
     * Extracts information about an image for an equipment type from a row in a database result set.
     * 
     * The resulting EquipmentTypeImage does NOT have its reference to the equipment it belongs to set.
     *
     * @param mixed[] $row the row in the database result
     * @return \Model\EquipmentTypeImage the image extracted from the information
     */
    public static function ExtractEquipmentTypeImageFromRow($row) {
        $image = new EquipmentTypeImage($row['eti_id']);
        $image->setEquipmentID($row['eti_et_id']);
        $image->setFilename($row['eti_filename']);
        $image->setIsDefault($row['eti_is_default'] ? true : false);
        return $image;
    }

 
    /**
     * Extract EquipmentCategory using information from the database row
     *
     * @param mixed[] $row the database row to extract information from
     * @param string $name the name of the `name` field (for use when EquipmentType is
     *                     also in row; `name` field overlaps)
     * @return \Model\EquipmentCategoryOld
     */
    public static function ExtractEquipmentTypeCategoryFromRow($row) {
        $equipmentTypeCategory = new EquipmentTypeCategory($row['etc_id']);
        $equipmentTypeCategory->setName($row['etc_name']);
        return $equipmentTypeCategory;
    }

}

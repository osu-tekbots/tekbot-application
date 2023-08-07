<?php
// Updated 11/5/2019
namespace DataAccess;

use Model\Equipment;
use Model\EquipmentHealth;
use Model\EquipmentCategory;
use Model\EquipmentImage;


/**
 * Handles all of the logic related to queries on capstone equipment resources in the database.
 */
class EquipmentDao {

    /** @var DatabaseConnection */
    private $conn;

    /** @var \Util\Logger */
    private $logger;

    /**
     * Creates a new instance of the data access object for capstone equipment data.
     *
     * @param DatabaseConnection $connection the connection to use to communiate with the database
     * @param \Util\Logger $logger the logger to use to log details about the interactions with the database
     */
    public function __construct($connection, $logger) {
        $this->conn = $connection;
        $this->logger = $logger;
    }

    /**
     * Fetches several equipments from a specified range.
     *
     * @param integer $offset the offset into the results to fetch
     * @param integer $limit the max number of results to fetch in this batch
     * @return \Model\Equipment[]|boolean an array of equipments on success, false otherwise
     */
    public function getBrowsableEquipment() {
        // $offset = 0, $limit = -1
        try {
            $sql = '
            SELECT * 
            FROM equipment, equipment_health
            WHERE equipment.health_id = equipment_health.health_id 
            AND is_public = 1 AND is_archived = 0
            ';

            // $params = array(
            //     ':public' => true,
            //     ':archived' => false
            // );
            $results = $this->conn->query($sql); //removed $params

            $equipments = array();
            foreach ($results as $row) {
                $equipment = self::ExtractEquipmentFromRow($row);
                $equipments[] = $equipment;
            }

            return $equipments;
        } catch (\Exception $e) {
            $this->logger->error('Failed to get any equipments: ' . $e->getMessage());
            return false;
        }
    }

   
    /**
     * Fetches several equipments from a specified range.
     *
     * @param integer $offset the offset into the results to fetch
     * @param integer $limit the max number of results to fetch in this batch
     * @return \Model\Equipment[]|boolean an array of equipments on success, false otherwise
     */
    public function getAdminEquipment($offset = 0, $limit = -1) {
        try {
            $sql = '
            SELECT * 
            FROM equipment, equipment_health
            WHERE equipment.is_archived = 0
            AND equipment.health_id = equipment_health.health_id
            ';
        
            $results = $this->conn->query($sql);

            $equipments = array();
            foreach ($results as $row) {
                $equipment = self::ExtractEquipmentFromRow($row);
                $this->getEquipmentImages($equipment, true);
                $equipments[] = $equipment;
            }

            return $equipments;
        } catch (\Exception $e) {
            $this->logger->error('Failed to get any equipments: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Fetches the equipment with the provided ID
     *
     * @param string $id
     * @return \Model\Equipment|boolean the equipment on success, false otherwise
     */
    public function getEquipment($id) {
        try {
            // First fetch the equipment
            $sql = '
            SELECT * 
            FROM equipment, equipment_health
            WHERE equipment.health_id = equipment_health.health_id 
            AND equipment.equipment_id = :id
            ';
            $params = array(':id' => $id);
            $results = $this->conn->query($sql, $params);
            if (\count($results) == 0) {
                return false;
            }

            $equipment = self::ExtractEquipmentFromRow($results[0]);

            $sql = '
            SELECT *
            FROM equipment_image 
            WHERE equipment_image.equipment_id = :id
            ORDER BY equipment_image.is_default DESC
            ';
            $results = $this->conn->query($sql, $params);

            $images = array();
            foreach ($results as $row){
                $image = self::ExtractEquipmentImageFromRow($row);
                $images[] = $image;
            }
            $equipment->setEquipmentImages($images);
         
            return $equipment;
        } catch (\Exception $e) {
            $this->logger->error("Failed to fetch equipment with id '$id': " . $e->getMessage());
            return false;
        }
    }


    /**
     * Adds a new capstone equipment entry into the database.
     *
     * @param \Model\Equipment $equipment the equipment to add
     * @return boolean true if successful, false otherwise
     */
    public function addNewEquipment($equipment) {
        try {
            $sql = '
            INSERT INTO equipment VALUES (
                :id,
                :name,
                :description,
                :health,
                :instructions,
                :notes,
                :check,
                :numparts,
                :partlist,
                :location,
                :replacementcost,
                :instances,
                :category,
                :ispublic,
                :isarchived,
                :dupdated,
                :dcreated
            )
            ';
            $params = array(
                ':id' => $equipment->getEquipmentID(),
                ':name' => $equipment->getEquipmentName(),
                ':description' => $equipment->getDescription(),
                ':health' => $equipment->getHealthID()->getId(),
                ':instructions' => $equipment->getUsageInstructions(),
                ':notes' => $equipment->getNotes(),
                ':check' => $equipment->getReturnCheck(),
                ':numparts' => $equipment->getNumberParts(),
                ':partlist' => $equipment->getPartList(),
                ':location' => $equipment->getLocation(),
                ':replacementcost' => $equipment->getReplacementCost(),
                ':instances' => $equipment->getInstances(),
                ':category' => $equipment->getCategoryID()->getId(),
                ':ispublic' => $equipment->getIsPublic(),
                ':isarchived' => $equipment->getIsArchived(),
                ':dupdated' => QueryUtils::FormatDate($equipment->getDateUpdated()),
                ':dcreated' => QueryUtils::FormatDate($equipment->getDateCreated()),
            );
            $this->conn->execute($sql, $params);
            return true;
        } catch (\Exception $e) {
            $this->logger->error('Failed to add new equipment: ' . $e->getMessage());
            return false;
        }
    }


    /**
     * Updates an existing capstone equipment entry into the database.
     *
     * @param \Model\Equipment $equipment the equipment to update
     * @return boolean true if successful, false otherwise
     */
    public function updateEquipment($equipment) {
        try {
            $sql = '
            UPDATE equipment SET
                equipment_name = :name,
                description = :description,
                health_id = :health,
                usage_instructions = :instructions,
                notes = :notes,
                return_check = :check,
                number_parts = :numparts,
                part_list = :partlist,
                location = :location,
                replacement_cost = :replacementcost,
                instances = :instances,
                is_public = :public,
                is_archived = :archived,
                date_updated = :dupdated
            WHERE equipment_id = :id
            ';
            $params = array(
                ':id' => $equipment->getEquipmentID(),
                ':name' => $equipment->getEquipmentName(),
                ':description' => $equipment->getDescription(),
                ':health' => $equipment->getHealthID()->getId(),
                ':instructions' => $equipment->getUsageInstructions(),
                ':notes' => $equipment->getNotes(),
                ':check' => $equipment->getReturnCheck(),
                ':numparts' => $equipment->getNumberParts(),
                ':partlist' => $equipment->getPartList(),
                ':location' => $equipment->getLocation(),
                ':replacementcost' => $equipment->getReplacementCost(),
                ':instances' => $equipment->getInstances(),
                ':public' => $equipment->getIsPublic(),
                ':archived' => $equipment->getIsArchived(),
                ':dupdated' => QueryUtils::FormatDate($equipment->getDateUpdated())
            );
            $this->conn->execute($sql, $params);
            return true;
        } catch (\Exception $e) {
            $id = $equipment->getEquipmentID();
            $this->logger->error("Failed to update equipment with id '$id': " . $e->getMessage());
            return false;
        }
    }

    public function updateEquipmentVisiblity($equipment){
        try {
            $sql = '
            UPDATE equipment SET
                is_public = :public,
                is_archived = :archived,
                date_updated = :dupdated
            WHERE equipment_id = :id
            ';
            $params = array(
                ':id' => $equipment->getEquipmentID(),
                ':public' => $equipment->getIsPublic(),
                ':archived' => $equipment->getIsArchived(),
                ':dupdated' => QueryUtils::FormatDate($equipment->getDateUpdated())
            );
            $this->conn->execute($sql, $params);
            return true;
        } catch (\Exception $e) {
            $id = $equipment->getEquipmentID();
            $this->logger->error("Failed to update equipment visibility with id '$id': " . $e->getMessage());
            return false;
        }
    }

    

    /**
     * Inserts metadata for a new image for a equipment in the databsae.
     *
     * @param \Model\EquipmentImage $image the image metadata to insert into the database
     * @return boolean true on success, false otherwise
     */
    public function addNewEquipmentImage($image) {
        try {
            $sql = '
            INSERT INTO equipment_image 
            (
                equipment_image_id, equipment_id, image_name, is_default
            ) VALUES (
                :id,
                :eid,
                :imagename,
                :default
            )';
            $params = array(
                ':id' => $image->getImageID(),
                ':eid' => $image->getEquipmentID(),
                ':imagename' => $image->getImageName(),
                ':default' => $image->getIsDefault()
            );
            $this->conn->execute($sql, $params);

            return true;
        } catch (\Exception $e) {
            $this->logger->error('Failed to add new image metadata: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Updates metadata for an image for a equipment in the databsae.
     *
     * @param \Model\EquipmentImage $image the image metadata to update into the database
     * @return boolean true on success, false otherwise
     */
    public function updateDefaultEquipmentImage($image) {
        try {
            $sql = '
            UPDATE equipment_image SET is_default = :default WHERE equipment_image_id = :id
           ';
            $params = array(
                ':id' => $image->getImageID(),
                ':default' => $image->getIsDefault()
            );
            $this->conn->execute($sql, $params);

            // Set isdefault to 0 for rest of images
            $equipmentID = $image->getEquipmentID();
            $sql = '
            UPDATE equipment_image SET is_default = 0 WHERE equipment_image.equipment_id = :eid AND equipment_image_id != :id
            ';
            $params = array(
                ':id' => $image->getImageID(),
                ':eid' => $image->getEquipmentID()
            );
            $this->conn->execute($sql, $params);

            return true;
        } catch (\Exception $e) {
            $this->logger->error('Failed to update image metadata: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Removes metadata for a new image for a equipment in the databsae.
     *
     * @param \Model\EquipmentImage $image the image metadata to insert into the database
     * @return boolean true on success, false otherwise
     */
    public function removeEquipmentImage($imageID) {
        try {
            $sql = '
            DELETE FROM equipment_image WHERE equipment_image_id = :id
            ';
            $params = array(
                ':id' => $imageID,
            );
            $this->conn->execute($sql, $params);

            return true;
        } catch (\Exception $e) {
            $this->logger->error('Failed to remove image metadata: ' . $e->getMessage());
            return false;
        }
    }


    /**
     * Fetches image metadata for an image associated with an equipment.
     * 
     * The image metadata will NOT include a reference to the equipment with which it is associated.
     *
     * @param string $id the ID of the image to fetch
     * @return \Model\EquipmentImage the image on success, false otherwise
     */
    public function getEquipmentImage($id) {
        try {
            $sql = 'SELECT * FROM equipment_image WHERE equipment_image_id = :id';
            $params = array(':id' => $id);
            $results = $this->conn->query($sql, $params);
            if (\count($results) == 0) {
                return false;
            }

            return self::ExtractEquipmentImageFromRow($results[0]);
        } catch (\Exception $e) {
            $this->logger->error("Failed to fetch image metadata with id '$id': " . $e->getMessage());
            return false;
        }
    }


    /**
     * Fetches the images related to an equipment item
     *
     * @param \Model\Equipment $equipment the project whose images to fetch
     * @param boolean $setImages determines whether the function will implicity set equipment images to the result of
     * the query.
     * @return \Model\Equipment[]|boolean an array if image metadata objects on success, false otherwise
     */
    public function getEquipmentImages($equipment, $setImages = false) {
        try {
            $sql = 'SELECT * FROM equipment_image WHERE equipment_id = :id';
            $params = array(':id' => $equipment->getEquipmentID());
            $results = $this->conn->query($sql, $params);
            $images = array();
            foreach ($results as $r) {
                $image = self::ExtractEquipmentImageFromRow($r);
                $image->setEquipment($equipment);
                $images[] = $image;
            }

            if ($setImages) {
             //   $equipment->setImageName($images);
            }

            return $images;
        } catch (\Exception $e) {
            $eid = $equipment->getEquipmentID();
            $this->logger->error("Failed to get image metadata for project with ID '$eid':" . $e->getMessage());
            return false;
        }
    }


    /**
     * Fetches image metadata for an image associated with a project.
     * 
     * The image metadata will NOT include a reference to the project with which it is associated.
     *
     * @param string $id the ID of the image to fetch
     * @return \Model\EquipmentImage the image on success, false otherwise
     */
    public function getDefaultEquipmentImage($id) {
        try {
            $sql = 'SELECT * FROM equipment_image WHERE equipment_id = :id AND is_default = :default';
            $params = array(
                ':id' => $id,
                ':default' => true
            );
            $results = $this->conn->query($sql, $params);
            if (\count($results) == 0) {
                return false;
            }

            return self::ExtractEquipmentImageFromRow($results[0]);
        } catch (\Exception $e) {
            $this->logger->error("Failed to fetch image metadata with id '$id': " . $e->getMessage());
            return false;
        }
    }

    /**
     * Fetches a list of categories for equipment categories
     *
     * @return \Model\EquipmentCategory[]|boolean an array of categories on success, false otherwise
     */
    public function getEquipmentCategory() {
        try {
            $sql = 'SELECT * FROM equipment_category';
            $results = $this->conn->query($sql);

            $categories = array();
            foreach ($results as $row) {
                $categories[] = self::ExtractEquipmentCategoryFromRow($row);
            }

            return $categories;
        } catch (\Exception $e) {
            $this->logger->error('Failed to get equipment category: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Fetches a list of categories for equipment health
     *
     * @return \Model\EquipmentHealth[]|boolean an array of categories on success, false otherwise
     */
    public function getEquipmentHealth() {
        try {
            $sql = 'SELECT * FROM equipment_health';
            $results = $this->conn->query($sql);

            $categories = array();
            foreach ($results as $row) {
                $categories[] = self::ExtractEquipmentHealthFromRow($row);
            }

            return $categories;
        } catch (\Exception $e) {
            $this->logger->error('Failed to get equipment health: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Creates a new Equipment object using information from the database row
     *
     * @param mixed[] $row the row in the database from which information is to be extracted
     * @return \Model\Equipment
     */
    public static function ExtractEquipmentFromRow($row) {
        $equipment = new Equipment($row['equipment_id']);
        $equipment->setEquipmentName($row['equipment_name']);
        $equipment->setDescription($row['description']);
        $equipment->setHealthID(self::ExtractEquipmentHealthFromRow($row, true));
        $equipment->setNotes($row['notes']);
        $equipment->setUsageInstructions($row['usage_instructions']);
        $equipment->setReturnCheck($row['return_check']);
        $equipment->setNumberParts($row['number_parts']);
        $equipment->setPartList($row['part_list']);
        $equipment->setLocation($row['location']);
        $equipment->setReplacementCost($row['replacement_cost']);
        $equipment->setInstances($row['instances']);
        //$equipment->setCategoryID(self::ExtractEquipmentCategoryFromRow($row, true));
        $equipment->setIsPublic($row['is_public']);
        $equipment->setIsArchived($row['is_archived']);
        $equipment->setDateCreated(new \DateTime(($row['date_created'] == '' ? 'now' : $row['date_created'])));
        $equipment->setDateUpdated(new \DateTime(($row['date_updated'] == '' ? 'now' : $row['date_updated'])));
        return $equipment;
    }

    /**
     * Extracts information about an image for a equipment from a row in a database result set.
     * 
     * The resulting EquipmentImage does NOT have its reference to the equipment it belongs to set.
     *
     * @param mixed[] $row the row in the database result
     * @return \Model\EquipmentImage the image extracted from the information
     */
    public static function ExtractEquipmentImageFromRow($row) {
        $image = new EquipmentImage($row['equipment_image_id']);
        $image->setEquipmentID($row['equipment_id']);
        $image->setImageName($row['image_name']);
        $image->setIsDefault($row['is_default'] ? true : false);
        return $image;
    }
 
    /**
     * Extract Equipment Category using information from the database row
     *
     * @param mixed[] $row the database row to extract information from
     * @param boolean $equipmentInRow indicates whether the project is also included in the row
     * @return \Model\EquipmentCategory
     */
    public static function ExtractEquipmentCategoryFromRow($row, $equipmentInRow = false) {
        $id = $equipmentInRow ? 'category_name' : 'category_id';
        $name = isset($row['category_name']) ? $row['category_name'] : null;
        return new EquipmentCategory($row[$id], $name);
    }

    /**
     * Extract Equipment Health using information from the database row
     *
     * @param mixed[] $row the database row to extract information from
     * @param boolean $equipmentInRow indicates whether the project is also included in the row
     * @return \Model\EquipmentHealth
     */
    public static function ExtractEquipmentHealthFromRow($row, $equipmentInRow = false) {
        $id = $equipmentInRow ? 'health_name' : 'health_id';
        $name = isset($row['health_name']) ? $row['health_name'] : null;
        return new EquipmentHealth($row[$id], $name);
    }


}

?>
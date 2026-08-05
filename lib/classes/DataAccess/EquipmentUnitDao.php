<?php
namespace DataAccess;

use Model\EquipmentUnit;


/**
 * Handles all of the logic related to queries on equipment units in the database.
 * 
 * NOTE: This DAO uses a view, `EquipmentUnitStatusView`, for `SELECT` queries instead of
 * a direct SQL table like most DAOs. This is because calculating `checkout_status` and
 * `health_status` is fairly involved -- repeating the code for it in every query would
 * be difficult to read and maintain consistently.
 */
class EquipmentUnitDao {

    /** @var DatabaseConnection */
    private $conn;

    /** @var \Util\Logger */
    private $logger;


    /**
     * Creates a new instance of the data access object for equipment data.
     *
     * @param DatabaseConnection $connection the connection to use to communiate with the database
     * @param \Util\Logger $logger the logger to use to log details about the interactions with the database
     */
    public function __construct($connection, $logger) {
        $this->conn = $connection;
        $this->logger = $logger;
    }


    /**
     * Fetches all individual units of the given equipment. For example, all individual
     * multimeters with their health status, location, etc.
     * 
     * Includes private units.
     * 
     * @param string $id The ID of the equipment to fetch units for
     * @param boolean $includeDeletedUnits Whether to include deleted units (NOT the same
     *                                     as private units) in the list
     *
     * @return \Model\EquipmentUnit[]|boolean an array of units on success, false otherwise
     */
    public function getAllUnitsByEquipmentID($id, $includeDeletedUnits = false) {
        try {
            $sql = '
            SELECT * FROM EquipmentUnitStatusView
            WHERE et_id = :et_id AND (NOT eu_is_deleted OR :include_deleted);
            ';
            $params = array(
                ':et_id' => $id,
                ':include_deleted' => $includeDeletedUnits
            );

            $results = $this->conn->query($sql, $params);

            $units = array();
            foreach ($results as $row) {
                $units[] = self::ExtractEquipmentUnitFromRow($row);
            }

            return $units;
        } catch (\Exception $e) {
            $this->logger->error("Failed to get equipment units for type '$id': " . $e->getMessage());
            return false;
        }
    }


    /**
     * Fetches the given unit, whether or not it is public.
     *
     * @param int $id The ID for the unit to fetch
     * 
     * @return \Model\EquipmentUnit[]|boolean an array of units on success, false otherwise
     */
    public function getUnit($id) {
        try {
            $sql = '
            SELECT * FROM EquipmentUnitStatusView
            WHERE eu_id = :eu_id;
            ';
            $params = array(
                ':eu_id' => $id
            );

            $results = $this->conn->query($sql, $params);

            return self::ExtractEquipmentUnitFromRow($results[0]);
        } catch (\Exception $e) {
            $this->logger->error("Failed to get equipment unit '$id': " . $e->getMessage());
            return false;
        }
    }


    /**
     * Adds the given equipment unit to the database.
     * 
     * @param \Model\EquipmentUnit $unit The unit to add to the database
     * 
     * @return int|boolean The unit's ID if successfully added, false otherwise
     */
    public function addUnit($unit) {
        try {
            $sql = 'INSERT INTO equipment_unit (
                eu_et_id, eu_notes, eu_location, eu_is_public, eu_date_created, eu_date_updated
            ) VALUES (
                :type_id, :notes, :location, :is_public, :date_created, :date_updated
            );';
            $params = [
                'type_id' => $unit->getEquipmentID(),
                'notes' => $unit->getNotes(),
                'location' => $unit->getLocation(),
                'is_public' => $unit->getIsPublic(),
                'date_created' => QueryUtils::formatDate($unit->getDateCreated()),
                'date_updated' => QueryUtils::formatDate($unit->getDateUpdated())
            ];

            $unitID = $this->conn->execute($sql, $params, true);

            // NOTE: health status (eho_id) `1` means "Fully Functional"
            $sql = 'INSERT INTO equipment_health_log (ehl_eu_id, ehl_eho_id)
                VALUES (:unit_id, 1);';
            $params = ['unit_id' => $unitID];

            $this->conn->execute($sql, $params);

            return $unitID;
        } catch (\Exception $e) {
            $this->logger->error("Failed to create equipment unit: " . $e->getMessage());
            return false;
        }
    }


    /**
     * Updates the equipment unit with the given ID.
     * 
     * @param \Model\EquipmentUnit $unit The new contents for the equipment unit
     * 
     * @return boolean Whether the unit was successfully updated
     */
    public function updateUnit($unit) {
        try {
            $sql = 'UPDATE equipment_unit
                SET eu_notes = :notes,
                    eu_location = :location,
                    eu_is_public = :is_public,
                    eu_date_updated = :date_updated
                WHERE eu_id = :id;
            ';
            $params = [
                'notes' => $unit->getNotes(),
                'location' => $unit->getLocation(),
                'is_public' => $unit->getIsPublic(),
                'date_updated' => QueryUtils::FormatDate($unit->getDateUpdated()),
                'id' => $unit->getUnitID()
            ];

            $this->conn->execute($sql, $params);

            return true;
        } catch (\Exception $e) {
            $this->logger->error("Failed to update equipment unit '$id': " . $e->getMessage());
            return false;
        }
    }


    /**
     * Soft-deletes the given equipment unit by setting `is_deleted = true`, making the
     * unit no longer show up on the equipment pages. Soft-delete used instead of
     * hard-delete to safeguard against accidents.
     * 
     * @param string $id The unit to soft-delete
     * 
     * @return boolean Whether the deletion succeeded
     */
    public function deleteUnit($id) {
        try {
            $sql = 'UPDATE equipment_unit SET eu_is_deleted = TRUE WHERE eu_id = :id;';
            $params = ['id' => $id];

            $this->conn->execute($sql, $params);

            return true;
        } catch (\Exception $e) {
            $this->logger->error("Failed to soft delete equipment unit '$id': " . $e->getMessage());
            return false;
        }
    }


    /**
     * Undoes soft-deletion for the given equipment unit by setting `is_deleted = false`,
     * making the unit longer show up on the equipment pages again.
     * 
     * @param string $id The unit to restore
     * 
     * @return boolean Whether the restoration succeeded
     */
    public function restoreUnit($id) {
        try {
            $sql = 'UPDATE equipment_unit SET eu_is_deleted = FALSE WHERE eu_id = :id;';
            $params = ['id' => $id];

            $this->conn->execute($sql, $params);

            return true;
        } catch (\Exception $e) {
            $this->logger->error("Failed to restore equipment unit '$id': " . $e->getMessage());
            return false;
        }
    }


    /**
     * Creates a new EquipmentUnit object using information from the database row
     *
     * @param mixed[] $row the row in the database from which information is to be extracted
     * @return \Model\EquipmentUnit
     */
    public static function ExtractEquipmentUnitFromRow($row) {
        $equipment = new EquipmentUnit($row['eu_id']);
        $equipment->setEquipmentID($row['et_id']);
        $equipment->setNotes($row['eu_notes']);
        $equipment->setLocation($row['eu_location']);
        $equipment->setIsPublic($row['eu_is_public']);
        $equipment->setCheckoutStatus($row['checkout_status']);
        $equipment->setHealthStatus($row['health_status']);
        $equipment->setIsDeleted($row['eu_is_deleted']);
        $equipment->setDateCreated(new \DateTime(($row['eu_date_created'] == '' ? 'now' : $row['eu_date_created'])));
        $equipment->setDateUpdated(new \DateTime(($row['eu_date_updated'] == '' ? 'now' : $row['eu_date_updated'])));
        return $equipment;
    }

}

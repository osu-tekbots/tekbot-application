<?php
namespace DataAccess;

use Model\EquipmentItem;


/**
 * Handles all of the logic related to queries on equipment items in the database.
 * 
 * NOTE: This DAO uses a view, `EquipmentItemStatusView`, for `SELECT` queries instead of
 * a direct SQL table like most DAOs. This is because calculating `checkout_status` and
 * `health_status` is fairly involved -- repeating the code for it in every query would
 * be difficult to read and maintain consistently.
 */
class EquipmentItemDao {

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
     * Fetches all individual items of the given equipment. For example, all individual
     * multimeters with their health status, location, etc.
     * 
     * Includes private items.
     * 
     * @param string $id The ID of the equipment to fetch items for
     * @param boolean $includeDeletedItems Whether to include deleted items (NOT the same
     *                                     as private items) in the list
     *
     * @return \Model\EquipmentItem[]|boolean an array of items on success, false otherwise
     */
    public function getAllItemsByEquipmentID($id, $includeDeletedItems = false) {
        try {
            $sql = '
            SELECT * FROM EquipmentItemStatusView
            WHERE et_id = :et_id AND (NOT is_deleted OR :include_deleted);
            ';
            $params = array(
                ':et_id' => $id,
                ':include_deleted' => $includeDeletedItems
            );

            $results = $this->conn->query($sql, $params);

            $items = array();
            foreach ($results as $row) {
                $items[] = self::ExtractEquipmentItemFromRow($row);
            }

            return $items;
        } catch (\Exception $e) {
            $this->logger->error("Failed to get equipment items for type '$id': " . $e->getMessage());
            return false;
        }
    }


    /**
     * Fetches the given item, whether or not it is public.
     *
     * @param int $id The ID for the item to fetch
     * 
     * @return \Model\EquipmentItem[]|boolean an array of items on success, false otherwise
     */
    public function getItem($id) {
        try {
            $sql = '
            SELECT * FROM EquipmentItemStatusView
            WHERE ei_id = :ei_id;
            ';
            $params = array(
                ':ei_id' => $id
            );

            $results = $this->conn->query($sql, $params);

            return self::ExtractEquipmentItemFromRow($results[0]);
        } catch (\Exception $e) {
            $this->logger->error("Failed to get equipment item '$id': " . $e->getMessage());
            return false;
        }
    }


    /**
     * Adds the given equipment item to the database.
     * 
     * @param \Model\EquipmentItem $item The item to add to the database
     * 
     * @return int|boolean The item's ID if successfully added, false otherwise
     */
    public function addItem($item) {
        try {
            $sql = 'INSERT INTO equipment_item (
                et_id, notes, `location`, is_public, date_created, date_updated
            ) VALUES (
                :type_id, :notes, :location, :is_public, :date_created, :date_updated
            );';
            $params = [
                'type_id' => $item->getEquipmentID(),
                'notes' => $item->getNotes(),
                'location' => $item->getLocation(),
                'is_public' => $item->getIsPublic(),
                'date_created' => QueryUtils::formatDate($item->getDateCreated()),
                'date_updated' => QueryUtils::formatDate($item->getDateUpdated())
            ];

            $itemID = $this->conn->execute($sql, $params, true);

            // NOTE: health status (eho_id) `1` means "Fully Functional"
            $sql = 'INSERT INTO equipment_health_log (ei_id, eho_id)
                VALUES (:item_id, 1);';
            $params = ['item_id' => $itemID];

            $this->conn->execute($sql, $params);

            return $itemID;
        } catch (\Exception $e) {
            $this->logger->error("Failed to create equipment item: " . $e->getMessage());
            return false;
        }
    }


    /**
     * Updates the equipment item with the given ID.
     * 
     * @param \Model\EquipmentItem $item The new contents for the equipment item
     * 
     * @return boolean Whether the item was successfully updated
     */
    public function updateItem($item) {
        try {
            $sql = 'UPDATE equipment_item
                SET notes = :notes,
                    `location` = :location,
                    is_public = :is_public,
                    date_updated = :date_updated
                WHERE ei_id = :id;
            ';
            $params = [
                'notes' => $item->getNotes(),
                'location' => $item->getLocation(),
                'is_public' => $item->getIsPublic(),
                'date_updated' => QueryUtils::FormatDate($item->getDateUpdated()),
                'id' => $item->getItemID()
            ];

            $this->conn->execute($sql, $params);

            return true;
        } catch (\Exception $e) {
            $this->logger->error("Failed to update equipment item '$id': " . $e->getMessage());
            return false;
        }
    }


    /**
     * Soft-deletes the given equipment item by setting `is_deleted = true`, making the
     * item no longer show up on the equipment pages. Soft-delete used instead of
     * hard-delete to safeguard against accidents.
     * 
     * @param string $id The item to soft-delete
     * 
     * @return boolean Whether the deletion succeeded
     */
    public function deleteItem($id) {
        try {
            $sql = 'UPDATE equipment_item SET is_deleted = TRUE WHERE ei_id = :id;';
            $params = ['id' => $id];

            $this->conn->execute($sql, $params);

            return true;
        } catch (\Exception $e) {
            $this->logger->error("Failed to soft delete equipment item '$id': " . $e->getMessage());
            return false;
        }
    }


    /**
     * Undoes soft-deletion for the given equipment item by setting `is_deleted = false`,
     * making the item longer show up on the equipment pages again.
     * 
     * @param string $id The item to restore
     * 
     * @return boolean Whether the restoration succeeded
     */
    public function restoreItem($id) {
        try {
            $sql = 'UPDATE equipment_item SET is_deleted = FALSE WHERE ei_id = :id;';
            $params = ['id' => $id];

            $this->conn->execute($sql, $params);

            return true;
        } catch (\Exception $e) {
            $this->logger->error("Failed to restore equipment item '$id': " . $e->getMessage());
            return false;
        }
    }


    /**
     * Creates a new EquipmentItem object using information from the database row
     *
     * @param mixed[] $row the row in the database from which information is to be extracted
     * @return \Model\EquipmentItem
     */
    public static function ExtractEquipmentItemFromRow($row) {
        $equipment = new EquipmentItem($row['ei_id']);
        $equipment->setEquipmentID($row['et_id']);
        $equipment->setNotes($row['notes']);
        $equipment->setLocation($row['location']);
        $equipment->setIsPublic($row['is_public']);
        $equipment->setCheckoutStatus($row['checkout_status']);
        $equipment->setHealthStatus($row['health_status']);
        $equipment->setIsDeleted($row['is_deleted']);
        $equipment->setDateCreated(new \DateTime(($row['date_created'] == '' ? 'now' : $row['date_created'])));
        $equipment->setDateUpdated(new \DateTime(($row['date_updated'] == '' ? 'now' : $row['date_updated'])));
        return $equipment;
    }

}

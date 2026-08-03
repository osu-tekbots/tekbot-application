<?php
namespace DataAccess;

use Model\EquipmentHealthLog;
use Model\EquipmentHealthOption;


/**
 * Handles all of the logic related to queries on equipment health statuses in the
 * database.
 */
class EquipmentHealthDao {

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
     * Fetches all possible health statuses an equipment item could have.
     *
     * @return \Model\EquipmentHealthOption[]|boolean an array of items on success, false otherwise
     */
    public function getAllHealthOptions() {
        try {
            $sql = 'SELECT * FROM equipment_health_option;';

            $results = $this->conn->query($sql);

            $items = array();
            foreach ($results as $row) {
                $items[] = self::ExtractEquipmentHealthOptionFromRow($row);
            }

            return $items;
        } catch (\Exception $e) {
            $this->logger->error("Failed to get equipment health options: " . $e->getMessage());
            return false;
        }
    }


    /**
     * Fetches all health log entries for an equipment item.
     * 
     * @param int $id The item to fetch health log entries for
     * 
     * @return \Model\EquipmentHealthLog[]|false The health log entries, or false if an error occured
     */
    public function getHealthLogsForItem($id) {
        try {
            $sql = 'SELECT * FROM equipment_health_log ehl
                    JOIN equipment_health_option eho ON eho.eho_id = ehl.eho_id
                WHERE ei_id = :ei_id;
            ';
            $params = ['ei_id' => $id];

            $results = $this->conn->query($sql, $params);

            $items = array();
            foreach ($results as $row) {
                $items[] = self::ExtractEquipmentHealthLogFromRow($row);
            }

            return $items;
        } catch (\Exception $e) {
            $this->logger->error("Failed to get equipment health logs for item $id: " . $e->getMessage());
            return false;
        }
    }


    /**
     * Adds a new health log entry for an equipment item.
     * 
     * @param \Model\EquipmentHealthLog $healthLog The health log to add
     * 
     * @return boolean Whether the insertion succeeded
     */
    public function addHealthLog($healthLog) {
        try {
            $sql = 'INSERT INTO equipment_health_log (`ec_id`, `ei_id`, `eho_id`, `notes`, `date_created`, `date_updated`)
                VALUES (:checkout_id, :item_id, :option_id, :notes, :date_created, :date_updated);';
            $params = [
                'checkout_id' => $healthLog->getCheckoutID(),
                'item_id' => $healthLog->getItemID(),
                'option_id' => $healthLog->getHealthOption()->getOptionID(),
                'notes' => $healthLog->getNotes(),
                'date_created' => QueryUtils::formatDate($healthLog->getDateCreated()),
                'date_updated' => QueryUtils::formatDate($healthLog->getDateUpdated())
            ];

            $this->conn->execute($sql, $params);

            return true;
        } catch (\Exception $e) {
            $this->logger->error("Failed to get add equipment health log: " . $e->getMessage());
            return false;
        }
    }


    /**
     * Creates a new EquipmentHealthOption object using information from the database row
     *
     * @param mixed[] $row the row in the database from which information is to be extracted
     * @return \Model\EquipmentHealthOption
     */
    public static function ExtractEquipmentHealthOptionFromRow($row) {
        $healthOption = new EquipmentHealthOption($row['eho_id']);
        $healthOption->setName($row['name']);
        return $healthOption;
    }


    /**
     * Creates a new EquipmentHealthLog object using information from the database row
     *
     * @param mixed[] $row the row in the database from which information is to be extracted
     * @return \Model\EquipmentHealthLog
     */
    public static function ExtractEquipmentHealthLogFromRow($row) {
        $healthLog = new EquipmentHealthLog($row['ehl_id']);
        $healthLog->setCheckoutID($row['ec_id']);
        $healthLog->setItemID($row['ei_id']);
        $healthLog->setHealthOption(self::ExtractEquipmentHealthOptionFromRow($row));
        $healthLog->setNotes($row['notes']);
        $healthLog->setDateCreated(new \DateTime(($row['date_created'] == '' ? 'now' : $row['date_created'])));
        $healthLog->setDateUpdated(new \DateTime(($row['date_updated'] == '' ? 'now' : $row['date_updated'])));
        return $healthLog;
    }

}

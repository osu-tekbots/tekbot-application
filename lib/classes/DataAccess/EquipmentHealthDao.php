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
     * Fetches all possible health statuses an equipment unit could have.
     *
     * @return \Model\EquipmentHealthOption[]|boolean an array of units on success, false otherwise
     */
    public function getAllHealthOptions() {
        try {
            $sql = 'SELECT * FROM equipment_health_option;';

            $results = $this->conn->query($sql);

            $units = array();
            foreach ($results as $row) {
                $units[] = self::ExtractEquipmentHealthOptionFromRow($row);
            }

            return $units;
        } catch (\Exception $e) {
            $this->logger->error("Failed to get equipment health options: " . $e->getMessage());
            return false;
        }
    }


    /**
     * Fetches all health log entries for an equipment unit.
     * 
     * @param int $id The unit to fetch health log entries for
     * 
     * @return \Model\EquipmentHealthLog[]|false The health log entries, or false if an error occured
     */
    public function getHealthLogsForUnit($id) {
        try {
            $sql = 'SELECT * FROM equipment_health_log
                    JOIN equipment_health_option ON eho_id = ehl_eho_id
                WHERE ehl_eu_id = :eu_id;
            ';
            $params = ['eu_id' => $id];

            $results = $this->conn->query($sql, $params);

            $units = array();
            foreach ($results as $row) {
                $units[] = self::ExtractEquipmentHealthLogFromRow($row);
            }

            return $units;
        } catch (\Exception $e) {
            $this->logger->error("Failed to get equipment health logs for unit $id: " . $e->getMessage());
            return false;
        }
    }


    /**
     * Adds a new health log entry for an equipment unit.
     * 
     * @param \Model\EquipmentHealthLog $healthLog The health log to add
     * 
     * @return boolean Whether the insertion succeeded
     */
    public function addHealthLog($healthLog) {
        try {
            $sql = 'INSERT INTO equipment_health_log (
                `ehl_ec_id`, `ehl_eu_id`, `ehl_eho_id`, `ehl_notes`, `ehl_date_created`, `ehl_date_updated`
            ) VALUES (
                :checkout_id, :unit_id, :option_id, :notes, :date_created, :date_updated
            );';
            $params = [
                'checkout_id' => $healthLog->getCheckoutID(),
                'unit_id' => $healthLog->getUnitID(),
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
        $healthOption->setName($row['eho_name']);
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
        $healthLog->setCheckoutID($row['ehl_ec_id']);
        $healthLog->setUnitID($row['ehl_eu_id']);
        $healthLog->setHealthOption(self::ExtractEquipmentHealthOptionFromRow($row));
        $healthLog->setNotes($row['ehl_notes']);
        $healthLog->setDateCreated(new \DateTime(($row['ehl_date_created'] == '' ? 'now' : $row['ehl_date_created'])));
        $healthLog->setDateUpdated(new \DateTime(($row['ehl_date_updated'] == '' ? 'now' : $row['ehl_date_updated'])));
        return $healthLog;
    }

}

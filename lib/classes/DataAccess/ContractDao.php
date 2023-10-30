<?php
// Updated 11/5/2019
namespace DataAccess;

use Model\Contract;
use Model\ContractType;


/**
 * Handles all of the logic related to queries on capstone equipment resources in the database.
 */
class ContractDao {

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
     * Fetches the equipment reservation with the provided ID
     *
     * @param string $id
     * @return \Model\Contract|boolean the equipment on success, false otherwise
     */
    public function getContract($cid) {
        try {
            $sql = ' SELECT * FROM contract WHERE contract_id = :cid ';
            $params = array(':cid' => $cid);
            $results = $this->conn->query($sql, $params);
            if (\count($results) == 0) {
                return false;
            }

            $contract = self::ExtractContractFromRow($results[0]);

            return $contract;
        } catch (\Exception $e) {
            $this->logger->error("Failed to fetch contract with id '$cid': " . $e->getMessage());
            return false;
        }
    }


    /**
     * Grabs the contracts for equipment contracts in the database.
     *
     * @param \Model\Contract $reservation the reservation to add
     * @return boolean true if successful, false otherwise
     */
    public function getEquipmentCheckoutContracts(){
        try {
            $sql = '
            SELECT * 
            FROM contract 
            WHERE contract_type_id = :type
            ';
            $params = array(':type' => ContractType::EQUIPMENT_CHECKOUT);
            $results = $this->conn->query($sql, $params);

            $contracts = array();
            foreach ($results as $row) {
                $contract = self::ExtractContractFromRow($row);
                $contracts[] = $contract;
            }
           
            return $contracts;
        } catch (\Exception $e) {
            $this->logger->error("Failed to get contracts: " . $e->getMessage());
            return false;
        }
    }


    public static function ExtractContractFromRow($row) {
        $contract = new Contract($row['contract_id']);
        $contract->setContractID($row['contract_id']);
        $contract->setDuration($row['duration']);
        $contract->setContractTypeID($row['contract_type_id']);
        $contract->setTitle($row['title']);
        $contract->setDescription($row['description']);

        return $contract;

    }

    /**
     * Creates a new contract type enum object by extracting the necessary information from a row in a database.
     * 
     *
     * @param mixed[] $row the row from the database
     * @param boolean $userInRow flag indicating whether entries from the user table are in the row or not
     * @return \Model\ContractType the user type extracted from the row
     */
    public static function ExtractContractTypeFromRow($row) {
        $id = 'id';
        $name = isset($row['type_name']) ? $row['type_name'] : null;
        return new ContractType($row[$id], $name);
    }

}

?>
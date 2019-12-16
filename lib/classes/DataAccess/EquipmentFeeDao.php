<?php
namespace DataAccess;

use Model\EquipmentFee;

/**
 * Handles all of the logic related to queries on capstone project resources in the database.
 */
class EquipmentFeeDao {

    /** @var DatabaseConnection */
    private $conn;

    /** @var \Util\Logger */
    private $logger;

    /**
     * Creates a new instance of the data access object for capstone project data.
     *
     * @param DatabaseConnection $connection the connection to use to communiate with the database
     * @param \Util\Logger $logger the logger to use to log details about the interactions with the database
     */
    public function __construct($connection, $logger) {
        $this->conn = $connection;
        $this->logger = $logger;
    }

    /**
     * Fetches fees associated with a user.
     *
     * @param string $userID the ID of the user whose projects to fetch
     * @return \Model\FeesOwed[]|boolean an array of projects on success, false otherwise
     */
    public function getFeesForUser($userID) {
        try {
            $sql = '
            SELECT * 
            FROM equipment_fee
            WHERE user_id = :uid
            ';
            $params = array(':uid' => $userID);
            $results = $this->conn->query($sql, $params);

            $fees = array();
            foreach ($results as $row) {
                $fee = self::ExtractEquipmentFeeFromRow($row, true);
                $fees[] = $fee;
            }
           
            return $fees;
        } catch (\Exception $e) {
            $this->logger->error("Failed to get fees for user '$userID': " . $e->getMessage());
            return false;
        }
    }

        /**
     * Fetches fees associated with an equipment checkout.
     *
     * @param string $checkoutID the ID of the checkout whose fee to fetch
     * @return \Model\FeesOwed[]|boolean an array of projects on success, false otherwise
     */
    public function getEquipmentFeeWithCheckoutID($checkoutID) {
        try {
            $sql = '
            SELECT * 
            FROM equipment_fee
            WHERE checkout_id = :cid
            ';
            $params = array(':cid' => $checkoutID);
            $results = $this->conn->query($sql, $params);
            if (\count($results) == 0) {
                return false;
            }

            $fee = self::ExtractEquipmentFeeFromRow($results[0]);
           
            return $fee;
        } catch (\Exception $e) {
            $this->logger->error("Failed to get fees for checkout '$checkoutID': " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Fetches the equipment fee with the provided ID
     *
     * @param string $id
     * @return \Model\EquipmentFee|boolean the equipment on success, false otherwise
     */
    public function getEquipmentFee($id) {
        try {
            $sql = '
            SELECT * 
            FROM equipment_fee
            WHERE id = :id
            ';
            $params = array(':id' => $id);
            $results = $this->conn->query($sql, $params);
            if (\count($results) == 0) {
                return false;
            }

            $fee = self::ExtractEquipmentFeeFromRow($results[0]);

            return $fee;
        } catch (\Exception $e) {
            $this->logger->error("Failed to fetch equipment fee with id '$id': " . $e->getMessage());
            return false;
        }
    }

        /**
     * Fetches fees associated with a user.
     *
     * @param string $userID the ID of the user whose projects to fetch
     * @return \Model\FeesOwed[]|boolean an array of projects on success, false otherwise
     */
    public function getAdminFees() {
        try {
            $sql = '
            SELECT * 
            FROM equipment_fee
            ';
            $results = $this->conn->query($sql);

            $fees = array();
            foreach ($results as $row) {
                $fee = self::ExtractEquipmentFeeFromRow($row, true);
                $fees[] = $fee;
            }
           
            return $fees;
        } catch (\Exception $e) {
            $this->logger->error("Failed to get fees for admin: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Adds a equipment fee entry into the database.
     *
     * @param \Model\FeesOwed $fee the equipment to add
     * @return boolean true if successful, false otherwise
     */
    public function addNewFee($fee) {
        try {
            $sql = '
            INSERT INTO equipment_fee VALUES (
                :id,
                :checkoutid,
                :userid,
                :notes,
                :paymentinfo,
                :amount,
                :ispaid,
                :ispending,
                :dupdated,
                :dcreated
            )
            ';
            $params = array(
                ':id' => $fee->getFeeID(),
                ':checkoutid' => $fee->getCheckoutID(),
                ':userid' => $fee->getUserID(),
                ':notes' => $fee->getNotes(),
                ':paymentinfo' => $fee->getPaymentInfo(),
                ':amount' => $fee->getAmount(),
                ':ispaid' => $fee->getIsPaid(),
                ':ispending' => $fee->getIsPending(),
                ':dupdated' => QueryUtils::FormatDate($fee->getDateUpdated()),
                ':dcreated' => QueryUtils::FormatDate($fee->getDateCreated())
            );
            $this->conn->execute($sql, $params);
            return true;
        } catch (\Exception $e) {
            $this->logger->error('Failed to add new fee: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Updates a fee entry into the database.
     *
     * @param \Model\FeesOwed $fee the equipment to add
     * @return boolean true if successful, false otherwise
     */
    public function updateFee($fee) {
        try {
            $sql = '
            UPDATE equipment_fee SET 
                checkout_id = :checkoutid,
                user_id = :userid,
                notes = :notes,
                payment_info = :paymentinfo,
                amount = :amount,
                is_paid = :ispaid,
                is_pending = :ispending,
                date_updated = :dupdated,
                date_created = :dcreated
            WHERE id = :id
            ';
            $params = array(
                ':id' => $fee->getFeeID(),
                ':checkoutid' => $fee->getCheckoutID(),
                ':userid' => $fee->getUserID(),
                ':notes' => $fee->getNotes(),
                ':paymentinfo' => $fee->getPaymentInfo(),
                ':amount' => $fee->getAmount(),
                ':ispaid' => $fee->getIsPaid(),
                ':ispending' => $fee->getIsPending(),
                ':dupdated' => QueryUtils::FormatDate($fee->getDateUpdated()),
                ':dcreated' => QueryUtils::FormatDate($fee->getDateCreated())
            );
            $this->conn->execute($sql, $params);
            return true;
        } catch (\Exception $e) {
            $id = $fee->getFeeID();
            $this->logger->error("Failed to update fee with id '$id': " . $e->getMessage());
            return false;
        }
    }

    /**
     * Creates a new CapstoneProject object using information from the database row
     *
     * @param mixed[] $row the row in the database from which information is to be extracted
     * @return \Model\CapstoneProject
     */
    public static function ExtractEquipmentFeeFromRow($row, $userInRow = false) {
        $fee = new EquipmentFee($row['id']);
        $fee->setCheckoutID($row['checkout_id']);
        $fee->setUserID($row['user_id']);
        $fee->setNotes($row['notes']);
        $fee->setPaymentInfo($row['payment_info']);
        $fee->setAmount($row['amount']);
        $fee->setIsPaid($row['is_paid']);
        $fee->setIsPending($row['is_pending']);
        $fee->setDateUpdated(new \DateTime($row['date_updated']));
        $fee->setDateCreated(new \DateTime($row['date_created']));
        
        return $fee;
    }

}


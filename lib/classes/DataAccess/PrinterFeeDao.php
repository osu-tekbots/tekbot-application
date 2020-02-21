<?php
namespace DataAccess;

use Model\PrintFee;

/**
 * Handles all of the logic related to queries on capstone project resources in the database.
 */
class PrinterFeeDao {

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
            FROM 3d_job_fees
            WHERE user_id = :uid
            ';
            $params = array(':uid' => $userID);
            $results = $this->conn->query($sql, $params);

            $fees = array();
            foreach ($results as $row) {
                $fee = self::ExtractPrintFeeFromRow($row);
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
    public function getEquipmentFeeWithJobID($jobID) {
        try {
            $sql = '
            SELECT * 
            FROM 3d_job_fees
            WHERE 3d_job_id = :jid
            ';
            $params = array(':jid' => $jobID);
            $results = $this->conn->query($sql, $params);
            if (\count($results) == 0) {
                return false;
            }

            $fee = self::ExtractPrintFeeFromRow($results[0]);
           
            return $fee;
        } catch (\Exception $e) {
            $this->logger->error("Failed to get fees for print '$jobID': " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Fetches the equipment fee with the provided ID
     *
     * @param string $id
     * @return \Model\EquipmentFee|boolean the equipment on success, false otherwise
     */
    public function getPrintFee($id) {
        try {
            $sql = '
            SELECT * 
            FROM 3d_job_fees
            WHERE id = :id
            ';
            $params = array(':id' => $id);
            $results = $this->conn->query($sql, $params);
            if (\count($results) == 0) {
                return false;
            }

            $fee = self::ExtractPrintFeeFromRow($results[0]);

            return $fee;
        } catch (\Exception $e) {
            $this->logger->error("Failed to fetch print fee with id '$id': " . $e->getMessage());
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
            FROM 3d_job_fees
            ';
            $results = $this->conn->query($sql);

            $fees = array();
            foreach ($results as $row) {
                $fee = self::ExtractPrintFeeFromRow($row);
                $fees[] = $fee;
            }
           
            return $fees;
        } catch (\Exception $e) {
            $this->logger->error("Failed to get fees for admin: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Fetches fees count associated with a user.
     *
     * @param string $userID the ID of the user whose projects to fetch
     * @return \Model\FeesOwed[]|boolean an array of projects on success, false otherwise
     */
    public function getPendingAdminFeesCount() {
        try {
            $sql = '
            SELECT COUNT(*) 
            FROM 3d_job_fees 
            WHERE is_pending = 1
            ';
            $results = $this->conn->query($sql);

            foreach ($results as $row) {
                return $row['COUNT(*)'];
            }
           
        } catch (\Exception $e) {
            $this->logger->error("Failed to get fees count for admin: " . $e->getMessage());
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
            INSERT INTO 3d_job_fees VALUES (
                :id,
                :jobid,
                :userid,
                :notes,
                :dcreated,
                :amount,
                :paymentinfo,
                :ispending,
                :ispaid,
                :dupdated
            )
            ';
            $params = array(
                ':id' => $fee->getPrintFeeId(),
                ':jobid' => $fee->getPrintJobId(),
                ':userid' => $fee->getUserId(),
                ':notes' => $fee->getCustomerNotes(),
                ':dcreated' => QueryUtils::FormatDate($fee->getDateCreated()),
                ':amount' => $fee->getAmount(),
                ':paymentinfo' => $fee->getPaymentInfo(),
                ':ispending' => $fee->getIsPending(),
                ':ispaid' => $fee->getIsPaid(),
                ':dupdated' => QueryUtils::FormatDate($fee->getDateUpdated())
            
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
            UPDATE 3d_job_fees SET 
                3d_job_id = :jobid,
                user_id = :userid,
                customer_notes = :notes,
                date_created = :dcreated,
                amount = :amount,
                payment_info = :paymentinfo,
                is_pending = :ispending,
                is_paid = :ispaid,
                date_updated = :dupdated,
            WHERE 3d_fee_id = :id
            ';
            $params = array(
                ':id' => $fee->getPrintFeeId(),
                ':jobid' => $fee->getPrintJobId(),
                ':userid' => $fee->getUserId(),
                ':notes' => $fee->getCustomerNotes(),
                ':dcreated' => QueryUtils::FormatDate($fee->getDateCreated()),
                ':amount' => $fee->getAmount(),
                ':paymentinfo' => $fee->getPaymentInfo(),
                ':ispending' => $fee->getIsPending(),
                ':ispaid' => $fee->getIsPaid(),
                ':dupdated' => QueryUtils::FormatDate($fee->getDateUpdated())
            
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
    public static function ExtractPrintFeeFromRow($row) {
        $fee = new PrintFee($row['3d_fee_id']);
        $fee->setPrintFeeId($row['3d_fee_id']);
        $fee->setPrintJobId($row['3d_job_id']);
        $fee->setUserId($row['user_id']);
        $fee->setCustomerNotes($row['customer_notes']);
        $fee->setDateCreated(new \DateTime($row['date_created']));
        $fee->setAmount($row['amount']);
        $fee->setPaymentInfo($row['payment_info']);
        $fee->setIsPending($row['is_pending']);
        $fee->setIsPaid($row['is_paid']);
        $fee->setDateUpdated(new \DateTime($row['date_updated']));
        
        return $fee;
    }

}


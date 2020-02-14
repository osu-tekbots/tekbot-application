<?php

namespace DataAccess;

use Model\PrintFee;

/**
 * Handles all of the logic related to queries on capstone 3dprinter resources in the database.
 */
class PrintFeeDao {

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
     * Creates a new PrintFee object using information from the database row
     *
     * @param mixed[] $row the row in the database from which information is to be extracted
     * @return \Model\PrintFee
     */
    public static function ExtractPrintFeeFromRow($row) {
        $printFee = new PrintFee($row['print_fee_id']);
        $printFee->setPrintJobId($row['print_job_id']);
		$printFee->setUserId($row['user_id']);
		$printFee->setCustomerNotes($row['customer_notes']);
		$printFee->setDateCreated($row['date_created']);
		$printFee->setPaymentInfo($row['payment_info']);
		$printFee->setIs_pending($row['is_pending']);
        $printFee->setIs_paid($row['is_paid']);
		$printFee->setDate_updated($row['date_updated']);
        return $printFee;
    }



}

?>
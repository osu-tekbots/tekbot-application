<?php

namespace DataAccess;

use Model\Printer;
use Model\PrintType;
use Model\PrintJob;

/**
 * Handles all of the logic related to queries on capstone 3dprinter resources in the database.
 */
class PrinterDao {

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
     * Fetches all Printers.
     * @return \Model\Printer[]|boolean an array of printers on success, false otherwise
     */
    public function getPrinters() {
        try {
            $sql = '
            SELECT * FROM `3d_printers`
            ';
            $results = $this->conn->query($sql);

            $printers = array();
            foreach ($results as $row) {
                $printer = self::ExtractPrinterFromRow($row);
                $printers[] = $printer;
            }
            return $printers;
        } catch (\Exception $e) {
            $this->logger->error('Failed to get any printers: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Fetches all Print Jobs.
     * @return \Model\PrinterJob[]|boolean an array of printers on success, false otherwise
     */
    public function getPrintJobs() {
        try {
            $sql = '
            SELECT * FROM `3d_jobs`
            ';
            $results = $this->conn->query($sql);

            $printerJobs = array();
            foreach ($results as $row) {
                $printJob = self::ExtractPrintJobFromRow($row);
                $printerJobs[] = $printJob;
            }
            return $printerJobs;
        } catch (\Exception $e) {
            $this->logger->error('Failed to get any printers: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Fetches all Print Types.
     * @return \Model\PrinterType[]|boolean an array of printers on success, false otherwise
     */
    public function getPrintTypes() {
        try {
            $sql = '
            SELECT * FROM `3d_print_type`
            ';
            $results = $this->conn->query($sql);

            $printTypes = array();
            foreach ($results as $row) {
                $printType = self::ExtractPrintTypeFromRow($row);
                $printTypes[] = $printType;
            }
            return $printTypes;
        } catch (\Exception $e) {
            $this->logger->error('Failed to get any printers: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Fetches all Print Jobs by ID.
     * @return \Model\PrinterJob[]|boolean an array of printers on success, false otherwise
     */
    public function getPrintJobsByID($id) {
        try {
            $sql = '
            SELECT * 
            FROM `3d_jobs`
            WHERE 3d_jobs.3d_job_id = :id
            ';
            $params = array(':id' => $id);
            $results = $this->conn->query($sql, $params);

            $printerJobs = array();
            foreach ($results as $row) {
                $printerJob = self::ExtractPrintJobFromRow($row);
                $printerJobs[] = $printerJob;
            }
            return $printerJobs;
        } catch (\Exception $e) {
            $this->logger->error('Failed to get any printers: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Fetches all Print Types by ID.
     * @return \Model\PrinterType[]|boolean an array of printers on success, false otherwise
     */
    public function getPrintTypesByID($id) {
        try {
            $sql = '
            SELECT * FROM `3d_print_type`
            WHERE 3d_print_type.3dprinter_type_id = :id
            ';
            $params = array(':id' => $id);
            $results = $this->conn->query($sql, $params);

            $printTypes = array();
            foreach ($results as $row) {
                $printType = self::ExtractPrintTypeFromRow($row);
                $printTypes[] = $printType;
            }
            return $printTypes;
        } catch (\Exception $e) {
            $this->logger->error('Failed to get any printers: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Fetches all Printers by ID.
     * @return \Model\Printer[]|boolean an array of printers on success, false otherwise
     */
    public function getPrinterByID($id) {
        try {
            $sql = '
            SELECT * FROM `3d_printers`
            WHERE 3d_printers.3dprinter_id = :id
            ';
            $params = array('id:' => $id);
            $results = $this->conn->query($sql, $params);

            $printers = array();
            foreach ($results as $row) {
                $printer = self::ExtractPrinterFromRow($row);
                $printers[] = $printer;
            }
            return $printers;
        } catch (\Exception $e) {
            $this->logger->error('Failed to get any printers: ' . $e->getMessage());
            return false;
        }
    }


    /**
     * Creates a new Equipment object using information from the database row
     *
     * @param mixed[] $row the row in the database from which information is to be extracted
     * @return \Model\Equipment
     */
    public static function ExtractPrinterFromRow($row) {
        $printer = new Printer($row['3dprinter_id']);
        $printer->setPrinterName($row['3dprinter_name']);
        $printer->setDescription($row['description']);
        $printer->setLocation($row['location']);
        return $printer;
    }


    public function addNewPrinter($printer) {
        try {
            $sql = '
            INSERT INTO 3d_printers (
                3dprinter_name,
                description,
                location
            )
            VALUES (
                :name,
                :description,
                :location
            )
            ';
            $params = array(
                ':name' => $printer->getPrinterName(),
                ':description' => $printer->getDescription(),
                ':location' => $printer->getLocation()
            );
            $this->conn->execute($sql, $params);
            return true;
        } catch (\Exception $e) {
            $this->logger->error('Failed to add new equipment: ' . $e->getMessage());
            return false;
        }
    }
    

    /**
     * Extracts information about an image for a equipment from a row in a database result set.
     * 
     * The resulting EquipmentImage does NOT have its reference to the equipment it belongs to set.
     *
     * @param mixed[] $row the row in the database result
     * @return \Model\EquipmentImage the image extracted from the information
     */
    public static function ExtractPrintTypeFromRow($row) {
        $printType = new PrinterType($row['print_type_id']);
        $printType->setPrintTypeName($row['name']);
        $printType->setPrinterId(self::ExtractPrinterFromRow($row));
        $printType->setHeadSize($row['head_size']);
		$printType->setPrecision($row['precision']);
        $printType->setBuildPlateSize($row['build_plate_size']);
        $printType->setCostPerGram($row['cost_per_gram']);
        return $printType;
    }
 
    /**
     * Extract Equipment Category using information from the database row
     *
     * @param mixed[] $row the database row to extract information from
     * @param boolean $equipmentInRow indicates whether the project is also included in the row
     * @return \Model\EquipmentCategory
     */
    public static function ExtractPrintJobFromRow($row) {
        $printJob = new PrintJob($row['print_job_id']);
		
        $printJob->setUserID($row['name']);
		$printJob->setPrintTypeID(self::ExtractPrintTypeFromRow($row));
        $printJob->setPrinterId(self::ExtractPrinterFromRow($row));
        $printJob->setDbFileName($row['db_filename']);
		$printJob->setStlFileName($row['stl_filename']);
        $printJob->setPaymentMethod($row['payment_method']);
        $printJob->setCourseGroupId($row['course_group_id']);
		$printJob->setVoucherCode($row['voucher_code']);
        $printJob->setDateCreated($row['date_created']);
        $printJob->setValidPrintCheck($row['valid_print_check']);
		$printJob->setUserConfirmCheck($row['user_confirm_check']);
        $printJob->setCompletePrintDate($row['complete_print_date']);
        $printJob->setEmployeeNotes($row['employee_notes']);
		$printJob->setMessageGroupId($row['message_group_id']);
        $printJob->setPendingCustomerResponse($row['pending_customer_repsonse']);
        $printJob->setDateUpdated($row['date_updated']);
		
        return $printJob;
    }





}

?>
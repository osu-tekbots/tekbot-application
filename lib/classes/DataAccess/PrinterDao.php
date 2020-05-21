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

			return self::ExtractPrintTypeFromRow($results[0]);
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
            $params = array(':id' => $id);
            $results = $this->conn->query($sql, $params);

            return self::ExtractPrinterFromRow($results[0]);
        } catch (\Exception $e) {
            $this->logger->error('Failed to get any printers: ' . $e->getMessage());
            return false;
        }
    }


    public function addNewPrintJob($printer) {
        try {
            $sql = '
            INSERT INTO 3d_jobs (
                3d_job_id,
                user_id,
                3dprinter_id,
                3dprinter_type_id,
                db_filename,
                stl_file_name,
                payment_method,
                course_group_id,
                voucher_code,
                date_created,
                valid_print_date,
                user_confirm_date,
                complete_print_date,
                employee_notes,
                customer_notes,
                message_group_id,
                pending_customer_response,
                date_updated
            )
            VALUES (
                :3d_job_id,
                :user_id,
                :3dprinter_id,
                :3dprinter_type_id,
                :db_filename,
                :stl_file_name,
                :payment_method,
                :course_group_id,
                :voucher_code,
                :date_created,
                :valid_print_date,
                :user_confirm_date,
                :complete_print_date,
                :employee_notes,
                :customer_notes,
                :message_group_id,
                :pending_customer_response,
                :date_updated
            )
            ';
            $params = array(
                ':3d_job_id' => $printer->getPrintJobID(),
                ':user_id' => $printer->getUserID(),
                ':3dprinter_id' => $printer->getPrintTypeID(),
                ':3dprinter_type_id' => $printer->getPrinterId(),
                ':db_filename' => $printer->getDbFileName(),
                ':stl_file_name' => $printer->getStlFileName(),
                ':payment_method' => $printer->getPaymentMethod(),
                ':course_group_id' => $printer->getCourseGroupId(),
                ':voucher_code' => $printer->getVoucherCode(),
                ':date_created' => $printer->getDateCreated(),
                ':valid_print_date' => $printer->getValidPrintCheck(),
                ':user_confirm_date' => $printer->getUserConfirmCheck(),
                ':complete_print_date' => $printer->getCompletePrintDate(),
                ':employee_notes' => $printer->getEmployeeNotes(),
                ':customer_notes' => $printer->getCustomerNotes(),
                ':message_group_id' => $printer->getMessageGroupId(),
                ':pending_customer_response' => $printer->getPendingCustomerResponse(),
                ':date_updated' => $printer->getDateUpdated()
            );
            $this->conn->execute($sql, $params);
            return true;
        } catch (\Exception $e) {
            $this->logger->error('Failed to add new print job: ' . $e->getMessage());
            return false;
        }
    }
    
    public function addNewPrintTypes($printer) {
        try {
            $sql = '
            INSERT INTO 3d_print_type (
                3dprint_type_name,
                print_type_description,
                3dprinter_id,
                head_size,
                3dprinter_precision,
                build_plate_size,
                cost_per_gram
            )
            VALUES (
                :name,
                :description,
                :printId,
                :head,
                :precision,
                :plateSize,
                :cost

            )
            ';
            $params = array(
                ':name' => $printer->getPrintTypeName(),
                ':description' => $printer->getDescription(),
                ':printId' => $printer->getPrinterId(),
                ':head' => $printer->getHeadSize(),
                ':precision' => $printer->getPrecision(),
                ':plateSize' => $printer->getBuildPlateSize(),
                ':cost' => $printer->getCostPerGram()
            );
            $this->conn->execute($sql, $params);
            return true;
        } catch (\Exception $e) {
            $this->logger->error('Failed to add new print type: ' . $e->getMessage());
            return false;
        }
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
            $this->logger->error('Failed to add new printer: ' . $e->getMessage());
            return false;
        }
    }

    public function updatePrinter($printer) {
        try {
            $sql = '
            UPDATE 3d_printers SET
                3dprinter_name = :name,
                description = :desc,
                location = :loc
            WHERE 3dprinter_id = :id
            ';
            $params = array(
                ':id' => $printer->getPrinterId(),
                ':name' => $printer->getPrinterName(),
                ':desc' => $printer->getDescription(),
                ':loc' => $printer->getLocation()
            );
            $this->conn->execute($sql, $params);
            return true;
        } catch (\Exception $e) {
            $this->logger->error('Failed to update printer: ' . $e->getMessage());
            return false;
        }
    }
	
				
	public function updatePrintType($printType) {
        try {
            $sql = '
            UPDATE 3d_print_type SET
                3dprint_type_name = :name,
                print_type_description = :description,
                3dprinter_id = :printer_id,
                head_size = :head_size,
                3dprinter_precision = :precision,
                build_plate_size = :build_plate_size,
				cost_per_gram = :cost_per_gram
            WHERE 3dprinter_type_id = :id
            ';
            $params = array(
                ':id' => $printType->getPrintTypeId(),
                ':name' => $printType->getPrintTypeName(),
                ':printer_id' => $printType->getPrinterId(),
                ':head_size' => $printType->getHeadSize(),
				':precision' => $printType->getPrecision(),
				':build_plate_size' => $printType->getBuildPlateSize(),
				':cost_per_gram' => $printType->getCostPerGram(),
				':description' => $printType->getDescription()
            );
            $this->conn->execute($sql, $params);
            return true;
        } catch (\Exception $e) {
            $this->logger->error('Failed to add new print type: ' . $e->getMessage());
            return false;
        }
    }
	
	public function updatePrintJob($printJob) {
        try {
            $sql = '
            UPDATE 3d_jobs SET
                user_id = :user_id,
                3dprinter_id = :3dprinter_id,
                3dprinter_type_id = :3dprinter_type_id,
                db_filename = :db_filename,
                stl_file_name = :stl_file_name,
                payment_method = :payment_method,
                course_group_id = :course_group_id,
                voucher_code = :voucher_code,
                date_created = :date_created,
                valid_print_date = :valid_print_date,
                user_confirm_date = :user_confirm_date,
                payment_confirmation = :payment_confirm_date,
                complete_print_date = :complete_print_date,
                employee_notes = :employee_notes,
                message_group_id = :message_group_id,
                pending_customer_response = :pending_customer_response,
                date_updated = :date_updated
			WHERE 3d_job_id = :3d_job_id
            ';
            $params = array(
                ':3d_job_id' => $printJob->getPrintJobID(),
                ':user_id' => $printJob->getUserID(),
                ':3dprinter_id' => $printJob->getPrintTypeID(),
                ':3dprinter_type_id' => $printJob->getPrinterId(),
                ':db_filename' => $printJob->getDbFileName(),
                ':stl_file_name' => $printJob->getStlFileName(),
                ':payment_method' => $printJob->getPaymentMethod(),
                ':course_group_id' => $printJob->getCourseGroupId(),
                ':voucher_code' => $printJob->getVoucherCode(),
                ':date_created' => $printJob->getDateCreated(),
                ':valid_print_date' => $printJob->getValidPrintCheck(),
                ':user_confirm_date' => $printJob->getUserConfirmCheck(),
                ':payment_confirm_date' => $printJob->getPaymentDate(),
                ':complete_print_date' => $printJob->getCompletePrintDate(),
                ':employee_notes' => $printJob->getEmployeeNotes(),
                ':message_group_id' => $printJob->getMessageGroupId(),
                ':pending_customer_response' => $printJob->getPendingCustomerResponse(),
                ':date_updated' => $printJob->getDateUpdated()
            );
            $this->conn->execute($sql, $params);
            return true;
        } catch (\Exception $e) {
            $this->logger->error('Failed to update print job: ' . $e->getMessage());
            return false;
        }
    }


	public function getPrintJobsForUser($uID) {
        try {
            $sql = '
            SELECT * FROM 3d_jobs
			WHERE 3d_jobs.user_id = :uID
            ';
            $params = array(
                ':uID' => $uID
            );
            $results = $this->conn->query($sql, $params);
            $printJobs = array();

            foreach ($results as $row) {
                $printJob = self::ExtractPrintJobFromRow($row);
                $printJobs[] = $printJob;
            }
            return $printJobs;
        } catch (\Exception $e) {
            $this->logger->error('Failed to obtain user\'s print jobs: ' . $e->getMessage());
            return false;
        }
    }

    public function deletePrinterByID($printerID) {
        try {
            $sql = '
            DELETE FROM 3d_printers
            WHERE 3dprinter_id = :id
            ';
            $params = array(
                ':id' => $printerID,
            );
            $this->conn->execute($sql, $params);

            return true;
        } catch (\Exception $e) {
            $this->logger->error('Failed to remove printer metadata: ' . $e->getMessage());
            return false;
        }
    }

    public function deletePrintTypeByID($printTypeID) {
        try {
            $sql = '
            DELETE FROM 3d_print_type
            WHERE 3dprinter_type_id = :id
            ';
            $params = array(
                ':id' => $printTypeID,
            );
            $this->conn->execute($sql, $params);

            return true;
        } catch (\Exception $e) {
            $this->logger->error('Failed to remove print type metadata: ' . $e->getMessage());
            return false;
        }
    }


    /**
     * Creates a new Equipment object using information from the database row
     *
     * @param mixed[] $row the row in the database from which information is to be extracted
     * @return \Model\Equipment
     */
    public static function ExtractPrinterFromRow($row, $printerInRow = false) {
        $printer = new Printer($row['3dprinter_id']);
		
		if($printerInRow){
			return $printer;
		}
        
		if(isset($row['3dprinter_name'])){
			$printer->setPrinterName($row['3dprinter_name']);
		}
		if(isset($row['description'])){
			$printer->setDescription($row['description']);
		}
		if(isset($row['location'])){
			$printer->setLocation($row['location']);
		}
       
        return $printer;
    }

    /**
     * Extracts information about an image for a equipment from a row in a database result set.
     * 
     * The resulting EquipmentImage does NOT have its reference to the equipment it belongs to set.
     *
     * @param mixed[] $row the row in the database result
     * @return \Model\EquipmentImage the image extracted from the information
     */
    public static function ExtractPrintTypeFromRow($row, $printTypeInRow = false) {
        $printType = new PrintType($row['3dprinter_type_id']);
		
		if($printTypeInRow){
			return $printType;
		}
		
        $printType->setPrintTypeName($row['3dprint_type_name']);
        $printType->setDescription($row['print_type_description']);
        //Cannot get setPrinterID to work
        $printType->setPrinterId(self::ExtractPrinterFromRow($row, true));
        $printType->setHeadSize($row['head_size']);
		$printType->setPrecision($row['3dprinter_precision']);
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
        $printJob = new PrintJob($row['3d_job_id']);
		
        $printJob->setUserID($row['user_id']);
		$printJob->setPrintTypeID(self::ExtractPrintTypeFromRow($row, true));
        $printJob->setPrinterId(self::ExtractPrinterFromRow($row, true));
        $printJob->setDbFileName($row['db_filename']);
		$printJob->setStlFileName($row['stl_file_name']);
        $printJob->setPaymentMethod($row['payment_method']);
        $printJob->setCourseGroupId($row['course_group_id']);
		$printJob->setVoucherCode($row['voucher_code']);
        $printJob->setDateCreated($row['date_created']);
        $printJob->setValidPrintCheck($row['valid_print_date']);
		$printJob->setUserConfirmCheck($row['user_confirm_date']);
		$printJob->setPaymentDate($row['payment_confirmation']);
        $printJob->setCompletePrintDate($row['complete_print_date']);
        $printJob->setEmployeeNotes($row['employee_notes']);
        $printJob->setCustomerNotes($row['customer_notes']);
		$printJob->setMessageGroupId($row['message_group_id']);
        $printJob->setPendingCustomerResponse($row['pending_customer_response']);
        $printJob->setDateUpdated($row['date_updated']);
		
        return $printJob;
    }
}

?>
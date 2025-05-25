<?php

namespace DataAccess;

use Model\Printer;
use Model\PrintType;
use Model\PrintJob;

/**
 * Handles all of the logic related to queries on  3dprinter resources in the database.
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
     * Fetches a list of print jobs that require Employee actions. This function is useful for creating alerts for employees.
     * @return \Model\PrintJob[]|boolean an array of printers on success, false otherwise
     */
     public function getPrintJobsRequiringAction() {
		 try {
            $sql = '
            SELECT * FROM `3d_jobs`
            WHERE
			pending_customer_response = 0 AND
			(complete_print_date = "" OR complete_print_date IS NULL) 
			ORDER BY date_created ASC';
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
     * Fetches a list of print jobs that were paid for with an account/voucher and are complete but haven't been charged
     * This function is useful for processing account fees
     * 
     * @return \Model\PrintJob[]|boolean an array of printers on success, false otherwise
     */
    public function getUnchargedCompleteJobs() {
        try {
            $sql = '
            SELECT * FROM `3d_jobs` 
            WHERE account_charge_date IS NULL
                AND NOT payment_method = "cc"
                AND NOT complete_print_date IS NULL';
            $results = $this->conn->query($sql);

            $printJobs = array();
            foreach ($results as $row) {
                $printJob = self::ExtractPrintJobFromRow($row);
                $printJobs[] = $printJob;
            }
            return $printJobs;
        } catch (\Exception $e) {
            $this->logger->error('Failed to obtain printer jobs requiring charging: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Updates the database with the charge date for all print jobs in $ids[]
     * 
     * @param DateTime $date The date to set as the charge date
     * @param Array $ids     The ids of the print jobs to update
     * 
     * @return boolean Whether the database was successfully updated
     */
    public function setChargeDate($date, $ids) {
        try {
            $ids = implode(',', $ids);
            $this->logger->info('Setting charge date for: '.$ids);

            $sql = '
            UPDATE 3d_jobs SET
                date_updated = :udate,
                account_charge_date = :date
			WHERE FIND_IN_SET(3d_job_id, :ids) <> 0
            ';
            $params = array(
                ':udate' => $date,
                ':date' => $date,
                ':ids' => $ids
            );
            $this->conn->execute($sql, $params);
            return true;
        } catch (\Exception $e) {
            $this->logger->error('Failed to update print job: ' . $e->getMessage());
            return false;
        }
    }


    /**
     * Fetches all Printers.
     * @return \Model\PrintJob[]|boolean an array of printers on success, false otherwise
     */
    public function getUnconfirmedPrintJobsForUser($uID) {
        try {
            $sql = '
            SELECT * FROM 3d_jobs
            WHERE 3d_jobs.user_id = :uID
            AND 3d_jobs.pending_customer_response = 1
            ';
            $params = array(
                ':uID' => $uID
            );
            $results = $this->conn->query($sql, $params);
            $laserJobs = array();

            foreach ($results as $row) {
                $laserJob = self::ExtractPrintJobFromRow($row);
                $laserJobs[] = $laserJob;
            }
            return $laserJobs;
        } catch (\Exception $e) {
            $this->logger->error('Failed to obtain user\'s printer jobs: ' . $e->getMessage());
            return false;
        }
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
     * @return \Model\PrintJob[]|boolean an array of printers on success, false otherwise
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
     * @return \Model\PrintType[]|boolean an array of printers on success, false otherwise
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
     * @return \Model\PrintJob[]|boolean an array of printers on success, false otherwise
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

    public function getPrintFilesToKeep() {
        try {
            $sql = '
                SELECT db_filename
                FROM `3d_jobs`
                WHERE complete_print_date >= DATE_SUB(CURDATE(), INTERVAL 2 YEAR)
            ';
            //Takes from db_file_name not stl_file_name
            $results = $this->conn->query($sql);
            

            $filesToKeep = [];

            foreach ($results as $row) {
                $filesToKeep[] = $row["db_filename"];
                
            }
            
            return $filesToKeep;

        } catch (\Exception $e) {
            $this->logger->error('Failed to get any valid stl file names: ' . $e->getMessage());
            return false;
        }
    }

    //Strictly deletes print jobs in the mysql db that are over a year old; the 
    //actual files for those jobs will not be deleted by this function
    public function deleteOldPrintJobs() {
        try {
            $sql = '
                DELETE FROM 3d_jobs
                WHERE complete_print_date < DATE_SUB(CURDATE(), INTERVAL 2 YEAR)
            ';
            $results = $this->conn->query($sql);
            return true;
        } catch (\Exception $e) {
            $this->logger->error('Failed to remove old print jobs: ' . $e->getMessage());
            return false;
        }
    }
    
    //Deletes all printer files from the uploads/prints folder that are not a part
    //of a valid 3d print job; valid print jobs are found by calling getPrintFilesToKeep;
    public function deleteOldAndOrphanPrintFiles($validFileNames) {
        $allFilePaths = glob(__DIR__ . '/../../../uploads/prints/*');
        try {
            foreach ($allFilePaths as $filePath) {
                $filename = basename($filePath);
                
                // if this file is NOT in the list, delete it
                if (!in_array($filename, $validFileNames, true)) {
                    
                    unlink($filePath);
                }
            }
           
            return true;
        } catch (\Exception $e) {
            
            $this->logger->error('Failed to remove files: ' . $e->getMessage());
            return false;
        }
    }

    //Call every once in a while to clear the db and the uploads folder
    public function purgeOldPrintFilesAndJobs() {
        $validFiles = $this->getPrintFilesToKeep();
        return ($this->deleteOldAndOrphanPrintFiles($validFiles) === true 
            &&  $this->deleteOldPrintJobs() === true);
    }
    /**
     * Fetches all Print Types by ID.
     * @return \Model\PrintType|boolean an array of printers on success, false otherwise
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


    public function addNewPrintJob(PrintJob $printJob) {
        try {
            $sql = '
            INSERT INTO 3d_jobs (
                3d_job_id,
                user_id,
                3dprinter_id,
                3dprinter_type_id,
                quantity,
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
                date_updated,
                account_code
            )
            VALUES (
                :3d_job_id,
                :user_id,
                :3dprinter_id,
                :3dprinter_type_id,
                :quantity,
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
                :date_updated,
                :account_code
            )
            ';
            $params = array(
                ':3d_job_id' => $printJob->getPrintJobID(),
                ':user_id' => $printJob->getUserID(),
                ':3dprinter_id' => $printJob->getPrinterId(),
                ':3dprinter_type_id' => $printJob->getPrintTypeID(),
                ':quantity' => $printJob->getQuantity(),
                ':db_filename' => $printJob->getDbFileName(),
                ':stl_file_name' => $printJob->getStlFileName(),
                ':payment_method' => $printJob->getPaymentMethod(),
                ':course_group_id' => $printJob->getCourseGroupId(),
                ':voucher_code' => $printJob->getVoucherCode(),
                ':date_created' => $printJob->getDateCreated(),
                ':valid_print_date' => $printJob->getValidPrintCheck(),
                ':user_confirm_date' => $printJob->getUserConfirmCheck(),
                ':complete_print_date' => $printJob->getCompletePrintDate(),
                ':employee_notes' => $printJob->getEmployeeNotes(),
                ':customer_notes' => $printJob->getCustomerNotes(),
                ':message_group_id' => $printJob->getMessageGroupId(),
                ':pending_customer_response' => $printJob->getPendingCustomerResponse(),
                ':date_updated' => $printJob->getDateUpdated(),
                ':account_code' => $printJob->getAccountCode()
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
                material_amount = :material_amount,
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
                date_updated = :date_updated,
                total_price = :total_price
			WHERE 3d_job_id = :3d_job_id
            ';
            $params = array(
                ':3d_job_id' => $printJob->getPrintJobID(),
                ':user_id' => $printJob->getUserID(),
                ':3dprinter_id' => $printJob->getPrinterId(),
                ':3dprinter_type_id' => $printJob->getPrintTypeID(),
                ':db_filename' => $printJob->getDbFileName(),
                ':stl_file_name' => $printJob->getStlFileName(),
                ':payment_method' => $printJob->getPaymentMethod(),
                ':material_amount' => $printJob->getMaterialAmount(),
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
                ':date_updated' => $printJob->getDateUpdated(),
                ':total_price' => $printJob->getTotalPrice()
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

    public function deletePrintJobByID($printJobId) {
        try {
            $sql = '
            DELETE FROM 3d_jobs
            WHERE 3d_job_id = :id
            ';
            $params = array(
                ':id' => $printJobId,
            );
            $this->conn->execute($sql, $params);

            return true;
        } catch (\Exception $e) {
            $this->logger->error('Failed to remove printjob: ' . $e->getMessage());
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
        $printJob->setQuantity($row['quantity']);
		$printJob->setMaterialAmount($row['material_amount']); //Added 3/8/2021
		$printJob->setVoucherCode($row['voucher_code']);
        $printJob->setAccountCode($row['account_code']);
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
        $printJob->setTotalPrice($row['total_price']);
		
        return $printJob;
    }
}

?>
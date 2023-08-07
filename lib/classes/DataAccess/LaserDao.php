<?php

namespace DataAccess;

use Model\Laser;
use Model\LaserJob;
use Model\LaserMaterial;

class LaserDao
{

    /** @var DatabaseConnection */
    private $conn;

    /** @var \Util\Logger */
    private $logger;

    /**
     * Creates a new instance of the data access object for laser data.
     *
     * @param DatabaseConnection $connection the connection to use to communiate with the database
     * @param \Util\Logger $logger the logger to use to log details about the interactions with the database
     */
    public function __construct($connection, $logger) {
        $this->conn = $connection;
        $this->logger = $logger;
    }


	/**
     * Fetches a list of laser cutting jobs that require Employee actions. This function is useful for creating alerts for employees.
     * @return \Model\LaserJob[]|boolean an array of laser jobs on success, false otherwise
     */
    public function getLaserJobsRequiringAction() {
        try {
            $sql = '
            SELECT * FROM `laser_jobs`
            WHERE
			pending_customer_response = 0 AND
			(complete_cut_date = "" OR complete_cut_date IS NULL) 
			ORDER BY date_created ASC';
            $results = $this->conn->query($sql);

           $laserJobs = array();
            foreach ($results as $row) {
                $laserJob = self::ExtractLaserJobFromRow($row);
                $laserJobs[] = $laserJob;
            }
            return $laserJobs;
        } catch (\Exception $e) {
            $this->logger->error('Failed to get any laser cuts: ' . $e->getMessage());
            return false;
        }
    }


    /**
     * Fetches a list of laser jobs that were paid for with an account/voucher and are complete but haven't been charged
     * This function is useful for processing account fees
     * 
     * @return \Model\LaserJob[]|boolean an array of laser jobs on success, false otherwise
     */
    public function getUnchargedCompleteJobs() {
        try {
            $sql = '
            SELECT * FROM `laser_jobs` 
            WHERE account_charge_date IS NULL
                AND NOT payment_method = "cc"
                AND NOT complete_cut_date IS NULL';
            $results = $this->conn->query($sql);

            $laserJobs = array();
            foreach ($results as $row) {
                $laserJob = self::ExtractLaserJobFromRow($row);
                $laserJobs[] = $laserJob;
            }
            return $laserJobs;
        } catch (\Exception $e) {
            $this->logger->error('Failed to obtain laser jobs requiring charging: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Updates the database with the charge date for all cut jobs in $ids[]
     * 
     * @param DateTime $date The date to set as the charge date
     * @param Array $ids     The ids of the cut jobs to update
     * 
     * @return boolean Whether the database was successfully updated
     */
    public function setChargeDate($date, $ids) {
        try {
            $ids = implode(',', $ids);
            $this->logger->info('Setting charge date for: '.$ids);

            $sql = '
            UPDATE laser_jobs SET
                date_updated = :udate,
                account_charge_date = :date
			WHERE FIND_IN_SET(laser_job_id, :ids) <> 0
            ';
            $params = array(
                ':udate' => $date,
                ':date' => $date,
                ':ids' => $ids
            );
            $this->conn->execute($sql, $params);
            return true;
        } catch (\Exception $e) {
            $this->logger->error('Failed to update laser job: ' . $e->getMessage());
            return false;
        }
    }


    /**
     * Fetches all Cutters.
     * @return \Model\Laser[]|boolean an array of printers on success, false otherwise
     */
    public function getLaserCutters() {
        try {
            $sql = '
            SELECT * FROM `laser_cutters`
            ';
            $results = $this->conn->query($sql);

            $laser_cutters = array();
            foreach ($results as $row) {
                $laserCutter = self::ExtractLaserCutterFromRow($row);
                $laser_cutters[] = $laserCutter;
            }
            return $laser_cutters;
        } catch (\Exception $e) {
            $this->logger->error('Failed to get any laser cutters: ' . $e->getMessage());
            return false;
        }
    }


    /**
     * Fetches all Print Types by ID.
     * @return \Model\LaserMaterial|boolean an array of printers on success, false otherwise
     */
    public function getLaserMaterialByID($id) {
        try {
            $sql = '
            SELECT * FROM `laser_cut_material`
            WHERE laser_cut_material.laser_cut_material_id = :id
            ';
            $params = array(':id' => $id);
            $results = $this->conn->query($sql, $params);

			return self::ExtractLaserCutMaterialFromRow($results[0]);
        } catch (\Exception $e) {
            $this->logger->error('Failed to get any laser materials: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Fetches all Print Jobs.
     * @return \Model\LaserJob[]|boolean an array of printers on success, false otherwise
    */
    public function getLaserJobs() {
        try {
            $sql = '
            SELECT * FROM `laser_jobs`
            ';
            $results = $this->conn->query($sql);

            $laserJobs = array();
            foreach ($results as $row) {
                $laserJob = self::ExtractLaserJobFromRow($row);
                $laserJobs[] = $laserJob;
            }
            return $laserJobs;
        } catch (\Exception $e) {
            $this->logger->error('Failed to get any laser cuts: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Fetches all Print Jobs by ID.
     * @return \Model\LaserJob[]|boolean an array of printers on success, false otherwise
    */
    public function getLaserJobById($id) {
        try {
            $sql = '
            SELECT * 
            FROM `laser_jobs`
            WHERE laser_jobs.laser_job_id = :id
            ';
            $params = array(':id' => $id);
            $results = $this->conn->query($sql, $params);

            $printerJobs = array();
            foreach ($results as $row) {
                $printerJob = self::ExtractLaserJobFromRow($row);
                $printerJobs[] = $printerJob;
            }
            return $printerJobs;
        } catch (\Exception $e) {
            $this->logger->error('Failed to get any laser Jobs: ' . $e->getMessage());
            return false;
        }
    }

    public function deleteCutJobByID($cutJobId) {
        try {
            $sql = '
            DELETE FROM laser_jobs
            WHERE laser_job_id = :id
            ';
            $params = array(
                ':id' => $cutJobId,
            );
            $this->conn->execute($sql, $params);

            return true;
        } catch (\Exception $e) {
            $this->logger->error('Failed to remove cut job: ' . $e->getMessage());
            return false;
        }
    }

    
    /**
     * Creates a new Equipment object using information from the database row
     *
     * @param \Model\Equipment $row the row in the database from which information is to be extracted
     * @return \Model\Equipment
    */
	public function updateCutJob(LaserJob $printJob) {
        try {
            $sql = '
            UPDATE laser_jobs SET
                user_id = :user_id,
                laser_cutter_id = :laser_cutter_id,
                laser_cut_material_id = :laser_cut_material_id,
                quantity = :quantity,
                db_filename = :db_filename,
                dxf_file_name = :dxf_file_name,
                payment_method = :payment_method,
                course_group_id = :course_group_id,
                voucher_code = :voucher_code,
                date_created = :date_created,
                valid_cut_date = :valid_cut_date,
                user_confirm_date = :user_confirm_date,
                payment_confirmation = :payment_confirmation,
                complete_cut_date = :complete_cut_date,
                employee_notes = :employee_notes,
                customer_notes = :customer_notes,
                message_group_id = :message_group_id,
                pending_customer_response = :pending_customer_response,
                date_updated = :date_updated,
                total_price = :total_price
			WHERE laser_job_id = :laser_job_id
            ';
            $params = array(
                ':laser_job_id' => $printJob->getLaserJobId(),
                ':user_id' => $printJob->getUserID(),
                ':laser_cutter_id' => $printJob->getLaserCutterId(),
                ':laser_cut_material_id' => $printJob->getLaserCutMaterialId(),
                ':quantity' => $printJob->getQuantity(),
                ':db_filename' => $printJob->getDbFilename(),
                ':dxf_file_name' => $printJob->getDxfFileName(),
                ':payment_method' => $printJob->getPaymentMethod(),
                ':course_group_id' => $printJob->getCourseGroupId(),
                ':voucher_code' => $printJob->getVoucherCode(),
                ':date_created' => $printJob->getDateCreated(),
                ':valid_cut_date' => $printJob->getValidCutDate(),
                ':user_confirm_date' => $printJob->getUserConfirmDate(),
                ':payment_confirmation' => $printJob->getPaymentDate(),
                ':complete_cut_date' => $printJob->getCompleteCutDate(),
                ':employee_notes' => $printJob->getEmployeeNotes(),
                ':customer_notes' => $printJob->getCustomerNotes(),
                ':message_group_id' => $printJob->getMessageGroupId(),
                ':pending_customer_response' => $printJob->getPendingCustomerResponse(),
                ':date_updated' => $printJob->getDateUpdate(),
                ':total_price' => $printJob->getTotalPrice()
            );
            $this->conn->execute($sql, $params);
            return true;
        } catch (\Exception $e) {
            $this->logger->error('Failed to update laser job: ' . $e->getMessage());
            return false;
        }
    }


    /**
     * Fetches all Cutters.
     * @return \Model\LaserJob[]|boolean an array of printers on success, false otherwise
    */
    public function getLaserJobsForUser($uID) {
        try {
            $sql = '
            SELECT * FROM laser_jobs
			WHERE laser_jobs.user_id = :uID
            ';
            $params = array(
                ':uID' => $uID
            );
            $results = $this->conn->query($sql, $params);
            $laserJobs = array();

            foreach ($results as $row) {
                $laserJob = self::ExtractLaserJobFromRow($row);
                $laserJobs[] = $laserJob;
            }
            return $laserJobs;
        } catch (\Exception $e) {
            $this->logger->error('Failed to obtain user\'s laser jobs: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Fetches all Cutters.
     * @return \Model\LaserJob[]|boolean an array of printers on success, false otherwise
    */
    public function getUnconfirmedLaserJobsForUser($uID) {
        try {
            $sql = '
            SELECT * FROM laser_jobs
            WHERE laser_jobs.user_id = :uID
            AND laser_jobs.pending_customer_response = 1
            ';
            $params = array(
                ':uID' => $uID
            );
            $results = $this->conn->query($sql, $params);
            $laserJobs = array();

            foreach ($results as $row) {
                $laserJob = self::ExtractLaserJobFromRow($row);
                $laserJobs[] = $laserJob;
            }
            return $laserJobs;
        } catch (\Exception $e) {
            $this->logger->error('Failed to obtain user\'s laser jobs: ' . $e->getMessage());
            return false;
        }
    }

    public function addNewLaserCutter(Laser $cutter) {
        try {
            $sql = '
            INSERT INTO laser_cutters (
                laser_cutter_name,
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
                ':name' => $cutter->getLaserName(),
                ':description' => $cutter->getDescription(),
                ':location' => $cutter->getLocation()
            );
            $this->conn->execute($sql, $params);
            return true;
        } catch (\Exception $e) {
            $this->logger->error('Failed to add new Laser Cutter: ' . $e->getMessage());
            return false;
        }
    }

    public function updateLaserMaterial(LaserMaterial $cutMaterial) {
        try {
            $sql = '
            UPDATE laser_cut_material SET
                laser_cut_material_name = :name,
                cut_material_description = :description,
				cost_per_sheet = :cost_per_sheet
            WHERE laser_cut_material_id = :id
            ';
            $params = array(
                ':id' => $cutMaterial->getLaserMaterialId(),
                ':name' => $cutMaterial->getLaserMaterialName(),
				':cost_per_sheet' => $cutMaterial->getCostPerSheet(),
				':description' => $cutMaterial->getDescription()
            );
            $this->conn->execute($sql, $params);
            return true;
        } catch (\Exception $e) {
            $this->logger->error('Failed to add new cut material type: ' . $e->getMessage());
            return false;
        }
    }

    public function deleteLaserMaterialByID($materialID) {
        try {
            $sql = '
            DELETE FROM laser_cut_material
            WHERE laser_cut_material_id = :id
            ';
            $params = array(
                ':id' => $materialID,
            );
            $this->conn->execute($sql, $params);

            return true;
        } catch (\Exception $e) {
            $this->logger->error('Failed to remove cut material metadata: ' . $e->getMessage());
            return false;
        }
    }

    public function addNewLaserMaterial(LaserMaterial $cutMaterial) {
        try {
            $sql = '
            INSERT INTO laser_cut_material (
                laser_cut_material_name,
                cut_material_description,
                cost_per_sheet
            )
            VALUES (
                :name,
                :description,
                :cost

            )
            ';
            $params = array(
                ':name' => $cutMaterial->getLaserMaterialName(),
                ':description' => $cutMaterial->getDescription(),
                ':cost' => $cutMaterial->getCostPerSheet()
            );
            $this->conn->execute($sql, $params);
            return true;
        } catch (\Exception $e) {
            $this->logger->error('Failed to add new cut material: ' . $e->getMessage());
            return false;
        }
    }

    public function deleteLaserByID($cutterID) {
        try {
            $sql = '
            DELETE FROM laser_cutters
            WHERE laser_cutter_id = :id
            ';
            $params = array(
                ':id' => $cutterID,
            );
            $this->conn->execute($sql, $params);

            return true;
        } catch (\Exception $e) {
            $this->logger->error('Failed to remove laser cutter metadata: ' . $e->getMessage());
            return false;
        }
    }

    public function updateLaserCutter(Laser $cutter) {
        try {
            $sql = '
            UPDATE laser_cutters SET
                laser_cutter_name = :name,
                description = :desc,
                location = :loc
            WHERE laser_cutter_id = :id
            ';
            $params = array(
                ':id' => $cutter->getLaserId(),
                ':name' => $cutter->getLaserName(),
                ':desc' => $cutter->getDescription(),
                ':loc' => $cutter->getLocation()
            );
            $this->conn->execute($sql, $params);
            return true;
        } catch (\Exception $e) {
            $this->logger->error('Failed to update laser cutter: ' . $e->getMessage());
            return false;
        }
    }


    /**
     * Fetches all Cut Materials
     * @return \Model\LaserMaterial[]|boolean an array of printers on success, false otherwise
    */
    public function getLaserCutMaterials() {
        try {
            $sql = '
            SELECT * FROM `laser_cut_material`
            ';
            $results = $this->conn->query($sql);

            $laser_cut_materials = array();
            foreach ($results as $row) {
                $laser_cut_material = self::ExtractLaserCutMaterialFromRow($row);
                $laser_cut_materials[] = $laser_cut_material;
            }
            return $laser_cut_materials;
        } catch (\Exception $e) {
            $this->logger->error('Failed to get any laser cutters: ' . $e->getMessage());
            return false;
        }
    }

    public function addNewCutJob($job) {
        try {
            $sql = '
            INSERT INTO laser_jobs (
                laser_job_id,
                user_id,
                laser_cutter_id,
                laser_cut_material_id,
                quantity,
                db_filename,
                dxf_file_name,
                payment_method,
                course_group_id,
                voucher_code,
                date_created,
                valid_cut_date,
                user_confirm_date,
                payment_confirmation,
                complete_cut_date,
                employee_notes,
                customer_notes,
                message_group_id,
                pending_customer_response,
                date_updated,
                account_code
            )
            VALUES (
                :laser_job_id,
                :user_id,
                :laser_cutter_id,
                :laser_cut_material_id,
                :quantity,
                :db_filename,
                :dxf_file_name,
                :payment_method,
                :course_group_id,
                :voucher_code,
                :date_created,
                :valid_cut_date,
                :user_confirm_date,
                :payment_confirmation,
                :complete_cut_date,
                :employee_notes,
                :customer_notes,
                :message_group_id,
                :pending_customer_response,
                :date_updated,
                :account_code
            )
            ';
            $params = array(
                ':laser_job_id' => $job->getLaserJobId(),
                ':user_id' => $job->getUserId(),
                ':laser_cutter_id' => $job->getLaserCutterId(),
                ':laser_cut_material_id' => $job->getLaserCutMaterialId(),
                ':quantity' => $job->getQuantity(),
                ':db_filename' => $job->getDbFilename(),
                ':dxf_file_name' => $job->getDxfFileName(),
                ':payment_method' => $job->getPaymentMethod(),
                ':course_group_id' => $job->getCourseGroupId(),
                ':voucher_code' => $job->getVoucherCode(),
                ':date_created' => $job->getDateCreated(),
                ':valid_cut_date' => $job->getValidCutDate(),
                ':user_confirm_date' => $job->getUserConfirmDate(),
                ':payment_confirmation' => $job->getPaymentDate(),
                ':complete_cut_date' => $job->getCompleteCutDate(),
                ':employee_notes' => $job->getEmployeeNotes(),
                ':customer_notes' => $job->getCustomerNotes(),
                ':message_group_id' => $job->getMessageGroupId(),
                ':pending_customer_response' => $job->getPendingCustomerResponse(),
                ':date_updated' => $job->getDateUpdate(),
                ':account_code' => $job->getAccountCode()
            );
            $this->conn->execute($sql, $params);
            return true;
        } catch (\Exception $e) {
            $this->logger->error('Failed to add new cut job: ' . $e->getMessage());
            return false;
        }
    }

    public function getLaserByID($id) {
        try {
            $sql = '
            SELECT * FROM `laser_cutters`
            WHERE laser_cutters.laser_cutter_id = :id
            ';
            $params = array(':id' => $id);
            $results = $this->conn->query($sql, $params);

            return self::ExtractLaserCutterFromRow($results[0]);
        } catch (\Exception $e) {
            $this->logger->error('Failed to get any laser cutters: ' . $e->getMessage());
            return false;
        }
    }

    public function getCutMaterialByID($id) {
        try {
            $sql = '
            SELECT * FROM `laser_cut_material`
            WHERE laser_cut_material.laser_cut_material_id = :id
            ';
            $params = array(':id' => $id);
            $results = $this->conn->query($sql, $params);

			return self::ExtractLaserCutMaterialFromRow($results[0]);
        } catch (\Exception $e) {
            $this->logger->error('Failed to get any cut materials: ' . $e->getMessage());
            return false;
        }
    }

    public static function ExtractLaserCutterFromRow($row, $laserInRow = false) {
        $laserCutter = new Laser($row['laser_cutter_id']);
		
		if($laserInRow){
			return $laserCutter;
		}
        
		if(isset($row['laser_cutter_name'])){
			$laserCutter->setLaserName($row['laser_cutter_name']);
		}
		if(isset($row['description'])){
			$laserCutter->setDescription($row['description']);
		}
		if(isset($row['location'])){
			$laserCutter->setLocation($row['location']);
		}
       
        return $laserCutter;
    }

    public static function ExtractLaserCutMaterialFromRow($row, $laserInRow = false) {
        $laser_cut_material = new LaserMaterial($row['laser_cut_material_id']);
		
		if($laserInRow){
			return $laser_cut_material;
		}
        
		if(isset($row['laser_cut_material_id'])){
			$laser_cut_material->setLaserMaterialId($row['laser_cut_material_id']);
		}
		if(isset($row['laser_cut_material_name'])){
			$laser_cut_material->setLaserMaterialName($row['laser_cut_material_name']);
		}
		if(isset($row['cut_material_description'])){
			$laser_cut_material->setDescription($row['cut_material_description']);
        }		
        if(isset($row['cost_per_sheet'])){
			$laser_cut_material->setCostPerSheet($row['cost_per_sheet']);
		}
       
        return $laser_cut_material;
    }

    public static function ExtractLaserJobFromRow($row) {
        $laserJob = new LaserJob($row['laser_job_id']);
		
        $laserJob->setUserID($row['user_id']);
		$laserJob->setLaserCutterId(self::ExtractLaserCutterFromRow($row, true));
        $laserJob->setLaserCutMaterialId(self::ExtractLaserCutMaterialFromRow($row, true));
        $laserJob->setQuantity($row['quantity']);
        $laserJob->setDbFileName($row['db_filename']);
		$laserJob->setDxfFileName($row['dxf_file_name']);
        $laserJob->setPaymentMethod($row['payment_method']);
        $laserJob->setCourseGroupId($row['course_group_id']);
		$laserJob->setVoucherCode($row['voucher_code']);
        $laserJob->setAccountCode($row['account_code']);
        $laserJob->setDateCreated($row['date_created']);
        $laserJob->setValidCutDate($row['valid_cut_date']);
		$laserJob->setUserConfirmDate($row['user_confirm_date']);
		$laserJob->setPaymentDate($row['payment_confirmation']);
        $laserJob->setCompleteCutDate($row['complete_cut_date']);
        $laserJob->setEmployeeNotes($row['employee_notes']);
        $laserJob->setCustomerNotes($row['customer_notes']);
		$laserJob->setMessageGroupId($row['message_group_id']);
        $laserJob->setPendingCustomerResponse($row['pending_customer_response']);
        $laserJob->setDateUpdate($row['date_updated']);
        $laserJob->setTotalPrice($row['total_price']);
		
        return $laserJob;
    }

}

<?php

namespace DataAccess;

use Model\Laser;
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
     * Fetches all Cutters.
     * @return \Model\Printer[]|boolean an array of printers on success, false otherwise
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
                complete_cut_date,
                employee_notes,
                customer_notes,
                message_group_id,
                pending_customer_response,
                date_updated
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
                :complete_cut_date,
                :employee_notes,
                :customer_notes,
                :message_group_id,
                :pending_customer_response,
                :date_updated
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
                ':complete_cut_date' => $job->getCompleteCutDate(),
                ':employee_notes' => $job->getEmployeeNotes(),
                ':customer_notes' => $job->getCustomerNotes(),
                ':message_group_id' => $job->getMessageGroupId(),
                ':pending_customer_response' => $job->getPendingCustomerResponse(),
                ':date_updated' => $job->getDateUpdate()
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

}

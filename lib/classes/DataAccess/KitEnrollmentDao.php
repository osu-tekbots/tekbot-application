<?php
// Updated 11/5/2019
namespace DataAccess;

use Model\KitEnrollment;
use Model\KitEnrollmentStatus;


/**
 * Handles all of the logic related to queries on capstone project resources in the database.
 */
class KitEnrollmentDao {

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
     * Fetches the equipment kit with the provided ID
     *
     * @param string $id
     * @return \Model\EquipmentCheckout|boolean the equipment on success, false otherwise
     */
    public function getKitEnrollment($id) {
        try {
            $sql = '
            SELECT * 
            FROM kit_enrollment, kit_enrollment_status
            WHERE kit_enrollment.kit_status_id = kit_enrollment_status.id 
            AND kit_enrollment.kit_id = :id
            
            ';
            $params = array(':id' => $id);
            $results = $this->conn->query($sql, $params);
            if (\count($results) == 0) {
                return false;
            }

            $kit = self::ExtractKitFromRow($results[0]);

            return $kit;
        } catch (\Exception $e) {
            $this->logger->error("Failed to fetch kit with id '$id': " . $e->getMessage());
            return false;
        }
    }

    /**
     * Fetches the kit enrollments with the provided student ID
     *
     * @param string $id
     * @return \Model\KitEnrollment|boolean the equipment on success, false otherwise
     */
    public function getKitEnrollmentsForUser($id) {
        try {
            $sql = '
            SELECT * 
            FROM kit_enrollment, kit_enrollment_status
            WHERE kit_enrollment.kit_status_id = kit_enrollment_status.id 
            AND kit_enrollment.osu_id = :id
            
            ';
            $params = array(':id' => $id);
            $results = $this->conn->query($sql, $params);
            
            $kits = array();
            foreach ($results as $row) {
                $kit = self::ExtractKitFromRow($row);
                $kits[] = $kit;
            }
           
            return $kits;
        } catch (\Exception $e) {
            $this->logger->error("Failed to fetch kit with id '$id': " . $e->getMessage());
            return false;
        }
    }

    public function getKitEnrollmentsByTerm($termID) {
        try {
            $sql = '
            SELECT * 
            FROM kit_enrollment, kit_enrollment_status
            WHERE kit_enrollment.kit_status_id = kit_enrollment_status.id 
            AND kit_enrollment.term_id = :term
            
            ';
            $params = array(':term' => $termID);
            $results = $this->conn->query($sql, $params);
            
            $kits = array();
            foreach ($results as $row) {
                $kit = self::ExtractKitFromRow($row);
                $kits[] = $kit;
            }
           
            return $kits;
        } catch (\Exception $e) {
            $this->logger->error("Failed to fetch kit with course code '$course': " . $e->getMessage());
            return false;
        }
    }

    /**
     * Fetches kits for admin.
     *
     * @return \Model\KitEnrollment[]|boolean an array of projects on success, false otherwise
     */
    public function getKitsForAdmin() {
        try {
            $sql = '
            SELECT * 
            FROM kit_enrollment, kit_enrollment_status
            WHERE kit_enrollment.kit_status_id = kit_enrollment_status.id 
            ';
            $results = $this->conn->query($sql);

            $kits = array();
            foreach ($results as $row) {
                $kit = self::ExtractKitFromRow($row);
                $kits[] = $kit;
            }
           
            return $kits;
        } catch (\Exception $e) {
            $this->logger->error("Failed to get admin checkouts: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Adds a new equipment kit entry into the database.
     *
     * @param \Model\KitEnrollment $kit the equipment to add
     * @return boolean true if successful, false otherwise
     */
    public function addNewKitEnrollment($kit) {
        try {
            $sql = '
            INSERT INTO kit_enrollment VALUES (
                :id,
                :name,
                :osuid,
                :onid,
                :course,
                :term,
                :statusid,
                :dupdated,
                :dcreated
            )
            ';
            $params = array(
                ':id' => $kit->getKitEnrollmentID(),
                ':name' => $kit->getFirstMiddleLastName(),
                ':osuid' => $kit->getOsuID(),
                ':onid' => $kit->getOnid(),
                ':course' => $kit->getCourseCode(),
                ':term' => $kit->getTermID(),
                ':statusid' => $kit->getKitStatusID(),
                ':dupdated' => QueryUtils::FormatDate($kit->getDateUpdated()),
                ':dcreated' => QueryUtils::FormatDate($kit->getDateCreated())
            );
            $this->conn->execute($sql, $params);
            return true;
        } catch (\Exception $e) {
            $this->logger->error('Failed to add new kit: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Updates an kit enrollment entry into the database.
     *
     * @param \Model\KitEnrollment $kit the kit to update
     * @return boolean true if successful, false otherwise
     */
    public function updateKitEnrollment($kit) {
        try {
            $sql = '
            UPDATE kit_enrollment SET
                fml_name = :name,
                osu_id = :osuid,
                onid = :onid,
                course_code = :course,
                term_id = :term,
                kit_status_id = :statusid,
                date_updated = :dupdated
            WHERE kit_id = :id;
            ';
            $params = array(
                ':id' => $kit->getKitEnrollmentID(),
                ':name' => $kit->getFirstMiddleLastName(),
                ':osuid' => $kit->getOsuID(),
                ':onid' => $kit->getOnid(),
                ':course' => $kit->getCourseCode(),
                ':term' => $kit->getTermID(),
                ':statusid' => $kit->getKitStatusID(),
                ':dupdated' => QueryUtils::FormatDate($kit->getDateUpdated())
            );
            $this->conn->execute($sql, $params);
            return true;
        } catch (\Exception $e) {
            $id = $kit->getKitEnrollmentID();
            $this->logger->error("Failed to update kit with id '$id': " . $e->getMessage());
            return false;
        }
    }

    /**
     * Fetches a list of categories for kit enrollment
     *
     * @return \Model\KitEnrollmentStatus[]|boolean an array of categories on success, false otherwise
     */
    public function getKitEnrollmentTypes() {
        try {
            $sql = 'SELECT * FROM kit_enrollment_status';
            $results = $this->conn->query($sql);

            $categories = array();
            foreach ($results as $row) {
                $categories[] = self::ExtractKitEnrollmentStatusFromRow($row);
            }

            return $categories;
        } catch (\Exception $e) {
            $this->logger->error('Failed to get kit enrollment status: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Extracts Checkout object using information from the database row
     *
     * @param mixed[] $row the row in the database from which information is to be extracted
     * @return \Model\KitEnrollment
     */
    public static function ExtractKitFromRow($row) {
        $kit = new KitEnrollment($row['kit_id']);
        $kit->setFirstMiddleLastName($row['fml_name']);
        $kit->setOsuID($row['osu_id']);
        $kit->setOnid($row['onid']);
        $kit->setCourseCode($row['course_code']);
        $kit->setTermID($row['term_id']);
        $kit->setKitStatusID(self::ExtractKitEnrollmentStatusFromRow($row));
        $kit->setDateUpdated(new \DateTime($row['date_updated']));
        $kit->setDateCreated(new \DateTime($row['date_created']));

        return $kit;
    }

    /**
     * Creates a new kit enrollment status enum object by extracting the necessary information from a row in a database.
     * 
     *
     * @param mixed[] $row the row from the database
     * @param boolean $userInRow flag indicating whether entries from the user table are in the row or not
     * @return \Model\KitEnrollmentStatus the user type extracted from the row
     */
    public static function ExtractKitEnrollmentStatusFromRow($row) {
        $id = 'id';
        $name = isset($row['status_name']) ? $row['status_name'] : null;
        return new KitEnrollmentStatus($row[$id], $name);
    }
}



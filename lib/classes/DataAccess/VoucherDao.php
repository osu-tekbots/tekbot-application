<?php
namespace DataAccess;

// use Model\CoursePrintAllowance;
// use Model\CourseGroup;
// use Model\CourseStudent;
use Model\Voucher;
use Model\Service;



class VoucherDao {

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


    /* Admin Functions */

    // Removed 8/31/23 -- Never implemented this version of handling vouchers; just making sure nothing breaks before deleting entirely
    /* public function getAdminCoursePrintAllowance($offset = 0, $limit = -1) {
        try {
            $sql = '
            SELECT * 
            FROM course_print_allowance
            ';
        
            $results = $this->conn->query($sql);

            $courseAllowances = array();
            foreach ($results as $row) {
                $courseAllowance = self::ExtractCoursePrintAllowanceFromRow($row);
                $courseAllowances[] = $courseAllowance;
            }

            return $courseAllowances;
        } catch (\Exception $e) {
            $this->logger->error('Failed to get any course print allowances: ' . $e->getMessage());
            return false;
        }
    }

    public function getGroupCourseStudentView(){
        try {
            $sql = 'SELECT * FROM StudentGroupCourseView';
           
            $results = $this->conn->query($sql);

            $courseStudents = array();
            foreach ($results as $row) {
                $courseStudent = self::ExtractStudentViewFromRow($row);
                $courseStudents[] = $courseStudent;
            }

            return $courseStudents;
        } catch (\Exception $e) {
            $this->logger->error("Failed to fetch course students: " . $e->getMessage());
            return false;
        }
    }

    public function getAdminCourseStudent() {
        try {
            $sql = 'SELECT * FROM course_student';
           
            $results = $this->conn->query($sql);

            $courseStudents = array();
            foreach ($results as $row) {
                $courseStudent = self::ExtractStudentGroupFromRow($row);
                $courseStudents[] = $courseStudent;
            }

            return $courseStudents;
        } catch (\Exception $e) {
            $this->logger->error("Failed to fetch course students: " . $e->getMessage());
            return false;
        }
    } */

    public function getAdminVouchers() {
        try {
            $sql = 'SELECT * FROM voucher_code';
           
            $results = $this->conn->query($sql);

            $vouchers = array();
            foreach ($results as $row) {
                $voucher = self::ExtractVoucherFromRow($row);
                $vouchers[] = $voucher;
            }

            return $vouchers;
        } catch (\Exception $e) {
            $this->logger->error("Failed to fetch voucher codes: " . $e->getMessage());
            return false;
        }
    }

    // Removed 8/31/23 -- Never implemented this version of handling vouchers; just making sure nothing breaks before deleting entirely
    /* public function getGroupsForSpecificCourse($id) {
        try {
            $sql = '
            SELECT * 
            FROM course_group
            WHERE allowance_id = :id
            ';
        
            $params = array(':id' => $id);
            $results = $this->conn->query($sql, $params);

            $courseGroups = array();
            foreach ($results as $row) {
                $courseGroup = self::ExtractCourseGroupFromRow($row);
                $courseGroups[] = $courseGroup;
            }

            return $courseGroups;
        } catch (\Exception $e) {
            $this->logger->error('Failed to get any course groups for current course: ' . $e->getMessage());
            return false;
        }
    } */


    /* Get specific entry from primary ID */

    // Removed 8/31/23 -- Never implemented this version of handling vouchers; just making sure nothing breaks before deleting entirely
    /* public function getCourseStudent($id) {
        try {
            $sql = 'SELECT * FROM course_student WHERE course_student_id = :id';
            $params = array(':id' => $id);
            $results = $this->conn->query($sql, $params);
            if (\count($results) == 0) {
                return false;
            }

            return self::ExtractStudentGroupFromRow($results[0]);
        } catch (\Exception $e) {
            $this->logger->error("Failed to fetch course student with id '$id': " . $e->getMessage());
            return false;
        }
    }

    public function getCoursePrintAllowance($id) {
        try {
            $sql = 'SELECT * FROM course_print_allowance WHERE allowance_id = :id';
            $params = array(':id' => $id);
            $results = $this->conn->query($sql, $params);
            if (\count($results) == 0) {
                return false;
            }

            return self::ExtractCoursePrintAllowanceFromRow($results[0]);
        } catch (\Exception $e) {
            $this->logger->error("Failed to fetch course print allowance with id '$id': " . $e->getMessage());
            return false;
        }
    }

    public function getCourseGroup($id) {
        try {
            $sql = 'SELECT * FROM course_group WHERE course_group_id = :id';
            $params = array(':id' => $id);
            $results = $this->conn->query($sql, $params);
            if (\count($results) == 0) {
                return false;
            }

            return self::ExtractCourseGroupFromRow($results[0]);
        } catch (\Exception $e) {
            $this->logger->error("Failed to fetch course group with id '$id': " . $e->getMessage());
            return false;
        }
    }

    public function getStudentsFromAllowanceIDView($id){
        try {
            $sql = 'SELECT * FROM StudentGroupCourseView WHERE allowance_id = :id';
            $params = array(':id' => $id);
            $results = $this->conn->query($sql, $params);
            $courseStudents = array();
            foreach ($results as $row) {
                $courseStudent = self::ExtractStudentViewFromRow($row);
                $courseStudents[] = $courseStudent;
            }

            return $courseStudents;
        } catch (\Exception $e) {
            $this->logger->error("Failed to fetch students with allowance id: $id: " . $e->getMessage());
            return false;
        }
    } */


    /* Update Existing Entries */
    
    // Removed 8/31/23 -- Never implemented this version of handling vouchers; just making sure nothing breaks before deleting entirely
    /* public function updateCourseStudent($student) {
        try {
            $sql = '
            UPDATE course_student SET
                course_group_id = :gid,
                onid = :onid,
                user_id = :uid
            WHERE course_student_id = :id
            ';
            $params = array(
                ':id' => $student->getCourseStudentID(),
                ':gid' => $student->getCourseGroupID(),
                ':onid' => $student->getOnid(),
                ':uid' => $student->getUserID()
            );
            $this->conn->execute($sql, $params);
            return true;
        } catch (\Exception $e) {
            $id = $student->getCourseStudentID();
            $this->logger->error("Failed to update course student with id '$id': " . $e->getMessage());
            return false;
        }
    }

    public function updateCoursePrintAllowance($course) {
        try {
            $sql = '
            UPDATE course_print_allowance SET
                course_name = :name,
                number_allowed_3dprints = :allowed3d,
                number_allowed_lasercuts = :allowedcut
            WHERE course_group_id = :id
            ';
            $params = array(
                ':id' => $course->getAllowanceID(),
                ':name' => $course->getCourseName(),
                ':allowed3d' => $course->getNumberAllowedPrints(),
                ':allowedcut' => $course->getNumberAllowedCuts()
            );
            $this->conn->execute($sql, $params);
            return true;
        } catch (\Exception $e) {
            $id = $course->getAllowanceID();
            $this->logger->error("Failed to update course print allowance with id '$id': " . $e->getMessage());
            return false;
        }
    }

    public function updateCourseGroup($courseGroup) {
        try {
            $sql = '
            UPDATE course_group SET
                group_name = :name,
                allowance_id = :a_id,
                academic_year = :a_year,
                date_expiration = :d_expiration,
                date_created = :d_created
            WHERE course_group_id = :id
            ';
            $params = array(
                ':id' => $courseGroup->getCourseGroupID(),
                ':name' => $courseGroup->getGroupName(),
                ':a_id' => $courseGroup->getAllowanceID(),
                ':a_year' => $courseGroup->getAcademicYear(),
                ':d_expiration' => QueryUtils::FormatDate($courseGroup->getDateExpiration()),
                ':d_created' => QueryUtils::FormatDate($courseGroup->getDateCreated())
            );
            $this->conn->execute($sql, $params);
            return true;
        } catch (\Exception $e) {
            $id = $courseGroup->getCourseGroupID();
            $this->logger->error("Failed to update course group with id '$id': " . $e->getMessage());
            return false;
        }
    } */

    /* Inserting new entries */

    // Removed 8/31/23 -- Never implemented this version of handling vouchers; just making sure nothing breaks before deleting entirely
    /* public function addNewCoursePrintAllowance($course) {
        try {
            $sql = '
            INSERT INTO course_print_allowance 
            (
                allowance_id, course_name, number_allowed_3dprints, number_allowed_lasercuts
            ) VALUES (
                :id,
                :name,
                :allowed3d,
                :allowedcut
            )';
            $params = array(
                ':id' => $course->getAllowanceID(),
                ':name' => $course->getCourseName(),
                ':allowed3d' => $course->getNumberAllowedPrints(),
                ':allowedcut' => $course->getNumberAllowedCuts()
            );
            $this->conn->execute($sql, $params);

            return true;
        } catch (\Exception $e) {
            $this->logger->error('Failed to add new course allowance: ' . $e->getMessage());
            return false;
        }
    }

    public function addNewCourseGroup($courseGroup) {
        try {
            $sql = '
            INSERT INTO course_group 
            (
                course_group_id, group_name, allowance_id, academic_year, date_expiration, date_created
            ) VALUES (
                :id,
                :name,
                :a_id,
                :a_year,
                :dexpired,
                :dcreated
            )';
            $params = array(
                ':id' => $courseGroup->getCourseGroupID(),
                ':name' => $courseGroup->getGroupName(),
                ':a_id' => $courseGroup->getAllowanceID(),
                ':a_year' => $courseGroup->getAcademicYear(),
                ':dexpired' => $courseGroup->getDateExpiration(),
                ':dcreated' => $courseGroup->getDateCreated()
            );
            $this->conn->execute($sql, $params);

            return true;
        } catch (\Exception $e) {
            $this->logger->error('Failed to add new course group: ' . $e->getMessage());
            return false;
        }
    }

    public function addNewCourseStudent($student) {
        try {
            $sql = '
            INSERT INTO course_student  
            (
                course_student_id, course_group_id, onid, user_id
            ) VALUES (
                :id,
                :gid,
                :onid,
                :uid
            )';
            $params = array(
                ':id' => $student->getCourseStudentID(),
                ':gid' => $student->getCourseGroupID(),
                ':onid' => $student->getOnid(),
                ':uid' => $student->getUserID()
            );
            $this->conn->execute($sql, $params);

            return true;
        } catch (\Exception $e) {
            $this->logger->error('Failed to add new student to group: ' . $e->getMessage());
            return false;
        }
    } */

    public function addNewVoucher($voucher) {
        try {
            $sql = '
            INSERT INTO voucher_code  
            (
                voucher_id, date_used, user_id, date_created, date_expired, service_id, account_code
            ) VALUES (
                :vid,
                :dt_used,
                :uid,
                :dt_created,
                :dt_expired,
                :service_id,
                :account_code
            )';
            $params = array(
                ':vid' => $voucher->getVoucherID(),
                ':dt_used' => $voucher->getDateUsed(),
                ':uid' => $voucher->getUserID(),
                ':dt_created' => QueryUtils::FormatDate($voucher->getDateCreated()),
                ':dt_expired' => QueryUtils::FormatDate($voucher->getDateExpired()),
                ':service_id' => $voucher->getServiceID(),
                ':account_code' => $voucher->getLinkedAccount()
            );
            $this->conn->execute($sql, $params);

            return true;
        } catch (\Exception $e) {
            $this->logger->error('Failed to add new voucher: ' . $e->getMessage());
            return false;
        }
    }

    public function getVoucher($id) {
        try {
            $sql = '
            SELECT * FROM voucher_code
            WHERE voucher_id = :id';
            $params = array(':id' => $id);
            $results = $this->conn->query($sql, $params);
            if (\count($results) == 0) {
                return false;
            }

            $voucher = self::ExtractVoucherFromRow($results[0]);
            return $voucher;

        } catch (\Exception $e) {
            $this->logger->error("Failed to fetch voucher with id '$id': " . $e->getMessage());
            return false;
        }
    }


    public function updateVoucher($voucher) {
        try {
            $sql = '
            UPDATE voucher_code SET
            date_used = :used,
            user_id = :userID
            WHERE voucher_id = :id
            ';
            $params = array(
                ':id' => $voucher->getVoucherID(),
                ':used' => $voucher->getDateUsed(),
                ':userID' => $voucher->getUserID(),

                // TODO: Add later when needed
                // ':created' => $voucher->getDateCreated(),
                // ':expired' => $voucher->getDateExpired(),
                // ':serviceID' => $voucher->getServiceID()
            );
            $this->conn->execute($sql, $params);
            return true;
        } catch (\Exception $e) {
            $id = $voucher->getVoucherID();
            $this->logger->error("Failed to update Voucher with id '$id': " . $e->getMessage());
            return false;
        }
    }
    // public function consumeVoucher($id) {
    //     try {
    //         $sql = '
    //         UPDATE voucher_code  
    //         (
    //             voucher_id, date_used, user_id, date_created, date_expired, service_id
    //         ) VALUES (
    //             :vid,
    //             :dt_used,
    //             :uid,
    //             :dt_created,
    //             :dt_expired,
    //             :service_id
    //         )';
    //         $params = array(
    //             ':vid' => $voucher->getVoucherID(),
    //             ':dt_used' => $voucher->getDateUsed(),
    //             ':uid' => $voucher->getUserID(),
    //             ':dt_created' => QueryUtils::FormatDate($voucher->getDateCreated()),
    //             ':dt_expired' => QueryUtils::FormatDate($voucher->getDateExpired()),
    //             ':service_id' => $voucher->getServiceID(),
    //         );
    //         $this->conn->execute($sql, $params);

    //         return true;
    //     } catch (\Exception $e) {
    //         $this->logger->error('Failed to add new voucher: ' . $e->getMessage());
    //         return false;
    //     }
    // }

    public function clearVouchers($currentDate) {
        try {
            $sql = 'DELETE FROM voucher_code 
            WHERE (date_expired < :date OR date_used IS NOT NULL)
            AND account_code IS NULL
            ';
            $params = array(
                ':date' => QueryUtils::FormatDate($currentDate)
            );
            $this->conn->execute($sql, $params);

            return true;
        } catch (\Exception $e) {
            $this->logger->error('Failed to remove vouchers: ' . $e->getMessage());
            return false;
        }
    }

    public function clearPrintVouchers($currentDate) {
        try {
            $sql = 'DELETE FROM voucher_code 
            WHERE (date_expired < :date
                OR date_used IS NOT NULL)
            AND voucher_code.service_id=2
            AND account_code IS NULL
            ';
            $params = array(
                ':date' => QueryUtils::FormatDate($currentDate)
            );
            $this->conn->execute($sql, $params);

            return true;
        } catch (\Exception $e) {
            $this->logger->error('Failed to remove print vouchers: ' . $e->getMessage());
            return false;
        }
    }

    public function clearCutVouchers($currentDate) {
        try {
            $sql = 'DELETE FROM voucher_code 
            WHERE (date_expired < :date
                OR date_used IS NOT NULL)
            AND service_id=5
            AND account_code IS NULL
            ';
            $params = array(
                ':date' => QueryUtils::FormatDate($currentDate)
            );
            $this->conn->execute($sql, $params);

            return true;
        } catch (\Exception $e) {
            $this->logger->error('Failed to remove cut vouchers: ' . $e->getMessage());
            return false;
        }
    }

    public function getServices() {
        try {
            $sql = 'SELECT * FROM tekbot_services';
            $results = $this->conn->query($sql);

            $services = array();

            foreach ($results as $row) {
                $service = self::ExtractServiceFromRow($row);
                $services[] = $service;
            }

            return $services;

        } catch (\Exception $e) {
            $this->logger->error("Failed to fetch tekbot services: " . $e->getMessage());
            return false;
        }
    }

    /* Extract using models */

    // Removed 8/31/23 -- Never implemented this version of handling vouchers; just making sure nothing breaks before deleting entirely
    /* public static function ExtractCoursePrintAllowanceFromRow($row) {
        $course = new CoursePrintAllowance($row['allowance_id']);
        $course->setCourseName($row['course_name']);
        $course->setNumberAllowedPrints($row['number_allowed_3dprints']);
        $course->setNumberAllowedCuts($row['number_allowed_lasercuts']);
        return $course;
    }

    public static function ExtractCourseGroupFromRow($row) {
        $courseGroup = new CourseGroup($row['course_group_id']);
        $courseGroup->setGroupName($row['group_name']);
        $courseGroup->setAllowanceID($row['allowance_id']);
        $courseGroup->setAcademicYear($row['academic_year']);
        $courseGroup->setDateExpiration($row['date_expiration']);
        $courseGroup->setDateCreated($row['date_created']);
       
        return $courseGroup;
    }

    public static function ExtractStudentGroupFromRow($row) {
        $student = new CourseStudent($row['course_student_id']);
        $student->setCourseGroupID($row['course_group_id']);
        $student->setOnid($row['onid']);
        $student->setUserID($row['user_id']);
       
        return $student;
    }

    public static function ExtractStudentViewFromRow($row){
        $student = new CourseStudent($row['course_student_id']);
        $student->setCourseGroupID($row['course_group_id']);
        $student->setOnid($row['onid']);
        $student->setUserID($row['user_id']);
        $student->setCourseGroup(self::ExtractCourseGroupFromRow($row));
        $student->setCourse(self::ExtractCoursePrintAllowanceFromRow($row));
       
        return $student;
    } */

    public static function ExtractVoucherFromRow($row){
        $voucher = new Voucher($row['voucher_id']);
        $voucher->setVoucherID($row['voucher_id']);
        $voucher->setDateUsed($row['date_used']);
        $voucher->setUserID($row['user_id']);
        $voucher->setDateCreated($row['date_created']);
        $voucher->setDateExpired($row['date_expired']);
        $voucher->setServiceID($row['service_id']);
        $voucher->setLinkedAccount($row['account_code']);
       
        return $voucher;
    }

    public static function ExtractServiceFromRow($row){
        $service = new Service($row['service_id']);
        $service->setServiceName($row['service_name']);
        return $service;
    }

}

?>
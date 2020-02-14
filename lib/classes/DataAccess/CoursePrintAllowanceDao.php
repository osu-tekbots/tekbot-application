<?php
// Updated 11/5/2019
namespace DataAccess;

use Model\CoursePrintAllowance;
use Model\CourseGroup;
use Model\CourseStudent;



class CoursePrintAllowanceDao {

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

    public function getAdminCoursePrintAllowance($offset = 0, $limit = -1) {
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

    public function getGroupsForSpecificCourse($id) {
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
    }


    /* Get specific entry from primary ID */

    public function getCourseStudent($id) {
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


    /* Update Existing Entries */
    
    public function updateCourseStudent($student) {
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
                term_code = :term_code,
                date_expiration = :d_expiration,
                date_created = :d_created
            WHERE course_group_id = :id
            ';
            $params = array(
                ':id' => $courseGroup->getCourseGroupID(),
                ':name' => $courseGroup->getGroupName(),
                ':a_id' => $courseGroup->getAllowanceID(),
                ':term_code' => $courseGroup->getTermCode(),
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
    }

    /* Inserting new entries */

    public function addNewCoursePrintAllowance($course) {
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
                course_group_id, group_name, allowance_id, date_expiration, date_created
            ) VALUES (
                :id,
                :name,
                :a_id,
                :t_code,
                :dexpired,
                :dcreated
            )';
            $params = array(
                ':id' => $courseGroup->getCourseGroupID(),
                ':name' => $courseGroup->getGroupName(),
                ':a_id' => $courseGroup->getAllowanceID(),
                ':t_code' => $courseGroup->getTermCode(),
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
    }

    /* Extract using models */

    public static function ExtractCoursePrintAllowanceFromRow($row) {
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
        $courseGroup->setTermCode($row['term_code']);
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


}

?>
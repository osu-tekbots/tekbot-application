<?php
namespace Model;

use Util\IdGenerator;

class CourseStudent {

    private $courseStudentID;
    private $courseGroupID;
    private $onid;
    private $userID;
    
    
    public function __construct($id = null) {
        if ($id == null) {

           
        } else {
            $this->setCourseStudentID($id);
        }
    }

    /**
     * Getters and Setters
     */
    public function getCourseStudentID(){
		return $this->courseStudentID;
	}

	public function setCourseStudentID($courseStudentID){
		$this->courseStudentID = $courseStudentID;
	}

	public function getCourseGroupID(){
		return $this->courseGroupID;
	}

	public function setCourseGroupID($courseGroupID){
		$this->courseGroupID = $courseGroupID;
	}

	public function getOnid(){
		return $this->onid;
	}

	public function setOnid($onid){
		$this->onid = $onid;
	}

	public function getUserID(){
		return $this->userID;
	}

	public function setUserID($userID){
		$this->userID = $userID;
	}

    

	

}
?>
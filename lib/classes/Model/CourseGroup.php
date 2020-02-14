<?php
namespace Model;

use Util\IdGenerator;

class CourseGroup {

    private $courseGroupID;
    private $groupName;
	private $allowanceID;
	private $termCode;
    private $dateExpiration;
    private $dateCreated;
    
    
    public function __construct($id = null) {
        if ($id == null) {
            $id = IdGenerator::generateSecureUniqueId();
            $this->setCourseGroupID($id);
           
        } else {
            $this->setCourseGroupID($id);
        }
    }

    /**
     * Getters and Setters
     */

    public function getCourseGroupID(){
		return $this->courseGroupID;
	}

	public function setCourseGroupID($courseGroupID){
		$this->courseGroupID = $courseGroupID;
	}

	public function getGroupName(){
		return $this->groupName;
	}

	public function setGroupName($groupName){
		$this->groupName = $groupName;
	}

	public function getAllowanceID(){
		return $this->allowanceID;
	}

	public function setAllowanceID($allowanceID){
		$this->allowanceID = $allowanceID;
	}


	public function getTermCode(){
		return $this->termCode;
	}

	public function setTermCode($termCode){
		$this->termCode = $termCode;
	}

	public function getDateExpiration(){
		return $this->dateExpiration;
	}

	public function setDateExpiration($dateExpiration){
		$this->dateExpiration = $dateExpiration;
	}

	public function getDateCreated(){
		return $this->dateCreated;
	}

	public function setDateCreated($dateCreated){
		$this->dateCreated = $dateCreated;
	}

	

}
?>
<?php
namespace Model;

class CoursePrintAllowance {

    private $allowanceID;
    private $courseName;
    private $numberAllowedPrints;
    private $numberAllowedCuts;
    
    
    public function __construct($id = null) {
        if ($id == null) {
           
        } else {
            $this->setAllowanceID($id);
        }
    }

    /**
     * Getters and Setters
     */

    public function getAllowanceID(){
		return $this->allowanceID;
	}

	public function setAllowanceID($allowanceID){
		$this->allowanceID = $allowanceID;
	}

	public function getCourseName(){
		return $this->courseName;
	}

	public function setCourseName($courseName){
		$this->courseName = $courseName;
	}

	public function getNumberAllowedPrints(){
		return $this->numberAllowedPrints;
	}

	public function setNumberAllowedPrints($numberAllowedPrints){
		$this->numberAllowedPrints = $numberAllowedPrints;
	}

	public function getNumberAllowedCuts(){
		return $this->numberAllowedCuts;
	}

	public function setNumberAllowedCuts($numberAllowedCuts){
		$this->numberAllowedCuts = $numberAllowedCuts;
	}

	

}
?>
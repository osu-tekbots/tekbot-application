<?php
// Updated 11/5/2019
namespace Model;

use Util\IdGenerator;

/**
 * Data structure representing an contract
 */
class Contract {
	
    
    /** @var int */
    private $contractID;

    /** @var ContractType */
    private $contractTypeID;

    /** @var time */
    private $duration;

    /** @var string */
    private $title;

    /** @var string */
    private $description;

    

    /**
     * Creates a new instance of an contract.
     * 
     * If an ID is provided, defaults will not be set. If an ID is not provided, a new ID will be generated and
     * defaults will be set.
     *
     * @param string|null $id the ID of the reservation. If null, an ID will be generated.
    */
    public function __construct($contractID = null, $duration = null) {
        if ($contractID == null && $duration == null) {
			 $this->setContractID(1);
			 $this->setDuration(24);
        } else {
			$this->setContractID($contractID);
			$this->setDuration($duration);
        }
    }

    /**
     * Getters and Setters
     */

	public function getContractID(){
		return $this->contractID;
	}

	public function setContractID($contractID){
		$this->contractID = $contractID;
	}

	public function getContractTypeID(){
		return $this->contractTypeID;
	}

	public function setContractTypeID($contractTypeID){
		$this->contractTypeID = $contractTypeID;
	}

	public function getDuration(){
		return $this->duration;
	}

	public function setDuration($duration){
		$this->duration = $duration;
	}

	public function getTitle(){
		return $this->title;
	}

	public function setTitle($title){
		$this->title = $title;
	}

	public function getDescription(){
		return $this->description;
	}

	public function setDescription($description){
		$this->description = $description;
	}

}
?>
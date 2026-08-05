<?php
namespace Model;

/**
 * Data structure representing an equipment health log entry
 */
class EquipmentHealthLog {

    /** @var int */
    private $logID;
	
    /** @var int */
    private $checkoutID;

	/** @var int */
	private $unitID;
	
	/** @var EquipmentHealthOption */
	private $healthOption;

    /** @var string */
    private $notes;
	
	/** @var \DateTime */
	private $dateCreated;
	
	/** @var \DateTime */
	private $dateUpdated;

    /**
     * Creates a new instance of an equipment health log entry.
     * 
     * If an ID is provided, defaults will not be set. If an ID is not provided,
     * defaults will be set.
     *
     * @param int|null $id the ID of the health log.
     */
    public function __construct($id = null) {
		$this->setLogID($id);
	
		if ($id == null){
            $this->setHealthOption(new EquipmentHealthOption);
            $this->setNotes('');
            $this->setDateCreated(new \DateTime());
            $this->setDateUpdated(new \DateTime());
        }
    }

    /**
     * Getters and Setters
     */

	public function getLogID(){
		return $this->logID;
	}

	public function setLogID($logID){
		$this->logID = $logID;
	}

	public function getCheckoutID(){
		return $this->checkoutID;
	}

	public function setCheckoutID($checkoutID){
		$this->checkoutID = $checkoutID;
	}

	public function getUnitID(){
		return $this->unitID;
	}

	public function setUnitID($unitID){
		$this->unitID = $unitID;
	}

	public function getHealthOption(){
		return $this->healthOption;
	}

	public function setHealthOption($healthOption){
		$this->healthOption = $healthOption;
	}

	public function getNotes(){
		return $this->notes;
	}

	public function setNotes($notes){
		$this->notes = $notes;
	}

	public function getDateCreated(){
		return $this->dateCreated;
	}

	public function setDateCreated($dateCreated){
		$this->dateCreated = $dateCreated;
	}

	public function getDateUpdated(){
		return $this->dateUpdated;
	}

	public function setDateUpdated($dateUpdated){
		$this->dateUpdated = $dateUpdated;
	}

}

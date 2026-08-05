<?php
namespace Model;

/**
 * Data structure representing an equipment unit
 */
class EquipmentUnit {
    
    /** @var int */
    private $unitID;

    /** @var string */
    private $equipmentID;

    /** @var string */
    private $notes;

    /** @var string */
    private $location;

    /** @var boolean */
    private $isPublic;

	/** @var boolean */
	private $isDeleted;

	/** @var string (computed property from view) */
	private $checkoutStatus;

	/** @var string (computed property from view) */
	private $healthStatus;

    /** @var \DateTime */
    private $dateCreated;
	
	/** @var \DateTime */
	private $dateUpdated;

    /**
     * Creates a new instance of an equipment unit.
     * 
     * If an ID is provided, defaults will not be set. If an ID is not provided,
     * defaults will be set.
     *
     * @param int|null $id the ID of the unit.
     */
    public function __construct($id = null) {
        $this->setUnitID($id);
        
        if ($id == null) {
            $this->setIsPublic(false);
			$this->setNotes('');
			$this->setCheckoutStatus("Available");
			$this->setHealthStatus("Fully Functional");
			$this->setIsDeleted(false);
			$this->setDateCreated(new \DateTime());
			$this->setDateUpdated(new \DateTime());
        }
    }

    /**
     * Getters and Setters
     */

	public function getUnitID(){
		return $this->unitID;
	}

	public function setUnitID($unitID){
		$this->unitID = $unitID;
	}

	public function getEquipmentID(){
		return $this->equipmentID;
	}

	public function setEquipmentID($equipmentID){
		$this->equipmentID = $equipmentID;
	}

	public function getNotes(){
		return $this->notes;
	}

	public function setNotes($notes){
		$this->notes = $notes;
	}

	public function getLocation(){
		return $this->location;
	}

	public function setLocation($location){
		$this->location = $location;
	}

	public function getIsPublic(){
		return $this->isPublic;
	}

	public function setIsPublic($isPublic){
		$this->isPublic = $isPublic;
	}

	public function getIsDeleted(){
		return $this->isDeleted;
	}

	public function setIsDeleted($isDeleted){
		$this->isDeleted = $isDeleted;
	}

	public function getCheckoutStatus(){
		return $this->checkoutStatus;
	}

	/** NOTE: this is a computed field and cannot be updated directly */
	public function setCheckoutStatus($checkoutStatus){
		$this->checkoutStatus = $checkoutStatus;
	}

	public function getHealthStatus(){
		return $this->healthStatus;
	}

	/** NOTE: this is a computed field and cannot be updated directly */
	public function setHealthStatus($healthStatus){
		$this->healthStatus = $healthStatus;
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

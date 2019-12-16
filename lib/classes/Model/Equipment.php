<?php
// Updated 11/5/2019
namespace Model;

use Util\IdGenerator;

/**
 * Data structure representing an Equipment
 */
class Equipment {
    
    /** @var int */
    private $equipmentID;

    /** @var string */
    private $equipmentName;
        
    /** @var string */
    private $description;

    /** @var EquipmentHealth */
    private $healthID;

    /** @var string */
    private $notes;

	/** @var string */
	private $usageInstructions;

    /** @var string */
    private $returnCheck;

    /** @var int */
    private $numberParts;

    /** @var string */
    private $partList;

    /** @var string */
	private $location;
	
	/** @var float */
	private $replacementCost;

	/** @var int */
	private $instances;

    /** @var EquipmentCategory */
	private $categoryID;
	
	/** @var EquipmentImage[] */
	private $equipmentImages;

	/** @var boolean */
	private $isPublic;

    /** @var boolean */
    private $isArchived;

    /** @var \DateTime */
    private $dateCreated;

    /** @var \DateTime */
    private $dateUpdated;

    /**
     * Creates a new instance of a Tekbot Equipment.
     * 
     * If an ID is provided, defaults will not be set. If an ID is not provided, a new ID will be generated and
     * defaults will be set.
     *
     * @param string|null $id the ID of the application. If null, a random ID will be generated.
    */
    public function __construct($id = null) {
        if ($id == null) {
            $id = IdGenerator::generateSecureUniqueId();
            $this->setEquipmentID($id);
            $this->setHealthID(new EquipmentHealth());
			$this->setCategoryID(new EquipmentCategory());
			$this->setIsArchived(FALSE);
			$this->setIsPublic(FALSE);
			$this->setInstances(1);
			$this->setDateCreated(new \DateTime());
        } else {
            $this->setEquipmentID($id);
        }
    }

    /**
     * Getters and Setters
     */

	public function getEquipmentID(){
		return $this->equipmentID;
	}

	public function setEquipmentID($equipmentID){
		$this->equipmentID = $equipmentID;
	}

	public function getEquipmentName(){
		return $this->equipmentName;
	}

	public function setEquipmentName($equipmentName){
		$this->equipmentName = $equipmentName;
	}

	public function getDescription(){
		return $this->description;
	}

	public function setDescription($description){
		$this->description = $description;
	}

	public function getHealthID(){
		return $this->healthID;
	}

	public function setHealthID($healthID){
		$this->healthID = $healthID;
	}

	public function getNotes(){
		return $this->notes;
	}

	public function setNotes($notes){
		$this->notes = $notes;
	}

	public function getUsageInstructions(){
		return $this->usageInstructions;
	}

	public function setUsageInstructions($usageInstructions){
		$this->usageInstructions = $usageInstructions;
	}

	public function getReturnCheck(){
		return $this->returnCheck;
	}

	public function setReturnCheck($returnCheck){
		$this->returnCheck = $returnCheck;
	}

	public function getNumberParts(){
		return $this->numberParts;
	}

	public function setNumberParts($numberParts){
		$this->numberParts = $numberParts;
	}

	public function getPartList(){
		return $this->partList;
	}

	public function setPartList($partList){
		$this->partList = $partList;
	}

	public function getLocation(){
		return $this->location;
	}

	public function setLocation($location){
		$this->location = $location;
	}

	public function getReplacementCost(){
		return $this->replacementCost;
	}

	public function setReplacementCost($replacementCost){
		$this->replacementCost = $replacementCost;
	}

	public function getInstances(){
		return $this->instances;
	}

	public function setInstances($instances){
		$this->instances = $instances;
	}

	public function getIsPublic(){
		return $this->isPublic;
	}

	public function setIsPublic($isPublic){
		$this->isPublic = $isPublic;
	}


	public function getCategoryID(){
		return $this->categoryID;
	}

	public function setCategoryID($categoryID){
		$this->categoryID = $categoryID;
	}

	public function getEquipmentImages(){
		return $this->equipmentImages;
	}

	public function setEquipmentImages($equipmentImages){
		$this->equipmentImages = $equipmentImages;
	}


	public function getIsArchived(){
		return $this->isArchived;
	}

	public function setIsArchived($isArchived){
		$this->isArchived = $isArchived;
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
?>
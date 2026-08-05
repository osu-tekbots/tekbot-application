<?php
namespace Model;

use Util\IdGenerator;

/**
 * Data structure representing an EquipmentType
 */
class EquipmentType {
    
    /** @var string */
    private $equipmentID;

    /** @var string */
    private $name;
        
    /** @var string */
    private $description;

	/** @var string */
	private $usageInstructions;

    /** @var string */
    private $notes;

    /** @var string */
    private $returnCheck;

    /** @var string */
    private $parts;
	
	/** @var float */
	private $replacementCost;

	/** @var EquipmentUnit[] */
	private $units;

    /** @var EquipmentTypeCategory */
	private $category;
	
	/** @var EquipmentTypeImage[] */
	private $images;

	/** @var boolean */
	private $isDeleted;

    /** @var \DateTime */
    private $dateCreated;

    /** @var \DateTime */
    private $dateUpdated;

    /**
     * Creates a new instance of a Tekbot EquipmentType.
     * 
     * If an ID is provided, defaults will not be set. If an ID is not provided, a new ID will be generated and
     * defaults will be set.
     *
     * @param string|null $id the ID of the application. If null, a random ID will be generated.
	 * 
	 * @return string the type's ID
     */
    public function __construct($id = null) {
        if ($id == null) {
            $id = IdGenerator::generateSecureUniqueId();
            $this->setEquipmentID($id);
			$this->setCategory(new EquipmentTypeCategory());
			$this->setIsDeleted(false);
			$this->setDateCreated(new \DateTime());
			$this->setDateUpdated(new \DateTime());
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

	public function getName(){
		return $this->name;
	}

	public function setName($name){
		$this->name = $name;
	}

	public function getDescription(){
		return $this->description;
	}

	public function setDescription($description){
		$this->description = $description;
	}

	public function getUsageInstructions(){
		return $this->usageInstructions;
	}

	public function setUsageInstructions($usageInstructions){
		$this->usageInstructions = $usageInstructions;
	}

	public function getNotes(){
		return $this->notes;
	}

	public function setNotes($notes){
		$this->notes = $notes;
	}

	public function getReturnCheck(){
		return $this->returnCheck;
	}

	public function setReturnCheck($returnCheck){
		$this->returnCheck = $returnCheck;
	}

	public function getParts(){
		return $this->parts;
	}

	public function setParts($parts){
		$this->parts = $parts;
	}

	public function getReplacementCost(){
		return $this->replacementCost;
	}

	public function setReplacementCost($replacementCost){
		$this->replacementCost = $replacementCost;
	}

	public function getUnits(){
		return $this->units;
	}

	public function setUnits($units){
		$this->units = $units;
	}

	public function getCategory(){
		return $this->category;
	}

	public function setCategory($category){
		$this->category = $category;
	}

	public function getImages(){
		return $this->images;
	}

	public function setImages($images){
		$this->images = $images;
	}

	public function getIsDeleted(){
		return $this->isDeleted;
	}

	public function setIsDeleted($isDeleted){
		$this->isDeleted = $isDeleted;
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
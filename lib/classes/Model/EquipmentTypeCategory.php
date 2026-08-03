<?php
namespace Model;

/**
 * Data structure representing an equipment category
 */
class EquipmentTypeCategory {
    const HARDWARE = 1;
    const ELECTRICAL = 2;
    const CHEMICAL = 3;

    /** @var int */
    private $categoryID;
	
	/** @var string */
	private $name;

    /**
     * Creates a new instance of an equipment type category.
     * 
     * If an ID is provided, defaults will not be set. If an ID is not provided, a new ID will be generated and
     * defaults will be set.
     *
     * @param int|null $id the ID of the category.
     */
    public function __construct($id = null) {
        if ($id != null){
            $this->setCategoryID($id);
        } else {
            $this->setCategoryID(self::HARDWARE);
            $this->setName('Hardware');
        }
    }

    /**
     * Getters and Setters
     */

	public function getCategoryID(){
		return $this->categoryID;
	}

	public function setCategoryID($categoryID){
		$this->categoryID = $categoryID;
	}

	public function getName(){
		return $this->name;
	}

	public function setName($name){
		$this->name = $name;
	}

}

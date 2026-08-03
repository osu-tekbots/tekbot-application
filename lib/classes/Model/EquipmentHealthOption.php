<?php
namespace Model;

/**
 * Data structure representing an equipment health option for a health log entry
 */
class EquipmentHealthOption {
    const FULLY_FUNCTIONAL = 1;
    const PARTIALLY_FUNCTIONAL = 2;
    const BROKEN = 3;

    /** @var int */
    private $optionID;
	
	/** @var string */
	private $name;

    /**
     * Creates a new instance of an equipment health option.
     * 
     * If an ID is provided, defaults will not be set. If an ID is not provided, a new ID will be generated and
     * defaults will be set.
     *
     * @param int|null $id the ID of the category.
     */
    public function __construct($id = null) {
        if ($id != null){
            $this->setOptionID($id);
        } else {
            $this->setOptionID(self::FULLY_FUNCTIONAL);
            $this->setName('Fully Functional');
        }
    }

    /**
     * Getters and Setters
     */

	public function getOptionID(){
		return $this->optionID;
	}

	public function setOptionID($optionID){
		$this->optionID = $optionID;
	}

	public function getName(){
		return $this->name;
	}

	public function setName($name){
		$this->name = $name;
	}

}

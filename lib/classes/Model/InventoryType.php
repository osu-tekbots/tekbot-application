<?php

namespace Model;

use Util\IdGenerator;

/**
 * Data structure representing a type of inventory part
 */
class InventoryType {
    
    /** @var int */
    private $id;

    /** @var string */
    private $description;

    /** @var boolean */
    private $archived;

    /** @var \DateTime */
    private $dateUpdated;

    /**
     * Creates a new instance of an inventory type.
     * 
     * If an ID is provided, defaults will not be set. If an ID is not provided, a new ID will be generated and
     * defaults will be set.
     *
     * @param string|null $id the ID of the type. If null, a random ID will be generated.
    */
    public function __construct($id = null) {
        if ($id == null) {
            $id = IdGenerator::generateSecureUniqueId();
        } else {
            $this->setId($id);
        }
    }

    /**
     * Getters and Setters
     */

	public function getId(){
		return $this->id;
	}

	public function setId($id){
		$this->id = $id;
	}

	public function getDescription(){
		return $this->description;
	}

	public function setDescription($description){
		$this->description = $description;
	}

	public function getArchived(){
		return $this->archived;
	}

	public function setArchived($archived){
		$this->archived = $archived;
	}

	public function getDateUpdated(){
		return $this->dateUpdated;
	}

	public function setDateUpdated($dateUpdated){
		$this->dateUpdated = $dateUpdated;
	}


}
?>
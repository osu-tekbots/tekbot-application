<?php
namespace Model;

use Util\IdGenerator;

/**
 * Data structure representing an equipment image
 */
class EquipmentTypeImage {
    /** @var string */
    private $imageID;

    /** @var string */
    private $equipmentId;
	
	/** @var string */
	private $filename;

    /** @var boolean */
    private $is_default;

    /**
     * Creates a new instance of an equipment image.
     * 
     * If an ID is provided, defaults will not be set. If an ID is not provided, a new ID will be generated and
     * defaults will be set.
     *
     * @param string|null $id the ID of the image.
     */
    public function __construct($id = null) {
        if ($id == null){
            $id = IdGenerator::generateSecureUniqueId();
            $this->setImageID($id);
            $this->setIsDefault(false);
        } else {
            $this->setImageID($id);
        }
    }

    /**
     * Getters and Setters
     */

	public function getImageID(){
		return $this->imageID;
	}

	public function setImageID($imageID){
		$this->imageID = $imageID;
	}

	public function getEquipmentId(){
		return $this->equipmentId;
	}

	public function setequipmentId($equipmentId){
		$this->equipmentId = $equipmentId;
	}

	public function getFileName(){
		return $this->filename;
	}

	public function setFilename($filename){
		$this->filename = $filename;
	}

	public function getIsDefault(){
		return $this->isDefault;
	}

	public function setIsDefault($isDefault){
		$this->isDefault = $isDefault;
	}

}

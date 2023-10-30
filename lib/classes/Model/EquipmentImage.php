<?php
// Updated 11/5/2019
namespace Model;

use Util\IdGenerator;

/**
 * Represents an image (JPEG, PNG, etc.) associated with a equipment. Equipments can have multiple
 * images.
 */
class EquipmentImage {

    /** @var string */
    private $imageID;
    /** @var string */
	private $equipmentID;
    /** @var Equipment */
    private $equipment;
    /** @var string */
    private $imageName;
    /** @var boolean */
    private $isDefault;
 
    public function __construct($id = null) {
        if ($id == null) {
			$id = IdGenerator::generateSecureUniqueId();
			$this->setImageID($id);
            $this->setIsDefault(false);
        }
        $this->setImageID($id);
    }

	public function getImageID(){
		return $this->imageID;
	}

	public function setImageID($imageID){
		$this->imageID = $imageID;
	}

	public function getEquipmentID(){
		return $this->equipmentID;
	}

	public function setEquipmentID($equipmentID){
		$this->equipmentID = $equipmentID;
	}

	public function getEquipment(){
		return $this->equipment;
	}

	public function setEquipment($equipment){
		$this->equipment = $equipment;
	}

	public function getImageName(){
		return $this->imageName;
	}

	public function setImageName($imageName){
		$this->imageName = $imageName;
	}

	public function getIsDefault(){
		return $this->isDefault;
	}

	public function setIsDefault($isDefault){
		$this->isDefault = $isDefault;
	}


}

?>
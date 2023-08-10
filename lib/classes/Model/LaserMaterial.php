<?php

namespace Model;

use Model\Laser;

/**
 * Data structure representing a Print Type
 */
class LaserMaterial {
    
	private $laserMaterialId;
	private $laserMaterialName;
	private $description;
	private $costPerSheet;

    /**
     * Creates a new instance of an equipment reservation.
     * 
     *
     * @param string|null $id the ID of the reservation. If null, a random ID will be generated.
    */
    public function __construct($id = null) {
        if ($id == null) {
 
        } else {
            $this->setLaserMaterialId($id);
        }
    }

    /**
     * Getters and Setters
     */
	public function getLaserMaterialId(){
		return $this->laserMaterialId;
	}

	public function setLaserMaterialId($laserMaterialId){
		$this->laserMaterialId = $laserMaterialId;
	}

	public function getLaserMaterialName(){
		return $this->laserMaterialName;
	}

	public function setLaserMaterialName($laserMaterialName){
		$this->laserMaterialName = $laserMaterialName;
	}

	public function getDescription(){
		return $this->description;
	}

	public function setDescription($description){
		$this->description = $description;
	}

	public function getCostPerSheet(){
		return $this->costPerSheet;
	}

	public function setCostPerSheet($costPerSheet){
		$this->costPerSheet = $costPerSheet;
	}
}
?>
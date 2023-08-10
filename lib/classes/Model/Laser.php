<?php

namespace Model;

/**
 * Data structure representing a 3d job
 */
class Laser {
    
	private $laserId;
	private $laserName;
	private $description;
	private $location;
	

    /**
     * Creates a new instance of an equipment reservation.
     * 
     * If an ID is provided, defaults will not be set. If an ID is not provided, a new ID will be generated and
     * defaults will be set.
     *
     * @param string|null $id the ID of the reservation. If null, a random ID will be generated.
    */
    public function __construct($id = null) {
        if ($id == null) {
 
        } else {
            $this->setLaserId($id);
        }
    } 

    /**
     * Getters and Setters
     */


    public function getLaserId(){
		return $this->laserId;
	}

	public function setLaserId($laserId){
		$this->laserId = $laserId;
	}

	public function getLaserName(){
		return $this->laserName;
	}

	public function setLaserName($laserName){
		$this->laserName = $laserName;
	}

	public function getDescription(){
		return $this->description;
    }
    
    public function setDescription($description){
		$this->description = $description;
	}

	public function getLocation(){
		return $this->location;
	}

	public function setLocation($location){
		$this->location = $location;
	}
}
?>
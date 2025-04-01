<?php

namespace Model;
use Util\IdGenerator;

/**
 * Data structure representing a Cart 
 */
class Cart {
    
	/** @var string */
	private $idKey;
	/** @var Date/Time (string) */
	private $lastUpdated;	
		
    /**
     * Creates a new instance of a cart
     * 
     *
     * @param string|null $id the ID of the cart. If null, a random ID will be generated.
    */
    public function __construct($id = null) {
        if ($id == null) {
			$idKey = IdGenerator::generateSecureUniquePartId(4); //Only want 4 characters
            $this->setIdKey($idKey);   
        } else {
            $this->setIdKey($id);
        }
    } 

    /**
     * Getters and Setters
     */
	public function getIdKey(){
		return $this->idKey;
	}

	public function setIdKey($data){
		$this->idKey = $data;
	}
	
	public function getLastUpdated(){
		return $this->lastUpdated;
	}
	
	public function setLastUpdated($data){
		$this->lastUpdated = $data;
	}
}


?>
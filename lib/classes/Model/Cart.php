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

	/** @var Date/Time (string) */                
	private $dateCreated; 

	/** @var array */
	private $contents; 
	// Array of Part objects
	
	/** @var int */
	private $editable;
	//1 means editable, 0 means locked

	/** @var int */
	private $isPermanent;
	//1 means Permament, 0 means can expire

    /**
     * Creates a new instance of a cart
     * 
     *
     * @param string|null $id the ID of the cart. If null, a random ID will be generated.
    */
    public function __construct($id = null, $editable = 1, $isPermanent = 0) {
        if ($id == null) {
			$idKey = IdGenerator::generateSecureUniquePartId(4); //Only want 4 characters
            $this->setIdKey($idKey);   
        } else {
            $this->setIdKey($id);
        }
		$this -> setEditableStatus($editable);
		$this -> setPermanence($isPermanent);

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

	public function getDateCreated(){
		return $this->dateCreated;
	}
	
	public function setDateCreated($data){
		$this-> dateCreated = $data;
	}

	public function getEditableStatus(){
		return $this->editable;
	}

	public function setEditableStatus($editable){
		$this->editable = $editable;
	}

	public function getPermanence(){
		return $this->isPermanent;
	}

	public function setPermanence($isPermanent){
		$this->isPermanent = $isPermanent;
	}

	public function getContents(){
		return $this->contents;
	}

	
	public function addContents($part, $quantity){
		if ($this->contents == null) {
            $this->contents = array();
		}
		if(isset($this->contents[$part -> getStocknumber()])) {
			//If the part already exists, just update the quantity
			$this->contents[$part -> getStocknumber()]['quantity'] += $quantity;
		} else {
			$this->contents[$part -> getStocknumber()]["part"] = $part;
			$this->contents[$part -> getStocknumber()]["quantity"] = $quantity;
		}
        return $this;
	}
	
	public function removeContents($stockNumber){
		if ($this->contents != null) {
            if (array_key_exists($stockNumber, $this->contents))
				unset($this->contents[$stockNumber]);
        }
		return $this;
	}

	public function setPartQuantity($part, $quantity) {
		
		if ($this->contents == null) {
			$this->contents = array();
		}
		if (isset($this->contents[$part->getStocknumber()])) {
			// If the part already exists, just update the quantity
			$this->contents[$part->getStocknumber()]['quantity'] = $quantity;
		} else {
			// If it doesn't exist, add it with the specified quantity
			$this->contents[$part->getStocknumber()] = [
				'part' => $part,
				'quantity' => $quantity
			];
		}
		return $this;
	}
	
}
?>
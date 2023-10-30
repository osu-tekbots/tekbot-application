<?php

namespace Model;
use Util\IdGenerator;

/**
 * Data structure representing a Part
 */
class Part {
    
	/** @var string */
	private $name;
	/** @var string */
	private $stocknumber;
	
	/** @var string */
	private $touchnetid;
	
	/** @var string */
	private $image;
	/** @var string */
	private $originalImage;
	/** @var string */
	private $datasheet;
	
	/** @var float */
	private $lastPrice;
	
	/** @var string */
	private $lastSupplier;
	
	/** @var int */
	private $typeID;
	/** @var string */
	private $manufacturer;
	/** @var string */
	private $manufacturerNumber;
	/** @var int */
	private $partMargin;
	/** @var int */
	private $stocked;
	/** @var int */
	private $archive;
	/** @var float */
	private $marketPrice;
	/** @var string */
	private $comment;
	/** @var string */
	private $publicDescription;
	/** @var Date/Time (string) */
	private $lastUpdated;
	/** @var string */
	private $type;
	/** @var int */
	private $quantity;
	/** @var string */
	private $location;
	/** @var Date/Time (string) */
	private $lastCounted;
	
    /**
     * Creates a new instance of an equipment reservation.
     * 
     *
     * @param string|null $id the ID of the reservation. If null, a random ID will be generated.
    */
    public function __construct($stockNumber = null) {
        if ($stockNumber == null) {
			$stockNumber = IdGenerator::generateSecureUniquePartId(6); //Only want 8 characters
            $this->setStocknumber($stockNumber);   
        } else {
            $this->setStocknumber($stockNumber);
        }
    } 

    /**
     * Getters and Setters
     */
	public function getStocknumber(){
		return $this->stocknumber;
	}

	public function setStocknumber($data){
		$this->stocknumber = $data;
	}

	public function getName(){
		return $this->name;
	}

	public function setName($data){
		$this->name = $data;
	}
	
	public function getTouchnetId(){
		return $this->touchnetid;
	}

	public function setTouchnetId($data){
		$this->touchnetid = $data;
	}
	
	public function getImage(){
		return $this->image;
	}
	
	public function setImage($data){
		$this->image = $data;
	}
	
	
	public function getOriginalImage(){
		return $this->originalImage;
	}
	
	public function setOriginalImage($data){
		$this->originalImage = $data;
	}
	
	
	public function getDatasheet(){
		return $this->datasheet;
	}
	
	public function setDatasheet($data){
		$this->datasheet = $data;
	}
	
	
	public function getLastPrice(){
		return $this->lastPrice;
	}
	
	public function setLastPrice($data){
		$this->lastPrice = $data;
	}
	
	
	public function getLastSupplier(){
		return $this->lastSupplier;
	}
	
	public function setLastSupplier($data){
		$this->lastSupplier = $data;
	}
	
	
	public function getTypeId(){
		return $this->typeID;
	}
	
	public function setTypeId($data){
		$this->typeID = $data;
	}
	
	
	public function getManufacturer(){
		return $this->manufacturer;
	}
	
	public function setManufacturer($data){
		$this->manufacturer = $data;
	}
	
	
	public function getManufacturerNumber(){
		return $this->manufacturerNumber;
	}
	
	public function setManufacturerNumber($data){
		$this->manufacturerNumber = $data;
	}
	
	
	public function getPartMargin(){
		return $this->partMargin;
	}
	
	public function setPartMargin($data){
		$this->partMargin = $data;
	}
	
	
	public function getStocked(){
		return $this->stocked;
	}
	
	public function setStocked($data){
		$this->stocked = $data;
	}
	
	
	public function getArchive(){
		return $this->archive;
	}
	
	public function setArchive($data){
		$this->archive = $data;
	}
	
	
	public function getMarketPrice(){
		return $this->marketPrice;
	}
	
	public function setMarketPrice($data){
		$this->marketPrice = $data;
	}
	
	
	public function getComment(){
		return $this->comment;
	}
	
	public function setComment($data){
		$this->comment = $data;
	}
	
	public function getPublicDescription(){
		return $this->publicDescription;
	}
	
	public function setPublicDescription($data){
		$this->publicDescription = $data;
	}
	
	
	public function getLastUpdated(){
		return $this->lastUpdated;
	}
	
	public function setLastUpdated($data){
		$this->lastUpdated = $data;
	}
	
	public function getType(){
		return $this->type;
	}
	
	public function setType($data){
		$this->type = $data;
	}
	
	public function getQuantity(){
		return $this->quantity;
	}
	
	public function setQuantity($data){
		$this->quantity = $data;
	}
	
	public function getLocation(){
		return $this->location;
	}
	
	public function setLocation($data){
		$this->location = $data;
	}
	
	public function getLastCounted(){
		return $this->lastCounted;
	}
	
	public function setLastCounted($data){
		$this->lastCounted = $data;
	}



}

/**
 * Data structure representing a Kit. This is a subset of Part 
 */
class Kit {
    
	/** @var string */
	private $name;
	/** @var string */
	private $stocknumber;
	
	
	/** @var string */
	private $image;

	/** @var int */
	private $typeID;
	/** @var string */
	private $type;
	
	/** @var array */
	private $contents;
	
	
    /**
     * Creates a new instance of a Kit.
     * 
     *
     * @param string|null $id the ID of the reservation. If null, a random ID will be generated.
    */
    public function __construct($stockNumber = null) {
        if ($stockNumber == null) {
			$stockNumber = IdGenerator::generateSecureUniqueId();
            $this->setStocknumber($stockNumber);   
        } else {
            $this->setStocknumber($stockNumber);
        }
    } 

    /**
     * Getters and Setters
     */
	public function getStocknumber(){
		return $this->stocknumber;
	}

	public function setStocknumber($data){
		$this->stocknumber = $data;
	}

	public function getName(){
		return $this->name;
	}

	public function setName($data){
		$this->name = $data;
	}
	
	public function getImage(){
		return $this->image;
	}
	
	public function setImage($data){
		$this->image = $data;
	}
	
	public function getTypeId(){
		return $this->typeID;
	}
	
	public function setTypeId($data){
		$this->typeID = $data;
	}
	
	public function getType(){
		return $this->type;
	}
	
	public function setType($data){
		$this->type = $data;
	}
	
	public function getContents(){
		return $this->contents;
	}
	
	public function addContents($stockNumber, $quantity){
		if ($this->contents == null) {
            $this->contents = array();
			$this->contents[$stockNumber] = $quantity;
        } else {
            $this->contents[$stockNumber] = $quantity;
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

}

?>
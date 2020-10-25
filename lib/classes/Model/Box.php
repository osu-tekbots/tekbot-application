<?php

namespace Model;

/**
 * Data structure representing a Locker
 */
class Box {
    
	/** @var string */
	private $number;
	/** @var string */
	private $boxKey;
	
	/** @var string */
	private $userId;
	/** @var string */
	private $orderNumber;
	/** @var int (0 = false) */
	private $locked;
	
	/** @var string */
	private $fillDate;
	/** @var string */
	private $fillby;
	/** @var string */
	private $pickupDate;
	/** @var int */
	private $battery;
		
    /**
     * Creates a new instance of an equipment reservation.
     * 
     *
     * @param string|null $id the ID of the reservation. If null, a random ID will be generated.
    */
    public function __construct($boxKey = null) {
        if ($boxKey == null) {
			$boxKey = IdGenerator::generateSecureUniqueId();
            $this->setStocknumber($boxKey);   
        } else {
            $this->setBoxKey($boxKey);
        }
    } 

    /**
     * Getters and Setters
     */
	public function getBoxKey(){
		return $this->boxKey;
	}

	public function setBoxKey($data){
		$this->boxKey = $data;
	}

	public function getNumber(){
		return $this->number;
	}

	public function setNumber($data){
		$this->number = $data;
	}
	
	public function getUserId(){
		return $this->userId;
	}

	public function setUserId($data){
		$this->userId = $data;
	}
	
	public function getOrderNumber(){
		return $this->orderNumber;
	}

	public function setOrderNumber($data){
		$this->orderNumber = $data;
	}
	
	public function getFillBy(){
		return $this->fillBy;
	}
	
	public function setFillBy($data){
		$this->fillBy = $data;
	}
	
	public function getFillDate(){
		return $this->fillDate;
	}
	
	public function setFillDate($data){
		$this->fillDate = $data;
	}
	
	public function getLocked(){
		return $this->locked;
	}
	
	public function setLocked($data){
		$this->locked = $data;
	}
	
	
	public function getPickupDate(){
		return $this->pickupDate;
	}
	
	public function setPickupDate($data){
		$this->pickupDate = $data;
	}
	
	public function getBattery(){
		return $this->battery;
	}
	
	public function setBattery($data){
		$this->battery = $data;
	}

}
?>
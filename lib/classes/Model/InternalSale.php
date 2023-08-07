<?php

namespace Model;
use Util\IdGenerator;

/**
 * Data structure representing a Sale
 */
class internalSale {
	
	/** @var int */
	private $saleId;

	/** @var timestamp */
	private $timestamp;
	
	/** @var string */
	private $email;
	/** @var string */
	private $account;
	/** @var int */
	private $amount;
	
	/** @var string */
	private $buyer;
	/** @var string */
	private $seller;
	/** @var string */
	private $description;
	
	/** @var datetime */
	private $processed;

    /**
     * Creates a new instance of an sale id.
     * 
     * If an ID is provided, defaults will not be set. If an ID is not provided, a new ID will be generated and
     * defaults will be set.
     *
     * @param string|null $id the ID of the checkout. If null, a random ID will be generated.
    */
    public function __construct($saleId = null) {
			$this->setSaleId($saleId);
    }

    /**
     * Getters and Setters
     */
	public function getSaleId(){
		return $this->saleId;
	}

	public function setSaleId($data){
		$this->saleId = $data;
	}

	public function getTimestamp(){
		return $this->timestamp;
	}

	public function setTimestamp($data){
		$this->timestamp = $data;
	}

	public function getEmail(){
		return $this->email;
	}

	public function setEmail($data){
		$this->email = $data;
	}
	
	public function getAccount(){
		return $this->account;
	}

	public function setAccount($data){
		$this->account = $data;
	}
	
	public function getAmount(){
		return $this->amount;
	}

	public function setAmount($data){
		$this->amount = $data;
	}
	
	public function getBuyer(){
		return $this->buyer;
	}
	
	public function setBuyer($data){
		$this->buyer = $data;
	}
	
	public function getSeller(){
		return $this->seller;
	}
	
	public function setSeller($data){
		$this->seller = $data;
	}
	
	public function getDescription(){
		return $this->description;
	}
	
	public function setDescription($data){
		$this->description = $data;
	}	
	
	public function getProcessed(){
		return $this->processed;
	}
	
	public function setProcessed($data){
		$this->processed = $data;
	}

}
?>
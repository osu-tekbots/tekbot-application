<?php

namespace Model;

/**
 * Data structure representing a Print Fee
 */
class PrintFee {
    
	private $printFeeId;
	private $printJobId;
	private $userId;
	private $customerNotes;
	private $dateCreated;
	private $amount;
	private $paymentInfo;
	private $isVerified;
	private $isPending;
	private $isPaid;
	private $dateUpdated;

    /**
     * Creates a new instance of an equipment reservation.
     * 
     *
     * @param string|null $id the ID of the reservation. If null, a random ID will be generated.
    */
    public function __construct($id = null) {
        if ($id == null) {
 
        } else {
            $this->setPrintFeeId($id);
        }
    }

    /**
     * Getters and Setters
     */
	public function getPrintFeeId(){
		return $this->printFeeId;
	}

	public function setPrintFeeId($printFeeId){
		$this->printFeeId = $printFeeId;
	}

	public function getPrintJobId(){
		return $this->printJobId;
	}

	public function setPrintJobId($printJobId){
		$this->printJobId = $printJobId;
	}

	public function getUserId(){
		return $this->userId;
	}

	public function setUserId($userId){
		$this->userId = $userId;
	}

	public function getCustomerNotes(){
		return $this->customerNotes;
	}

	public function setCustomerNotes($customerNotes){
		$this->customerNotes = $customerNotes;
	}

	public function getDateCreated(){
		return $this->dateCreated;
	}

	public function setDateCreated($dateCreated){
		$this->dateCreated = $dateCreated;
	}

	public function getAmount(){
		return $this->amount;
	}

	public function setAmount($amount){
		$this->amount = $amount;
	}

	public function getPaymentInfo(){
		return $this->paymentInfo;
	}

	public function setPaymentInfo($paymentInfo){
		$this->paymentInfo = $paymentInfo;
	}
	
	public function getIsVerified(){
		return $this->isVerified;
	}

	public function setIsVerified($isVerified){
		$this->isVerified = $isVerified;
	}

	public function getIsPending(){
		return $this->isPending;
	}

	public function setIsPending($isPending){
		$this->isPending = $isPending;
	}

	public function getIsPaid(){
		return $this->isPaid;
	}

	public function setIsPaid($isPaid){
		$this->isPaid = $isPaid;
	}

	public function getDateUpdated(){
		return $this->dateUpdated;
	}

	public function setDateUpdated($dateUpdated){
		$this->dateUpdated = $dateUpdated;
	}
}
?>
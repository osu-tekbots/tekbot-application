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
	private $paymentInfo;
	private $is_pending;
	private $is_paid;
	private $date_updated;

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

	public function getPaymentInfo(){
		return $this->paymentInfo;
	}

	public function setPaymentInfo($paymentInfo){
		$this->paymentInfo = $paymentInfo;
	}

	public function getIs_pending(){
		return $this->is_pending;
	}

	public function setIs_pending($is_pending){
		$this->is_pending = $is_pending;
	}

	public function getIs_paid(){
		return $this->is_paid;
	}

	public function setIs_paid($is_paid){
		$this->is_paid = $is_paid;
	}

	public function getDate_updated(){
		return $this->date_updated;
	}

	public function setDate_updated($date_updated){
		$this->date_updated = $date_updated;
	}
}
?>
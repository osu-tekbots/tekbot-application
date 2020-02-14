<?php
namespace Model;
// Updated 11/5/2019
use Util\IdGenerator;

/**
 * Data structure representing equipment fees
 */
class EquipmentFee {

    /** @var int */
    private $feeID;

    /** @var int */
	private $checkoutID;
	
	/** @var EquipmentCheckout */
	private $checkout;

	/** @var int */
	private $userID;

	/** @var User */
	private $user;
	
	/** @var string */
	private $paymentInfo;
	
    /** @var string */
    private $notes;

    /** @var float */
    private $amount;

    /** @var boolean */
	private $isPaid;
	
	/** @var boolean */
	private $isPending;

    /** @var \DateTime */
    private $dateUpdated;

    /** @var \DateTime */
    private $dateCreated;

     /**
     * Creates a new instance of an equipment fee.
     * 
     * If an ID is provided, defaults will not be set. If an ID is not provided, a new ID will be generated and
     * defaults will be set.
     *
     * @param string|null $id the ID of the fee. If null, a random ID will be generated.
    */
    public function __construct($id = null) {
        if ($id == null) {
            $id = IdGenerator::generateSecureUniqueId();
            $this->setFeeID($id);
            $this->setDateCreated(new \DateTime());
        } else {
            $this->setFeeID($id);
        }
    }

    /**
     * Getters and Setters
     */

	public function getFeeID(){
		return $this->feeID;
	}

	public function setFeeID($feeID){
		$this->feeID = $feeID;
	}

	public function getCheckoutID(){
		return $this->checkoutID;
	}

	public function setCheckoutID($checkoutID){
		$this->checkoutID = $checkoutID;
	}

	public function getCheckout(){
		return $this->checkout;
	}

	public function setCheckout($checkout){
		$this->checkout = $checkout;
	}

	public function getUserID(){
		return $this->userID;
	}

	public function setUserID($userID){
		$this->userID = $userID;
	}

	public function getUser(){
		return $this->user;
	}

	public function setUser($user){
		$this->user = $user;
	}

	public function getPaymentInfo(){
		return $this->paymentInfo;
	}

	public function setPaymentInfo($paymentInfo){
		$this->paymentInfo = $paymentInfo;
	}

	public function getNotes(){
		return $this->notes;
	}

	public function setNotes($notes){
		$this->notes = $notes;
	}

	public function getAmount(){
		return $this->amount;
	}

	public function setAmount($amount){
		$this->amount = $amount;
	}

	public function getIsPaid(){
		return $this->isPaid;
	}

	public function setIsPaid($isPaid){
		$this->isPaid = $isPaid;
	}

	public function getIsPending(){
		return $this->isPending;
	}

	public function setIsPending($isPending){
		$this->isPending = $isPending;
	}

	public function getDateUpdated(){
		return $this->dateUpdated;
	}

	public function setDateUpdated($dateUpdated){
		$this->dateUpdated = $dateUpdated;
	}

	public function getDateCreated(){
		return $this->dateCreated;
	}

	public function setDateCreated($dateCreated){
		$this->dateCreated = $dateCreated;
	}


  
}
?>
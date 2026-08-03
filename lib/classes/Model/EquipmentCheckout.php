<?php
namespace Model;

/**
 * Data structure representing an Equipment Checkout
 */
class EquipmentCheckout {
    
    /** @var int */
    private $checkoutID;

    /** @var int */
	private $reservationID;

    /** @var int */
	private $itemID;

    /** @var string */
	private $userID;

    /** @var \DateTime */
    private $dateCheckedOut;

    /** @var \DateTime */
    private $dateDue;

	/** @var \Datetime|null */
	private $dateReturned;

    /** @var \DateTime */
    private $dateUpdated;


    /**
     * Creates a new instance of an equipment checkout.
     * 
     * If an ID is provided, defaults will not be set. If an ID is not provided, a new ID will be generated and
     * defaults will be set.
     *
     * @param int|null $id the ID of the checkout. If null, a random ID will be generated.
    */
    public function __construct($id = null) {
        $this->setCheckoutID($id);
	
		if ($id == null) {
            $this->setDateCheckedOut(new \DateTime());
            $this->setDateUpdated(new \DateTime());
        }
    }

    /**
     * Getters and Setters
     */

	public function getCheckoutID(){
		return $this->checkoutID;
	}

	public function setCheckoutID($checkoutID){
		$this->checkoutID = $checkoutID;
	}

	public function getReservationID(){
		return $this->reservationID;
	}

	public function setReservationID($reservationID){
		$this->reservationID = $reservationID;
	}

	public function getItemID(){
		return $this->itemID;
	}

	public function setItemID($itemID){
		$this->itemID = $itemID;
	}

	public function getUserID(){
		return $this->userID;
	}

	public function setUserID($userID){
		$this->userID = $userID;
	}

	public function getDateCheckedOut(){
		return $this->dateCheckedOut;
	}

	public function setDateCheckedOut($dateCheckedOut){
		$this->dateCheckedOut = $dateCheckedOut;
	}

	public function getDateDue(){
		return $this->dateDue;
	}

	public function setDateDue($dateDue){
		$this->dateDue = $dateDue;
	}

	public function getDateReturned(){
		return $this->dateReturned;
	}

	public function setDateReturned($dateReturned){
		$this->dateReturned = $dateReturned;
	}

	public function getDateUpdated(){
		return $this->dateUpdated;
	}

	public function setDateUpdated($dateUpdated){
		$this->dateUpdated = $dateUpdated;
	}
}

?>
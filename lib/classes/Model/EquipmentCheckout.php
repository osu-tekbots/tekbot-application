<?php
namespace Model;

use Util\IdGenerator;
// Updated 11/5/2019

/**
 * Data structure representing an Equipment Checkout
 */
class EquipmentCheckout {
    
    /** @var int */
    private $checkoutID;

    /** @var int */
	private $userID;
	
	/** @var User */
	private $user;

    /** @var int */
	private $reservationID;
	
	/** @var EquipmentReservation */
	private $reservation;

	/** @var int */
	private $equipmentID;

	/** @var Equipment */
	private $equipment;

	/** @var CheckoutStatus */
	private $statusID;

	/** @var ContractType */
	private $contractID;

    /** @var \DateTime */
    private $pickupTime;
    
    /** @var \DateTime */
    private $returnTime;

    /** @var \DateTime */
    private $deadlineTime;
    
    /** @var string */
    private $notes;

    /** @var \DateTime */
    private $dateUpdated;

    /** @var \DateTime */
    private $dateCreated;


    

    /**
     * Creates a new instance of an equipment checkout.
     * 
     * If an ID is provided, defaults will not be set. If an ID is not provided, a new ID will be generated and
     * defaults will be set.
     *
     * @param string|null $id the ID of the checkout. If null, a random ID will be generated.
    */
    public function __construct($id = null) {
        if ($id == null) {
            $id = IdGenerator::generateSecureUniqueId();
            $this->setCheckoutID($id);
            $this->getStatusID(new EquipmentCheckoutStatus());
            $this->setDateCreated(new \DateTime());
        } else {
            $this->setCheckoutID($id);
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

	public function getReservationID(){
		return $this->reservationID;
	}

	public function setReservationID($reservationID){
		$this->reservationID = $reservationID;
	}

	public function getReservation(){
		return $this->reservation;
	}

	public function setReservation($reservation){
		$this->reservation = $reservation;
	}
	
	public function getEquipmentID(){
		return $this->equipmentID;
	}

	public function setEquipmentID($equipmentID){
		$this->equipmentID = $equipmentID;
	}

	public function getEquipment(){
		return $this->equipment;
	}

	public function setEquipment($equipment){
		$this->equipment = $equipment;
	}

	public function getStatusID(){
		return $this->statusID;
	}

	public function setStatusID($statusID){
		$this->statusID = $statusID;
	}

	public function getContractID(){
		return $this->contractID;
	}

	public function setContractID($contractID){
		$this->contractID = $contractID;
	}

	public function getPickupTime(){
		return $this->pickupTime;
	}

	public function setPickupTime($pickupTime){
		$this->pickupTime = $pickupTime;
	}

	public function getReturnTime(){
		return $this->returnTime;
	}

	public function setReturnTime($returnTime){
		$this->returnTime = $returnTime;
	}

	public function getDeadlineTime(){
		return $this->deadlineTime;
	}

	public function setDeadlineTime($deadlineTime){
		$this->deadlineTime = $deadlineTime;
	}

	public function getNotes(){
		return $this->notes;
	}

	public function setNotes($notes){
		$this->notes = $notes;
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
<?php
// Updated 11/5/2019
namespace Model;

use Util\IdGenerator;

/**
 * Data structure representing an equipment Reservation
 */
class EquipmentReservation {
    
    /** @var int */
    private $reservationID;

    /** @var string */
    private $equipmentID;

    /** @var Equipment */
    private $equipment;

    /** @var string */
    private $userID;

    /** @var User */
    private $user;

    /** @var \DateTime */
    private $datetimeReserved;

    /** @var \DateTime */
    private $datetimeExpired;

    /** @var boolean */
    private $isActive;

    /**
     * Creates a new instance of an equipment reservation.
     * 
     * If an ID is provided, defaults will not be set. If an ID is not provided, a new ID will be generated and
     * defaults will be set.
     *
     * @param string|null $id the ID of the reservation. If null, a random ID will be generated.
    */
    public function __construct($id = null) {
        if ($id == null) {
            $id = IdGenerator::generateSecureUniqueId();
            $this->setReservationID($id);
			$this->getDatetimeReserved(new \DateTime());
        } else {
            $this->setEquipmentID($id);
        }
    }

    /**
     * Getters and Setters
     */

	public function getReservationID(){
		return $this->reservationID;
	}

	public function setReservationID($reservationID){
		$this->reservationID = $reservationID;
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

	public function getDatetimeReserved(){
		return $this->datetimeReserved;
	}

	public function setDatetimeReserved($datetimeReserved){
		$this->datetimeReserved = $datetimeReserved;
	}

	public function getDatetimeExpired(){
		return $this->datetimeExpired;
	}

	public function setDatetimeExpired($datetimeExpired){
		$this->datetimeExpired = $datetimeExpired;
	}

	public function getIsActive(){
		return $this->isActive;
	}

	public function setIsActive($isActive){
		$this->isActive = $isActive;
	}

}
?>
<?php
namespace Model;

/**
 * Data structure representing an equipment reservation
 */
class EquipmentReservation {

    /** @var int */
    private $reservationID;
	
	/** @var int */
	private $unitID;
	
	/** @var string */
	private $userID;
	
	/** @var \DateTime */
	private $dateReserved;
	
	/** @var boolean */
	private $isEmployeeDismissed;

    /**
     * Creates a new instance of an equipment reservation.
     * 
     * If an ID is provided, defaults will not be set. If an ID is not provided, a new ID will be generated and
     * defaults will be set.
     *
     * @param int|null $id the ID of the reservation.
     */
    public function __construct($id = null) {
        $this->setReservationID($id);
	
		if ($id == null){
            $this->setDateReserved(new \DateTime());
            $this->setIsEmployeeDismissed(false);
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

	public function getUnitID(){
		return $this->unitID;
	}

	public function setUnitID($unitID){
		$this->unitID = $unitID;
	}

	public function getUserID(){
		return $this->userID;
	}

	public function setUserID($userID){
		$this->userID = $userID;
	}

	public function getDateReserved(){
		return $this->dateReserved;
	}

	public function setDateReserved($dateReserved){
		$this->dateReserved = $dateReserved;
	}

	public function getIsEmployeeDismissed(){
		return $this->isEmployeeDismissed;
	}

	public function setIsEmployeeDismissed($isEmployeeDismissed){
		$this->isEmployeeDismissed = $isEmployeeDismissed;
	}

}

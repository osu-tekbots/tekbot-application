<?php

namespace Model;

/**
 * Data structure representing a Locker
 */
class Locker {
    
	private $lockerId;
	private $lockerNumber;
	private $lockerRoomId;
	private $status;
	private $location;
	private $free;
	private $userId;
	private $dueDate;

    /**
     * Creates a new instance of an equipment reservation.
     * 
     *
     * @param string|null $id the ID of the reservation. If null, a random ID will be generated.
    */
    public function __construct($id = null) {
        if ($id == null) {
 
        } else {
            $this->setLockerId($id);
        }
    } 

    /**
     * Getters and Setters
     */
	public function getLockerId(){
		return $this->lockerId;
	}

	public function setLockerId($data){
		$this->lockerId = $data;
	}

	public function getLockerNumber(){
		return $this->lockerNumber;
	}

	public function setLockerNumber($data){
		$this->lockerNumber = $data;
	}
	
	public function getLockerRoomId(){
		return $this->lockerRoomId;
	}

	public function setLockerRoomId($data){
		$this->lockerRoomId = $data;
	}
	
	public function getStatus(){
		return $this->status;
	}
	
	public function setStatus($data){
		$this->status = $data;
	}
	

	public function getLocation(){
		return $this->location;
	}
	
	public function setLocation($data){
		$this->location = $data;
	}
	
	public function getFree(){
		return $this->free;
	}
	
	public function setFree($data){
		$this->free = $data;
	}
	
	public function getUserId(){
		return $this->userId;
	}
	
	public function setUserId($data){
		$this->userId = $data;
	}
	
	public function getDueDate(){
		return $this->dueDate;
	}
	
	public function setDueDate($data){
		$this->dueDate = $data;
	}
}
?>
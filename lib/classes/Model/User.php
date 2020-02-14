<?php
// Updated 11/5/2019
namespace Model;

use Util\IdGenerator;

/**
 * Data structure representing a user
 */
class User {

    /** @var string */
    private $userID;

    /** @var string */
    private $firstName;

    /** @var string */
    private $lastName;

    /** @var string */
    private $email;

    /** @var string */
    private $phone;

    /** @var string */
    private $onid;

    /** @var UserAccessLevel */
    private $accessLevelID;

    /** @var \DateTime */
    private $dateUpdated;

    /** @var \DateTime */
    private $dateCreated;

    /** @var \DateTime */
    private $dateLastLogin;

     /**
     * Constructs a new instance of a user in the capstone system.
     * 
     * If no ID is provided, the alphanumeric ID will be generated using a random, cryptographically secure approach.
     *
     * @param string|null $id the ID of the user. If null, a new ID will be generated for the user.
     */
    public function __construct($id = null) {
        if ($id == null) {
            $id = IdGenerator::generateSecureUniqueId();
            $this->setUserID($id);
            $this->setAccessLevelID(new UserAccessLevel());
            $this->setDateCreated(new \DateTime());
        } else {
			$this->setUserID($id);
			$this->setDateLastLogin(new \DateTime());
        }
    }
    /** 
     * Getters and Setters
    */
    
	public function getUserID(){
		return $this->userID;
	}

	public function setUserID($userID){
		$this->userID = $userID;
	}

	public function getFirstName(){
		return $this->firstName;
	}

	public function setFirstName($firstName){
		$this->firstName = $firstName;
	}

	public function getLastName(){
		return $this->lastName;
	}

	public function setLastName($lastName){
		$this->lastName = $lastName;
	}

	public function getEmail(){
		return $this->email;
	}

	public function setEmail($email){
		$this->email = $email;
	}

	public function getPhone(){
		return $this->phone;
	}

	public function setPhone($phone){
		$this->phone = $phone;
	}

	public function getOnid(){
		return $this->onid;
	}

	public function setOnid($onid){
		$this->onid = $onid;
	}

	public function getAccessLevelID(){
		return $this->accessLevelID;
	}

	public function setAccessLevelID($accessLevelID){
		$this->accessLevelID = $accessLevelID;
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

	public function getDateLastLogin(){
		return $this->dateLastLogin;
	}

	public function setDateLastLogin($dateLastLogin){
		$this->dateLastLogin = $dateLastLogin;
	}

}
?>
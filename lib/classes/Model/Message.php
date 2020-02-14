<?php
namespace Model;

use Util\IdGenerator;

class Message {

    private $messageID;
    private $messageGroupID;
    private $userID;
    private $messageText;
    private $isEmployee;
    private $dateCreated;
    
    
    public function __construct($id = null) {
        if ($id == null) {
            $id = IdGenerator::generateSecureUniqueId();
            $this->setMessageID($id);
            $this->setDateCreated(new \DateTime());
           
        } else {
            $this->setMessageID($id);
        }
    }

    /**
     * Getters and Setters
     */

	public function getMessageID(){
		return $this->messageID;
	}

	public function setMessageID($messageID){
		$this->messageID = $messageID;
	}

	public function getMessageGroupID(){
		return $this->messageGroupID;
	}

	public function setMessageGroupID($messageGroupID){
		$this->messageGroupID = $messageGroupID;
	}

	public function getUserID(){
		return $this->userID;
	}

	public function setUserID($userID){
		$this->userID = $userID;
	}

	public function getMessageText(){
		return $this->messageText;
	}

	public function setMessageText($messageText){
		$this->messageText = $messageText;
	}

	public function getIsEmployee(){
		return $this->isEmployee;
	}

	public function setIsEmployee($isEmployee){
		$this->isEmployee = $isEmployee;
	}

	public function getDateCreated(){
		return $this->dateCreated;
	}

	public function setDateCreated($dateCreated){
		$this->dateCreated = $dateCreated;
	}

	

}
?>
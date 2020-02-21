<?php
namespace Model;

use Util\IdGenerator;

class VoucherCode {

    private $voucherID;
    private $dateUsed;
    private $userID;
    private $dateCreated;
    
    
    public function __construct($id = null) {
        if ($id == null) {
            $id = IdGenerator::generateSecureUniqueId(8);
            $this->setVoucherID($id);
           
        } else {
            $this->setVoucherID($id);
        }
    }

    /**
     * Getters and Setters
     */

    public function getVoucherID(){
		return $this->voucherID;
	}

	public function setVoucherID($voucherID){
		$this->voucherID = $voucherID;
	}

	public function getDateUsed(){
		return $this->dateUsed;
	}

	public function setDateUsed($dateUsed){
		$this->dateUsed = $dateUsed;
	}

	public function getUserID(){
		return $this->userID;
	}

	public function setUserID($userID){
		$this->userID = $userID;
	}

	public function getDateCreated(){
		return $this->dateCreated;
	}

	public function setDateCreated($dateCreated){
		$this->dateCreated = $dateCreated;
	}

	

}
?>
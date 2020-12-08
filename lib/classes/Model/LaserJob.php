<?php

namespace Model;

use Util\IdGenerator;

/**
 * Data structure representing a 3d job
 */
class LaserJob {
    
    private $laserJobId;
    private $userId;
    private $laserCutterId;
    private $laserCutMaterialId;
    private $quantity;
    private $dbFilename;
    private $dxfFileName;
    private $paymentMethod;
    private $courseGroupId;
    private $voucherCode;
    private $dateCreated;
    private $validCutDate;
    private $userConfirmDate;
    private $completeCutDate;
    private $employeeNotes;
    private $customerNotes;
    private $messageGroupId;
    private $pendingCustomerResponse;
    private $dateUpdate;
    
	

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
            $this->setLaserJobId($id);
			$this->setDateCreated(new \DateTime());
        } else {
            $this->setLaserJobId($id);
        }
    }

    /**
     * Getters and Setters
     */
    
    public function getLaserJobId(){
		return $this->laserJobId;
	}

	public function setLaserJobId($laserJobId){
		$this->laserJobId = $laserJobId;
	}

	public function getUserId(){
		return $this->userId;
	}

	public function setUserId($userId){
		$this->userId = $userId;
	}

	public function getLaserCutterId(){
		return $this->laserCutterId;
	}

	public function setLaserCutterId($laserCutter){
		$this->laserCutterId = $laserCutter->getLaserId();
	}

	public function getLaserCutMaterialId(){
		return $this->laserCutMaterialId;
	}

	public function setLaserCutMaterialId($laserCutMaterial){
		$this->laserCutMaterialId = $laserCutMaterial->getLaserMaterialId();
	}

	public function getQuantity(){
		return $this->quantity;
	}

	public function setQuantity($quantity){
		$this->quantity = $quantity;
	}

	public function getDbFilename(){
		return $this->dbFilename;
	}

	public function setDbFilename($dbFilename){
		$this->dbFilename = $dbFilename;
	}

	public function getDxfFileName(){
		return $this->dxfFileName;
	}

	public function setDxfFileName($dxfFileName){
		$this->dxfFileName = $dxfFileName;
	}

	public function getPaymentMethod(){
		return $this->paymentMethod;
	}

	public function setPaymentMethod($paymentMethod){
		$this->paymentMethod = $paymentMethod;
	}

	public function getCourseGroupId(){
		return $this->courseGroupId;
	}

	public function setCourseGroupId($courseGroupId){
		$this->courseGroupId = $courseGroupId;
	}

	public function getVoucherCode(){
		return $this->voucherCode;
	}

	public function setVoucherCode($voucherCode){
		$this->voucherCode = $voucherCode;
	}

	public function getDateCreated(){
		return $this->dateCreated;
	}

	public function setDateCreated($dateCreated){
		$this->dateCreated = $dateCreated;
	}

	public function getValidCutDate(){
		return $this->validCutDate;
	}

	public function setValidCutDate($validCutDate){
		$this->validCutDate = $validCutDate;
	}

	public function getUserConfirmDate(){
		return $this->userConfirmDate;
	}

	public function setUserConfirmDate($userConfirmDate){
		$this->userConfirmDate = $userConfirmDate;
	}

	public function getCompleteCutDate(){
		return $this->completeCutDate;
	}

	public function setCompleteCutDate($completeCutDate){
		$this->completeCutDate = $completeCutDate;
	}

	public function getEmployeeNotes(){
		return $this->employeeNotes;
	}

	public function setEmployeeNotes($employeeNotes){
		$this->employeeNotes = $employeeNotes;
	}

	public function getCustomerNotes(){
		return $this->customerNotes;
	}

	public function setCustomerNotes($customerNotes){
		$this->customerNotes = $customerNotes;
	}

	public function getMessageGroupId(){
		return $this->messageGroupId;
	}

	public function setMessageGroupId($messageGroupId){
		$this->messageGroupId = $messageGroupId;
	}

	public function getPendingCustomerResponse(){
		return $this->pendingCustomerResponse;
	}

	public function setPendingCustomerResponse($pendingCustomerResponse){
		$this->pendingCustomerResponse = $pendingCustomerResponse;
	}

	public function getDateUpdate(){
		return $this->dateUpdate;
	}

	public function setDateUpdate($dateUpdate){
		$this->dateUpdate = $dateUpdate;
	}
}
?>
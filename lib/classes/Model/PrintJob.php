<?php

namespace Model;

use Util\IdGenerator;

/**
 * Data structure representing a 3d job
 */
class PrintJob {
    
	private $printJobID;
    private $userID;
	private $printerID;
	private $printTypeID;
    private $quantity;
	private $dbFileName;
	private $stlFileName;
	private $paymentMethod;
	private $courseGroupID;
	private $voucherCode;
	private $dateCreated;
	private $validPrintCheck;
	private $userConfirmCheck;
	private $paymentConfirmDate;
	private $completePrintDate;
	private $employeeNotes;
	private $customerNotes;
	private $messageGroupID;
	private $pendingCustomerResponse;
	private $dateUpdated;
	

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
            $this->setPrintJobID($id);
			$this->setDateCreated(new \DateTime());
        } else {
            $this->setPrintJobID($id);
        }
    }

    /**
     * Getters and Setters
     */

	public function getPrintJobID(){
		return $this->printJobID;
	}

	public function setPrintJobID($printJobID){
		$this->printJobID = $printJobID;
	}
	
	public function getUserID(){
		return $this->userID;
	}

	public function setUserID($userID){
		$this->userID = $userID;
	}
	
	public function getPrintTypeID(){
		return $this->printTypeID;
	}

	public function setPrintTypeID($PrintType){
		$this->printTypeID = $PrintType->getPrintTypeID();
	}
	
		
	public function getPrinterId(){
		return $this->printerId;
	}

	public function setPrinterId($Printer){
		$this->printerId = $Printer->getPrinterId();
	}
	
	
	public function getDbFileName(){
		return $this->dbFileName;
	}

	public function setDbFileName($DbFileName){
		$this->dbFileName = $DbFileName;
	}
	
	public function getStlFileName(){
		return $this->stlFileName;
	}

	public function setStlFileName($StlFileName){
		$this->stlFileName = $StlFileName;
	}
	
	public function getPaymentMethod(){
		return $this->paymentMethod;
	}

	public function setPaymentMethod($PaymentMethod){
		$this->paymentMethod = $PaymentMethod;
	}
	
	public function getCourseGroupId(){
		return $this->courseGroupId;
	}

	public function setCourseGroupId($CourseGroupId){
		$this->courseGroupId = $CourseGroupId;
	}
	
	public function getVoucherCode(){
		return $this->voucherCode;
	}

	public function setVoucherCode($VoucherCode){
		$this->voucherCode = $VoucherCode;
	}
	
	public function getDateCreated(){
		return $this->dateCreated;
	}

	public function setDateCreated($DateCreated){
		$this->dateCreated = $DateCreated;
	}
	
	public function getValidPrintCheck(){
		return $this->validPrintCheck;
	}

	public function setValidPrintCheck($ValidPrintCheck){
		$this->validPrintCheck = $ValidPrintCheck;
	}
	
	public function getUserConfirmCheck(){
		return $this->userConfirmCheck;
	}

	public function setUserConfirmCheck($UserConfirmCheck){
		$this->userConfirmCheck = $UserConfirmCheck;
	}

	public function getPaymentDate(){
		return $this->paymentConfirmDate;
	}

	public function setPaymentDate($paymentConfirmDate){
		$this->paymentConfirmDate = $paymentConfirmDate;
	}
	
	public function getCompletePrintDate(){
		return $this->completePrintDate;
	}

	public function setCompletePrintDate($CompletePrintDate){
		$this->completePrintDate = $CompletePrintDate;
	}
	
	public function getEmployeeNotes(){
		return $this->employeeNotes;
	}

	public function setEmployeeNotes($EmployeeNotes){
		$this->employeeNotes = $EmployeeNotes;
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

	public function setMessageGroupId($MessageGroupId){
		$this->messageGroupId = $MessageGroupId;
	}
	
	public function getPendingCustomerResponse(){
		return $this->pendingCustomerResponse;
	}

	public function setPendingCustomerResponse($PendingCustomerResponse){
		$this->pendingCustomerResponse = $PendingCustomerResponse;
	}
	
	public function getDateUpdated(){
		return $this->dateUpdated;
	}

	public function setDateUpdated($DateUpdated){
		$this->dateUpdated = $DateUpdated;
	}

	public function getQuantity(){
		return $this->quantity;
	}

	public function setQuantity($quantity){
		$this->quantity = $quantity;
	}
	
}
?>
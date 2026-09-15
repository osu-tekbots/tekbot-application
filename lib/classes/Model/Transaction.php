<?php
namespace Model;

use Util\IdGenerator;

/**
 * Data structure representing a Transaction
 */
class Transaction {
    const PENDING = 'Pending';
    const SUCCESS = 'Success';
    const CANCELED = 'Canceled';
    
    /** @var string */
    private $transactionId;

    /** @var double */
    private $amount;

    /** @var string */
    private $status;

    /** @var \Model\TransactionDelivery */
    private $deliveryMethod;

    /** @var string */
    private $userId;

    /** @var string */
    private $shippingName;

    /** @var string */
    private $shippingEmail;

    /** @var string */
    private $shippingPhone;

    /** @var string */
    private $shippingAddress1;

    /** @var string */
    private $shippingAddress2;

    /** @var string */
    private $shippingCity;

    /** @var string */
    private $shippingState;

    /** @var string */
    private $shippingCountry;

    /** @var string */
    private $shippingCode;

    /** @var string|null */
    private $tpgTransId;

    /**
     * AKA "order number"; included on Touchnet receipt
     * @var string|null
     */
    private $sysTrackingId;

    /** @var string|null */
    private $cardType;

    /** @var string */
    private $employeeNotes;

    /** @var \DateTime|null */
    private $datePaid;

    /** @var \DateTime|null */
    private $dateFulfilled;

    /** @var \DateTime */
    private $dateCreated;

    /** @var \DateTime */
    private $dateUpdated;

    /**
     *  Creates new instance of a Transaction
     * 
     *  @param string|null $id the ID of the transaction. If null, a random ID will be generated.
     */
    public function __construct($id = null) {
        if ($id == null) {
			$id = IdGenerator::generateSecureUniqueId();
            $this->setStatus(self::PENDING);
            $this->setDateCreated(new \DateTime);
            $this->setDateUpdated(new \DateTime);
        }

        $this->setTransactionId($id);
    }
    

    /**
     * Getters and Setters
     */
    public function getTransactionId() {
        return $this->transactionId;
    }

    public function setTransactionId($transactionId) {
        $this->transactionId = $transactionId;
    }

    public function getAmount() {
        return $this->amount;
    }

    public function setAmount($amount) {
        $this->amount = $amount;
    }

    public function getStatus() {
        return $this->status;
    }

    public function setStatus($status) {
        $this->status = $status;
    }

    public function getDeliveryMethod() {
        return $this->deliveryMethod;
    }

    public function setDeliveryMethod($deliveryMethod) {
        $this->deliveryMethod = $deliveryMethod;
    }

    public function getUserId() {
        return $this->userId;
    }

    public function setUserId($userId) {
        $this->userId = $userId;
    }

    public function getShippingName() {
        return $this->shippingName;
    }

    public function setShippingName($shippingName) {
        $this->shippingName = $shippingName;
    }

    public function getShippingEmail() {
        return $this->shippingEmail;
    }

    public function setShippingEmail($shippingEmail) {
        $this->shippingEmail = $shippingEmail;
    }

    public function getShippingPhone() {
        return $this->shippingPhone;
    }

    public function setShippingPhone($shippingPhone) {
        $this->shippingPhone = $shippingPhone;
    }

    public function getShippingAddress1() {
        return $this->shippingAddress1;
    }

    public function setShippingAddress1($shippingAddress1) {
        $this->shippingAddress1 = $shippingAddress1;
    }

    public function getShippingAddress2() {
        return $this->shippingAddress2;
    }

    public function setShippingAddress2($shippingAddress2) {
        $this->shippingAddress2 = $shippingAddress2;
    }

    public function getShippingCity() {
        return $this->shippingCity;
    }

    public function setShippingCity($shippingCity) {
        $this->shippingCity = $shippingCity;
    }

    public function getShippingState() {
        return $this->shippingState;
    }

    public function setShippingState($shippingState) {
        $this->shippingState = $shippingState;
    }

    public function getShippingCountry() {
        return $this->shippingCountry;
    }

    public function setShippingCountry($shippingCountry) {
        $this->shippingCountry = $shippingCountry;
    }

    public function getShippingCode() {
        return $this->shippingCode;
    }

    public function setShippingCode($shippingCode) {
        $this->shippingCode = $shippingCode;
    }

    public function getTpgTransId() {
        return $this->tpgTransId;
    }

    public function setTpgTransId($tpgTransId) {
        $this->tpgTransId = $tpgTransId;
    }

    public function getSysTrackingId() {
        return $this->sysTrackingId;
    }

    public function setSysTrackingId($sysTrackingId) {
        $this->sysTrackingId = $sysTrackingId;
    }

    public function getCardType() {
        return $this->cardType;
    }

    public function setCardType($cardType) {
        $this->cardType = $cardType;
    }

    public function getEmployeeNotes() {
        return $this->employeeNotes;
    }

    public function setEmployeeNotes($employeeNotes) {
        $this->employeeNotes = $employeeNotes;
    }

    public function getDatePaid() {
        return $this->datePaid;
    }

    public function setDatePaid($datePaid) {
        $this->datePaid = $datePaid;
    }

    public function getDateFulfilled() {
        return $this->dateFulfilled;
    }

    public function setDateFulfilled($dateFulfilled) {
        $this->dateFulfilled = $dateFulfilled;
    }

    public function getDateCreated() {
        return $this->dateCreated;
    }

    public function setDateCreated($dateCreated) {
        $this->dateCreated = $dateCreated;
    }

    public function getDateUpdated() {
        return $this->dateUpdated;
    }

    public function setDateUpdated($dateUpdated) {
        $this->dateUpdated = $dateUpdated;
    }
}

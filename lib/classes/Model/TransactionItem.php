<?php
namespace Model;

use Util\IdGenerator;

/**
 * Data structure representing a TransactionItem
 */
class TransactionItem {

    /** @var int */
    private $itemId;

    /** @var string */
    private $transactionId;

    /** @var string */
    private $type;

    /** @var string */
    private $name;

    /** @var string */
    private $stocknumber;

    /** @var double */
    private $price;

    /** @var int */
    private $quantity;

    /** @var int */
    private $quantityRefunded;

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
            $this->setQuantityRefunded(0);
            $this->setDateCreated(new \DateTime);
            $this->setDateUpdated(new \DateTime);
        }

        $this->setItemId($id);
    }
    

    /**
     * Getters and Setters
     */
    public function getItemId() {
        return $this->itemId;
    }

    public function setItemId($itemId) {
        $this->itemId = $itemId;
    }

    public function getTransactionId() {
        return $this->transactionId;
    }

    public function setTransactionId($transactionId) {
        $this->transactionId = $transactionId;
    }

    public function getPrice() {
        return $this->price;
    }

    public function setPrice($price) {
        $this->price = $price;
    }

    public function getType() {
        return $this->type;
    }

    public function setType($type) {
        $this->type = $type;
    }

    public function getName() {
        return $this->name;
    }

    public function setName($name) {
        $this->name = $name;
    }

    public function getStocknumber() {
        return $this->stocknumber;
    }

    public function setStocknumber($stocknumber) {
        $this->stocknumber = $stocknumber;
    }

    public function getQuantity() {
        return $this->quantity;
    }

    public function setQuantity($quantity) {
        $this->quantity = $quantity;
    }

    public function getQuantityRefunded() {
        return $this->quantityRefunded;
    }

    public function setQuantityRefunded($quantityRefunded) {
        $this->quantityRefunded = $quantityRefunded;
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

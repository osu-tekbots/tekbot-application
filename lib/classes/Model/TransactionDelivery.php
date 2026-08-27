<?php
namespace Model;

use Util\IdGenerator;

/**
 * Data structure representing a TransactionDelivery
 */
class TransactionDelivery {
    const PICKUP = 0;


    /** @var int */
    private $id;

    /** @var string */
    private $name;

    /** @var boolean */
    private $isDefault;

    /** @var double */
    private $baseCost;

    /** @var double */
    private $itemCost;


    /**
     *  Creates new instance of a Transaction
     * 
     *  @param string|null $id the ID of the transaction. If null, a random ID will be generated.
     */
    public function __construct($id = null) {
        if ($id == null) {
			$id = self::PICKUP;
        }

        $this->setId($id);
    }
    

    /**
     * Getters and Setters
     */
    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function getName() {
        return $this->name;
    }

    public function setName($name) {
        $this->name = $name;
    }

    public function getIsDefault() {
        return $this->isDefault;
    }

    public function setIsDefault($isDefault) {
        $this->isDefault = $isDefault;
    }

    public function getBaseCost() {
        return $this->baseCost;
    }

    public function setBaseCost($baseCost) {
        $this->baseCost = $baseCost;
    }

    public function getItemCost() {
        return $this->itemCost;
    }

    public function setItemCost($itemCost) {
        $this->itemCost = $itemCost;
    }
}

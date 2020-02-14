<?php
namespace Model;
// Updated 11/5/2019
/**
 * Data class representing an contract type enumeration
 */
class ContractType {
    const EQUIPMENT_CHECKOUT = 1;
    const LOCKER_CHECKOUT = 2;

    /** @var integer */
    private $id;
    
    /** @var  string */
    private $name;

    /**
     * Constructs a new instance of a contractType.
     *
     * @param integer $id the ID of the contractType. This should come directly from the database.
     * @param string $name the name associated with the contractType
     */
    public function __construct($id = null, $name = null) {
        if ($id == null && $name == null) {
            $this->setId(self::EQUIPMENT_CHECKOUT);
            $this->setName('Equipment Checkout');
        } else {
            $this->setId($id);
            $this->setName($name);
        }
    }
    

    /**
     * Get the value of id
     */ 
    public function getId() {
        return $this->id;
    }

    /**
     * Set the value of id
     *
     * @return  self
     */ 
    public function setId($id) {
        $this->id = $id;

        return $this;
    }

    /**
     * Get the value of name
     */ 
    public function getName() {
        return $this->name;
    }

    /**
     * Set the value of name
     *
     * @return  self
     */ 
    public function setName($name) {
        $this->name = $name;

        return $this;
    }
}
?>
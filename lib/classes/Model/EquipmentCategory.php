<?php
namespace Model;

/**
 * Data class representing an TekbotEquipmentCategory enumeration
 */
class EquipmentCategory {
    const HARDWARE = 1;
    const ELECTRICAL = 2;
    const CHEMICAL = 3;

    /** @var integer */
    private $id;
    
    /** @var  string */
    private $name;

    /**
     * Constructs a new instance of a TekbotEquipmentCategory.
     *
     * @param integer $id the ID of the TekbotEquipmentCategory. This should come directly from the database.
     * @param string $name the name associated with the TekbotEquipmentCategory
     */
    public function __construct($id = null, $name = null) {
        if ($id == null && $name == null) {
            $this->setId(self::HARDWARE);
            $this->setName('HARDWARE');
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
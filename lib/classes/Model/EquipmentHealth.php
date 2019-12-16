<?php
namespace Model;

/**
 * Data class representing an TekbotEquipmentHealth enumeration
 */
class EquipmentHealth {
    const FULLY_FUNCTIONAL = 1;
    const PARTIAL_FUNCTIONALITY = 2;
    const BROKEN = 3;

    /** @var integer */
    private $id;
    
    /** @var  string */
    private $name;

    /**
     * Constructs a new instance of a TekbotEquipmentHealth.
     *
     * @param integer $id the ID of the TekbotEquipmentHealth. This should come directly from the database.
     * @param string $name the name associated with the TekbotEquipmentHealth
     */
    public function __construct($id = null, $name = null) {
        if ($id == null && $name == null) {
            $this->setId(self::FULLY_FUNCTIONAL);
            $this->setName('FULLY_FUNCTIONAL');
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
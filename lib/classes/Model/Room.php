<?php

namespace Model;
use Util\IdGenerator;

/**
 * Data structure representing a Room
 */
class Room {

    /** @var int */
    private $id;

    /** @var int */
    private $name;

    /** @var string */
    private $map;

        /**
     *  Creates new instance of a Room
     * 
     *  @param string $
     *  if param = null generate ID
     */

    public function __construct($roomId = NULL) {
        if ($roomId != null) {
            $this->setId($roomId);
        }
    }
    

    /**
     * Getters and Setters
     */

    public function getId() {
        return $this->id;
    }

    public function setId($data) {
        $this->id = $data;
    }

    public function getName() {
        return $this->name;
    }

    public function setName($data) {
        $this->name = $data;
    }

    public function getMap() {
        return $this->map;
    }

    public function setMap($data) {
        $this->map = $data;
    }
}
?>
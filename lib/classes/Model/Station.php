<?php

namespace Model;
use Util\IdGenerator;
use Model\Room;

/**
 * Data structure representing a Station
 */
class Station {

    /** @var int */
    private $id;

    /** @var int */
    private $name;

    /** @var int */
    private $roomId;

    /** @var string */
	private $image;

    /** @var Model\Room */
    private $room;
    

    /**
     *  Creates new instance of a Station
     * 
     *  @param string $station ID
     *  if param = null generate ID
     */

    public function __construct($stationId = null) {
        if ($stationId == null) {
            $this->setId($stationId);
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

    public function getRoomId() {
        return $this->roomId;
    }

    public function setRoomId($data) {
        $this->roomId = $data;
    }

    public function getImage() {
        return $this->image;
    }

    public function setImage($data) {
        $this->image = $data;
    }
    public function getRoom() {
        return $this->room;
    }

    public function setRoom($data) {
        $this->room = $data;
    }

}
?>
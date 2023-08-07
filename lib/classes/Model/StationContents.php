<?php

namespace Model;
use Util\IdGenerator;

/**
 * Data structure representing a Station
 */
class StationContents {

    /** @var int */
    private $id;

    /** @var int */
    private $stationId;

    /** @var int */
    private $equipmentId;

    /** @var int */
    private $invid;

    /** @var int */
    private $status;

    /** @var string */
    private $comment;

        /**
     *  Creates new instance of a Station
     * 
     *  @param string $station ID
     *  if param = null generate ID
     */

    public function __construct($stationId) {
        if ($stationId == null) {
			$StationId = IdGenerator::generateSecureUniqueId(6); //Only want 8 characters
            $this->setId($stationId);   
        } else {
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

    public function getStationId() {
        return $this->stationId;
    }

    public function setStationId($data) {
        $this->stationId = $data;
    }

    public function getEquipmentId() {
        return $this->equipmentId;
    }

    public function setEquipmentId($data) {
        $this->equipmentId = $data;
    }

    public function getInvid() {
        return $this->invid;
    }

    public function setInvid($data) {
        $this->invid = $data;
    }

    public function getStatus() {
        return $this->status;
    }

    public function setStatus($data) {
        $this->status = $data;
    }

    public function getComment() {
        return $this->comment;
    }

    public function setComment($data) {
        $this->comment = $data;
    }
}
?>
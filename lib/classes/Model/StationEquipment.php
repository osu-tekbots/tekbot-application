<?php

namespace Model;
use Util\IdGenerator;

/**
 * Data structure representing a Station
 */
class StationEquipment {

    /** @var int */
    private $id;

    /** @var string */
    private $model;

    /** @var string */
    private $type;

    /** @var string */
    private $manual;

    /** @var string */
	private $image;
    

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

    public function getModel() {
        return $this->model;
    }

    public function setModel($data) {
        $this->model = $data;
    }

    public function getType() {
        return $this->type;
    }

    public function setType($data) {
        $this->type = $data;
    }

    public function getManual() {
        return $this->manual;
    }

    public function setManual($data) {
        $this->manual = $data;
    }

    public function getImage() {
        return $this->image;
    }

    public function setImage($data) {
        $this->image = $data;
    }

}
?>
<?php

namespace Model;

use Model\Printer;

/**
 * Data structure representing a Print Type
 */
class PrintType {
    
	private $printTypeId;
	private $printTypeName;
	private $printerId;
	private $headSize;
	private $precision;
	private $buildPlateSize;
	private $costPerGram;
	private $description;

    /**
     * Creates a new instance of an equipment reservation.
     * 
     *
     * @param string|null $id the ID of the reservation. If null, a random ID will be generated.
    */
    public function __construct($id = null) {
        if ($id == null) {
 
        } else {
            $this->setPrintTypeId($id);
        }
    }

    /**
     * Getters and Setters
     */
	public function getPrintTypeId(){
		return $this->printTypeId;
	}

	public function setPrintTypeId($PrintTypeId){
		$this->printTypeId = $PrintTypeId;
	}
	public function getPrintTypeName(){
		return $this->printTypeName;
	}

	public function setPrintTypeName($PrintTypeName){
		$this->printTypeName = $PrintTypeName;
	}
	
	public function getPrinterId(){
		return $this->printerId;
	}

	public function setPrinterId($Printer){
		$this->printerId = $Printer->getPrinterId();
	}
	
	public function getHeadSize(){
		return $this->headSize;
	}

	public function setHeadSize($HeadSize){
		$this->headSize = $HeadSize;
	}
	public function getPrecision(){
		return $this->precision;
	}

	public function setPrecision($Precision){
		$this->precision = $Precision;
	}
	
	public function getBuildPlateSize(){
		return $this->buildPlateSize;
	}

	public function setBuildPlateSize($BuildPlateSize){
		$this->buildPlateSize = $BuildPlateSize;
	}
	
	public function getCostPerGram(){
		return $this->costPerGram;
	}

	public function setCostPerGram($CostPerGram){
		$this->costPerGram = $CostPerGram;
	}

	public function getDescription(){
		return $this->description;
	}

	public function setDescription($description){
		$this->description = $description;
	}
	
}
?>
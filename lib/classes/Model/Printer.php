<?php

namespace Model;

/**
 * Data structure representing a Printer
 */
class Printer {
    
	private $printerId;
	private $printerName;
	private $description;
	private $location;
	

    /**
     * Creates a new instance of an equipment reservation.
     * 
     *
     * @param string|null $id the ID of the reservation. If null, a random ID will be generated.
    */
    public function __construct($id = null) {
        if ($id == null) {
 
        } else {
            $this->setPrinterId($id);
        }
    } 

    /**
     * Getters and Setters
     */
	public function getPrinterId(){
		return $this->printerId;
	}

	public function setPrinterId($printerId){
		$this->printerId = $printerId;
	}

	public function getPrinterName(){
		return $this->printerName;
	}

	public function setPrinterName($printerName){
		$this->printerName = $printerName;
	}

	public function getDescription(){
		return $this->description;
	}

	public function setDescription($description){
		$this->description = $description;
	}

	public function getLocation(){
		return $this->location;
	}

	public function setLocation($location){
		$this->location = $location;
	}
}
?>
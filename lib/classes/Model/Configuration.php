<?php

namespace Model;

use \DataAccess\QueryUtils;

/**
 * Data structure representing a Locker
 */
class Configuration {
    
	/** @var int */
	private $id;
	/** @var string */
	private $lastCronEmailTime;
		
    /**
     * Creates a new instance of the configuration.
     * 
     *
     * @param string|null $id the ID of the configuration.
    */
    public function __construct($boxKey = null) {
        $this->setId($boxKey);
    } 

    /**
     * Getters and Setters
     */
	public function getId(){
		return $this->id;
	}

	public function setId($data){
		$this->id = $data;
	}

	public function getLastCronEmailTime(){
		return $this->lastCronEmailTime;
	}

	public function setLastCronEmailTime($data){

        if(gettype($data) == 'string')
            $this->lastCronEmailTime = $data;
        else if(is_a($data, 'DateTime'))
            $this->lastCronEmailTime = QueryUtils::FormatDate($data);
	}
}
?>
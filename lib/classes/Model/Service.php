<?php
namespace Model;

class Service {

    private $serviceID;
    private $serviceName;

    public function __construct($id = null) {
        if ($id == null) {

        } else {
            $this->setServiceID($id);
        }
    }

    /**
     * Getters and Setters
     */

	public function getServiceID() {
		return $this->serviceID;
	}

	public function setServiceID($serviceID) {
		$this->serviceID = $serviceID;
    }	
    
    public function getServiceName() {
		return $this->serviceName;
	}

	public function setServiceName($serviceName) {
		$this->serviceName = $serviceName;
	}	
	

}
?>
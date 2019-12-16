<?php
// Updated 11/5/2019
namespace Model;

use Util\IdGenerator;

/**
 * Data structure representing a kit enrollment
 */
class KitEnrollment {
    
    /** @var int */
    private $kitEnrollmentID;

    /** @var string */
    private $firstMiddleLastName;

	/** @var string */
	private $osuID;

    /** @var string */
    private $onid;

    /** @var string */
	private $courseCode;
	
	/** @var string */
	private $termID;

    /** @var int */
    private $kitStatusID;

    /** @var \DateTime */
    private $dateUpdated;

    /** @var \DateTime */
    private $dateCreated;

    /**
     * Creates a new instance of an equipment reservation.
     * 
     * If an ID is provided, defaults will not be set. If an ID is not provided, a new ID will be generated and
     * defaults will be set.
     *
     * @param string|null $id the ID of the reservation. If null, a random ID will be generated.
    */
    public function __construct($id = null) {
        if ($id == null) {
            $id = IdGenerator::generateSecureUniqueId();
			$this->setKitEnrollmentID($id);
			$this->setKitStatus($this->getStatusID(new KitEnrollmentStatus()))
			$this->setDateCreated(new \DateTime());
        } else {
            $this->setKitEnrollmentID($id);
        }
    }

    /**
     * Getters and Setters
     */

	public function getKitEnrollmentID(){
		return $this->kitEnrollmentID;
	}

	public function setKitEnrollmentID($kitEnrollmentID){
		$this->kitEnrollmentID = $kitEnrollmentID;
	}

	public function getFirstMiddleLastName(){
		return $this->firstMiddleLastName;
	}

	public function setFirstMiddleLastName($firstMiddleLastName){
		$this->firstMiddleLastName = $firstMiddleLastName;
	}

	public function getOsuID(){
		return $this->osuID;
	}

	public function setOsuID($osuID){
		$this->osuID = $osuID;
	}

	public function getOnid(){
		return $this->onid;
	}

	public function setOnid($onid){
		$this->onid = $onid;
	}

	public function getCourseCode(){
		return $this->courseCode;
	}

	public function setCourseCode($courseCode){
		$this->courseCode = $courseCode;
	}

	public function getTermID(){
		return $this->termID;
	}

	public function setTermID($termID){
		$this->termID = $termID;
	}

	public function getKitStatusID(){
		return $this->kitStatusID;
	}

	public function setKitStatusID($kitStatusID){
		$this->kitStatusID = $kitStatusID;
	}

	public function getDateUpdated(){
		return $this->dateUpdated;
	}

	public function setDateUpdated($dateUpdated){
		$this->dateUpdated = $dateUpdated;
	}

	public function getDateCreated(){
		return $this->dateCreated;
	}


}
?>
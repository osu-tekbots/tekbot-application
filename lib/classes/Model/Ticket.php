<?php

namespace Model;
use Util\IdGenerator;

/**
 * Data structure representing a Ticket
 */
class Ticket {
    
    /** @var int */
    private $id;

    /** @var int */
    private $stationId;

    /** @var string */
    private $image;

    /** @var string */
    private $issue;

    /** @var string */
    private $email;

    /** @var string */
    private $comment;

    /** @var string */
    private $response;

    /** @var int */
    private $status;

    /** @var Date/Time */
    private $created;

    /** @var Date/Time */
    private $resolved;

    /** @var int */
    private $isEscalated;

    /** @var string */
    private $escalatedComments;

    /** @var string */
    private $room;

    /** @var int */
    private $deskNumber;

    /**
     *  Creates new instance of a Ticket
     * 
     *  @param string $Id 
     *  if param = null generate ID
     */

    public function __construct($id = null) {
        if ($id == null) {
            $this->setId($id);
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

    public function getImage() {
        return $this->image;
    }

    public function setImage($data) {
        $this->image = $data;
    }

    public function getIssue() {
        return $this->issue;
    }

    public function setIssue($data) {
        $this->issue = $data;
    }

    public function getEmail() {
        return $this->email;
    }

    public function setEmail($data) {
        $this->email = $data;
    }

    public function getComment() {
        return $this->comment;
    }

    public function setComment($data) {
        $this->comment = $data;
    }

    public function getResponse() {
        return $this->response;
    }

    public function setResponse($data) {
        $this->response = $data;
    }

    public function getStatus() {
        return $this->status;
    }

    public function setStatus($data) {
        $this->status = $data;
    }

    public function getCreated() {
        return $this->created;
    }

    public function setCreated($data) {
        $this->created = $data;
    }

    public function getResolved() {
        return $this->resolved;
    }

    public function setResolved($data) {
        $this->resolved = $data;
    }

   public function getIsEscalated() {
        return $this->isEscalated;
    }

    public function setIsEscalated($data) {
         $this->isEscalated = $data;
    }

    public function getEscalatedComments() {
        return $this->escalatedComments;
    }

    public function setEscalatedComments($data) {
        $this->escalatedComments = $data;
    }

    // public function getRoom() {
    //     return $this->room;
    // }

    // public function setRoom($data) {
    //      $this->room = $data;
    // }

    // public function getDeskNumber() {
    //     return $this->deskNumber;
    // }

    // public function setDeskNumber($data) {
    //      $this->deskNumber = $data;
    // }

}
?>
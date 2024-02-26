<?php

namespace Model;
use Util\IdGenerator;

/**
 * Data structure representing a Task
 */
class Task {
    
    /** @var int */
    private $id;

    /** @var int */
    private $description;

    /** @var \DateTime */
    private $created;

    /** @var \DateTime */
    private $completed;

    /** @var string */
    private $creator;

    /** @var string */
    private $completer;

    /** @var bool */
    private $urgent;

    /**
     *  Creates new instance of a Task
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

    public function getDescription() {
        return $this->description;
    }

    public function setDescription($data) {
        $this->description = $data;
    }

    public function getCreated() {
        return $this->created;
    }

    public function setCreated($data) {
        $this->created = $data;
    }

    public function getCompleted() {
        return $this->completed;
    }

    public function setCompleted($data) {
        $this->completed = $data;
    }

    public function getCreator() {
        return $this->creator;
    }

    public function setCreator($data) {
        $this->creator = $data;
    }

    public function getCompleter() {
        return $this->completer;
    }

    public function setCompleter($data) {
        $this->completer = $data;
    }

    public function getUrgent() {
        return $this->urgent;
    }

    public function setUrgent($data) {
        $this->urgent = $data;
    }
}
?>
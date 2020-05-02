<?php

namespace Model;

/**
 * Data structure representing a Locker
 */
class Message {
    
	/** @var string */
	private $message_id;
	
	/** @var string */
	private $subject;
	
	/** @var string */
	private $body;
	
	/** @var int */
	private $format;	

    /**
     * Creates a new instance of an equipment reservation.
     * 
     *
     * @param string|null $id the ID of the reservation. If null, a random ID will be generated.
    */
    public function __construct($id = null) {
        if ($id == null) {
			$id = IdGenerator::generateSecureUniqueId();
            $this->setMessageId($id);   
        } else {
            $this->setMessageId($id);
        }
    } 

    /**
     * Getters and Setters
     */
	public function getMessageId(){
		return $this->message_id;
	}

	public function setMessageId($data){
		$this->message_id = $data;
	}

	public function getSubject(){
		return $this->subject;
	}

	public function setSubject($data){
		$this->subject = $data;
	}
	
	public function getBody(){
		return $this->body;
	}

	public function setBody($data){
		$this->body = $data;
	}
	
	public function getFormat(){
		return $this->format;
	}
	
	public function setFormat($data){
		$this->format = $data;
	}
	
	
	/**
     * Accepts an array of keywords to replace into $body assuming it tis a template with patterns like {{replace_me}}
     * 
     *
     * @param string|null $keywords the array of words to be replaced. Words to be replaced are keys in array.
    */
	public function fillTemplateBody($keywords){
		$result = $this->body;
		if ($keywords != '')
			foreach ($keywords as $k => $v) {	
				$result =  str_replace('{{' . $k . '}}', $v , $result);
			}
		return $result;
	}
	
	/**
     * Accepts an array of keywords to replace into $subject assuming it tis a template with patterns like {{replace_me}}
     * 
     *
     * @param string|null $keywords the array of words to be replaced. Words to be replaced are keys in array.
    */
	public function fillTemplateSubject($keywords){
		$result = $this->subject;
		if ($keywords != '')
			foreach ($keywords as $k => $v) {	
				$result =  str_replace('{{' . $k . '}}', $v , $result);
			}
		return $result;
	}
}
?>
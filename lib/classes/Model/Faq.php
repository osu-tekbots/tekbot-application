<?php
namespace Model;
// Updated 11/5/2019
use Util\IdGenerator;

/**
 * Data structure representing equipment fees
 */
class Faq {

    /** @var int */
    private $faqID;
	
	/** @var string */
	private $category;
	
    /** @var string */
    private $question;

    /** @var string */
    private $answer;

     /**
     * Creates a new instance of an FAQ.
     * 
     * If an ID is provided, defaults will not be set. If an ID is not provided, a new ID will be generated and
     * defaults will be set.
     *
     * @param string|null $id the ID of the fee. If null, a random ID will be generated.
    */
    public function __construct($id = null) {
        if ($id != null){
            $this->setFaqID($id);
        }
    }

    /**
     * Getters and Setters
     */

	public function getFaqID(){
		return $this->faqID;
	}

	public function setFaqID($faqID){
		$this->faqID = $faqID;
	}

	public function getCategory(){
		return $this->category;
	}

	public function setCategory($category){
		$this->category = $category;
	}

	public function getQuestion(){
		return $this->question;
	}

	public function setQuestion($question){
		$this->question = $question;
	}

	public function getAnswer(){
		return $this->answer;
	}

	public function setAnswer($answer){
		$this->answer = $answer;
	}


  
}
?>
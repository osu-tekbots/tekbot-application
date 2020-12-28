<?php
namespace Email;

use Util\Security;
use DataAccess\QueryUtils;
use DataAccess\UserDao;

class TekBotsMailer extends Mailer {
    /**
     * Constructs a new instance of a mailer specifically for capstone project-related emails
     *
     * @param string $from the from address for emails
     * @param string|null $subjectTag an optional subject tag to prefix the provided subject tag with
     */
    public function __construct($from, $subjectTag = null) {
        parent::__construct($from, $subjectTag);
    }

    public function sendLockerEmail($user, $locker, $message) {
        $replacements = Array();
		
		$replacements['email'] = Security::HtmlEntitiesEncode($user->getEmail());
		$replacements['name'] = Security::HtmlEntitiesEncode($user->getFirstName() . " " . $user->getLastName());
		$replacements['lockernumber'] = $locker->getLockerNumber();
		
		$subject = $message->fillTemplateSubject($replacements);
		$body = $message->fillTemplateBody($replacements);

        return $this->sendEmail($replacements['email'], $subject, $body, true);
    }
	
	public function sendBoxEmail($user, $box, $message) {
        $replacements = Array();
		
		$replacements['email'] = Security::HtmlEntitiesEncode($user->getEmail());
		$replacements['name'] = Security::HtmlEntitiesEncode($user->getFirstName() . " " . $user->getLastName());
		$replacements['number'] = $box->getNumber();
		$replacements['contents'] = $box->getContents();
		$replacements['filldate'] = date('m/d/y', strtotime($box->getFillDate()));
		
		$subject = $message->fillTemplateSubject($replacements);
		$body = $message->fillTemplateBody($replacements);

        return $this->sendEmail($replacements['email'], $subject, $body, true);
    }
	
	public function sendPrinterEmail($user, $job, $message) {
        $replacements = Array();
		
		$replacements['email'] = Security::HtmlEntitiesEncode($user->getEmail());
		$replacements['name'] = Security::HtmlEntitiesEncode($user->getFirstName() . " " . $user->getLastName());
		$replacements['printJobID'] = $job->getPrintJobID();
		
		
		
		
		
		
		$subject = $message->fillTemplateSubject($replacements);
		$body = $message->fillTemplateBody($replacements);

        return $this->sendEmail($replacements['email'], $subject, $body);
    }

    
}

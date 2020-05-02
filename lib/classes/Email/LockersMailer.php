<?php
namespace Email;

use Util\Security;
use DataAccess\QueryUtils;
use DataAccess\UserDao;

class LockersMailer extends Mailer {
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

        return $this->sendEmail($replacements['email'], $subject, $body);
    }

    
}

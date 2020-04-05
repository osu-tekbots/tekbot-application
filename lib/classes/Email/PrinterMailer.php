<?php
namespace Email;

use Util\Security;
use DataAccess\QueryUtils;

class PrinterMailer extends Mailer {
    /**
     * Constructs a new instance of a mailer specifically for capstone project-related emails
     *
     * @param string $from the from address for emails
     * @param string|null $subjectTag an optional subject tag to prefix the provided subject tag with
     */
    public function __construct($from, $subjectTag = null) {
        parent::__construct($from, $subjectTag);
    }

    public function sendPrintConfirmationEmail($user, $printJob, $link) {
        $userName = Security::HtmlEntitiesEncode($user->getFirstName()) 
        . ' ' 
		. Security::HtmlEntitiesEncode($user->getLastName());

        $email = Security::HtmlEntitiesEncode($user->getEmail());


        $subject = "Confirm your 3D Print Submission";


        $message = "
        Dear $userName,

        We have received your print and have verified that we will be able to print. (MORE DETAILS WILL BE INSERTED).

        Confirmation link: $link
        
        ";

        return $this->sendEmail($email, $subject, $message);
    }

    public function sendPrintCompleteEmail($user, $printJob) {
        $userName = Security::HtmlEntitiesEncode($user->getFirstName()) 
        . ' ' 
		. Security::HtmlEntitiesEncode($user->getLastName());

        $email = Security::HtmlEntitiesEncode($user->getEmail());

        $stlFileName = $printJob->getStlFileName();

        $subject = "Your 3D Print is ready for pickup";


        $message = "
        Dear $userName,

        We have completed your print: $stlFileName. You may come pick it up during our store hours.
        
        ";

        return $this->sendEmail($email, $subject, $message);
    }
}

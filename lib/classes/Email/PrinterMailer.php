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

        $stlFileName = $printJob->getStlFileName();

        $subject = "Confirm your 3D Print Submission";


        $message = "
        Dear $userName,

        We have received 3D print submission file ($stlFileName) and our staff will be able to print after your payment and confirmation.
        
        If you submitted this print under a course, then you just need to click the verification link below. Otherwise, please visit ___,
        submit a payment, and then click the verification link below.

        (MORE DETAILS WILL BE INSERTED LATER).

        Confirmation link: $link
        
        Sincerely,

        TekBots 
        Oregon State University
        KEC 1110 (Hours: 10AM - 1PM)

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
        
        Sincerely,

        TekBots 
        Oregon State University
        KEC 1110 (Hours: 10AM - 1PM)
        ";

        return $this->sendEmail($email, $subject, $message);
    }
}

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
	 * @param string $bounceAddress the email address to direct notices about emails that bounced to
     * @param string|null $subjectTag an optional subject tag to prefix the provided subject tag with
	 * @param \Util\Logger|null $logger an optional logger to capture error messages from the mail() function
     */
    public function __construct($from, $bounceEmail, $subjectTag = null, $logger = null) {
        parent::__construct($from, $bounceEmail, $subjectTag, $logger);
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
	
	public function sendEquipmentEmail($user, $checkout, $equipment, $message) {
        $replacements = Array();
		
		$replacements['email'] = Security::HtmlEntitiesEncode($user->getEmail());
		$replacements['name'] = Security::HtmlEntitiesEncode($user->getFirstName() . " " . $user->getLastName());
		$replacements['equipname'] = Security::HtmlEntitiesEncode($equipment->getEquipmentName());
		$replacements['equipid'] = Security::HtmlEntitiesEncode($equipment->getEquipmentID());
		if ($checkout != null){
			$temp = $checkout->getPickupTime();
			if (is_string($temp))
				$replacements['pickupDate'] = date('D, m/d/y', strtotime($temp));
			else
				$replacements['pickupDate'] = get_class($temp);

			//I have no idea why this comes back as a string. Everything points to this being a DateTime but it errors if I treat the return value that way.
			// Patch to use strtotime instead.
			$temp = $checkout->getDeadlineTime();
			if (is_string($temp))
				$replacements['deadlineDate'] = date('D, m/d/y', strtotime($temp));
			else
				$replacements['deadlineDate'] = $temp->format('D, m/d/y');
		} else {
			$replacements['pickupDate'] = '<i>&lt;Undefined&gt;</i>';
			$replacements['deadlineDate'] = '<i>&lt;Undefined&gt;</i>';
		}
				
		$subject = $message->fillTemplateSubject($replacements);
		$body = $message->fillTemplateBody($replacements);

        return $this->sendEmail($replacements['email'], $subject, $body, true);
    }
	
	public function sendPrinterEmail($user, $printJob, $printType, $message) {
        $replacements = Array();
		
		$replacements['email'] = Security::HtmlEntitiesEncode($user->getEmail());
		$replacements['name'] = Security::HtmlEntitiesEncode($user->getFirstName() . " " . $user->getLastName());
		$replacements['printJobID'] = $printJob->getPrintJobID();
		$replacements['filename'] = Security::HtmlEntitiesEncode($printJob->getStlFileName());
		// Check type before number_format b/c sendTestMessage() inserts string & causes failure
		$replacements['costpergram'] = gettype($printType->getCostPerGram()) == 'double' ? number_format($printType->getCostPerGram(),2) : '<i>&lt;test&gt;</i>';
		$replacements['grams'] = $printJob->getMaterialAmount();
		$replacements['quantity'] = $printJob->getQuantity();
		$replacements['totalcost'] = gettype($printJob->getTotalPrice()) == 'double' ? number_format($printJob->getTotalPrice(),2) : '<i>&lt;test&gt;</i>';
		$replacements['paymentMethod'] = $printJob->getPaymentMethod();
		
		$subject = $message->fillTemplateSubject($replacements);
		$body = $message->fillTemplateBody($replacements);

        return $this->sendEmail($replacements['email'], $subject, $body, true);
    }
	
	public function sendLaserEmail($user, $laserJob, $laserMaterial, $message) {
        $replacements = Array();
		
		$replacements['email'] = Security::HtmlEntitiesEncode($user->getEmail());
		$replacements['name'] = Security::HtmlEntitiesEncode($user->getFirstName() . " " . $user->getLastName());
		$replacements['laserJobId'] = $laserJob->getLaserJobId();
		$replacements['filename'] = Security::HtmlEntitiesEncode($laserJob->getDxfFileName());
		$replacements['material'] = $laserMaterial->getDescription();
		$replacements['quantity'] = $laserJob->getQuantity();
		// Check type before number_format b/c sendTestMessage() inserts string & causes failure
		$replacements['materialCost'] = gettype($laserMaterial->getCostPerSheet()) == 'double' ? number_format($laserMaterial->getCostPerSheet(),2) : '<i>&lt;test&gt;</i>';
		$replacements['totalcost'] = gettype($laserMaterial->getCostPerSheet()) == 'double' ? number_format($laserMaterial->getCostPerSheet() * $laserJob->getQuantity(),2) : '<i>&lt;test&gt;</i>';
		$replacements['paymentMethod'] = $laserJob->getPaymentMethod();
		
		$subject = $message->fillTemplateSubject($replacements);
		$body = $message->fillTemplateBody($replacements);

        return $this->sendEmail($replacements['email'], $subject, $body, true);
    }

	/**
	 * Sends an email using the appropriate message format for ticket emails
	 * 
	 * @param ticket        The ticket object to pull data from
	 * @param message       The message template to fill in
	 * @param recieverEmail The email address to send the email to -- generally either the submitter or tekbot-worker
	 * @param employeeEmail The email address of the employee who initiated a request (for insertion in escalation emails)
	 * 
	 * @return bool Whether the email sent successfully
	 */
	public function sendTicketEmail($ticket, $message, $recieverEmail, $employeeEmail="") {
		$replacements = Array();
		// TODO: maybe add contents from action handler
		$replacements['email'] = $ticket->getEmail();
		$replacements['empEmail'] = $employeeEmail;
		$replacements['contents'] = $ticket->getIssue();
		$replacements['response'] = $ticket->getResponse();
		$replacements['id'] = $ticket->getId();

		$subject = $message->fillTemplateSubject($replacements);
		$body = $message->fillTemplateBody($replacements);

        return $this->sendEmail($recieverEmail, $subject, $body, true);
	}

	public function sendRecountEmail($part, $message, $email = "tekbot-worker@engr.oregonstate.edu") {
		$replacements = Array();

		$replacements['email'] = $email;
		$replacements['stockNumber'] = $part->getStocknumber();
		$replacements['itemName'] = $part->getName();

		$subject = $message->fillTemplateSubject($replacements);
		$body = $message->fillTemplateBody($replacements);

		return $this->sendEmail($replacements['email'], $subject, $body, true);
	}

	public function sendToolProcessFeesEmail($unprocessed, $type, $message, $email) {
        $replacements = Array();
		$replacements['email'] = $email;
		$replacements['date'] = date("m/d/y", time());	
		$replacements['transactions'] = '
		<table>
			<tr>
				<th style="padding: 0 10px"># of '.$type.'</th>
				<th style="padding: 0 10px">Amount</th>
				<th style="padding: 0 10px">Account Code</th>
			</tr>';

		// Combine charges to 1 per account code
		$accountsGrouped = array();
		foreach($unprocessed as $up) {
			if(!$up->getAccountCode()) continue;

			if(array_key_exists($up->getAccountCode(), $accountsGrouped)) {
				$accountsGrouped[$up->getAccountCode()][0] += $up->getTotalPrice();
				$accountsGrouped[$up->getAccountCode()][1]++;
			} else {
				$accountsGrouped += array(
					$up->getAccountCode() => array(
						$up->getTotalPrice(),
						1));
			}
		}

		// Generate transactions replacement
		foreach ($accountsGrouped as $acct => $details){
			$replacements['transactions'] .= '<tr>'.
				'<td style="text-align: center">' . $details[1] . '</td>
				<td style="text-align: center">$ ' . number_format($details[0],2) . '</td>
				<td style="text-align: center">' . $acct . '</td></tr>
				';
		}
		$replacements['transactions'] .= '</table>';
		if(count($accountsGrouped) == 0) {
			$replacements['transactions'] .= '<i>Did not find any jobs needing to be billed </i>';
		}

		
		$subject = $message->fillTemplateSubject($replacements);
		$body = $message->fillTemplateBody($replacements);

        return $this->sendEmail($replacements['email'], $subject, $body, true);
    }

	public function sendBillAllEmail($unprocessed, $message, $email) {
        $replacements = Array();
		$replacements['email'] = $email;
		$replacements['date'] = date("m/d/y", time());	
		$replacements['transactions'] = '';

		// NEW 7/6/23: Sort unprocessed transactions by account number
		usort($unprocessed, fn($a, $b) => strcasecmp($a->getAccount(), $b->getAccount()));

		$prevAccount = '';
		foreach ($unprocessed as $up){
			// Add a newline to seperate transactions from different accounts
			if($up->getAccount() != $prevAccount)
				$replacements['transactions'] .= '<br>';
			$prevAccount = $up->getAccount();
			
			$replacements['transactions'] .= '<p><b>ID:</b>' . $up->getSaleID() . '&emsp;'. date("m/d/y",strtotime($up->getTimestamp())).'&emsp;<b>Amount:</b>$' . $up->getAmount() . '&emsp;<b>Account:</b>' . $up->getAccount() . '<br>';
		}

		
		$subject = $message->fillTemplateSubject($replacements);
		//below are a list of JV to be transmitted look at old one
		$body = $message->fillTemplateBody($replacements);

        return $this->sendEmail($replacements['email'], $subject, $body, true);
    }

    
}

<?php
namespace Api;

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
// Updated 11/5/2019

use Model\User;
use Model\Locker;
use Model\PrintJob;
use Model\PrintType;
use Model\Box;
use Model\LaserJob;
use Model\LaserMaterial;
use Model\Equipment;
use Model\Ticket;
use Model\Part;
use Model\InternalSale;
use Model\Station;
use Model\Room;

/**
 * Defines the logic for how to handle AJAX requests made to modify user information.
 */
class MessageActionHandler extends ActionHandler {

    /** @var \DataAccess\MessageDao */
    private $dao;

    /** @var \Email\TekBotsMailer */
    private $mailer;

    /**
     * Constructs a new instance of the action handler for requests on messages.
     *
     * @param \DataAccess\MessageDao $dao the data access object for messages
     * @param \Email\TekBotsMailer $mailer the object for sending TekBots site emails
     * @param \Util\Logger $logger the logger to use for logging information about actions
     */
    public function __construct($dao, $mailer, $logger)
    {
        parent::__construct($logger);
        $this->dao = $dao;
        $this->mailer = $mailer;
    }

    /**
     * Updates profile information about a user in the database based on data in an HTTP request.
     * 
     * This function, after invocation is finished, will exit the script via the `ActionHandler\respond()` function.
     *
     * @return void
     */
    public function updateMessageDB() {
        // Ensure the user has permission to make the change
        $this->verifyAccessLevel('employee');

        // Ensure the required parameters exist
        $this->requireParam('message_id');
		$this->requireParam('subject');
        $this->requireParam('body');
        $this->requireParam('format');

        $body = $this->requestBody;
		
        // Get the existing user.
        $message = $this->dao->getMessageByID($body['message_id']);
        if(!$message) {
            $this->respond(new Response(Response::NOT_FOUND, 'Failed to get message from DB'));
        }

        // Update the Message
        $message->setSubject($body['subject']);
        $message->setBody($body['body']);
        $message->setFormat($body['format']);

        $ok = $this->dao->updateMessage($message);

        if(!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to update message'));
        }

        $this->respond(new Response(Response::OK, 'Successfully updated message'));

    }
	
	 /**
     * Sends a message
     * 
     * 
     *
     * @return result of attempt
     */ // CHANGE TO sendTestMessage()
    public function sendMessage() {
        // Ensure the user has permission to make the change
        $this->verifyAccessLevel('employee');

        // Ensure the required parameters exist
        $this->requireParam('message_id');
        $this->requireParam('email');
        $content = $this->requestBody;
		
		$message = $this->dao->getMessageByID($content['message_id']);

        // NEW -- Use the functions in TekBotsMailer to follow DRY principles & make it a true test of the email
        $toolId = $message->getToolId();

        $user = new User();
        $user->setFirstName('John');
        $user->setLastName('Doe');
        $user->setEmail($content['email']);

        $ok = false;

        switch($toolId) {
            case 1: // Lockers
                $locker = new Locker();
                $this->fillObject($locker);
                $ok = $this->mailer->sendLockerEmail($user, $locker, $message);
                break;
            case 2: // 3D Prints
                $printJob = new PrintJob();
                $this->fillObject($printJob, ['setPrintTypeID', 'setPrinterId']);
                $printType = new PrintType();
                $this->fillObject($printType, ['setPrinterId']);
                $ok = $this->mailer->sendPrinterEmail($user, $printJob, $printType, $message);
                break;
            case 4: // TekBoxes
                $box = new Box();
                $this->fillObject($box);
                $ok = $this->mailer->sendBoxEmail($user, $box, $message);
                break;
            case 5: // Laser Cuts
                $laserJob = new LaserJob();
                $this->fillObject($laserJob, ['setLaserCutterId', 'setLaserCutMaterialId']);
                $laserMaterial = new LaserMaterial();
                $this->fillObject($laserMaterial);
                $ok = $this->mailer->sendLaserEmail($user, $laserJob, $laserMaterial, $message);
                break;
            case 6: // Equipment
                $equipment = new Equipment();
                $this->fillObject($equipment);
                $ok = $this->mailer->sendEquipmentEmail($user, null, $equipment, $message);
                break;
            case 7: // Tickets
                $ticket = new Ticket();
                $room = new Room();
                $station = new Station();
                $this->fillObject($ticket);
                $this->fillObject($room);
                $this->fillObject($station);
                $station->setRoom($room);
                $ok = $this->mailer->sendTicketEmail($ticket, $station, $message, $user->getEmail(), $user->getEmail());
                break;
            case 8: // Inventory
                $part = new Part();
                $this->fillObject($part);
                $ok = $this->mailer->sendRecountEmail($part, $message, $user->getEmail());
                break;
            case 9: // Internal billing
                $unprocessed = Array();
                for($i = 0; $i < 5; $i++) {
                    for($j = 0; $j < 3; $j++) {
                        $internalSale = new InternalSale();
                        $this->fillObject($internalSale);
                        $internalSale->setAccount("<i>&lt;test$j&gt;</i>");
                        array_push($unprocessed, $internalSale);
                    }
                }
                $ok = $this->mailer->sendBillAllEmail($unprocessed, $message, $user->getEmail());
                break;
            default:
                $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Tool not found; failed to fill email'));
        }

        if(!$ok) $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to send email to user'));
        $this->respond(new Response(Response::OK, 'Successfully sent email'));
    }

    /**
     * Handles the HTTP request on the API resource. 
     * 
     * This effectively will invoke the correct action based on the `action` parameter value in the request body. If
     * the `action` parameter is not in the body, the request will be rejected. The assumption is that the request
     * has already been authorized before this function is called.
     *
     * @return void
     */
    public function handleRequest() {
        // Make sure the action parameter exists
        $this->requireParam('action');

        // Call the correct handler based on the action
        switch($this->requestBody['action']) {

            case 'updateMessage':
                $this->updateMessageDB();
				break;

			case 'sendMessage':
                $this->sendMessage();
				break;

            default:
                $this->respond(new Response(Response::BAD_REQUEST, 'Invalid action on user resource'));
        }
    }

    /**
     * Fills the given object with the HTML markup for an italicized string "<test>". Operates directly on the object
     * instead of creating a duplicate, and uses `set__()` methods to fill the object.
     * 
     * Used for sending test emails.
     * 
     * @param \Model\* $object The object to fill with the string "<test>"
     * @param string[] $exceptions Setter methods to ignore when filling the object (eg "setPurpose")
     */
    private function fillObject(&$object, $exceptions=[]) {
        foreach(get_class_methods($object) as $method) {
            if(str_contains(strtolower($method), 'set') && !in_array($method, $exceptions)) {
                $object->$method('<i>&lt;test&gt;</i>');
            }
        }
    }
}
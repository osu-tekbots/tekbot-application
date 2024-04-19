<?php
// Updated 11/5/2019
namespace Api;

use Model\Ticket;
use Email\TekBotsMailer;
use DataAccess\TicketDao;
use DataAccess\LabDao;

/**
 * Defines the logic for how to handle AJAX requests made to modify user information.
 */
class TicketsActionHandler extends ActionHandler {

    /** @var \DataAccess\* */
    private $ticketDao;
	private $labDao;
	private $messageDao;
    /** @var \Util\ConfigManager */
    private $configManager;
    /** @var \Email\TekBotsMailer */
    private $mailer;
	
	/******
	$replacements is an array that contains items that should be accessable for emails/template replacement. General things are filled here with overwriting when needed in document
	***/
	private $replacements;

    /**
     * Constructs a new instance of the action handler for requests on user resources.
     *
     * @param \DataAccess\TicketDao $ticketDao the data access object for tickets
     * @param \DataAccess\LabDao $labDao the data access object for lab stations
     * @param \DataAccess\MessageDao $messageDao the data access object for messages
     * @param \Email\TekbotsMailer $mailer the class for sending TekBots site emails
     * @param \Util\ConfigManger $configManager the class for getting site configuration information
     * @param \Util\Logger $logger the logger to use for logging information about actions
     */
    public function __construct($ticketDao, $labDao, $messageDao, $mailer, $configManager, $logger)
    {
        parent::__construct($logger);
        $this->ticketDao = $ticketDao;
		$this->labDao = $labDao;
		$this->messageDao = $messageDao;
        $this->configManager = $configManager;
        $this->mailer = $mailer;
    }

	/**
     * Updates profile information about a user in the database based on data in an HTTP request.
     * 
     * This function, after invocation is finished, will exit the script via the `ActionHandler\respond()` function.
     *
     * @return void
     */

    public function handleResolveTicket() {
        // Ensure the required parameters are set
        $this->requireParam('id');
        $this->requireParam('messageId');
        // $this->requireParam('message'); // -- Can't require or employeeTicketList.php breaks

        $body = $this->requestBody;
        
        // Update the database message if it changed before submit was clicked
        if(isset($body['message'])) {
            $ok = $this->ticketDao->updateTicketResponse($body['id'], $body['message']);
            if(!$ok)  {
                $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Resolve Message Update Failed'));
            }
        }

		$ticket = $this->ticketDao->getTicketById($body['id']);
		$message = $this->messageDao->getMessageByID($body['messageId']);

        //$ticket->setContents($body['contents']);

        if($ticket->getStatus() == 1) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Ticket Already Resolved.'));
        }


        $ok = $this->ticketDao->resolveTicket($ticket);
        if(!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Ticket Failed to Resolve.'));
        }

        $ok = $this->mailer->sendTicketEmail($ticket, NULL, $message, $ticket->getEmail());
		if(!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Email send Failed'));
        }

        $this->respond(new Response(Response::OK, 'Ticket Successfully Resolved'));

    }

    public function handleUpdateResponse() {
        // Ensure the required parameters are set
        $this->requireParam('id');
        $this->requireParam('message');

        $body = $this->requestBody;

        $ticket = $this->ticketDao->getTicketById($body['id']);

        $ok = $this->ticketDao->updateTicketResponse($ticket->getId(), $body['message']);
        if(!$ok)  {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Resolve Message Update Failed'));
        }
        $this->respond(new Response(Response::OK, 'Resolve Message Successfully Updated'));
    }

    public function handleUpdateComment() {
        // Ensure the required parameters are set
        $this->requireParam('id');
        $this->requireParam('message');

        $body = $this->requestBody;

        $ok = $this->ticketDao->updateTicketComment($body['id'], $body['message']);
        if(!$ok)  {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Comment Update Failed'));
        }
        $this->respond(new Response(Response::OK, 'Comment Successfully Updated'));
    }

    public function handleEscalateTicket() {
        // Ensure the required parameters are set
        $this->requireParam('id');
        $this->requireParam('messageId');
        $this->requireParam('empEmail');

        $body = $this->requestBody;
		$ticket = $this->ticketDao->getTicketById($body['id']);
		$message = $this->messageDao->getMessageByID($body['messageId']);

        $ok = $this->ticketDao->escalateTicket($ticket);
        if(!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Ticket Failed to Escalate.'));
        }

        $ok = $this->mailer->sendTicketEmail($ticket, NULL, $message, $this->configManager->getWorkerMaillist(), $body['empEmail']);
		if(!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Email send Failed'));
        }

        $this->respond(new Response(Response::OK, 'Ticket Successfully Escalated'));
    }

    public function handleCreateTicket() {
		$this->requireParam('roomId');
        $this->requireParam('benchId');
        $this->requireParam('email');
        $this->requireParam('issue');
        $this->requireParam('image');
		$this->requireParam('messageID');

		$body = $this->requestBody;

        $ticket = new Ticket();
        $station = $this->labDao->getStationByRoomAndBench($body['roomId'], $body['benchId']);
        $ticket->setStationId($station->getId());
        $ticket->setImage($body['image']);
        $ticket->setIssue($body['issue']);
        $ticket->setEmail($body['email']);
        $ticket->setCreated((new \DateTime())->format('Y-m-d H:i:s'));
        $ticket->setStatus(0);
        $ticket->setIsEscalated(0);
        
        $ok = $this->ticketDao->addNewTicket($ticket);
        if (!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to create new ticket'));
        }

		$message = $this->messageDao->getMessageByID($body['messageID']);
        $ok = $this->mailer->sendTicketEmail($ticket, $station, $message, $this->configManager->getWorkerMaillist());
		if (!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to email employees'));
        }

        $this->respond(new Response(
            Response::CREATED, 
            'Successfully submitted ticket')
        );

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

            case 'resolveTicket':
                $this->handleResolveTicket();
				break;

			case 'escalateTicket':
                $this->handleEscalateTicket();
				break;
            
            case 'createTicket':
                $this->handleCreateTicket();
                break;
            
            case 'updateResponse':
                $this->handleUpdateResponse();
                break;

            case 'updateComment':
                $this->handleUpdateComment();
                break;

            default:
                $this->respond(new Response(Response::BAD_REQUEST, 'Invalid action on Ticket resource'));
        }
    }

}

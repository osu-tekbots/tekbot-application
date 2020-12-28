<?php
// Updated 11/5/2019
namespace Api;

use Model\Message;

/**
 * Defines the logic for how to handle AJAX requests made to modify user information.
 */
class MessageActionHandler extends ActionHandler {

    /** @var \DataAccess\MessageDao */
    private $dao;

    /**
     * Constructs a new instance of the action handler for requests on user resources.
     *
     * @param \DataAccess\UsersDao $dao the data access object for users
     * @param \Util\Logger $logger the logger to use for logging information about actions
     */
    public function __construct($dao, $logger)
    {
        parent::__construct($logger);
        $this->dao = $dao;
    }

    /**
     * Updates profile information about a user in the database based on data in an HTTP request.
     * 
     * This function, after invocation is finished, will exit the script via the `ActionHandler\respond()` function.
     *
     * @return void
     */
    public function updateMessageDB() {
        // Ensure the required parameters exist
        $this->requireParam('message_id');
		$this->requireParam('subject');
        $this->requireParam('body');
        $this->requireParam('format');

        $body = $this->requestBody;
		
        // Get the existing user. 
        // TODO: If it isn't found, send a NOT_FOUND back to the client
        $message = $this->dao->getMessageByID($body['message_id']);

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
     */
    public function sendMessage() {
        // Ensure the required parameters exist
        $this->requireParam('message_id');
        $this->requireParam('email');
        $this->requireParam('replacements');
        $content = $this->requestBody;
		
		$message = $this->dao->getMessageByID($content['message_id']);
			
		$body = $message->fillTemplateBody($content['replacements']);
		$subject = $message->fillTemplateSubject($content['replacements']);

		$headers = "From:tekbot-worker@engr.oregonstate.edu\r\n";
		$headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html;charset=UTF-8\r\n";
		
        $ok = mail($content['email'], $subject, $body, $headers);

        if(!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to send email'));
        }

        $this->respond(new Response(Response::OK, 'Successfully sent email'));

    }

    /**
     * Request handler for updating the user type after a user has logged in for the first time.
     *
     * @return void
     */
    function addMessage() {
        $uid = $this->getFromBody('message_id');
        

        if(!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to add user profile information'));
        }

        $this->respond(new Response(Response::OK, 'Successfully added profile information'));
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

			case 'addMessage':
                $this->addMessage();
				break;

            default:
                $this->respond(new Response(Response::BAD_REQUEST, 'Invalid action on user resource'));
        }
    }

}
<?php
namespace Api;

/**
 * Defines the logic for how to handle AJAX requests made to send emails.
 */
class EmailActionHandler extends ActionHandler {

    /**
     * Constructs a new instance of the action handler for email requests.
     *
     * @param \Email\Mailer  $mailer  the class for sending emails
     * @param \Util\Logger $logger the logger to use for logging information about actions
     */
    public function __construct($mailer, $logger)
    {
        parent::__construct($logger);
        $this->mailer = $mailer;
    }

    /**
     * Sends an email.
     * 
     * @param string addresses Must exist in the POST request body.
     * @param string subject Must exist in the POST request body.
     * @param string body Must exist in the POST request body.
     * 
     * @return \Api\Response HTTP response for whether the API call successfully completed
     */
    public function handleSendEmail() {
        // Ensure the user has permission to make the change
        $this->verifyAccessLevel('employee');
        
        // Ensure the required parameters exist
        $this->requireParam('addresses');
        $this->requireParam('subject');
        $this->requireParam('body');
        $body = $this->requestBody;

        //  Send the email
		$ok = $this->mailer->sendEmail($body['addresses'], $body['subject'], $body['body'], NULL, 'tekbot-worker@engr.oregonstate.edu');
        
        // Use Response object to send email action results 
        if(!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Email not sent'));
        }
		$this->respond(new Response(Response::OK, 'Email sent'));
    }

    /**
     * Handles the HTTP request on the API resource. 
     * 
     * This effectively will invoke the correct action based on the `action` parameter value in the request body. If
     * the `action` parameter is not in the body, the request will be rejected. The assumption is that the request
     * has already been authorized before this function is called.
     *
     * @return \Api\Response HTTP response for whether the API call successfully completed
     */
    public function handleRequest() {
        // Make sure the action parameter exists
        $this->requireParam('action');
		
		// Call the correct handler based on the action
        switch($this->requestBody['action']) {
            case 'sendEmail':
                $this->handleSendEmail();
                break;

            default:
                $this->respond(new Response(Response::BAD_REQUEST, 'Invalid action on email resource'));
        }
    }
}
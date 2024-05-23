<?php
namespace Api;

class SessionActionHandler extends ActionHandler {

    public function __construct($logger) {
        parent::__construct($logger);
    }

    /**
     * Updates Session variable named tekbotSiteTerm that holds a termID value
     * 
     * @return void
     */
    public function setTermID() {
        $this->requireParam('termID');
        
        $body = $this->requestBody;

        $_SESSION['tekbotSiteTerm'] = $body['termID'];
        $this->respond(new Response(Response::OK, 'Successfully changed term ID'));
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

            case 'setTermID':
                $this->setTermID();
				break;

            default:
                $this->respond(new Response(Response::BAD_REQUEST, 'Invalid action on user resource'));
        }
    }
}

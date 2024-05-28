<?php
namespace Api;

/**
 * Defines the logic for how to handle AJAX requests made to modify TekBox information.
 */
class BoxActionHandler extends ActionHandler {

    /** @var \DataAccess\BoxDao */
    private $boxDao;
    /** @var \DataAccess\UserDao */
	private $userDao;
    /** @var \DataAccess\MessageDao */
	private $messageDao;
    /** @var \Email\TekBotsMailer */
    private $mailer;

    /**
     * Constructs a new instance of the action handler for requests on TekBox resources.
     *
     * @param \DataAccess\BoxDao  $boxDao  the data access object for TekBoxes
     * @param \DataAccess\UserDao $userDao the data access object for users
     * @param \DataAccess\MessageDao $messageDao the data access object for messages
     * @param \Email\TekBotsMailer $mailer the object for sending TekBots site emails
     * @param \Util\Logger $logger the logger to use for logging information about actions
     */
    public function __construct($boxDao, $userDao, $messageDao, $mailer, $logger)
    {
        parent::__construct($logger);
        $this->boxDao = $boxDao;
		$this->userDao = $userDao;
		$this->messageDao = $messageDao;
		$this->mailer = $mailer;
    }

	/**
     * Updates profile information about a user in the database based on data in an HTTP request.
     * 
     * This function, after invocation is finished, will exit the script via the `ActionHandler\respond()` function.
     *
     * @return void
     */
    public function handleFillBox() {
        // Ensure the user has permission to make the change
        $this->verifyAccessLevel('employee');

        // Ensure the required parameters exist
        $this->requireParam('userId');
		$this->requireParam('boxId');
		$this->requireParam('messageId');
        $this->requireParam('fillById');
        $this->requireParam('contents');
        $body = $this->requestBody;

		$user = $this->userDao->getUserbyId($body['userId']);
		$box = $this->boxDao->getBoxById($body['boxId']);
		$message = $this->messageDao->getMessageByID($body['messageId']);

        // Update the Message
        $box->setUserId($body['userId']);
        $box->setContents($body['contents']);
        $box->setFillBy($body['fillById']);
        $box->setLocked(1);
        $box->setFillDate(date("Y-m-d H:i:s",time()));
        
        $ok = $this->boxDao->updateBox($box);
        if(!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Box failed to fill.'));
        }

        $ok = $this->mailer->sendBoxEmail($user, $box, $message);
		if(!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Email send Failed'));
        }

        $this->respond(new Response(Response::OK, 'Box Fill Successful'));

    }
	
	public function handleResetBox() {
        // Ensure the user has permission to make the change
        $this->verifyAccessLevel('employee');

        // Ensure the required parameters exist
        $this->requireParam('boxId');
		$body = $this->requestBody;
        
        $ok = $this->boxDao->resetBox($body['boxId']);
        if(!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Box failed to reset.'));
        }

        $this->respond(new Response(Response::OK, 'Box Reset Successful'));

    }
	
	public function handleLockBox() {
        // Ensure the user has permission to make the change
        $this->verifyAccessLevel('employee');

        // Ensure the required parameters exist
        $this->requireParam('boxId');
		$body = $this->requestBody;
        
        $ok = $this->boxDao->lockBox($body['boxId']);
        if(!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Box not Locked.'));
        }

        $this->respond(new Response(Response::OK, 'Box Locked'));

    }
	
	public function handleLock() {
        // Ensure the user has permission to make the change
        $this->verifyAccessLevel(['user', 'employee']);

        // Ensure the required parameters exist
        $this->requireParam('boxId');
		$body = $this->requestBody;
        
		if ($_SESSION['userID'] == $body['uId']){
			$ok = $this->boxDao->lockBox($body['boxId']);
			if(!$ok) {
				$this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Box not Locked.'));
			}

			$this->respond(new Response(Response::OK, 'Box Locked'));
		} else 
			$this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Not Authorized'));
    }
	
	public function handleUnlockBox() {
        // Ensure the user has permission to make the change
        $this->verifyAccessLevel('employee');

        // Ensure the required parameters exist
        $this->requireParam('boxId');
		$body = $this->requestBody;
        
        $ok = $this->boxDao->unlockBox($body['boxId']);
        if(!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Box not Unlocked.'));
        }

        $this->respond(new Response(Response::OK, 'Box Unlocked'));

    }
	
	public function handleUnlock() {
        // Ensure the user has permission to make the change
        $this->verifyAccessLevel(['user', 'employee']);

        // Ensure the required parameters exist
        $this->requireParam('boxId');
		$body = $this->requestBody;
        
		if ($_SESSION['userID'] == $body['uId']){
			$ok = $this->boxDao->unlockBox($body['boxId']);
			if(!$ok) {
				$this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Box not Unlocked.'));
			}
			$this->respond(new Response(Response::OK, 'Box Unlocked'));
		} else 
			$this->respond(new Response(Response::UNAUTHORIZED, 'Not Authorized'));
    }
	
	public function handleEmptyBox() {
        // Ensure the user has permission to make the change
        $this->verifyAccessLevel('employee');

        // Ensure the required parameters exist
        $this->requireParam('boxId');
		$this->requireParam('messageId');
        $body = $this->requestBody;

		$box = $this->boxDao->getBoxById($body['boxId']);
		$user = $this->userDao->getUserbyId($box->getUserId());
		$message = $this->messageDao->getMessageByID($body['messageId']);

        $ok = $this->boxDao->resetBox($body['boxId']);
        if(!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Box failed to empty.'));
        }

        $ok = $this->mailer->sendBoxEmail($user, $box, $message);
		if(!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Email send Failed'));
        }

        $this->respond(new Response(Response::OK, 'Box Emptied Sucessful'));
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

            case 'fillBox':
                $this->handleFillBox();
				break;

			case 'resetBox':
                $this->handleResetBox();
				break;

			case 'emptyBox':
                $this->handleEmptyBox();
				break;
		
			case 'lockAdmin':
                $this->handleLockBox();
				break;
				
			case 'unlockAdmin':
                $this->handleUnlockBox();
				break;

			case 'lock':
                $this->handleLock();
				break;
				
			case 'unlock':
                $this->handleUnlock();
				break;

            default:
                $this->respond(new Response(Response::BAD_REQUEST, 'Invalid action on Box resource'));
        }
    }

}
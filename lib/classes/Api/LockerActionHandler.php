<?php
// Updated 11/5/2019
namespace Api;

use Model\Locker;
use Email\TekBotsMailer;
use DataAccess\UserDao;

/**
 * Defines the logic for how to handle AJAX requests made to modify user information.
 */
class LockerActionHandler extends ActionHandler {

    /** @var \DataAccess\* */
    private $lockerDao;
	private $userDao;
	private $messageDao;
	
	/******
	$replacements is an array that contains items that should be accessable for emails/template replacement. General things are filled here with overwriting when needed in document
	***/
	private $replacements;

    /**
     * Constructs a new instance of the action handler for requests on user resources.
     *
     * @param \DataAccess\LockerDao $lockerDao the data access object for lockers
     * @param \DataAccess\UsersDao $userDao the data access object for users
     * @param \DataAccess\MessageDao $messageDao the data access object for messages
     * @param \Email\TekBotsMailer $mailer the object for sending TekBots site emails
     * @param \Util\Logger $logger the logger to use for logging information about actions
     */
    public function __construct($lockerDao, $userDao, $messageDao, $mailer, $logger)
    {
        parent::__construct($logger);
        $this->lockerDao = $lockerDao;
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
    public function handleCheckoutLocker() {
        // Ensure the user has permission to make the change
        $this->verifyAccessLevel('employee');
        
        // Ensure the required parameters exist
        $this->requireParam('lockerId');
		$this->requireParam('userId');
		$this->requireParam('messageId');
        $body = $this->requestBody;

		$user = $this->userDao->getUserbyId($body['userId']);
		$locker = $this->lockerDao->getLockerByID($body['lockerId']);
		$message = $this->messageDao->getMessageByID($body['messageId']);

        // Get locker due date
        $thisMonth = intval(date('m'));
        $thisYear = intval(date('Y'));
        $dateString = "September 1 ".($thisYear + ($thisMonth < 9 ? 0 : 1));
        $dueDate = new \DateTime($dateString);

        // Update the locker
        $locker->setUserId($body['userId']);
        $locker->setDueDate($dueDate);
        
        $ok = $this->lockerDao->updateLocker($locker);
        if(!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Locker Failed to Checkout'));
        }

        $ok = $this->mailer->sendLockerEmail($user, $locker, $message);
		if(!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Email sned Failed'));
        }

        $this->respond(new Response(Response::OK, 'Locker Checkout Sucessful'));

    }
	
	/**
     * Updates profile information about a user in the database based on data in an HTTP request.
     * 
     * This function, after invocation is finished, will exit the script via the `ActionHandler\respond()` function.
     *
     * @return void
     */
    public function handleReturnLocker() {
        // Ensure the user has permission to make the change
        $this->verifyAccessLevel('employee');
        
        // Ensure the required parameters exist
        $this->requireParam('lockerId');
		$this->requireParam('userId');
		$this->requireParam('messageId');
        $body = $this->requestBody;

		$user = $this->userDao->getUserbyId($body['userId']);
		$locker = $this->lockerDao->getLockerByID($body['lockerId']);
		$message = $this->messageDao->getMessageByID($body['messageId']);

        // Update the Message
        $locker->setUserId('');
        $locker->setDueDate(NULL);
        
        $ok = $this->lockerDao->updateLocker($locker);
        if(!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Locker Failed to Return'));
        }

        $ok = $this->mailer->sendLockerEmail($user, $locker, $message);
		if(!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Email send Failed'));
        }

        $this->respond(new Response(Response::OK, 'Locker Returned'));

    }
	
	/**
     * Updates profile information about a user in the database based on data in an HTTP request.
     * 
     * This function, after invocation is finished, will exit the script via the `ActionHandler\respond()` function.
     *
     * @return void
     */
    public function handleRemindLocker() {
        // Ensure the user has permission to make the change
        $this->verifyAccessLevel('employee');
        
        // Ensure the required parameters exist
        $this->requireParam('lockerId');
		$this->requireParam('userId');
		$this->requireParam('messageId');
        $body = $this->requestBody;

		$user = $this->userDao->getUserbyId($body['userId']);
		$locker = $this->lockerDao->getLockerByID($body['lockerId']);
		$message = $this->messageDao->getMessageByID($body['messageId']);

        
        $ok = $this->mailer->sendLockerEmail($user, $locker, $message);
		if(!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Email send Failed'));
        }

        $this->respond(new Response(Response::OK, 'Reminder Email Sent'));

    }
	
	/**
     * Renews a locker for a user in the database based on data in an HTTP request.
     * 
     * This function, after invocation is finished, will exit the script via the `ActionHandler\respond()` function.
     *
     * @return void
     */
    public function handleRenewLocker() {
        // Ensure the user has permission to make the change
        $this->verifyAccessLevel('employee');
        
        // Ensure the required parameters exist
        $this->requireParam('lockerId');
		$this->requireParam('userId');
        $body = $this->requestBody;

        $messageID = 'ffhipohqwirytsey';

		$user = $this->userDao->getUserbyId($body['userId']);
		$locker = $this->lockerDao->getLockerByID($body['lockerId']);
		$message = $this->messageDao->getMessageByID($messageID);

        // Get new locker due date
        $thisMonth = intval(date('m'));
        $thisYear = intval(date('Y'));
        $dateString = "September 1 ".($thisYear + ($thisMonth < 9 ? 0 : 1));
        $dueDate = new \DateTime($dateString);

        // Update the locker
        $locker->setDueDate($dueDate);

        $ok = $this->lockerDao->updateLocker($locker);
        if(!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to Update Due Date'));
        }
        
        $ok = $this->mailer->sendLockerEmail($user, $locker, $message);
		if(!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Email Send Failed'));
        }

        $this->respond(new Response(Response::OK, 'Locker Renewed'));

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

            case 'checkoutLocker':
                $this->handleCheckoutLocker();
				break;

			case 'remindLocker':
                $this->handleRemindLocker();
				break;

			case 'returnLocker':
                $this->handleReturnLocker();
				break;

            case 'renewLocker':
                $this->handleRenewLocker();
                break;

            default:
                $this->respond(new Response(Response::BAD_REQUEST, 'Invalid action on Locker resource'));
        }
    }

}
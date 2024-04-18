<?php
// Updated 06/29/2022
namespace Api;

use Model\InternalSale;
use Email\TekBotsMailer;
use DataAccess\UserDao;


/**
 * Defines the logic for how to handle AJAX requests made to modify user information.
 */
class InternalSalesActionHandler extends ActionHandler {

    /** @var \DataAccess\* */
    private $internalSalesDao;
    /** @var \Email\TekBotsMailer */
    private $mailer;
	private $userDao;
	private $messageDao;

    /** @var \Util\ConfigManager */
	private $configManager;
	
	/** @var \Util\Logger */
 //   private $logger;
	
	/******
	$replacements is an array that contains items that should be accessable for emails/template replacement. General things are filled here with overwriting when needed in document
	***/
	private $replacements;

    /**
     * Constructs a new instance of the action handler for requests on user resources.
     *
     * @param \DataAccess\UsersDao $dao the data access object for users
     * @param \Util\Logger $logger the logger to use for logging information about actions
     */
    public function __construct($internalSalesDao, $mailer, $userDao, $messageDao, $configManager, $logger)
    {
        parent::__construct($logger);
        $this->internalSalesDao = $internalSalesDao;
        $this->mailer = $mailer;
		$this->userDao = $userDao;
		$this->messageDao = $messageDao;
        $this->configManager = $configManager;
    }

	/**
     * Adds sale in the database based on data in an HTTP request.
     * This function, after invocation is finished, will exit the script via the `ActionHandler\respond()` function.
     * @return void
     */
    public function handleAddSale() {
        // Ensure the user has permission to make the change
        $this->verifyAccessLevel('employee');

        // Ensure the required parameters exist
        $this->requireParam('buyer');
		$this->requireParam('email');
        $this->requireParam('account');
		$this->requireParam('amount');
        $this->requireParam('seller');
		$this->requireParam('description');
        $body = $this->requestBody;

        $sale = new InternalSale();
        $sale->setBuyer($body['buyer']);
        $sale->setEmail($body['email']);
        $sale->setAccount($body['account']);
        $sale->setAmount($body['amount']);
        $sale->setSeller($body['seller']);
        $sale->setDescription($body['description']);
        
        $ok = $this->internalSalesDao->addSale($sale);
        if(!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Sale Failed to Update'));
        }
        $this->respond(new Response(Response::OK, 'Internal Sale Added'));
		
    }

     /**
     * Deletes sale in the database based on data in an HTTP request.
     * This function, after invocation is finished, will exit the script via the `ActionHandler\respond()` function.
     * @return void
     */
    public function handleDeleteSale() {
        // Ensure the user has permission to make the change
        $this->verifyAccessLevel('employee');
        
        $this->requireParam('saleId');
        $body = $this->requestBody; 

        $ok = $this->internalSalesDao->deleteSaleByID($body['saleId']);

        if (!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to delete sale'));
        }

        $this->respond(new Response(
            Response::OK,
            'Successfully deleted sale'
        ));
    }

    /**
     * This function will bill all that are unprocessesed
     * and send Don an email
     * @return void
     */
    public function handleInternalSalesBillAll() {
        // Ensure the user has permission to make the change
        $this->verifyAccessLevel('employee');
        
        $this->requireParam('messageID');
        $body = $this->requestBody; 

        $message = $this->messageDao->getMessageByID($body['messageID']);
        $unprocessedsales = $this->internalSalesDao->getUnprocessed();
        $ok = $this->mailer->sendBillAllEmail($unprocessedsales, $message, $this->configManager->getWorkerMaillist());        
        if (!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to send bill all email'));
        }

        if(!$response = $this->internalSalesDao->processAll())
			$this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Could not update dates on sales.'));

        $this->respond(new Response(
            Response::OK,
            'Successfully billed all'
        ));
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

            case 'addSale':
                $this->handleAddSale();
				break;

            case 'deleteSale':
                $this->handleDeleteSale();
                break;

            case 'billAllInternalSales':
                $this->handleInternalSalesBillAll();
                break;

            default:
                $this->respond(new Response(Response::BAD_REQUEST, 'Invalid action on Sale'));
        }
    }

}
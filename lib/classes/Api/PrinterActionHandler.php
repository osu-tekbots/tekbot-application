<?php
namespace Api;

use Model\Printer;
use Model\PrintFee;
use Model\PrintJob;
use Model\PrintType;
use DataAccess\QueryUtils;



/**
 * Defines the logic for how to handle AJAX requests made to modify printer information.
 */
class PrinterActionHandler extends ActionHandler {


    private $printFeeDao;
    /** @var \DataAccess\printerDao */
    private $printerDao;
    /** @var \Email\ProjectMailer */
    //private $mailer;
    /** @var \Util\ConfigManager */
    private $config;
    
    /**
     * Constructs a new instance of the action handler for requests on printer resources.
     *
     * @param \DataAccess\CapstoneProjectsDao $printerDao the data access object for printers
     * @param \DataAccess\CapstoneProjectsDao $usersDao the data access object for users
     * @param \Email\ProjectMailer $mailer the mailer used to send printer related emails
     * @param \Util\ConfigManager $config the configuration manager providing access to site config
     * @param \Util\Logger $logger the logger to use for logging information about actions
     */
    public function __construct($printerDao, $printFeeDao, $config, $logger) {
        parent::__construct($logger);
        $this->printerDao = $printerDao;
        //$this->mailer = $mailer;
        $this->config = $config;


        $this->printFeeDao = $printFeeDao;
    }

    /**
     * Creates a new printer entry in the database.
     *
     * @return void
     */
    public function handleCreatePrinter() {
        // Ensure all the requred parameters are present
        $this->requireParam('title');
        $body = $this->requestBody;

        $printer = new Printer();

        $printer->setPrinterName($body['title']);
        $printer->setDescription($body['description']);
        $printer->setLocation($body['location']);

        $ok = $this->printerDao->addNewPrinter($printer);
        if (!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to create new printer'));
        }

        $this->respond(new Response(
            Response::CREATED, 
            'Successfully created new printer resource', 
            array('id' => $printer->getPrinterID())
        ));
    }

    public function handleRemovePrinter() {
        $this->requireParam('printerID');
        $body = $this->requestBody;
        $ok = $this->printerDao->deletePrinterByID($body['printerID']);
        if (!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to delete printer'));
        }
        $this->respond(new Response(
            Response::OK, 
            'Successfully delete printer resource')
        );
    }

    public function handleRemovePrintType() {
        $this->requireParam('printTypeID');
        $body = $this->requestBody;
        $ok = $this->printerDao->deletePrintTypeByID($body['printTypeID']);
        if (!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to delete print type'));
        }
        $this->respond(new Response(
            Response::OK, 
            'Successfully delete print type resource')
        );
    }


    /**
     * Updates fields editable from the user interface in a printer entry in the database.
     *
     * @return void
     */
    public function handleSavePrinter() {

        $id = $this->getFromBody('printerId');
        $name = $this->getFromBody('printerName');
        $description = $this->getFromBody('description');
		$location = $this->getFromBody('location');
         
        $printer = $this->printerDao->getPrinterByID($id);
   
        if (empty($printer)){
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Unable to obtain printer from ID'));
        }

        $printer->setPrinterName($name);
        $printer->setDescription($description);
        $printer->setLocation($location);

        $ok = $this->printerDao->updatePrinter($printer);
        if (!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to save printer'));
        }

        $this->respond(new Response(
            Response::OK,
            'Successfully saved printer'
        ));
    }

    // This returns the information to be stored within the view print modal
    public function handleGeneratePrintModal() {

        $id = $this->getFromBody('printID');
         
        $print = $this->printerDao->getPrintJobsByID($id);
   
        if (empty($print)){
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Unable to obtain print job from ID'));
        }

        $title = "Print Modal";
        $buttons = "<button type='button' class='btn btn-secondary' data-dismiss='modal'>Close</button>";
        

        $modalBody = "
        <!-- Modal Header -->
        <div class='modal-header'>
            <h4 class='modal-title'>$title</h4>
            <button type='button' class='close' data-dismiss='modal'>&times;</button>
        </div>

        <!-- Modal body -->
		<div class='modal-body'>
        </div>

        <!-- Modal footer -->
        <div class='modal-footer'>
        $buttons
        </div>
        ";

 

        $this->respond(new Response(
            Response::OK,
            'Successfully saved printer'
        ));
    }
	


    /**
     * Creates a new print fee entry in the database.
     *
     * @return void
     */
    public function handleCreatePrintFee() {
        //MARK: Implmenet this function, reference function above

        $this->requireParam('print_fee_id');
        $this->requireParam('print_job_id');
        $this->requireParam('user_id');
        $this->requireParam('date_created');
        $this->requireParam('is_pending');
        $this->requireParam('is_paid');

        $body = $this->requestBody;

        $printFee = new PrintFee();

        $printFee->setPrintFeeId($body['print_fee_id']);
        $printFee->setPrintJobId($body['print_job_id']);
        $printFee->setUserId($body['user_id']);
        $printFee->setCustomerNotes($body['customer_notes']);
        $printFee->setDateCreated($body['date_created']);
        $printFee->setPaymentInfo($body['payment_info']);
        $printFee->setIs_pending($body['is_pending']);
        $printFee->setIs_paid($body['is_paid']);
        $printFee->setDate_updated(new \DateTime());

        //AddNewPrinterFee not implemented
        $ok = $this->printFeeDao->addNewPrinterFee($printFee);
        if (!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to create new printer'));
        }

        $this->respond(new Response(
            Response::CREATED, 
            'Successfully created new printer resource', 
            array('id' => $printerFee->getPrintFeeId())
        ));
    }


    /**
     * Updates fields editable from the user interface in a print fee entry in the database.
     *
     * @return void
     */
    public function handleSavePrintFee() {
		
		$body = $this->requestBody;
		
        $printFeeID = $this->getFromBody('print_fee_id');
        $printJobID = $this->getFromBody('print_job_id');
        $userID = $this->getFromBody('user_id');
        $customerNotes = $this->getFromBody('customer_notes');
        $dateCreated = $this->getFromBody('date_created');
        $paymentInfo = $this->getFromBody('payment_info');
        $isPending = $this->getFromBody('is_pending');
        $isPaid = $this->getFromBody('is_paid');

        //Dao function to be implemented
        $printFee = $this->printerFeeDao->getPrinterFeeByID($printFeeID);
        if (empty($printFee)){
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Unable to obtain print fee from ID'));
        }
        
        $printFee->setPrintFeeId($body['print_fee_id']);
        $printFee->setPrintJobId($body['print_job_id']);
        $printFee->setUserId($body['user_id']);
        $printFee->setCustomerNotes($body['customer_notes']);
        $printFee->setDateCreated($body['date_created']);
        $printFee->setPaymentInfo($body['payment_info']);
        $printFee->setIs_pending($body['is_pending']);
        $printFee->setIs_paid($body['is_paid']);
        $printFee->setDate_updated(new \DateTime());

        //Dao not implemented
        $ok = $this->printerFeeDao->updatePrintFee($printFee);
        if (!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to save print fee'));
        }

        $this->respond(new Response(
            Response::OK,
            'Successfully saved print fee'
        ));
    }

    /**
     * Creates a new printer job in the database.
     *
     * @return void
     */
    public function handleCreatePrintJob() {

		$body = $this->requestBody;
        

		$this->requireParam('userId');
        $this->requireParam('printerId');
        $this->requireParam('printTypeId');
        $this->requireParam('dbFileName');
        $this->requireParam('stlFileName');
		
		//Print Job ID and Date Created attributes are assigned in constructor
		$printJob = new PrintJob();
        
        
        //FIXME: Fill out once you do client side
        // Front end values that are not foreign keys
		$printJob->setDbFileName($body['dbFileName']);
		$printJob->setStlFileName($body['stlFileName']);
		$printJob->setPaymentMethod($body['payment']);
		$printJob->setCourseGroupId($body['courseGroup']);
		$printJob->setVoucherCode($body['voucherCode']);
        $printJob->setEmployeeNotes($body['employeeNotes']);
        
        // Front end values that are foreign keys
        $printType = $this->printerDao->getPrintTypesByID($body['printTypeId']);
        $printer = $this->printerDao->getPrinterByID($body['printerId']);

        $printJob->setUserID($body['userId']);
		$printJob->setPrintTypeID($printType);
        $printJob->setPrinterId($printer);

        
        // Values generated at API call
            // Check if formatting is correct
        $printJob->setDateCreated((new \DateTime())->format('Y-m-d H:i:s'));
        $printJob->setPendingCustomerResponse(false);



        // To be done
        // Not sure why this needs to be set to null, need to check
		$printJob->setMessageGroupId(null);
		// $printJob->setValidPrintCheck($body['']);
		// $printJob->setUserConfirmCheck($body['']);
		// $printJob->setCompletePrintDate($body['']);
		// $printJob->setPendingCustomerResponse($body['']);
		// $printJob->setDateUpdated($body['']);
        
        $ok = $this->printerDao->addNewPrintJob($printJob);
        if (!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to create new print job'));
        }

        $this->respond(new Response(
            Response::CREATED, 
            'Successfully submitted print job')
        );
		
    }
	
	
	    /**
     * Updates fields editable from the user interface in a print job entry in the database.
     *
     * @return void
     */
    public function handleSavePrintJob() {

		$body = $this->requestBody;
		
		//FIXME: Fill out once you do client side
        $printJobId = $this->getFromBody('print_job_id');
		$userId = $this->getFromBody('');
		$printTypeId = $this->getFromBody('');
		$printerId = $this->getFromBody('');
		$dbFileName = $this->getFromBody('');
		$stlFileName = $this->getFromBody('');
		$paymentMethod = $this->getFromBody('');
		$courseGroupId = $this->getFromBody('');
		$voucherCode = $this->getFromBody('');
		$validPrintCheck = $this->getFromBody('');
		$userConfirmCheck = $this->getFromBody('');
		$completePrintDate = $this->getFromBody('');
		$employeeNotes = $this->getFromBody('');
		$messageGroupId = $this->getFromBody('');
		$pendingCustomerResponse = $this->getFromBody('');
		$dateUpdated = $this->getFromBody('');

		//Print Job ID and Date Created attributes are assigned in constructor
		$printJob = new PrintJob($printJobId);
		
		if (empty($printJob)){
			$this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Unable to obtain print job from ID'));
		}
		  
        $printJob->setUserID($userId);
		$printJob->setPrintTypeID($printTypeId);
		$printJob->setPrinterId($printerId);
		$printJob->setDbFileName($dbFileName);
		$printJob->setStlFileName($stlFileName);
		$printJob->setPaymentMethod($paymentMethod);
		$printJob->setCourseGroupId($courseGroupId);
		$printJob->setVoucherCode($voucherCode);
		$printJob->setValidPrintCheck($validPrintCheck);
		$printJob->setUserConfirmCheck($userConfirmCheck);
		$printJob->setCompletePrintDate($completePrintDate);
		$printJob->setEmployeeNotes($employeeNotes);
		$printJob->setMessageGroupId($messageGroupId);
		$printJob->setPendingCustomerResponse($pendingCustomerResponse);
		$printJob->setDateUpdated($dateUpdated);

        $ok = $this->printerFeeDao->updatePrintJob($printJob);
        if (!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to save print fee'));
        }

        $this->respond(new Response(
            Response::OK,
            'Successfully saved print job'
        ));
    }
	
	
	public function handleCreatePrintType(){
        $body = $this->requestBody;

        $printer = $this->printerDao->getPrinterByID($body['printerID']);

        $printType = new PrintType();

        $printType->setPrintTypeName($body['name']);
        $printType->setPrinterId($printer);
        $printType->setHeadSize($body['headSize']);
        $printType->setPrecision($body['precision']);
        $printType->setBuildPlateSize($body['plateSize']);
        $printType->setCostPerGram($body['cost']);
        $printType->setDescription($body['description']);

        $ok = $this->printerDao->addNewPrintTypes($printType);
        if (!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to create new printer type'));
        }

        $this->respond(new Response(
            Response::CREATED, 
            'Successfully created new printer type resource', 
            array('id' => $printType->getPrintTypeId())
        ));
	}
	
	public function handleSavePrintType(){
		$id = $this->getFromBody('id');
		$name = $this->getFromBody('name');
		$description = $this->getFromBody('description');
		$printerId = $this->getFromBody('printerId');
		$headSize = $this->getFromBody('head');
		$precision = $this->getFromBody('precision');
		$buildPlateSize = $this->getFromBody('build');
		$costPerGram = $this->getFromBody('cost');
         
        $printType = $this->printerDao->getPrintTypesByID($id);
   
        if (empty($printType)){
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Unable to obtain print type from ID'));
        }

		$printType->setPrintTypeName($name);
        $printType->setDescription($description);
        $printType->setPrinterId($this->printerDao->getPrinterByID($printerId));
        $printType->setHeadSize($headSize);
        $printType->setPrecision($precision);
        $printType->setBuildPlateSize($buildPlateSize);
        $printType->setCostPerGram($costPerGram);

        $ok = $this->printerDao->updatePrintType($printType);
        if (!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to save print type'));
        }

        $this->respond(new Response(
            Response::OK,
            'Successfully saved print type'
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
        $action = $this->getFromBody('action');


        // Call the correct handler based on the action
        switch ($action) {

            case 'createprinter':
                $this->handleCreatePrinter();
            case 'saveprinter':
                $this->handleSavePrinter();
			case 'removeprinter':
            	$this->handleRemovePrinter();
            case 'renderprintmodal':
                $this->handleGeneratePrintModal();
				
			case 'createprintjob':
				$this->handleCreatePrintJob();
			case 'saveprintjob':
				$this->handleSavePrintJob();
			// case 'removeprintjob':
			// 	$this->handleRemovePrintJob();
			
			case 'createprinttype':
				$this->handleCreatePrintType();
			case 'saveprinttype':
                $this->handleSavePrintType();
            case 'removeprinttype':
                $this->handleRemovePrintType();

            case 'createprintfee':
                $this->handleCreatePrintFee();
            case 'saveprintfee':
                $this->handleSavePrintFee();
			//case 'removeprintfee':
			//	$this->handleRemovePrintFee();
            
            default:
                $this->respond(new Response(Response::BAD_REQUEST, 'Invalid action on printer resource'));
        }
		
    }

    private function getAbsoluteLinkTo($path) {
        return $this->config->getBaseUrl() . $path;
    }
}

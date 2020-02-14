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


    // private $printFeeDao;

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
    public function __construct($printerDao, $config, $logger) {
        parent::__construct($logger);
        $this->printerDao = $printerDao;
        //$this->mailer = $mailer;
        $this->config = $config;

        // $this->printFeeDao = $printFeeDao;
    }

    /**
     * Creates a new printer entry in the database.
     *
     * @return void
     */
    public function handleCreatePrinter() {
        // Ensure all the requred parameters are present
        $this->requireParam('title');


        $printer = new Printer();
        $printer->setPrinterName($body['title']);
		$printer->setDescription($body['description']);
        $printer->setDateCreated(new \DateTime());

        $ok = $this->printerDao->addNewPrinter($printer);
        if (!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to create new printer'));
        }

        $this->respond(new Response(
            Response::CREATED, 
            'Successfully created new printer resource', 
            array('id' => $printer->getprinterID())
        ));
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

		//check
        $ok = $this->printerDao->updatePrinter($printer);
        if (!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to save printer'));
        }

        $this->respond(new Response(
            Response::OK,
            'Successfully saved printer'
        ));
    }


    // /**
    //  * Creates a new print fee entry in the database.
    //  *
    //  * @return void
    //  */
    // public function handleCreatePrintFee() {
    //     //MARK: Implmenet this function, reference function above
    //     $this->requireParam('print_fee_id');
    //     $this->requireParam('print_job_id');
    //     $this->requireParam('user_id');
    //     $this->requireParam('date_created');
    //     $this->requireParam('is_pending');
    //     $this->requireParam('is_paid');

    //     $body = $this->requestBody;

    //     $printFee = new PrintFee();

    //     $printFee->setPrintFeeId($body['print_fee_id'])
    //     $printFee->setPrintJobId($body['print_job_id'])
    //     $printFee->setUserId($body['user_id'])
    //     $printFee->setCustomerNotes($body['customer_notes'])
    //     $printFee->setDateCreated($body['date_created'])
    //     $printFee->setPaymentInfo($body['payment_info'])
    //     $printFee->setIs_pending($body['is_pending'])
    //     $printFee->setIs_paid($body['is_paid'])
    //     $printFee->setDate_updated(new \DateTime())

    //     //AddNewPrinterFee not implemented
    //     $ok = $this->printFeeDao->addNewPrinterFee($printFee);
    //     if (!$ok) {
    //         $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to create new printer'));
    //     }

    //     $this->respond(new Response(
    //         Response::CREATED, 
    //         'Successfully created new printer resource', 
    //         array('id' => $printerFee->getPrintFeeId())
    //     ));
    // }


    // /**
    //  * Updates fields editable from the user interface in a printer entry in the database.
    //  *
    //  * @return void
    //  */
    // public function handleSavePrintFee() {
	// 	//MARK: Implement this function, reference function above
    // }


 

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
			
			// //MARK: Add create print fee and function for it
            // case 'createprintfee':
            //     $this->handleCreatePrintFee();
			// //MARK: Add save print fee and function for it
			
            default:
                $this->respond(new Response(Response::BAD_REQUEST, 'Invalid action on printer resource'));
        }
    }

    private function getAbsoluteLinkTo($path) {
        return $this->config->getBaseUrl() . $path;
    }
}

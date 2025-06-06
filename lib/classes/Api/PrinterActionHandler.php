<?php
namespace Api;

use Model\Printer;
use Model\PrintFee;
use Model\PrintJob;
use Model\PrintType;

/**
 * Defines the logic for how to handle AJAX requests made to modify printer information.
 */
class PrinterActionHandler extends ActionHandler {

    /** @var \DataAccess\VoucherDao */
    private $voucherDao;

    private $printFeeDao;
    /** @var \DataAccess\printerDao */
    private $printerDao;
    /** @var \Email\TekBotsMailer */
    private $mailer;
    /** @var \Util\ConfigManager */
    private $config;
    /** @var \DataAccess\UsersDao */
    private $userDao;

    /** @var \DataAccess\MessageDao */
    private $messageDao;
    
    /**
     * Constructs a new instance of the action handler for requests on printer resources.
     *
     * @param \DataAccess\CapstoneProjectsDao $printerDao the data access object for printers
     * @param \DataAccess\CapstoneProjectsDao $usersDao the data access object for users
     * @param \Email\ProjectMailer $mailer the mailer used to send printer related emails
     * @param \Util\ConfigManager $config the configuration manager providing access to site config
     * @param \Util\Logger $logger the logger to use for logging information about actions
     */
    public function __construct($printerDao, $printFeeDao, $voucherDao, $userDao, $mailer, $messageDao, $config, $logger) {
        parent::__construct($logger);
        $this->printerDao = $printerDao;
        $this->mailer = $mailer;
        $this->config = $config;
        $this->userDao = $userDao;
        $this->voucherDao = $voucherDao;
        $this->printFeeDao = $printFeeDao;

        $this->messageDao = $messageDao;
    }

    public function handlePurgingOldPrints() {
        $ok = $this->printerDao->purgeOldPrintFilesAndJobs();
        
        if ($ok) {
            $this->respond(new Response(Response::OK, 'Old prints purged successfully'));
        } else {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to purge old prints'));
        }
    }

    /**
     * Creates a new printer entry in the database.
     *
     * @return void
     */
    public function handleCreatePrinter() {
        // Ensure the user has permission to make the change
        $this->verifyAccessLevel('employee');

        // Ensure all the required parameters are present
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
        // Ensure the user has permission to make the change
        $this->verifyAccessLevel('employee');
        
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
        // Ensure the user has permission to make the change
        $this->verifyAccessLevel('employee');
        
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
        // Ensure the user has permission to make the change
        $this->verifyAccessLevel('employee');

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
    // Unused: removed 05/13/2024 to see if anything breaks
    /* public function handleGeneratePrintModal() {
        // Ensure the user has permission to make the change
        $this->verifyAccessLevel(['user', 'employee']);

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
    } */
	


    /**
     * Creates a new print fee entry in the database.
     *
     * @return void
     */
    public function handleCreatePrintFee() {
        // Ensure the user has permission to make the change
        $this->verifyAccessLevel('employee');

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
        $printFee->setIsPending($body['is_pending']);
        $printFee->setIsPaid($body['is_paid']);
        $printFee->setDateUpdated(new \DateTime());

        //AddNewPrinterFee not implemented
        $ok = $this->printFeeDao->addNewPrinterFee($printFee);
        if (!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to create new printer'));
        }

        $this->respond(new Response(
            Response::CREATED, 
            'Successfully created new printer resource', 
            array('id' => $printFee->getPrintFeeId())
        ));
    }


    /**
     * Updates fields editable from the user interface in a print fee entry in the database.
     *
     * @return void
     */
    public function handleSavePrintFee() {
        // Ensure the user has permission to make the change
        $this->verifyAccessLevel('employee');
		
		$body = $this->requestBody;
		
        $printFeeID = $this->getFromBody('print_fee_id');

        //Dao function to be implemented
        $printFee = $this->printFeeDao->getPrinterFeeByID($printFeeID);
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
        // Ensure the user has permission to make the change
        $this->verifyAccessLevel(['user', 'employee']);
        
		$this->requireParam('userId');
        $this->requireParam('printerId');
        $this->requireParam('printTypeId');
        $this->requireParam('dbFileName');
        $this->requireParam('stlFileName');
        $this->requireParam('quantity');
		$this->requireParam('messageID');
		$body = $this->requestBody;
        
		
		//Print Job ID and Date Created attributes are assigned in constructor
		$printJob = new PrintJob();
        
		
        if ($body['payment'] == "voucher"){
			if($body['voucherCode']) {
				$voucher = $this->voucherDao->getVoucher($body['voucherCode']);
				// TODO: Fix if there are more services added
				if($voucher && $voucher->getServiceID() == 2) {
					
					// Ensures that voucher code has not been used
					$isUsed = $voucher->getDateUsed();
					if($isUsed) {
						$this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Voucher code has already been used'));
					}

					// Ensures that voucher code has not expired
					$dateUsed = (new \DateTime())->format('Y-m-d H:i:s');


					$dateExpired = (new \DateTime($voucher->getDateExpired()))->format('Y-m-d H:i:s');
					if($dateUsed > $dateExpired) {
						$this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Voucher code has been expired'));
					}
					
				} else {
					$this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Voucher is not valid'));
				}
				
				$printJob->setVoucherCode($body['voucherCode']);
			}
        } else if($body['payment'] == "account") {
            if($body['accountCode']) {
                // TODO: Add verification for account code
                $printJob->setAccountCode($body['accountCode']);
            }
        }
        
        //FIXME: Fill out once you do client side
        // Front end values that are not foreign keys
		$printJob->setDbFileName($body['dbFileName']);
		$printJob->setStlFileName($body['stlFileName']);
		$printJob->setPaymentMethod($body['payment']);
		$printJob->setCourseGroupId($body['courseGroup']);
        $printJob->setCustomerNotes($body['customerNotes']);
        $printJob->setEmployeeNotes($body['employeeNotes']);
        $printJob->setQuantity($body['quantity']);

        
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
		
		$user = $this->userDao->getUserByID($body['userId']);
		$printType = $this->printerDao->getPrintTypesByID($printJob->getPrintTypeID());
		$message = $this->messageDao->getMessageByID($body['messageID']);
        $ok = $this->mailer->sendPrinterEmail($user, $printJob, $printType, $message);
		if (!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to send confirmation email'));
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
    // Unused: removed 05/13/2024 to see if anything breaks
    /* public function handleSavePrintJob() {
        // Ensure the user has permission to make the change
        $this->verifyAccessLevel(['user', 'employee']);
		
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

        $ok = $this->printerDao->updatePrintJob($printJob);
        if (!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to save print fee'));
        }

        $this->respond(new Response(
            Response::OK,
            'Successfully saved print job'
        ));
    } */
    
    
    public function handleDeletePrintJob() {
        // Ensure the user has permission to make the change
        $this->verifyAccessLevel(['user', 'employee']);

        $body = $this->requestBody;
        // $printJob = $this->printerDao->getPrintJobsByID($body['printJobID']);
        $ok = $this->printerDao->deletePrintJobByID($body['printJobID']);

        if (!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to delete print job'));
        }

        $this->respond(new Response(
            Response::OK,
            'Successfully deleted print job'
        ));
    }
	
	
	public function handleCreatePrintType(){
        // Ensure the user has permission to make the change
        $this->verifyAccessLevel('employee');
        
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
        // Ensure the user has permission to make the change
        $this->verifyAccessLevel('employee');
        
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
    
    public function handleProcessPrintJob() {
        // Ensure the user has permission to make the change
        $this->verifyAccessLevel('employee');
        
        $this->requireParam('printJobID');
        $this->requireParam('userID');

        $body = $this->requestBody;

        $printJobID = $body['printJobID'];
        $userID = $body['userID'];

        $printJob = $this->printerDao->getPrintJobsByID($printJobID);
        if (empty($printJob)) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Unable to obtain print job from ID'));
        }
        $printJob = $printJob[0];

        $printJob->setValidPrintCheck((new \DateTime())->format('Y-m-d H:i:s'));
        $printJob->setDateUpdated((new \DateTime())->format('Y-m-d H:i:s'));
        $printJob->setUserConfirmCheck((new \DateTime())->format('Y-m-d H:i:s'));
        $printJob->setPaymentDate((new \DateTime())->format('Y-m-d H:i:s'));
        $printJob->setCompletePrintDate((new \DateTime())->format('Y-m-d H:i:s'));
        $printJob->setPendingCustomerResponse(0);

        $ok = $this->printerDao->updatePrintJob($printJob);
        if (!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to update print job'));
        }

		//Check if a voucher was used for this print. If so, mark it as used now.
		if ($printJob->getPaymentMethod() == "voucher"){
			if($printJob->getVoucherCode()) {
				$voucher = $this->voucherDao->getVoucher($printJob->getVoucherCode());
				$dateUsed = (new \DateTime())->format('Y-m-d H:i:s');
				$voucher->setUserID($userID);
				$voucher->setDateUsed($dateUsed);
				$ok = $this->voucherDao->updateVoucher($voucher);
				if(!$ok) {
					$this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to update voucher code'));
				}
			}
		}

        $this->respond(new Response(
            Response::CREATED, 
            'Successfully processed print job')
        );
    }

    public function handleSendCustomerConfirm() {
        // Ensure the user has permission to make the change
        $this->verifyAccessLevel('employee');
        
		$this->requireParam('printJobID');
		$this->requireParam('userID');
		// $this->requireParam('printCost');
		$this->requireParam('material_amount');
		$this->requireParam('messageID');
		
		$body = $this->requestBody;

        $printJobID = $body['printJobID'];

        $printJob = $this->printerDao->getPrintJobsByID($printJobID);
        if (empty($printJob)) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Unable to obtain print job from ID'));
        }
        $printJob = $printJob[0];
        
        $printType = $this->printerDao->getPrintTypesByID($printJob->getPrintTypeID());

        $printJob->setValidPrintCheck((new \DateTime())->format('Y-m-d H:i:s'));
        $printJob->setPendingCustomerResponse(true);
		$printJob->setMaterialAmount($body['material_amount']);
        $printJob->setTotalPrice($printJob->getQuantity() * $printType->getCostPerGram() * $printJob->getMaterialAmount());

        $printJob->setDateUpdated((new \DateTime())->format('Y-m-d H:i:s'));

        $ok = $this->printerDao->updatePrintJob($printJob);
        if (!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to update print job'));
        }

        $user = $this->userDao->getUserByID($body['userID']);
		$message = $this->messageDao->getMessageByID($body['messageID']);
        $ok = $this->mailer->sendPrinterEmail($user, $printJob, $printType, $message);
		if (!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to send email to user'));
        }

        $this->respond(new Response(
            Response::CREATED, 
            'Successfully updated print job and send email')
        );

    }

    
 
    function handleCustomerConfirmPrintJob() {
        // Ensure the user has permission to make the change
        $this->verifyAccessLevel(['user', 'employee']);
        
        $body = $this->requestBody;

		$this->requireParam('printJobID');

        $printJobID = $body['printJobID'];

        $printJob = $this->printerDao->getPrintJobsByID($printJobID);
        if (empty($printJob)) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Unable to obtain print job from ID'));
        }

        $printJob = $printJob[0];
        
        $printJob->setDateUpdated((new \DateTime())->format('Y-m-d H:i:s'));


        $printJob->setPendingCustomerResponse(0);
        $printJob->setUserConfirmCheck((new \DateTime())->format('Y-m-d H:i:s'));
        

        $ok = $this->printerDao->updatePrintJob($printJob);
        if (!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to update print job'));
        }

        $this->respond(new Response(
            Response::CREATED, 
            'Successfully confirmed print job')
        );

    }
 
    function handleCompletePrintJob() {
        // Ensure the user has permission to make the change
        $this->verifyAccessLevel('employee');
        
		$this->requireParam('printJobID');
		$this->requireParam('userID');
		$this->requireParam('messageID');
		$body = $this->requestBody;
		
        $printJob = $this->printerDao->getPrintJobsByID($body['printJobID']);
        if (empty($printJob)) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Unable to obtain print job from ID'));
        }

        $printJob = $printJob[0];
        $printJob->setDateUpdated((new \DateTime())->format('Y-m-d H:i:s'));
        $printJob->setCompletePrintDate((new \DateTime())->format('Y-m-d H:i:s'));

        $user = $this->userDao->getUserByID($body['userID']);
		$printType = $this->printerDao->getPrintTypesByID($printJob->getPrintTypeID());
		$message = $this->messageDao->getMessageByID($body['messageID']);
        $ok = $this->mailer->sendPrinterEmail($user, $printJob, $printType, $message);
		if (!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to send email to user'));
        }

		//Check if a voucher was used for this print. If so, mark it as used now.
		if ($printJob->getPaymentMethod() == "voucher"){
			if($printJob->getVoucherCode()) {
				$voucher = $this->voucherDao->getVoucher($printJob->getVoucherCode());
				$dateUsed = (new \DateTime())->format('Y-m-d H:i:s');
				$userID = $body['userID'];
				$voucher->setUserID($userID);
				$voucher->setDateUsed($dateUsed);
				$ok = $this->voucherDao->updateVoucher($voucher);
				if(!$ok) {
					$this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to update voucher code'));
				}
			}
		}

        //added to fix account code completion error 4/22/2022 by Travis
        //check if account code was used for this print.
        

		$ok = $this->printerDao->updatePrintJob($printJob);
        if (!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to update print job'));
        }

        $this->respond(new Response(
            Response::CREATED, 
            'Successfully updated print job and send email')
        );

    }

    public function handleUpdateEmployeeNotes() {
        // Ensure the user has permission to make the change
        $this->verifyAccessLevel('employee');
        
        $body = $this->requestBody;

		$this->requireParam('printJobID');
        $this->requireParam('employeeNotes');

        $printJobID = $body['printJobID'];

        $printJob = $this->printerDao->getPrintJobsByID($printJobID);
        if (empty($printJob)) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Unable to obtain print job from ID'));
        }

        $printJob = $printJob[0];
        
        $printJob->setEmployeeNotes($body['employeeNotes']);

        $ok = $this->printerDao->updatePrintJob($printJob);
        if (!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to update print job'));
        }

        $this->respond(new Response(
            Response::CREATED, 
            'Successfully updated employee notes')
        );

    }

    public function handleVerifyPrintPayment() {
        // Ensure the user has permission to make the change
        $this->verifyAccessLevel('employee');
        
        $body = $this->requestBody;
        $this->requireParam('printJobID');
        $printJobID = $body['printJobID'];
        
        $printJob = $this->printerDao->getPrintJobsByID($printJobID);
        if (empty($printJob)) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Unable to obtain print job from ID'));
        }
        $printJob = $printJob[0];

        $printJob->setPaymentDate((new \DateTime())->format('Y-m-d H:i:s'));
        $ok = $this->printerDao->updatePrintJob($printJob);
        if (!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to update print job'));
        }

        $this->respond(new Response(
            Response::CREATED, 
            'Successfully updated print job')
        );
    }

    /**
     * Sends an email with the contents entered into the webpage to the user who submitted the 3D print
     * 
     * @return void
     */
    public function handleSendUserEmail() {
        // Ensure the user has permission to make the change
        $this->verifyAccessLevel('employee');
        
        $this->requireParam('printJobID');
        $this->requireParam('email');
        $this->requireParam('message');
        $body = $this->requestBody;

        if($body['message'] == '') $this->respond(new Response(Response::BAD_REQUEST, "Email body is empty"));

        $ok = $this->mailer->sendEmail($body['email'], '3D Print Submission Follow-up', $body['message'], false, $this->config->getWorkerMaillist());
		if (!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to send email to user'));
        }

        $this->respond(new Response(Response::OK, 'Successfully sent email'));
    }

    /**
     * Sends an email to Don with info about all uncharged print jobs & marks them as charged
     * 
     * @return void
     */
    public function handleProcessAllFees() {
        // Ensure the user has permission to make the change
        $this->verifyAccessLevel('employee');
        
        $this->requireParam('messageID');
        $body = $this->requestBody;

        $unprocessedJobs = $this->printerDao->getUnchargedCompleteJobs();

        foreach($unprocessedJobs as $job) {
            if($job->getPaymentMethod() == 'voucher') {
                $voucher = $this->voucherDao->getVoucher($job->getVoucherCode());
                if(!$voucher) $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to get account code for voucher: '.$job->getVoucherCode()));
                $job->setAccountCode($voucher->getLinkedAccount());
            }
        }

        $message = $this->messageDao->getMessageByID($body['messageID']);

        $ok = $this->mailer->sendToolProcessFeesEmail($unprocessedJobs, 'Prints', $message, $this->config->getWorkerMaillist());
        if(!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to send proccess fees email'));
        }

        $jobIds = [];
        foreach($unprocessedJobs as $up) {
            $jobIds[] = $up->getPrintJobId();
        }

        $ok = $this->printerDao->setChargeDate((new \DateTime())->format('Y-m-d H:i:s'), $jobIds);
        if(!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Did not mark print jobs as charged'));
        }

        $this->respond(new Response(Response::OK, 'Sent process fees email and updated jobs'));
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
                return;
            case 'saveprinter':
                $this->handleSavePrinter();
                return;
			case 'removeprinter':
            	$this->handleRemovePrinter();
                return;
            // case 'renderprintmodal':
            //     $this->handleGeneratePrintModal();
			case 'purgeOldPrintJobs':
                $this -> handlePurgingOldPrints();
                return;
			case 'createprintjob':
				$this->handleCreatePrintJob();
                return;
			// case 'saveprintjob':
			// 	$this->handleSavePrintJob();

            case 'deletePrintJob':
                $this->handleDeletePrintJob();
                return;
            case 'processPrintJob':
                    $this->handleProcessPrintJob();
                    return;
			
			case 'createprinttype':
				$this->handleCreatePrintType();
                return;
			case 'saveprinttype':
                $this->handleSavePrintType();
                return;
            case 'removeprinttype':
                $this->handleRemovePrintType();
                return;

            case 'createprintfee':
                $this->handleCreatePrintFee();
                return;
            case 'saveprintfee':
                $this->handleSavePrintFee();
                return;

            
            case 'customerConfirmPrint':
                $this->handleCustomerConfirmPrintJob();
                return;

            case 'sendCustomerConfirm':
                $this->handleSendCustomerConfirm();
                return;

            case 'completePrintJob':
                $this->handleCompletePrintJob();
                return;
            
            case 'updateEmployeeNotes':
                $this->handleUpdateEmployeeNotes();
                return;

            case 'verifyPrintPayment':
                $this->handleVerifyPrintPayment();
                return;

            case 'sendUserEmail':
                $this->handleSendUserEmail();
                return;
            
            case 'processAllFees':
                $this->handleProcessAllFees();
                return;
            default:
                $this->respond(new Response(Response::BAD_REQUEST, 'Invalid action on printer resource'));
        }
		
    }
}

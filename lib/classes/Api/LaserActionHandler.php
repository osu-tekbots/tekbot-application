<?php
namespace Api;

use Model\LaserJob;
use Model\LaserMaterial;
use Model\Laser;

class LaserActionHandler extends ActionHandler {

    private $coursePrintAllowanceDao;

    /** @var \DataAccess\LaserDao */
    private $laserDao;
    /** @var \Email\PrinterMailer */
    private $mailer;
    /** @var \Util\ConfigManager */
    private $config;
    /** @var \DataAccess\UsersDao */
    private $userDao;

    private $messageDao;

    public function __construct($laserDao, $coursePrintAllowanceDao, $userDao, $messageDao, $config, $logger) {
        parent::__construct($logger);
        $this->laserDao = $laserDao;
        $this->config = $config;
        $this->userDao = $userDao;
        $this->coursePrintAllowanceDao = $coursePrintAllowanceDao;
        $this->messageDao = $messageDao;
    }


    /**
     * Creates a new printer entry in the database.
     *
     * @return void
     */
    public function handleCreateLaserCutter() {
        // Ensure all the requred parameters are present
        $this->requireParam('title');
        $body = $this->requestBody;

        $printer = new Laser();

        $printer->setLaserName($body['title']);
        $printer->setDescription($body['description']);
        $printer->setLocation($body['location']);

        $ok = $this->laserDao->addNewLaserCutter($printer);
        if (!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to create new printer'));
        }

        $this->respond(new Response(
            Response::CREATED, 
            'Successfully created new printer resource', 
            array('id' => $printer->getLaserId())
        ));
    }

    public function handleRemoveLaserCutter() {
        $this->requireParam('laserID');
        $body = $this->requestBody;
        $ok = $this->laserDao->deleteLaserByID($body['laserID']);
        if (!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to delete laser cutter'));
        }
        $this->respond(new Response(
            Response::OK, 
            'Successfully delete laser cutter resource')
        );
    }

    
    /**
     * Updates fields editable from the user interface in a printer entry in the database.
     *
     * @return void
     */
    public function handleSaveLaserCutter() {

        $id = $this->getFromBody('laserId');
        $name = $this->getFromBody('laserName');
        $description = $this->getFromBody('description');
		$location = $this->getFromBody('location');
         
        $printer = $this->laserDao->getLaserByID($id);
   
        if (empty($printer)){
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Unable to obtain laser cutter from ID'));
        }

        $printer->setLaserName($name);
        $printer->setDescription($description);
        $printer->setLocation($location);

        $ok = $this->laserDao->updateLaserCutter($printer);
        if (!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to save laser cutter'));
        }

        $this->respond(new Response(
            Response::OK,
            'Successfully saved laser cutter'
        ));
    }

    public function handleCreateLaserJob() {
        $body = $this->requestBody;
        
        
		$this->requireParam('userId');
        $this->requireParam('cutterId');
        $this->requireParam('quantity');
        $this->requireParam('cutMaterialId');
        $this->requireParam('dbFileName');
        $this->requireParam('dxfFileName');


        $laserJob = new LaserJob();

        if($body['voucherCode']) {
            $voucher = $this->coursePrintAllowanceDao->getVoucher($body['voucherCode']);
            // TODO: Fix if there are more services added
            if($voucher && $voucher->getServiceID() == 5) {
                
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
                
                // As a result of current structure, cannot consume voucher here in case the user chooses to cancel their order

                // $userID = $body['userId'];
                // $voucher->setUserID($userID);
                // $voucher->setDateUsed($dateUsed);
                // $ok = $this->coursePrintAllowanceDao->updateVoucher($voucher);
                // if(!$ok) {
                //     $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to update voucher code'));
                // }
            } else {
                $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Voucher is not valid'));
            }
        }

        $laserJob->setDbFileName($body['dbFileName']);
		$laserJob->setDxfFileName($body['dxfFileName']);
		$laserJob->setPaymentMethod($body['payment']);
		$laserJob->setCourseGroupId($body['courseGroup']);
		$laserJob->setVoucherCode($body['voucherCode']);
        $laserJob->setCustomerNotes($body['customerNotes']);
        $laserJob->setEmployeeNotes($body['employeeNotes']);
        $laserJob->setQuantity($body['quantity']);
        

        // Front end values that are foreign keys
        $cutMaterial = $this->laserDao->getCutMaterialByID($body['cutMaterialId']);
        $laserCutter = $this->laserDao->getLaserByID($body['cutterId']);

        $laserJob->setUserID($body['userId']);
		$laserJob->setLaserCutMaterialId($cutMaterial);
        $laserJob->setLaserCutterId($laserCutter);


        // Values generated at API call
            // Check if formatting is correct
        $laserJob->setDateCreated((new \DateTime())->format('Y-m-d H:i:s'));
        $laserJob->setPendingCustomerResponse(false);



        // To be done
        // Not sure why this needs to be set to null, need to check
		$laserJob->setMessageGroupId(null);
		// $laserJob->setValidPrintCheck($body['']);
		// $laserJob->setUserConfirmCheck($body['']);
		// $laserJob->setCompletePrintDate($body['']);
		// $laserJob->setPendingCustomerResponse($body['']);
		// $laserJob->setDateUpdated($body['']);
        
        $ok = $this->laserDao->addNewCutJob($laserJob);
        if (!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to create new cut job'));
        }

        $this->respond(new Response(
            Response::CREATED, 
            'Successfully submitted cut job')
        );

    }

    //  /**
    //  * Creates a new printer job in the database.
    //  *
    //  * @return void
    //  */
    // public function handleCreatePrintJob() {

	// 	$body = $this->requestBody;
        

	// 	$this->requireParam('userId');
    //     $this->requireParam('printerId');
    //     $this->requireParam('printTypeId');
    //     $this->requireParam('dbFileName');
    //     $this->requireParam('stlFileName');
		
	// 	//Print Job ID and Date Created attributes are assigned in constructor
	// 	$printJob = new PrintJob();
        
    //     if($body['voucherCode']) {
    //         $voucher = $this->coursePrintAllowanceDao->getVoucher($body['voucherCode']);
    //         // TODO: Fix if there are more services added
    //         if($voucher && $voucher->getServiceID() == 2) {
                
    //             // Ensures that voucher code has not been used
    //             $isUsed = $voucher->getDateUsed();
    //             if($isUsed) {
    //                 $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Voucher code has already been used'));
    //             }

    //             // Ensures that voucher code has not expired
    //             $dateUsed = (new \DateTime())->format('Y-m-d H:i:s');


    //             $dateExpired = (new \DateTime($voucher->getDateExpired()))->format('Y-m-d H:i:s');
    //             if($dateUsed > $dateExpired) {
    //                 $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Voucher code has been expired'));
    //             }
                
    //             // As a result of current structure, cannot consume voucher here in case the user chooses to cancel their order

    //             // $userID = $body['userId'];
    //             // $voucher->setUserID($userID);
    //             // $voucher->setDateUsed($dateUsed);
    //             // $ok = $this->coursePrintAllowanceDao->updateVoucher($voucher);
    //             // if(!$ok) {
    //             //     $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to update voucher code'));
    //             // }
    //         } else {
    //             $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Voucher is not valid'));
    //         }
    //     }
        
    //     //FIXME: Fill out once you do client side
    //     // Front end values that are not foreign keys
	// 	$printJob->setDbFileName($body['dbFileName']);
	// 	$printJob->setStlFileName($body['stlFileName']);
	// 	$printJob->setPaymentMethod($body['payment']);
	// 	$printJob->setCourseGroupId($body['courseGroup']);
	// 	$printJob->setVoucherCode($body['voucherCode']);
    //     $printJob->setCustomerNotes($body['customerNotes']);
    //     $printJob->setEmployeeNotes($body['employeeNotes']);
        
    //     // Front end values that are foreign keys
    //     $printType = $this->printerDao->getPrintTypesByID($body['printTypeId']);
    //     $printer = $this->printerDao->getPrinterByID($body['printerId']);

    //     $printJob->setUserID($body['userId']);
	// 	$printJob->setPrintTypeID($printType);
    //     $printJob->setPrinterId($printer);

        
    //     // Values generated at API call
    //         // Check if formatting is correct
    //     $printJob->setDateCreated((new \DateTime())->format('Y-m-d H:i:s'));
    //     $printJob->setPendingCustomerResponse(false);



    //     // To be done
    //     // Not sure why this needs to be set to null, need to check
	// 	$printJob->setMessageGroupId(null);
	// 	// $printJob->setValidPrintCheck($body['']);
	// 	// $printJob->setUserConfirmCheck($body['']);
	// 	// $printJob->setCompletePrintDate($body['']);
	// 	// $printJob->setPendingCustomerResponse($body['']);
	// 	// $printJob->setDateUpdated($body['']);
        
    //     $ok = $this->printerDao->addNewPrintJob($printJob);
    //     if (!$ok) {
    //         $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to create new print job'));
    //     }

    //     $this->respond(new Response(
    //         Response::CREATED, 
    //         'Successfully submitted print job')
    //     );
        
    
    public function handleUpdateEmployeeNotes() {
        $body = $this->requestBody;

		$this->requireParam('laserJobID');
        $this->requireParam('employeeNotes');

        $laserJobID = $body['laserJobID'];

        $laserJob = $this->laserDao->getLaserJobById($laserJobID);
        if (empty($laserJob)) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Unable to obtain laser job from ID'));
        }

        $laserJob = $laserJob[0];
        
        $laserJob->setEmployeeNotes($body['employeeNotes']);

        $ok = $this->laserDao->updateCutJob($laserJob);
        if (!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to update laser job'));
        }

        $this->respond(new Response(
            Response::CREATED, 
            'Successfully updated employee notes')
        );

    }

    public function handleSendCustomerConfirm() {
        $body = $this->requestBody;

		$this->requireParam('laserJobID');
		$this->requireParam('userID');

        $laserJobID = $body['laserJobID'];

        $printJob = $this->laserDao->getLaserJobById($laserJobID);
        if (empty($printJob)) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Unable to obtain laser job from ID'));
        }

        $printJob = $printJob[0];

        $printJob->setValidCutDate((new \DateTime())->format('Y-m-d H:i:s'));
        $printJob->setPendingCustomerResponse(true);

        $printJob->setDateUpdate((new \DateTime())->format('Y-m-d H:i:s'));

        $ok = $this->laserDao->updateCutJob($printJob);
        if (!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to update laser job'));
        }

        $user = $this->userDao->getUserByID($body['userID']);

        // $replacements = array(
        //     "name" => $user->getFirstName(),
        //     "print" => $printJob->getStlFileName()
        // );

        // $ok = $this->printerEmailer('wersspdoifwkjfd', $user->getEmail(), $replacements);
        // if (!$ok) {
        //     $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to send email to user'));
        // }
        // $link = "https://eecs.oregonstate.edu/education/tekbotSuite/tekbot/ajax/jobhandler.php?id={$printJobID}&action=approve";
        // $user = $this->userDao->getUserByID($body['userID']);
        // $this->mailer->sendPrintConfirmationEmail($user, $printJob, $link);

        $this->respond(new Response(
            Response::CREATED, 
            'Successfully updated laser job and (not) send email')
        );

    }
    
    function handleCustomerConfirmCutJob() {
        $body = $this->requestBody;

		$this->requireParam('laserJobID');

        $laserJobID = $body['laserJobID'];

        $printJob = $this->laserDao->getLaserJobById($laserJobID);
        if (empty($printJob)) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Unable to obtain laser job from ID'));
        }

        $printJob = $printJob[0];
        
        $printJob->setDateUpdate((new \DateTime())->format('Y-m-d H:i:s'));


        $printJob->setPendingCustomerResponse(0);
        $printJob->setUserConfirmDate((new \DateTime())->format('Y-m-d H:i:s'));
        

        $ok = $this->laserDao->updateCutJob($printJob);
        if (!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to update laser job'));
        }

        $this->respond(new Response(
            Response::CREATED, 
            'Successfully confirmed laser job')
        );

    }

    public function handleVerifyCutPayment() {
        $body = $this->requestBody;
        $this->requireParam('laserJobID');
        $laserJobID = $body['laserJobID'];
        
        $printJob = $this->laserDao->getLaserJobById($laserJobID);
        if (empty($printJob)) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Unable to obtain laser job from ID'));
        }
        $printJob = $printJob[0];

        $printJob->setPaymentDate((new \DateTime())->format('Y-m-d H:i:s'));
        $ok = $this->laserDao->updateCutJob($printJob);
        if (!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to update laser job'));
        }

        $this->respond(new Response(
            Response::CREATED, 
            'Successfully updated laser job')
        );
    }


    public function handleDeleteCutJob() {

        $body = $this->requestBody;
        $this->requireParam('laserJobID');

        $ok = $this->laserDao->deleteCutJobByID($body['laserJobID']);

        if (!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to delete cut job'));
        }

        $this->respond(new Response(
            Response::OK,
            'Successfully deleted cut job'
        ));
    }

    public function handleProcessCutJob() {
        $body = $this->requestBody;

        $this->requireParam('laserJobID');
        $laserJobID = $body['laserJobID'];

        $printJob = $this->laserDao->getLaserJobById($laserJobID);
        if (empty($printJob)) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Unable to obtain print job from ID'));
        }
        $printJob = $printJob[0];

        $printJob->setValidCutDate((new \DateTime())->format('Y-m-d H:i:s'));
        $printJob->setDateUpdate((new \DateTime())->format('Y-m-d H:i:s'));
        $printJob->setUserConfirmDate((new \DateTime())->format('Y-m-d H:i:s'));
        $printJob->setPaymentDate((new \DateTime())->format('Y-m-d H:i:s'));
        $printJob->setCompleteCutDate((new \DateTime())->format('Y-m-d H:i:s'));
        $printJob->setPendingCustomerResponse(0);

        $ok = $this->laserDao->updateCutJob($printJob);
        if (!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to update print job'));
        }

        $this->respond(new Response(
            Response::CREATED, 
            'Successfully processed print job')
        );
    }

    public function handleCreateLaserMaterial(){
        $body = $this->requestBody;

        $printType = new LaserMaterial();

        $printType->setLaserMaterialName($body['name']);
        $printType->setCostPerSheet($body['cost']);
        $printType->setDescription($body['description']);

        $ok = $this->laserDao->addNewLaserMaterial($printType);
        if (!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to create new cut material'));
        }

        $this->respond(new Response(
            Response::CREATED, 
            'Successfully created new cut material type resource', 
            array('id' => $printType->getLaserMaterialId())
        ));
	}
	
	public function handleSaveLaserMaterial(){
		$id = $this->getFromBody('id');
		$name = $this->getFromBody('name');
		$description = $this->getFromBody('description');
		$costPerGram = $this->getFromBody('cost');
         
        $printType = $this->laserDao->getLaserMaterialByID($id);
   
        if (empty($printType)){
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Unable to obtain laser material from ID'));
        }

		$printType->setLaserMaterialName($name);
        $printType->setDescription($description);
        $printType->setCostPerSheet($costPerGram);

        $ok = $this->laserDao->updateLaserMaterial($printType);
        if (!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to save laser material'));
        }

        $this->respond(new Response(
            Response::OK,
            'Successfully saved laser material'
        ));
    }
    

        public function handleRemoveLaserMaterial() {
        $this->requireParam('materialID');
        $body = $this->requestBody;
        $ok = $this->laserDao->deleteLaserMaterialByID($body['materialID']);
        if (!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to delete cut material'));
        }
        $this->respond(new Response(
            Response::OK, 
            'Successfully delete cut material resource')
        );
    }

    function handleCompleteCutJob() {
        $body = $this->requestBody;

		$this->requireParam('laserJobID');
		$this->requireParam('userID');

        $laserJobID = $body['laserJobID'];

        $printJob = $this->laserDao->getLaserJobById($laserJobID);
        if (empty($printJob)) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Unable to obtain laser job from ID'));
        }

        $printJob = $printJob[0];
        
        $printJob->setDateUpdate((new \DateTime())->format('Y-m-d H:i:s'));


        $printJob->setCompleteCutDate((new \DateTime())->format('Y-m-d H:i:s'));

        if($printJob->getVoucherCode()) {

            $voucher = $this->coursePrintAllowanceDao->getVoucher($printJob->getVoucherCode());
            $dateUsed = (new \DateTime())->format('Y-m-d H:i:s');
            $userID = $body['userID'];
            $voucher->setUserID($userID);
            $voucher->setDateUsed($dateUsed);
            $ok = $this->coursePrintAllowanceDao->updateVoucher($voucher);
            if(!$ok) {
                $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to update voucher code'));
            }
        }

        $ok = $this->laserDao->updateCutJob($printJob);
        if (!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to update laser job'));
        }

        // $user = $this->userDao->getUserByID($body['userID']);

        // Change email method here
        // $this->mailer->sendPrintCompleteEmail($user, $printJob);

        // $replacements = array(
        //     "name" => $user->getFirstName(),
        //     "print" => $printJob->getStlFileName()
        // );

        // $ok = $this->printerEmailer('iutrwoejrlkdfjla', $user->getEmail(), $replacements);
        // if (!$ok) {
        //     $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to send email to user'));
        // }

        $this->respond(new Response(
            Response::CREATED, 
            'Successfully updated laser job and (not) send email')
        );

    }


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

            case 'createLaserCutter':
                $this->handleCreateLaserCutter();
            case 'saveLaserCutter':
                $this->handleSaveLaserCutter();
			case 'removeLaserCutter':
            	$this->handleRemoveLaserCutter();


            case 'createLaserMaterial':
                $this->handleCreateLaserMaterial();
            case 'saveLaserMaterial':
                $this->handleSaveLaserMaterial();
            case 'removeLaserMaterial':
                $this->handleRemoveLaserMaterial();

            case 'createCutJob':
                $this->handleCreateLaserJob();

            case 'updateEmployeeNotes':
                $this->handleUpdateEmployeeNotes();

            case 'sendCustomerConfirm':
                $this->handleSendCustomerConfirm();
            case 'customerConfirmCut':
                $this->handleCustomerConfirmCutJob();

            case 'verifyCutPayment':
                $this->handleVerifyCutPayment();

            case 'deleteCutJob':
                $this->handleDeleteCutJob();
            case 'processCutJob':
                    $this->handleProcessCutJob();

            case 'completeCutJob':
                $this->handleCompleteCutJob();

            default:
                $this->respond(new Response(Response::BAD_REQUEST, 'Invalid action on laser resource'));
        }
		
    }

}
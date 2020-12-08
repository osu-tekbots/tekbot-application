<?php
namespace Api;

use Model\LaserJob;
use Model\LaserMaterial;
use Model\Laser;

class LaserActionHandler extends ActionHandler {

    private $coursePrintAllowanceDao;

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

            case 'createCutJob':
                $this->handleCreateLaserJob();


            default:
                $this->respond(new Response(Response::BAD_REQUEST, 'Invalid action on printer resource'));
        }
		
    }

}
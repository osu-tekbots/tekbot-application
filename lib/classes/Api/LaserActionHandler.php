<?php
namespace Api;

use Model\Laser;
use Model\LaserJob;
use Model\LaserMaterial;
use DataAccess\QueryUtils;
use Util\Security;
use Email\TekBotsMailer;

class LaserActionHandler extends ActionHandler {

    private $voucherDao;

    /** @var \DataAccess\LaserDao */
    private $laserDao;
    /** @var \Email\TekBotsMailer */
    private $mailer;
    /** @var \Util\ConfigManager */
    private $config;
    /** @var \DataAccess\UsersDao */
    private $userDao;

    private $messageDao;

    public function __construct($laserDao, $voucherDao, $userDao, $mailer, $messageDao, $config, $logger) {
        parent::__construct($logger);
        $this->laserDao = $laserDao;
        $this->mailer = $mailer;
        $this->config = $config;
        $this->userDao = $userDao;
        $this->voucherDao = $voucherDao;
        $this->messageDao = $messageDao;
    }

    /**
     * Creates a new printer entry in the database.
     *
     * @return void
     */
    public function handleCreateLaserCutter() {
        // Ensure the user has permission to make the change
        $this->verifyAccessLevel('employee');
        
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
        // Ensure the user has permission to make the change
        $this->verifyAccessLevel('employee');
        
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
        // Ensure the user has permission to make the change
        $this->verifyAccessLevel('employee');

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
        // Ensure the user has permission to make the change
        $this->verifyAccessLevel(['user', 'employee']);
        
		$this->requireParam('userId');
        $this->requireParam('cutterId');
        $this->requireParam('quantity');
        $this->requireParam('cutMaterialId');
        $this->requireParam('dbFileName');
        $this->requireParam('dxfFileName');
		$this->requireParam('messageID');
		$body = $this->requestBody;

        $laserJob = new LaserJob();

        if($body['voucherCode']) {
            $voucher = $this->voucherDao->getVoucher($body['voucherCode']);
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
            } else {
                $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Voucher is not valid'));
            }

            $laserJob->setVoucherCode($body['voucherCode']);
        } else if($body['payment'] == "account") {
            // TODO: Add verification for account code
            $laserJob->setAccountCode($body['accountCode']);
        }

        $laserJob->setDbFileName($body['dbFileName']);
		$laserJob->setDxfFileName($body['dxfFileName']);
		$laserJob->setPaymentMethod($body['payment']);
		$laserJob->setCourseGroupId($body['courseGroup']);
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

		$user = $this->userDao->getUserByID($body['userId']);
		$laserMaterial = $this->laserDao->getLaserMaterialByID($laserJob->getLaserCutMaterialId());
		$message = $this->messageDao->getMessageByID($body['messageID']);
        $ok = $this->mailer->sendLaserEmail($user, $laserJob, $laserMaterial, $message);
		if (!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to send email to user'));
        }

        $this->respond(new Response(
            Response::CREATED, 
            'Successfully submitted cut job')
        );

    }
    
    public function handleUpdateEmployeeNotes() {
        // Ensure the user has permission to make the change
        $this->verifyAccessLevel('employee');
        
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
        // Ensure the user has permission to make the change
        $this->verifyAccessLevel('employee');
        
        $this->requireParam('laserJobID');
		$this->requireParam('userID');
		$this->requireParam('cutCost');
		// $this->requireParam('messageID');
		$body = $this->requestBody;

        $laserJobID = $body['laserJobID'];

        $laserJob = $this->laserDao->getLaserJobById($laserJobID);
        if (empty($laserJob)) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Unable to obtain laser job from ID'));
        }

        $laserJob = $laserJob[0];
        $laserMaterial = $this->laserDao->getLaserMaterialByID($laserJob->getLaserCutMaterialId());

        $laserJob->setValidCutDate((new \DateTime())->format('Y-m-d H:i:s'));
        $laserJob->setPendingCustomerResponse(true);

        $laserJob->setDateUpdate((new \DateTime())->format('Y-m-d H:i:s'));
        $laserJob->setTotalPrice($laserMaterial->getCostPerSheet() * $laserJob->getQuantity());
        $laserJob->setEmployeeNotes($laserJob->getEmployeeNotes() . "\nEmailed Cost: $" . number_format($laserJob->getTotalPrice(), 2));

        $ok = $this->laserDao->updateCutJob($laserJob);
        if (!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to update laser job'));
        }
		 
        $user = $this->userDao->getUserByID($body['userID']);
		$message = $this->messageDao->getMessageByID('jdkslkfajllkjfas');
		// $message = $this->messageDao->getMessageByID($body['messageID']);
        $ok = $this->mailer->sendLaserEmail($user, $laserJob, $laserMaterial, $message);
		if (!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to send email to user'));
        }

        $this->respond(new Response(
            Response::CREATED, 
            'Successfully updated laser job and sent email')
        );

    }
    
    function handleCustomerConfirmCutJob() {
        // Ensure the user has permission to make the change
        $this->verifyAccessLevel(['user', 'employee']);
        
        $body = $this->requestBody;

		$this->requireParam('laserJobID');

        $laserJobID = $body['laserJobID'];

        $laserJob = $this->laserDao->getLaserJobById($laserJobID);
        if (empty($laserJob)) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Unable to obtain laser job from ID'));
        }

        $laserJob = $laserJob[0];
        
        $laserJob->setDateUpdate((new \DateTime())->format('Y-m-d H:i:s'));


        $laserJob->setPendingCustomerResponse(0);
        $laserJob->setUserConfirmDate((new \DateTime())->format('Y-m-d H:i:s'));
        

        $ok = $this->laserDao->updateCutJob($laserJob);
        if (!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to update laser job'));
        }

        $this->respond(new Response(
            Response::CREATED, 
            'Successfully confirmed laser job')
        );

    }

    public function handleVerifyCutPayment() {
        // Ensure the user has permission to make the change
        $this->verifyAccessLevel('employee');
        
        $body = $this->requestBody;
        $this->requireParam('laserJobID');
        $laserJobID = $body['laserJobID'];
        
        $laserJob = $this->laserDao->getLaserJobById($laserJobID);
        if (empty($laserJob)) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Unable to obtain laser job from ID'));
        }
        $laserJob = $laserJob[0];

        $laserJob->setPaymentDate((new \DateTime())->format('Y-m-d H:i:s'));
        $ok = $this->laserDao->updateCutJob($laserJob);
        if (!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to update laser job'));
        }

        $this->respond(new Response(
            Response::CREATED, 
            'Successfully updated laser job')
        );
    }

    public function handleDeleteCutJob() {
        // Ensure the user has permission to make the change
        $this->verifyAccessLevel(['user', 'employee']);

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
        // Ensure the user has permission to make the change
        $this->verifyAccessLevel('employee');
        
        $body = $this->requestBody;

        $this->requireParam('laserJobID');
        $laserJobID = $body['laserJobID'];

        $laserJob = $this->laserDao->getLaserJobById($laserJobID);
        if (empty($laserJob)) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Unable to obtain print job from ID'));
        }
        $laserJob = $laserJob[0];

        $laserJob->setValidCutDate((new \DateTime())->format('Y-m-d H:i:s'));
        $laserJob->setDateUpdate((new \DateTime())->format('Y-m-d H:i:s'));
        $laserJob->setUserConfirmDate((new \DateTime())->format('Y-m-d H:i:s'));
        $laserJob->setPaymentDate((new \DateTime())->format('Y-m-d H:i:s'));
        $laserJob->setCompleteCutDate((new \DateTime())->format('Y-m-d H:i:s'));
        $laserJob->setPendingCustomerResponse(0);

        $ok = $this->laserDao->updateCutJob($laserJob);
        if (!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to update print job'));
        }

        $this->respond(new Response(
            Response::CREATED, 
            'Successfully processed print job')
        );
    }

    public function handleCreateLaserMaterial(){
        // Ensure the user has permission to make the change
        $this->verifyAccessLevel('employee');
        
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
        // Ensure the user has permission to make the change
        $this->verifyAccessLevel('employee');
        
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
        // Ensure the user has permission to make the change
        $this->verifyAccessLevel('employee');
        
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
        // Ensure the user has permission to make the change
        $this->verifyAccessLevel('employee');
        
        $this->requireParam('laserJobID');
		$this->requireParam('userID');
		// $this->requireParam('messageID');
		$body = $this->requestBody;

        $laserJobID = $body['laserJobID'];

        $laserJob = $this->laserDao->getLaserJobById($laserJobID);
        if (empty($laserJob)) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Unable to obtain laser job from ID'));
        }

        $laserJob = $laserJob[0];
        
        $laserJob->setDateUpdate((new \DateTime())->format('Y-m-d H:i:s'));


        $laserJob->setCompleteCutDate((new \DateTime())->format('Y-m-d H:i:s'));

        if($laserJob->getVoucherCode()) {

            // If there ever is an issue here, vouchers is not a foreign key anymore. A possibile error is if there somehow happened to be 2 vouchers with the same id values
            $voucher = $this->voucherDao->getVoucher($laserJob->getVoucherCode());
            $dateUsed = (new \DateTime())->format('Y-m-d H:i:s');
            $userID = $body['userID'];
            $voucher->setUserID($userID);
            $voucher->setDateUsed($dateUsed);
            $ok = $this->voucherDao->updateVoucher($voucher);
            if(!$ok) {
                $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to update voucher code'));
            }
        }

        $ok = $this->laserDao->updateCutJob($laserJob);
        if (!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to update laser job'));
        }

		$user = $this->userDao->getUserByID($body['userID']);
		$laserMaterial = $this->laserDao->getLaserMaterialByID($laserJob->getLaserCutMaterialId());
		$message = $this->messageDao->getMessageByID('ajlsekgjowefj');
		// $message = $this->messageDao->getMessageByID($body['messageID']);
        $ok = $this->mailer->sendLaserEmail($user, $laserJob, $laserMaterial, $message);
		if (!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to send email to user'));
        }

        $this->respond(new Response(
            Response::CREATED, 
            'Successfully updated laser job and sent email')
        );

    }


    /**
     * Sends an email with the contents entered into the webpage to the user who submitted the laser cut
     * 
     * @return void
     */
    public function handleSendUserEmail() {
        // Ensure the user has permission to make the change
        $this->verifyAccessLevel('employee');
        
        $this->requireParam('laserJobID');
        $this->requireParam('email');
        $this->requireParam('message');
        $body = $this->requestBody;

        if($body['message'] == '') $this->respond(new Response(Response::BAD_REQUEST, "Email body is empty"));

        $ok = $this->mailer->sendEmail($body['email'], 'Laser Cut Submission Follow-up', $body['message'], false);
		if (!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to send email to user'));
        }

        $this->respond(new Response(Response::OK, 'Successfully sent email'));
    }

    /**
     * Sends an email to Don with info about all uncharged laser cut jobs & marks them as charged
     * 
     * @return void
     */
    public function handleProcessAllFees() {
        // Ensure the user has permission to make the change
        $this->verifyAccessLevel('employee');
        
        $this->requireParam('messageID');
        $body = $this->requestBody;

        $unprocessedJobs = $this->laserDao->getUnchargedCompleteJobs();

        foreach($unprocessedJobs as $job) {
            if($job->getPaymentMethod() == 'voucher') {
                $voucher = $this->voucherDao->getVoucher($job->getVoucherCode());
                if(!$voucher) $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to get account code for voucher: '.$job->getVoucherCode()));
                $job->setAccountCode($voucher->getLinkedAccount());
            }
        }

        $message = $this->messageDao->getMessageByID($body['messageID']); 

        $ok = $this->mailer->sendToolProcessFeesEmail($unprocessedJobs, 'Cuts', $message);

        if(!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to send proccess fees email'));
        }

        $jobIds = [];
        foreach($unprocessedJobs as $up) {
            $jobIds[] = $up->getLaserJobId();
        }

        $ok = $this->laserDao->setChargeDate((new \DateTime())->format('Y-m-d H:i:s'), $jobIds);
        if(!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Did not mark cut jobs as charged'));
        }

        $this->respond(new Response(Response::OK, 'Sent process fees email and updated cut jobs'));
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
                
            case 'sendUserEmail':
                $this->handleSendUserEmail();
            
            case 'processAllFees':
                $this->handleProcessAllFees();

            default:
                $this->respond(new Response(Response::BAD_REQUEST, 'Invalid action on laser resource'));
        }
		
    }

}
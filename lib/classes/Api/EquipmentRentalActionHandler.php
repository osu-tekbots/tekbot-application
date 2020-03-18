<?php
namespace Api;
// This action handler will contain handlers for equipment checkout and equipment reservation
use Model\EquipmentCheckout;
use Model\EquipmentCheckoutStatus;
use Model\EquipmentReservation;
use Model\Equipment;
use Model\EquipmentFee;
use Model\User;
use Model\UserAccessLevel;
use DataAccess\QueryUtils;
use Util\Security;


/**
 * Defines the logic for how to handle AJAX requests made to modify project information.
 */
class EquipmentRentalActionHandler extends ActionHandler {

    /** @var \DataAccess\EquipmentCheckout */
    private $EquipmentCheckoutDao;
    /** @var \DataAccess\EquipmentReservation */
    private $EquipmentReservationDao;
    /** @var \DataAccess\ContractDao */
    private $ContractDao;
    /** @var \DataAccess\UsersDao */
    private $UsersDao;
    /** @var \DataAccess\EquipmentFeeDao */
    private $EquipmentFeeDao;
    /** @var \DataAccess\EquipmentDao */
    private $EquipmentDao;
    /** @var \Email\EquipmentRentalMailer */
    private $mailer;
    /** @var \Util\ConfigManager */
    private $config;

    /**
     * Constructs a new instance of the action handler for requests on project resources.
     *
     * @param \DataAccess\EquipmentCheckoutDao $EquipmentCheckoutDao the data access object for checkouts
     * @param \DataAccess\EquipmentReservationDao $EquipmentReservationDao the data access object for reservations
     * @param \Email\EquipmentRentalMailer $mailer the mailer used to send project related emails
     * @param \Util\ConfigManager $config the configuration manager providing access to site config
     * @param \Util\Logger $logger the logger to use for logging information about actions
     */
    public function __construct($EquipmentCheckoutDao, $EquipmentReservationDao, $ContractDao, $UsersDao, $EquipmentFeeDao, $EquipmentDao ,$mailer, $config, $logger) {
        parent::__construct($logger);
        $this->EquipmentCheckoutDao = $EquipmentCheckoutDao;
        $this->EquipmentReservationDao = $EquipmentReservationDao;
        $this->ContractDao = $ContractDao;
        $this->UsersDao = $UsersDao;
        $this->EquipmentFeeDao = $EquipmentFeeDao;
        $this->EquipmentDao = $EquipmentDao;
        $this->mailer = $mailer;
        $this->config = $config;
    }

    /**
     * Creates a new equipment checkout entry in the database.
     *
     * @return void
     */
    public function handleCreateEquipmentCheckout() {
        // Ensure all the requred parameters are present
        $this->requireParam('contractID');
        $this->requireParam('reservationID');
        $this->requireParam('userID');
        $this->requireParam('equipmentID');

        $body = $this->requestBody;

        // Get duration of checkout using the contract ID
        $contract = $this->ContractDao->getContract($body['contractID']);
        if (empty($contract)){
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, "Failed to get contract duration"));
        }
        $duration = $contract->getDuration();
        

        $checkout = new EquipmentCheckout();
        $checkout->setReservationID($body['reservationID']);
        $checkout->setUserID($body['userID']);
        $checkout->setEquipmentID($body['equipmentID']);
        $checkout->setStatusID(EquipmentCheckoutStatus::CHECKED_OUT);
        $checkout->setContractID($body['contractID']);
        $checkout->setPickupTime(new \DateTime());
        $checkout->setDeadlineTime(QueryUtils::timeAddToCurrent($duration));
        $checkout->setDateUpdated(new \DateTime());

    
        $ok = $this->EquipmentCheckoutDao->addNewCheckout($checkout);
        if (!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to create new equipment checkout'));
        }

        $reservation = $this->EquipmentReservationDao->getReservation($body['reservationID']);
        if (empty($reservation)){
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Unable to obtain reservation from ID'));
        }

        $reservation->setIsActive(FALSE);

        $ok = $this->EquipmentReservationDao->updateReservation($reservation);
        if (!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to close reservation'));
        }

        // Create email (pass in checkout and link)
        $user = $this->UsersDao->getUserByID($body['userID']);
        $link = $this->getAbsoluteLinkTo('pages/myProfile.php?id=' . $body['userID']);
        $this->mailer->sendEquipmentCheckoutEmail($checkout, $user, $link);

        $this->respond(new Response(
            Response::CREATED, 
            'Successfully created new equipment checkout', 
            array('id' => $checkout->getCheckoutID())
        ));
    }

    /**
     * Returns an equipment checkout entry in the database.
     *
     * @return void
     */
    public function handleReturnEquipmentCheckout() {
        // Ensure all the requred parameters are present
        $this->requireParam('checkoutID');
        $this->requireParam('checkoutNotes');

        $body = $this->requestBody;

        $checkoutID = $body['checkoutID'];
        $checkoutNotes = $body['checkoutNotes'];

        $checkout = $this->EquipmentCheckoutDao->getCheckout($checkoutID);
        if (empty($checkout)) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Unable to obtain checkout from ID'));
        }
        $deadlineTime = $checkout->getDeadlineTime();
        if (QueryUtils::isLate($deadlineTime)){
            $checkout->setStatusID(EquipmentCheckoutStatus::RETURNED_LATE);
        } else {
            $checkout->setStatusID(EquipmentCheckoutStatus::RETURNED);
        }

        $checkout->setNotes($checkoutNotes);
        $checkout->setReturnTime(new \DateTime());
        $checkout->setDateUpdated(new \DateTime());
    
        $ok = $this->EquipmentCheckoutDao->updateCheckout($checkout);
        if (!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to create return equipment'));
        }

        $this->respond(new Response(
            Response::CREATED, 
            'Successfully returned equipment'
        ));
    }

    /**
     * Handles when 'Handout' button is called.  Creates a modal on the employeeEquipment page
     */
    public function handleEquipmentModalHandout() {
          // Ensure all the requred parameters are present
          $this->requireParam('reservationID');
          $body = $this->requestBody;

          $reservation = $this->EquipmentReservationDao->getReservation($body['reservationID']);
          if (empty($reservation)) {
            $this->respond(new Response(Response::BAD_REQUEST, 'Unable to obtain reservation from ID'));
        }
          
          $hours = QueryUtils::timeAddToCurrent("24:00:00");
          $reservationID = $reservation->getReservationID();
          $equipmentID = $reservation->getEquipmentID();
          $userID = $reservation->getUserID();
          $contracts = $this->ContractDao->getEquipmentCheckoutContracts();
          
          $user = $this->UsersDao->getUserByID($userID);
          $equipment = $this->EquipmentDao->getEquipment($equipmentID);
      
          $equipmentName = Security::HtmlEntitiesEncode($equipment->getEquipmentName());
          $equipmentLocation = Security::HtmlEntitiesEncode($equipment->getLocation());
          $equipmentNotes = Security::HtmlEntitiesEncode($equipment->getNotes());
          $equipmentHealth = $equipment->getHealthID()->getName();
      
          $email = Security::HtmlEntitiesEncode($user->getEmail());
          $name = Security::HtmlEntitiesEncode($user->getFirstName()) 
          . ' ' 
          . Security::HtmlEntitiesEncode($user->getLastName());
          $phoneNumber = Security::HtmlEntitiesEncode($user->getPhone()); 
          $onid = Security::HtmlEntitiesEncode($user->getOnid());
      
          $modal = "
              <div class='modal fade' id='newHandoutModal$reservationID'>
                  <br><br><br><br>
              <div class='modal-dialog modal-lg'>
                  <div class='modal-content'>
      
                          <!-- Modal Header -->
                          <div class='modal-header'>
                          <h4 class='modal-title'>Hand out $equipmentName to $name</h4>
                          <button type='button' class='close' data-dismiss='modal'>&times;</button>
                          </div>
              
                          <!-- Modal body -->
                          <div class='modal-body'>
                              <h4 id='projectNameApplicationHeader'>$equipmentName</h4>
                              <p><b>Location:</b> $equipmentLocation</p>
                              <p><b>Health:</b> $equipmentHealth</p>
                              ";
                              if (!empty($equipmentNotes)){
                                  $modal .= "<p><b>Notes:</b> $equipmentNotes</p>";
                              }
                              $modal .= "
                              <h4 id='projectNameApplicationHeader'>$name</h4>
                              <p><b>ONID:</b> $onid</p>
                              <p><b>Email:</b> $email</p>
                              <br>
                              <select class='contract' id='$reservationID'>";
                                  foreach($contracts as $c){
                                      $contractID = $c->getContractID();
                                      $contractTitle = $c->getTitle();
                                      $modal .= "<option value='$contractID'>$contractTitle</option>";
                                  }
                              $modal .= "
                              </select>
                              <h5>Deadline Time:  <div style='display:inline-block;' id='deadline$reservationID'>$hours</div></h5>
                              <h6 class='text-secondary'>Weekends are accounted for, holidays are not.</h6>
                          </div>
      
                          <!-- Modal footer -->
                          <div class='modal-footer'>
                          <button type='button' class='btn btn-success' data-dismiss='modal' id='handoutEquipmentBtn$reservationID'>Handout</button>
                          <button type='button' class='btn btn-secondary' data-dismiss='modal'>Cancel</button>
                          </div>
      
                      </div>
                  </div>
              </div>

              <script type='text/javascript'>
        $('.contract').on('change', function() {
            let reservationID = $(this).attr('id');
            let contractID = $(this).attr('value');
            let deadlineID = '#deadline' + reservationID;
            $('#contract$reservationID').val();
            let data = {
                action: 'updateDeadlineText',
                contractID: contractID
            };
            api.post('/equipmentrental.php', data).then(res => {
                $(deadlineID).html(res.message);
            }).catch(err => {
                snackbar(err.message, 'error');
            });

        });

    
 		$('#handoutEquipmentBtn$reservationID').on('click', function() {
            let reservationID = '$reservationID';
            let contractID = $('#contract$reservationID').val();
            let userID = '$userID';
            let equipmentID = '$equipmentID';
 			let data = {
 				action: 'checkoutEquipment',
                reservationID: reservationID,
                contractID: contractID,
                userID: userID,
                equipmentID: equipmentID
 			};
 			api.post('/equipmentrental.php', data).then(res => {
 				$('#activeReservation$listNumber').remove();
                 snackbar(res.message, 'success');
                 setTimeout(function(){
                    window.location.reload(1);
                 }, 2000);
 			}).catch(err => {
 				snackbar(err.message, 'error');
 			});
         });
         
 	</script>
      
          ";

          $this->respond(new Response(
            Response::OK, 
            $modal            
        ));
    }


    
    /**
     * Returns the new date based on deadline change
     *
     * @return void
     */
    public function handleUpdateDeadlineText() {
        $contractID = $this->requireParam('contractID');

        $body = $this->requestBody;
        $id = $body['contractID'];
        
        $contract = $this->ContractDao->getContract($id);
        if (empty($contract)){
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, "Failed to get contractID"));
        }
        $duration = $contract->getDuration();
        $new_deadline = QueryUtils::timeAddToCurrent($duration);

        if (empty($new_deadline)) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to update text'));
        }

        $this->respond(new Response(
            Response::CREATED, 
            $new_deadline            
        ));
    }



    /**
     * Creates a new equipment reservation entry in the database.
     *
     * @return void
     */
    public function handleCreateEquipmentReservation() {
        $reserveDuration = 1;
        $this->requireParam('equipmentID');
        $this->requireParam('userID');

        $body = $this->requestBody;

        $isEquipmentAvailable = $this->EquipmentReservationDao->getEquipmentAvailableStatus($body['equipmentID']);
        if (!$isEquipmentAvailable) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Equipment already reserved or checked out'));
        }

        $reservation = new EquipmentReservation();
        $reservation->setEquipmentID($body['equipmentID']);
        $reservation->setUserID($body['userID']);
        $reservation->setDatetimeReserved(new \DateTime());
        $reservation->setDatetimeExpired(QueryUtils::HoursFromCurrentDate($reserveDuration));
        $reservation->setIsActive(true);

        $ok = $this->EquipmentReservationDao->addNewReservation($reservation);
        if (!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to create equipment reservation'));
        }

        $user = $this->UsersDao->getUserByID($body['userID']);

        $link = $this->getAbsoluteLinkTo('pages/myProfile.php?id=' . $body['userID']);
        $this->mailer->sendReservationAgreementEmail($user, $link);

        $this->respond(new Response(
            Response::OK,
            'Successfully created equipment reservation'
        ));
        
    }

    /**
     * Cancels an equipment reservation entry in the database.
     *
     * @return void
     */
    public function handleEquipmentCancelReservation(){
        $this->requireParam('reservationID');

        $body = $this->requestBody;
        $reservationID = $body['reservationID'];

        $reservation = $this->EquipmentReservationDao->getReservation($reservationID);
        if (empty($reservation)){
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Unable to obtain reservation from ID'));
        }

        $reservation->setIsActive(FALSE);

        $ok = $this->EquipmentReservationDao->updateReservation($reservation);
        if (!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to cancel reservation'));
        }

        $this->respond(new Response(
            Response::OK,
            'Successfully cancelled reservation'
        ));
        
    }
    
    /**
     * Assigns an equipment fee entry in the database.
     *
     * @return void
     */
    public function handleAssignEquipmentFees() {
        $this->requireParam('reservationID');
        $this->requireParam('checkoutID');
        $this->requireParam('feeAmount');
        $this->requireParam('userID');
        $this->requireParam('feeNotes');

        $body = $this->requestBody;


        // Make sure they have values 
        if ($body['feeAmount'] == 0 || empty($body['feeNotes'])){
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Both the amount and notes need to have input!'));
        }

        $equipmentFee = new EquipmentFee();
        $equipmentFee->setCheckoutID($body['checkoutID']);
        $equipmentFee->setUserID($body['userID']);
        $equipmentFee->setNotes($body['feeNotes']);
        $equipmentFee->setAmount($body['feeAmount']);
        $equipmentFee->setIsPaid(false);
        $equipmentFee->setIsPending(false);
        $equipmentFee->setDateUpdated(new \Datetime());

        $ok = $this->EquipmentFeeDao->addNewFee($equipmentFee);
        if (!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to assign fee'));
        }


        $user = $this->UsersDao->getUserByID($body['userID']);
        $link = $this->getAbsoluteLinkTo('pages/myProfile.php?id=' . $body['userID']);
        $this->mailer->sendAssignEquipmentFeesEmail($user, $equipmentFee, $link);

        // Assign fees
        $this->respond(new Response(
            Response::OK,
            'Successfully assigned fee'
        ));
    }

    public function handlePayEquipmentFees() {
        $this->requireParam('touchnetID');
        $this->requireParam('feeID');

        $body = $this->requestBody;

        $feeID = $body['feeID'];
        $touchnetID = $body['touchnetID'];

        $equipmentFee = $this->EquipmentFeeDao->getEquipmentFee($feeID);
        if (empty($equipmentFee)) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Unable to obtain checkout from ID'));
        }
        $equipmentFee->setPaymentInfo($touchnetID);
        $equipmentFee->setIsPending(1);

        $ok = $this->EquipmentFeeDao->updateFee($equipmentFee);
        if (!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to update fee'));
        }

        /*
        $userID = $equipmentFee->getUserID();

        // Send email
        $user = $this->UsersDao->getUserByID($userID);
        $this->mailer->sendPaidEquipmentFeesEmail($user, $equipmentFee);
        */
        
        $this->respond(new Response(
            Response::CREATED, 
            'Successfully submitted fee payment'
        ));
    }


    public function handleApproveEquipmentFees() {
        $this->requireParam('feeID');
        $body = $this->requestBody;
        $feeID = $body['feeID'];

        $equipmentFee = $this->EquipmentFeeDao->getEquipmentFee($feeID);
        if (empty($equipmentFee)) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Unable to obtain checkout from ID'));
        }
        $equipmentFee->setIsPending(0);
        $equipmentFee->setIsPaid(1);

        $ok = $this->EquipmentFeeDao->updateFee($equipmentFee);
        if (!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to update fee'));
        }

        $userID = $equipmentFee->getUserID();
        // Send email to user that fee is successful
        $user = $this->UsersDao->getUserByID($userID);
        $this->mailer->sendApproveEquipmentFeesEmail($user, $equipmentFee);

        $this->respond(new Response(
            Response::CREATED, 
            'Successfully updated fee payment'
        ));
    }

    public function handleRejectEquipmentFees() {
        $this->requireParam('feeID');
        $body = $this->requestBody;
        $feeID = $body['feeID'];

        $equipmentFee = $this->EquipmentFeeDao->getEquipmentFee($feeID);
        if (empty($equipmentFee)) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Unable to obtain checkout from ID'));
        }
        $equipmentFee->setIsPending(0);
        $equipmentFee->setIsPaid(0);

        $ok = $this->EquipmentFeeDao->updateFee($equipmentFee);
        if (!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to update fee'));
        }

        $userID = $equipmentFee->getUserID();
        // Handle reject email
        $user = $this->UsersDao->getUserByID($userID);
        $this->mailer->sendRejectEquipmentFeesEmail($user, $equipmentFee);

        $this->respond(new Response(
            Response::CREATED, 
            'Successfully updated fee payment'
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

            case 'createReservation':
                $this->handleCreateEquipmentReservation();

            case 'cancelReservation':
                $this->handleEquipmentCancelReservation();

            case 'checkoutEquipment':
                $this->handleCreateEquipmentCheckout();

            case 'returnEquipment':
                $this->handleReturnEquipmentCheckout();

            case 'updateDeadlineText':
                $this->handleUpdateDeadlineText();

            case 'assignEquipmentFees':
                $this->handleAssignEquipmentFees();

            case 'payEquipmentFees':
                $this->handlePayEquipmentFees();

            case 'approveEquipmentFees':
                $this->handleApproveEquipmentFees();

            case 'rejectEquipmentFees':
                $this->handleRejectEquipmentFees();
            
            case 'equipmentModalHandout':
                $this->handleEquipmentModalHandout();

            
            default:
                $this->respond(new Response(Response::BAD_REQUEST, 'Invalid action on project resource'));
        }
    }

    private function getAbsoluteLinkTo($path) {
        return $this->config->getBaseUrl() . $path;
    }
}
<?php
namespace Api;

// This action handler will contain handlers for equipment checkout and equipment reservation
use Model\EquipmentCheckout;
use Model\EquipmentReservation;
use Model\EquipmentHealthLog;
use Model\EquipmentHealthOption;
use Model\User;


/**
 * Defines the logic for how to handle AJAX requests made to modify project information.
 */
class EquipmentCheckoutActionHandler extends ActionHandler {

    /** @var \DataAccess\EquipmentCheckoutDao */
    private $equipmentCheckoutDao;
    /** @var \DataAccess\EquipmentHealthDao */
    private $equipmentHealthDao;
    /** @var \DataAccess\EquipmentItemDao */
    private $equipmentItemDao;
    /** @var \DataAccess\EquipmentTypeDao */
    private $equipmentTypeDao;
	/** @var \DataAccess\MessageDao */
	private $messageDao;
    /** @var \DataAccess\UsersDao */
    private $userDao;
    /** @var \Email\TekBotsMailer */
    private $mailer;
    /** @var \Util\ConfigManager */
    private $config;

    /**
     * Constructs a new instance of the action handler for requests on project resources.
     *
     * @param \DataAccess\EquipmentCheckoutDao $equipmentCheckoutDao the data access object for checkouts
     * @param \DataAccess\EquipmentItemDao $equipmentItemDao the data access object for equipment items
     * @param \DataAccess\EquipmentTypeDao $equipmentTypeDao the data access object for equipment types
     * @param \DataAccess\MessageDao $messageDao the data access object for email messages
     * @param \DataAccess\UserDao $userDao the data access object for users
     * @param \Email\TekBotsMailer $mailer the mailer used to send equipment related emails
     * @param \Util\ConfigManager $config the configuration manager providing access to site config
     * @param \Util\Logger $logger the logger to use for logging information about actions
     */
    public function __construct($equipmentCheckoutDao, $equipmentHealthDao, $equipmentItemDao, $equipmentTypeDao, $messageDao, $userDao, $mailer, $config, $logger) {
        parent::__construct($logger);
        $this->equipmentHealthDao = $equipmentHealthDao;
        $this->equipmentCheckoutDao = $equipmentCheckoutDao;
        $this->equipmentItemDao = $equipmentItemDao;
        $this->equipmentTypeDao = $equipmentTypeDao;
		$this->messageDao = $messageDao;
        $this->userDao = $userDao;
        $this->mailer = $mailer;
        $this->config = $config;
    }


    /**
     * Creates a new equipment reservation entry in the database.
     *
     * @return void
     */
    public function handleReserveEquipment() {
        // Ensure the user has permission to make the change
        $this->verifyAccessLevel(['user', 'employee']);

        $equipmentID = $this->getFromBody('equipmentID');
        $userID = $this->getFromBody('userID');

        $itemID = $this->equipmentCheckoutDao->getAvailableItem($equipmentID);
        if (!$itemID) {
            $this->respond(new Response(Response::BAD_REQUEST, 'No available units for this equipment'));
        }

        $reservation = new EquipmentReservation();
        $reservation->setItemID($itemID);
        $reservation->setUserID($userID);

        $ok = $this->equipmentCheckoutDao->addReservation($reservation);
        if (!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to reserve equipment'));
        }

        // Create email
        $messageID = 'wersdhwrujhssfuj';

        $user = $this->userDao->getUserByID($userID);
        $equipment = $this->equipmentTypeDao->getEquipmentByItemID($itemID);
		$message = $this->messageDao->getMessageByID($messageID);
        $ok = $this->mailer->sendEquipmentEmail($user, null, $equipment, $message);

        $this->respond(new Response(
            Response::OK,
            'Successfully reserved equipment'
        ));
    }


    /**
     * Cancels an equipment reservation entry in the database.
     *
     * @return void
     */
    public function handleCancelEquipmentReservation(){
        // Ensure the user has permission to make the change
        $this->verifyAccessLevel('employee');

        $reservationID = $this->getFromBody('reservationID');

        $ok = $this->equipmentCheckoutDao->dismissReservation($reservationID);
        if (!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to cancel reservation'));
        }

        $this->respond(new Response(Response::OK, 'Successfully cancelled reservation'));
    }


    /**
     * Returns details to populate the equipment checkout modal. Necessary for dynamic
     * population of available equipment items.
     * 
     * @return void
     */
    public function handleGetCheckoutDetails() {
        // Ensure the user has permission to make the change
        $this->verifyAccessLevel('employee');

        $equipmentID = $this->getFromBody('equipmentID');
        $userID = $this->getFromBody('userID');
        $reservedItemID = $this->getFromBody('reservedItemID', false);

        $equipment = $this->equipmentTypeDao->getEquipment($equipmentID);
        if (!$equipment) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to get equipment details'));
        }

        $user = $this->userDao->getUserByID($userID);
        if (!$user) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to get user details'));
        }

        $this->respond(new Response(
            Response::OK,
            'Found checkout details',
            [
                'equipment' => [
                    'name' => $equipment->getName(),
                    'notes' => $equipment->getNotes()
                ],
                'user' => [
                    'name' => $user->getFirstName()." ".$user->getLastName(),
                    'onid' => $user->getOnid(),
                    'email' => $user->getEmail()
                ],
                'items' => array_map(
                    fn($item) => [
                        'id' => $item->getItemID(),
                        'location' => $item->getLocation(),
                        'healthStatus' => $item->getHealthStatus(),
                        'notes' => $item->getNotes(),
                        'isPublic' => $item->getIsPublic()
                    ],
                    array_values( // Needed to avoid `array_filter` turning this into an associative array
                        array_filter(
                            $equipment->getInstances(), 
                            fn($item) => $item->getCheckoutStatus() == 'Available' || $item->getItemID() == $reservedItemID
                        )
                    )
                ),
            ]
        ));
    }


    /**
     * Creates a new equipment checkout entry in the database.
     *
     * @return void
     */
    public function handleCheckoutEquipment() {
        // Ensure the user has permission to make the change
        $this->verifyAccessLevel('employee');

        $itemID = $this->getFromBody('itemID');
        $userID = $this->getFromBody('userID');
        $reservationID = $this->getFromBody('reservationID', false);
        $dateDue = $this->getFromBody('dateDue');

        if (!$itemID) $this->respond(new Response(Response::BAD_REQUEST, 'Unit must be specified'));
        if (!$dateDue) $this->respond(new Response(Response::BAD_REQUEST, 'Return deadline must be specified'));
        if (!$reservationID) $reservationID = null;
        
        $activeReservation = $this->equipmentCheckoutDao->getActiveReservation($itemID);
        if ($activeReservation && $activeReservation->getReservationID() != $reservationID) {
            // Employee checkout page is outdated; trying to hand out item that's since been reserved elsewhere
            $this->respond(new Response(Response::BAD_REQUEST, 'This unit is actively reserved by someone else'));
        }

        $checkout = new EquipmentCheckout();
        $checkout->setReservationID($reservationID);
        $checkout->setItemID($itemID);
        $checkout->setUserID($userID);
        $checkout->setDateDue(new \DateTime($dateDue));

        $ok = $this->equipmentCheckoutDao->addCheckout($checkout);
        if (!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to check out equipment'));
        }

        // Create email
        $messageID = 'wersspdohssfuj';

        $user = $this->userDao->getUserByID($userID);
        $equipment = $this->equipmentTypeDao->getEquipmentByItemID($itemID);
		$message = $this->messageDao->getMessageByID($messageID);
        $ok = $this->mailer->sendEquipmentEmail($user, $checkout, $equipment, $message);

        $this->respond(new Response(Response::OK, 'Successfully checked out equipment'));
    }


    /**
     * Returns an equipment checkout entry in the database.
     *
     * @return void
     */
    public function handleReturnEquipment() {
        // Ensure the user has permission to make the change
        $this->verifyAccessLevel('employee');

        $checkoutID = $this->getFromBody('checkoutID');
        $location = $this->getFromBody('location');
        $notes =  $this->getFromBody('notes');
        $healthStatus =  $this->getFromBody('healthStatus');

        $checkout = $this->equipmentCheckoutDao->getCheckout($checkoutID);
        if (!$checkout) {
            $this->respond(new Response(Response::NOT_FOUND, 'Failed to find checkout'));
        }

        $checkout->setDateReturned(new \DateTime);
        $checkout->setDateUpdated(new \DateTime);

        $ok = $this->equipmentCheckoutDao->updateCheckout($checkout);
        if (!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to update checkout'));
        }

        $item = $this->equipmentItemDao->getItem($checkout->getItemID());
        if (!$item) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to find checked out unit'));
        }

        if ($location != $item->getLocation()) {
            $item->setLocation($location);
            $item->setDateUpdated(new \DateTime);

            $ok = $this->equipmentItemDao->updateItem($item);
            if (!$ok) {
                $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to update unit location'));
            }
        }

        $healthLog = new EquipmentHealthLog();
        $healthLog->setCheckoutID($checkoutID);
        $healthLog->setItemID($checkout->getItemID());
        $healthLog->setHealthOption(new EquipmentHealthOption($healthStatus));
        $healthLog->setNotes($notes);

        $ok = $this->equipmentHealthDao->addHealthLog($healthLog);
        if (!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to update health log'));
        }

        $messageID = 'fsrt56pdohssfuj';

        $user = $this->userDao->getUserByID($checkout->getUserID());
        $equipment = $this->equipmentTypeDao->getEquipmentByItemID($checkout->getItemID());
        $message = $this->messageDao->getMessageByID($messageID);
        $ok = $this->mailer->sendEquipmentEmail($user, $checkout, $equipment, $message);
        if (!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to send return email'));
        }

        $this->respond(new Response(Response::OK, 'Successfully returned equipment'));
    }


    /**
     * Handles the HTTP request on the API resource. 
     * 
     * This effectively will invoke the correct action based on the `action` parameter value in the request body. If
     * the `action` parameter is not in the body, the request will be rejected. Each action will individually handle its
     * authorization.
     *
     * @return void
     */
    public function handleRequest() {
        // Make sure the action parameter exists
        $action = $this->getFromBody('action');

        // Call the correct handler based on the action
        switch ($action) {

            case 'reserveEquipment':
                $this->handleReserveEquipment();

            case 'cancelReservation':
                $this->handleCancelEquipmentReservation();

            case 'getCheckoutDetails':
                $this->handleGetCheckoutDetails();

            case 'checkoutEquipment':
                $this->handleCheckoutEquipment();

            case 'returnEquipment':
                $this->handleReturnEquipment();
            
            default:
                $this->respond(new Response(Response::BAD_REQUEST, 'Invalid action on checkout resource'));
        }
    }
}
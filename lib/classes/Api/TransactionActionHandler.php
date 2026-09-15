<?php
namespace Api;

use Model\Transaction;
use Model\TransactionItem;
use Model\TransactionDelivery;

include_once PUBLIC_FILES . '/lib/cookies.php';
include_once PUBLIC_FILES . '/modules/inventoryFunctions.php';


/**
 * Defines the logic for how to handle AJAX requests made related to transactions.
 */
class TransactionActionHandler extends ActionHandler {

    /** @var \DataAccess\InventoryDao */
    private $inventoryDao;
    /** @var \DataAccess\TransactionDao */
    private $transactionDao;
    /** @var \DataAccess\MessageDao */
	private $messageDao;
    /** @var \Email\TekBotsMailer */
    private $mailer;
    /** @var \Util\ConfigManager */
    private $config;

    /**
     * Constructs a new instance of the action handler for requests related to transactions.
     *
     * @param \DataAccess\InventoryDao $inventoryDao the data access object for TekBots' inventory
     * @param \DataAccess\TransactionDao $transactionDao the data access object for transactions
     * @param \DataAccess\MessageDao $messageDao the data access object for messages
     * @param \Email\TekBotsMailer $mailer the object for sending TekBots site emails
     * @param \Util\ConfigManager $config the configuration manager providing access to site config
     * @param \Util\Logger $logger the logger to use for logging information about actions
     */
    public function __construct($inventoryDao, $transactionDao, $messageDao, $mailer, $config, $logger)
    {
        parent::__construct($logger);
        $this->inventoryDao = $inventoryDao;
        $this->transactionDao = $transactionDao;
        $this->messageDao = $messageDao;
        $this->mailer = $mailer;
        $this->config = $config;
    }


    /**
     * Creates a transaction in the database based on data in an HTTP request and returns
     * data needed to forward the user to Touchnet for payment.
     * 
     * This function, after invocation is finished, will exit the script via the `ActionHandler\respond()` function.
     *
     * @return void
     */
    public function handleCheckout() {
        refreshCartSession($this->inventoryDao);
        $cart = $_SESSION['cart'];
        
        $transaction = new Transaction();
        $transactionItems = [];

        $totalQuantity = 0;
        $totalCost = 0;
        foreach ($cart->getContents() as $item) {
            $totalQuantity += $item['quantity'];

            $studentPrice = $item['part']->getMarketPrice() ?: getStudentPrice($item['part']->getLastPrice());
            $totalCost += $item['quantity'] * $studentPrice;

            $i = new TransactionItem();
            $i->setTransactionId($transaction->getTransactionId());
            $i->setType($item['part']->getType());
            $i->setName($item['part']->getName());
            $i->setStocknumber($item['part']->getStocknumber());
            $i->setPrice($studentPrice);
            $i->setQuantity($item['quantity']);
            $transactionItems[] = $i;
        }

        $method = $this->getFromBody('method');
        $deliveryMethod = $this->transactionDao->getDeliveryMethod($method);
        if (!$deliveryMethod) {
            $this->respond(new Response(Response::BAD_REQUEST, 'Unable to find method'));
        }
        
        $totalCost += $deliveryMethod->getBaseCost() + $deliveryMethod->getItemCost() * $totalQuantity;

        $transaction->setUserId($_SESSION['userID']);
        $transaction->setDeliveryMethod($deliveryMethod);
        $transaction->setAmount($totalCost);

        if ($method != TransactionDelivery::PICKUP) {
            $name = $this->getFromBody('name');
            $email = $this->getFromBody('email');
            $phone = $this->getFromBody('phone');
            $address1 = $this->getFromBody('address1');
            $address2 = $this->getFromBody('address2');
            $city = $this->getFromBody('city');
            $state = $this->getFromBody('state');
            $country = $this->getFromBody('country');
            $zip = $this->getFromBody('zip');
            
            if (in_array('', [$name, $email, $phone, $address1, $city, $state, $country, $zip])) {
                $this->respond(new Response(
                    Response::BAD_REQUEST, 'Required parameters cannot be empty'
                ));
            }

            $transaction->setShippingName($name);
            $transaction->setShippingEmail($email);
            $transaction->setShippingPhone($phone);
            $transaction->setShippingAddress1($address1);
            $transaction->setShippingAddress2($address2);
            $transaction->setShippingCity($city);
            $transaction->setShippingState($state);
            $transaction->setShippingCountry($country);
            $transaction->setShippingCode($zip);
        }

        $ok = $this->transactionDao->addTransaction($transaction, $transactionItems);
        if(!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to create transaction'));
        }

        $apiConfig = $this->config->getThirdPartyApiConfig();

        // uPay uses validation keys to decrease the risk of tampering with the AMT field. See
        // their documentation for details on generating the key passed to their API.
        $validationKey = \base64_encode(
            \md5("{$apiConfig['upay']['validation_key']}{$transaction->getTransactionId()}{$transaction->getAmount()}", true)
        );

        // Sending data to uPay requires an HTTP POST. That can't be done via a redirect,
        // so just send back
        $this->respond(new Response(
            Response::CREATED,
            'Successfully started transaction',
            [
                'amount' => $transaction->getAmount(),
                'siteId' => $apiConfig['upay']['site_id'],
                'transactionId' => $transaction->getTransactionId(),
                'url' => $apiConfig['upay']['site_url'],
                'validationKey' => $validationKey,
            ]
        ));
    }


    /**
     * Updates a transaction with data from Touchnet.
     * 
     * This function, after invocation is finished, will exit the script via the `ActionHandler\respond()` function.
     *
     * @return void
     */
    public function handleUpdateTransaction() {
        $apiConfig = $this->config->getThirdPartyApiConfig();

        $postingKey = $this->getFromBody('posting_key');
        $transactionId = $this->getFromBody('EXT_TRANS_ID');
        $status = $this->getFromBody('pmt_status');

        if ($postingKey != $apiConfig['upay']['posting_key']) {
            // Security check: did this request actually come from Touchnet?
            $this->respond(new Response(Response::BAD_REQUEST, 'Failed to find transaction'));
        }

        $transaction = $this->transactionDao->getTransaction($transactionId);
        if (!$transaction) {
            $this->respond(new Response(Response::BAD_REQUEST, 'Failed to find transaction'));
        }
        
        switch ($status) {
            case 'success':
                if ($transaction->getAmount() != $this->getFromBody('pmt_amt')) {
                    $this->logger->error(
                        "Transaction amount {$transaction->getAmount()} did not match POST " .
                        "amount {$this->getFromBody('pmt_amt')} for {$transactionId}"
                    );
                    $this->respond(new Response(Response::BAD_REQUEST, 'Transaction amount did not match'));
                }

                $transaction->setStatus       (Transaction::SUCCESS);
                $transaction->setTpgTransId   ($this->getFromBody('tpg_trans_id'));
                $transaction->setSysTrackingId($this->getFromBody('sys_tracking_id'));
                $transaction->setCardType     ($this->getFromBody('card_type'));
                $transaction->setDatePaid     (new \DateTime($this->getFromBody('pmt_date')));
                break;
            
            case 'cancelled':
                $transaction->setStatus(Transaction::CANCELED);
                break;
            
            default:
                $this->respond(new Response(Response::BAD_REQUEST, "Unexpected status '$status'"));
        }

        $transaction->setDateUpdated(new \DateTime);
        $ok = $this->transactionDao->updateTransaction($transaction);
        if (!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Unable to update transaction'));
        }

        $this->respond(new Response(Response::OK, 'Updated transaction'));
    }


    /**
     * Updates the employee note for a transaction, allowing employees to keep track of
     * unusual situations like partial refunds.
     * 
     * This function, after invocation is finished, will exit the script via the `ActionHandler\respond()` function.
     *
     * @return void
     */
    public function handleUpdateEmployeeNotes() {
        $this->verifyAccessLevel('employee');

        $transactionId = $this->getFromBody('id');
        $notes = $this->getFromBody('notes');

        $transaction = $this->transactionDao->getTransaction($transactionId);
        if (!$transaction) {
            $this->respond(new Response(Response::BAD_REQUEST, 'Failed to find transaction'));
        }

        $transaction->setEmployeeNotes($notes);
        $transaction->setDateUpdated(new \DateTime);

        $ok = $this->transactionDao->updateTransaction($transaction);
        if (!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Unable to update transaction'));
        }

        $this->respond(new Response(Response::OK, 'Updated employee notes'));
    }


    /**
     * Marks a transaction as fulfilled, indicating that the materials paid for have been
     * picked up/shipped.
     * 
     * This function, after invocation is finished, will exit the script via the `ActionHandler\respond()` function.
     *
     * @return void
     */
    public function handleFulfillTransaction() {
        $this->verifyAccessLevel('employee');

        $transactionId = $this->getFromBody('id');

        $transaction = $this->transactionDao->getTransaction($transactionId);
        if (!$transaction) {
            $this->respond(new Response(Response::BAD_REQUEST, 'Failed to find transaction'));
        }

        $transaction->setDateFulfilled(new \DateTime);
        $transaction->setDateUpdated(new \DateTime);

        $ok = $this->transactionDao->updateTransaction($transaction);
        if (!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Unable to update transaction'));
        }

        $this->respond(new Response(Response::OK, 'Updated transaction'));
    }


    /**
     * Adds a new delivery method that users can select when paying
     * 
     * This function, after invocation is finished, will exit the script via the `ActionHandler\respond()` function.
     *
     * @return void
     */
    public function handleAddDeliveryMethod() {
        $this->verifyAccessLevel('employee');

        $name = $this->getFromBody('name');
        $baseCost = $this->getFromBody('baseCost');
        $itemCost = $this->getFromBody('itemCost');
        
        $delivery = new TransactionDelivery();
        $delivery->setName($name);
        $delivery->setBaseCost($baseCost);
        $delivery->setItemCost($itemCost);

        $ok = $this->transactionDao->addDeliveryMethod($delivery);
        if (!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Unable to add delivery method'));
        }

        $this->respond(new Response(Response::CREATED, 'Added delivery method'));
    }


    /**
     * Updates the delivery method that will show up as the default selection when paying.
     * 
     * This function, after invocation is finished, will exit the script via the `ActionHandler\respond()` function.
     *
     * @return void
     */
    public function handleSetDefaultDeliveryMethod() {
        $this->verifyAccessLevel('employee');

        $methodId = $this->getFromBody('id');

        $ok = $this->transactionDao->setDefaultDeliveryMethod($methodId);
        if (!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Unable to update default delivery method'));
        }

        $this->respond(new Response(Response::OK, 'Default delivery method updated'));
    }


    /**
     * Updates the name displayed for a given delivery method.
     * 
     * This function, after invocation is finished, will exit the script via the `ActionHandler\respond()` function.
     *
     * @return void
     */
    public function handleSetDeliveryMethodName() {
        $this->verifyAccessLevel('employee');

        $methodId = $this->getFromBody('id');
        $name = $this->getFromBody('name');

        $method = $this->transactionDao->getDeliveryMethod($methodId);
        if (!$method) {
            $this->respond(new Response(Response::NOT_FOUND, 'Could not find delivery method'));
        }

        $method->setName($name);

        $ok = $this->transactionDao->updateDeliveryMethod($method);
        if (!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Unable to update delivery method name'));
        }

        $this->respond(new Response(Response::OK, 'Delivery method updated'));
    }


    /**
     * Updates the base cost (cost for using the delivery method) for the given delivery
     * method.
     * 
     * This function, after invocation is finished, will exit the script via the `ActionHandler\respond()` function.
     *
     * @return void
     */
    public function handleSetDeliveryMethodBaseCost() {
        $this->verifyAccessLevel('employee');

        $methodId = $this->getFromBody('id');
        $cost = $this->getFromBody('cost');

        $method = $this->transactionDao->getDeliveryMethod($methodId);
        if (!$method) {
            $this->respond(new Response(Response::NOT_FOUND, 'Could not find delivery method'));
        }

        $method->setBaseCost($cost);

        $ok = $this->transactionDao->updateDeliveryMethod($method);
        if (!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Unable to update delivery method base cost'));
        }

        $this->respond(new Response(Response::OK, 'Delivery method updated'));
    }


    /**
     * Updates the item cost (cost per item included in the order) for the given delivery
     * method.
     * 
     * This function, after invocation is finished, will exit the script via the `ActionHandler\respond()` function.
     *
     * @return void
     */
    public function handleSetDeliveryMethodItemCost() {
        $this->verifyAccessLevel('employee');

        $methodId = $this->getFromBody('id');
        $cost = $this->getFromBody('cost');

        $method = $this->transactionDao->getDeliveryMethod($methodId);
        if (!$method) {
            $this->respond(new Response(Response::NOT_FOUND, 'Could not find delivery method'));
        }

        $method->setItemCost($cost);

        $ok = $this->transactionDao->updateDeliveryMethod($method);
        if (!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Unable to update delivery method item cost'));
        }

        $this->respond(new Response(Response::OK, 'Delivery method updated'));
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
            case 'checkout':
                $this->handleCheckout();
                break;

            case 'updateTransaction':
                $this->handleUpdateTransaction();
                break;

            case 'updateEmployeeNotes':
                $this->handleUpdateEmployeeNotes();
                break;

            case 'fulfillTransaction':
                $this->handleFulfillTransaction();
                break;

            case 'addDeliveryMethod':
                $this->handleAddDeliveryMethod();
                break;

            case 'setDefaultDeliveryMethod':
                $this->handleSetDefaultDeliveryMethod();
                break;

            case 'setDeliveryMethodName':
                $this->handleSetDeliveryMethodName();
                break;

            case 'setDeliveryMethodBaseCost':
                $this->handleSetDeliveryMethodBaseCost();
                break;

            case 'setDeliveryMethodItemCost':
                $this->handleSetDeliveryMethodItemCost();
                break;

            default:
                $this->respond(new Response(Response::BAD_REQUEST, 'Invalid action on transaction resource'));
        }
    }

}
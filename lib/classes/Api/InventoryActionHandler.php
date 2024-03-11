<?php
// Updated 11/5/2019
namespace Api;

use Model\Part;
use Model\InventoryType;
use Email\TekBotsMailer;
use DataAccess\UserDao;

/**
 * Defines the logic for how to handle AJAX requests made to modify user information.
 */
class InventoryActionHandler extends ActionHandler {

    /** @var \DataAccess\* */
    private $inventoryDao;
	private $userDao;
	private $messageDao;
	
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
    public function __construct($inventoryDao, $userDao, $messageDao, $logger)
    {
        parent::__construct($logger);
        $this->inventoryDao = $inventoryDao;
		$this->userDao = $userDao;
		$this->messageDao = $messageDao;
    }

	/**
     * Updates part location information  in the database based on data in an HTTP request.
     * 
     * This function, after invocation is finished, will exit the script via the `ActionHandler\respond()` function.
     *
     * @return void
     */
    public function handleUpdateLocation() {
        // Ensure the user has permission to make the change
        $this->verifyAccessLevel('employee');
        
        // Ensure the required parameters exist
        $this->requireParam('stockNumber');
		$this->requireParam('location');
        $body = $this->requestBody;

		$part = $this->inventoryDao->getPartByStocknumber($body['stockNumber']);
		
		 // Update the part
        $part->setLocation($body['location']);
        
        $ok = $this->inventoryDao->updatePart($part);
        if(!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Part Failed to Update'));
        }
		$this->logger->info($body['stockNumber'] . ' Location Updated: '.$body['location']);
        $this->respond(new Response(Response::OK, 'Inventory Location Updated: '.$body['location']));
		
           

    }


	/**
     * Updates part location information  in the database based on data in an HTTP request.
     * 
     * This function, after invocation is finished, will exit the script via the `ActionHandler\respond()` function.
     *
     * @return void
     */
    public function handleUpdateQuantity() {
        // Ensure the user has permission to make the change
        $this->verifyAccessLevel('employee');
        
        // Ensure the required parameters exist
        $this->requireParam('stockNumber');
		$this->requireParam('amount');
        $body = $this->requestBody;

		$part = $this->inventoryDao->getPartByStocknumber($body['stockNumber']);
		
		 // Update the part
        $part->setQuantity($body['amount']);
        
        $ok = $this->inventoryDao->updatePart($part);
        if(!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Part Failed to Update'));
        }

        $this->respond(new Response(Response::OK, 'On Hand Quantity Updated: '. $body['amount']));

    }
	
	
	/**
     * This section handles individual value updates for a part.
     * 
     */
	 
	/**
     * Updates part type information  in the database based on data from an HTTP request.
     * This function, after invocation is finished, will exit the script via the `ActionHandler\respond()` function.
     * @return void
     */
	public function handleUpdateType() {
        // Ensure the user has permission to make the change
        $this->verifyAccessLevel('employee');
        
        // Ensure the required parameters exist
        $this->requireParam('stockNumber');
		$this->requireParam('typeId');
        $body = $this->requestBody;


        $type = $this->inventoryDao->getTypeById($body['typeId']);
        if($type->getArchived())
        $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Type is unavaliable'));

		$part = $this->inventoryDao->getPartByStocknumber($body['stockNumber']);
        $part->setTypeId($body['typeId']);
        $ok = $this->inventoryDao->updatePart($part);
        if(!$ok)
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Part Failed to Update'));
		else
			$this->respond(new Response(Response::OK, 'Part Type Updated'));
    }
	
	/**
     * Updates the last price paid information  in the database based on data from an HTTP request.
     * This function, after invocation is finished, will exit the script via the `ActionHandler\respond()` function.
     * @return void
     */
	public function handleUpdateLastPrice() {
        // Ensure the user has permission to make the change
        $this->verifyAccessLevel('employee');
        
        // Ensure the required parameters exist
        $this->requireParam('stockNumber');
		$this->requireParam('lastPrice');
        $body = $this->requestBody;
		$part = $this->inventoryDao->getPartByStocknumber($body['stockNumber']);
        $part->setLastPrice($body['lastPrice']);
        $ok = $this->inventoryDao->updatePart($part);
        if(!$ok)
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Part Failed to Update'));
		else
			$this->respond(new Response(Response::OK, 'Last Price Paid Updated'));
    }
	
	/**
     * Updates part description information  in the database based on data from an HTTP request.
     * This function, after invocation is finished, will exit the script via the `ActionHandler\respond()` function.
     * @return void
     */
	public function handleUpdateDescription() {
        // Ensure the user has permission to make the change
        $this->verifyAccessLevel('employee');
        
        // Ensure the required parameters exist
        $this->requireParam('stockNumber');
		$this->requireParam('description');
        $body = $this->requestBody;
		$part = $this->inventoryDao->getPartByStocknumber($body['stockNumber']);
        $part->setName($body['description']);
        $ok = $this->inventoryDao->updatePart($part);
        if(!$ok)
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Part Failed to Update'));
		else
			$this->respond(new Response(Response::OK, 'Description Updated'));
    }
	
	/**
     * Updates the name for the last supplier the item was purchased from in the database based on data from an HTTP request.
     * This function, after invocation is finished, will exit the script via the `ActionHandler\respond()` function.
     * @return void
     */
	public function handleUpdateLastSupplier() {
        // Ensure the user has permission to make the change
        $this->verifyAccessLevel('employee');
        
        // Ensure the required parameters exist
        $this->requireParam('stockNumber');
		$this->requireParam('lastSupplier');
        $body = $this->requestBody;
		$part = $this->inventoryDao->getPartByStocknumber($body['stockNumber']);
        $part->setLastSupplier($body['lastSupplier']);
        $ok = $this->inventoryDao->updatePart($part);
        if(!$ok)
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Part Failed to Update'));
		else
			$this->respond(new Response(Response::OK, 'Last Supplier Name Updated'));
    }
	
	/**
     * Updates the name of the Manufacturer in the database based on data from an HTTP request.
     * This function, after invocation is finished, will exit the script via the `ActionHandler\respond()` function.
     * @return void
     */
	public function handleUpdateManufacturer() {
        // Ensure the user has permission to make the change
        $this->verifyAccessLevel('employee');
        
        // Ensure the required parameters exist
        $this->requireParam('stockNumber');
		$this->requireParam('manufacturer');
        $body = $this->requestBody;
		$part = $this->inventoryDao->getPartByStocknumber($body['stockNumber']);
        $part->setManufacturer($body['manufacturer']);
        $ok = $this->inventoryDao->updatePart($part);
        if(!$ok)
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Part Failed to Update'));
		else
			$this->respond(new Response(Response::OK, 'Manufacturer Updated'));
    }
	public function handleUpdateManufacturerNumber() {
        // Ensure the user has permission to make the change
        $this->verifyAccessLevel('employee');
        
        // Ensure the required parameters exist
        $this->requireParam('stockNumber');
		$this->requireParam('manufacturerNumber');
        $body = $this->requestBody;
		$part = $this->inventoryDao->getPartByStocknumber($body['stockNumber']);
        $part->setManufacturerNumber($body['manufacturerNumber']);
        $ok = $this->inventoryDao->updatePart($part);
        if(!$ok)
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Part Failed to Update'));
		else
			$this->respond(new Response(Response::OK, 'Manufacturer Part Number Updated'));
    }
	public function handleUpdateTouchnetId() {
        // Ensure the user has permission to make the change
        $this->verifyAccessLevel('employee');
        
        // Ensure the required parameters exist
        $this->requireParam('stockNumber');
		$this->requireParam('touchnetId');
        $body = $this->requestBody;
		$part = $this->inventoryDao->getPartByStocknumber($body['stockNumber']);
        $part->setTouchnetId($body['touchnetId']);
        $ok = $this->inventoryDao->updatePart($part);
        if(!$ok)
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Part Failed to Update'));
		else
			$this->respond(new Response(Response::OK, 'Touchnet ID Updated'));
    }
	public function handleUpdateMarketPrice() {
        // Ensure the user has permission to make the change
        $this->verifyAccessLevel('employee');
        
        // Ensure the required parameters exist
        $this->requireParam('stockNumber');
		$this->requireParam('marketPrice');
        $body = $this->requestBody;
		$part = $this->inventoryDao->getPartByStocknumber($body['stockNumber']);
        $part->setMarketPrice($body['marketPrice']);
        $ok = $this->inventoryDao->updatePart($part);
        if(!$ok)
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Part Failed to Update'));
		else
			$this->respond(new Response(Response::OK, 'Market Price Updated'));
    }
	public function handleCalculateMarketPrice() {
        // Ensure the user has permission to make the change
        $this->verifyAccessLevel('employee');
        
        $markup = .4;
		
		// Ensure the required parameters exist
        $this->requireParam('stockNumber');
		$this->requireParam('lastPrice');
        $body = $this->requestBody;
		$part = $this->inventoryDao->getPartByStocknumber($body['stockNumber']);
        $part->setMarketPrice((1+$markup)*$body['lastPrice']);
        $ok = $this->inventoryDao->updatePart($part);
        if(!$ok)
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Part Failed to Update'));
		else
			$this->respond(new Response(Response::OK, 'Market Price Calculated: ' . $body['lastPrice'] . ' *  ' . (1+$markup) . ' = ' . ((1+$markup)*$body['lastPrice'])));
    }
	
	public function handleCalculateLastPrice() {
        //This is only used for kits

        // Ensure the user has permission to make the change
        $this->verifyAccessLevel('employee');
        
        $kitfee = 1.25;
		
		// Ensure the required parameters exist
		// Assuming this is only called on a kit
        $this->requireParam('stockNumber');
		$this->requireParam('lastPrice');
        $body = $this->requestBody;
        $part = $this->inventoryDao->getPartByStocknumber($body['stockNumber']);
        
		$contents = $this->inventoryDao->getKitContentsByStocknumber($body['stockNumber']);
		$cost = $kitfee;
		foreach ($contents AS $key => $value){
			$p = $this->inventoryDao->getPartByStocknumber($key);
			$cost += ($p->getLastPrice() * $value);
		}
		
		$part->setLastPrice($cost);
        $ok = $this->inventoryDao->updatePart($part);
        if(!$ok)
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Part Failed to Update'));
		else
			$this->respond(new Response(Response::OK, 'Market Price Calculated'));
    }
	
	public function handleUpdatePartMargin() {
        // Ensure the user has permission to make the change
        $this->verifyAccessLevel('employee');
        
        // Ensure the required parameters exist
        $this->requireParam('stockNumber');
		$this->requireParam('partMargin');
        $body = $this->requestBody;
		$part = $this->inventoryDao->getPartByStocknumber($body['stockNumber']);
        $part->setPartMargin($body['partMargin']);
        $ok = $this->inventoryDao->updatePart($part);
        if(!$ok)
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Part Failed to Update'));
		else
			$this->respond(new Response(Response::OK, 'Part Margin Updated'));
    }
	public function handleAddPart() {
        // Ensure the user has permission to make the change
        $this->verifyAccessLevel('employee');
        
        // Ensure the required parameters exist
        $this->requireParam('type');
		$this->requireParam('desc');
        $body = $this->requestBody;

        $type = $this->inventoryDao->getTypeById($body['type']);
        if($type->getArchived())
        $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Type is unavaliable'));
		
		$part = new Part();
        $part->setTypeId($body['type']);
        $part->setName($body['desc']);
        $ok = $this->inventoryDao->addPart($part);
        if(!$ok)
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Part Failed to be added'));
		else
			$this->respond(new Response(Response::OK, 'Part Added'));
    }
	public function handleUpdateArchived() {
        // Ensure the user has permission to make the change
        $this->verifyAccessLevel('employee');
        
        // Ensure the required parameters exist
        $this->requireParam('stockNumber');
		$this->requireParam('archived');
        $body = $this->requestBody;
		$part = $this->inventoryDao->getPartByStocknumber($body['stockNumber']);
        $part->setArchive($body['archived']);
        $ok = $this->inventoryDao->updatePart($part);
        if(!$ok)
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Part Failed to Update'));
		else
			$this->respond(new Response(Response::OK, 'Status Updated'));
    }
	public function handleUpdateStocked() {
        // Ensure the user has permission to make the change
        $this->verifyAccessLevel('employee');
        
        // Ensure the required parameters exist
        $this->requireParam('stockNumber');
		$this->requireParam('stocked');
        $body = $this->requestBody;
		$part = $this->inventoryDao->getPartByStocknumber($body['stockNumber']);
        $part->setStocked($body['stocked']);
        $ok = $this->inventoryDao->updatePart($part);
        if(!$ok)
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Part Failed to Update'));
		else
			$this->respond(new Response(Response::OK, 'Status Updated'));
    }
	public function handleUpdateComment() {
        // Ensure the user has permission to make the change
        $this->verifyAccessLevel('employee');
        
        // Ensure the required parameters exist
        $this->requireParam('stockNumber');
		$this->requireParam('comment');
        $body = $this->requestBody;
		$part = $this->inventoryDao->getPartByStocknumber($body['stockNumber']);
        $part->setComment($body['comment']);
        $ok = $this->inventoryDao->updatePart($part);
        if(!$ok)
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Part Failed to Update'));
		else
			$this->respond(new Response(Response::OK, 'Comment Updated'));
    }
	public function handleUpdatePublicDesc() {
        // Ensure the user has permission to make the change
        $this->verifyAccessLevel('employee');
        
        // Ensure the required parameters exist
        $this->requireParam('stockNumber');
		$this->requireParam('publicDescription');
        $body = $this->requestBody;
		$part = $this->inventoryDao->getPartByStocknumber($body['stockNumber']);
        $part->setPublicDescription($body['publicDescription']);
        $ok = $this->inventoryDao->updatePart($part);
        if(!$ok)
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Part Failed to Update'));
		else
			$this->respond(new Response(Response::OK, 'Public Description Updated'));
    }
	public function handleUpdateKitQuantity() {
        // Ensure the user has permission to make the change
        $this->verifyAccessLevel('employee');
        
        // Ensure the required parameters exist
        $this->requireParam('stockNumber');
		$this->requireParam('childid');
        $this->requireParam('quantity');
        $body = $this->requestBody;

        $ok = $this->inventoryDao->updateKitQuantity($body['stockNumber'],$body['childid'],$body['quantity']);
        if(!$ok)
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to Update'));
		else
			$this->respond(new Response(Response::OK, 'Quantity Updated'));
    }
	public function handleAddKitContents() {
        // Ensure the user has permission to make the change
        $this->verifyAccessLevel('employee');
        
        // Ensure the required parameters exist
        $this->requireParam('stockNumber');
		$this->requireParam('childid');
        $this->requireParam('quantity');
        $body = $this->requestBody;

        $ok = $this->inventoryDao->addKitContents($body['stockNumber'],$body['childid'],$body['quantity']);
        if(!$ok)
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to Update'));
		else
			$this->respond(new Response(Response::OK, 'Quantity Updated'));
    }
	public function handleAddSupplierForPart() {
        // Ensure the user has permission to make the change
        $this->verifyAccessLevel('employee');
        
        // Ensure the required parameters exist
        $this->requireParam('stockNumber');
		$this->requireParam('link');
        $this->requireParam('partnumber');
        $this->requireParam('supplier');
        $body = $this->requestBody;

        $ok = $this->inventoryDao->addPartSupplier($body['stockNumber'],$body['supplier'],$body['partnumber'],$body['link']);
        if(!$ok)
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to Update'));
		else
			$this->respond(new Response(Response::OK, 'Supplier Added'));
    }
	
	public function handleRemoveSupplierForPart() {
        // Ensure the user has permission to make the change
        $this->verifyAccessLevel('employee');
        
        // Ensure the required parameters exist
		$this->requireParam('id');
        $body = $this->requestBody;

        $ok = $this->inventoryDao->removePartSupplier($body['id']);
        if(!$ok)
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to Update'));
		else
			$this->respond(new Response(Response::OK, 'Supplier Removed'));
    }
	
	public function handleRemoveKitContents() {
        // Ensure the user has permission to make the change
        $this->verifyAccessLevel('employee');
        
        // Ensure the required parameters exist
        $this->requireParam('stockNumber');
		$this->requireParam('childid');
        $body = $this->requestBody;

        $ok = $this->inventoryDao->removeKitContents($body['stockNumber'],$body['childid']);
        if(!$ok)
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to Update'));
		else
			$this->respond(new Response(Response::OK, 'Quantity Updated'));
    }

    public function handleSendRecountEmail() {
        // Ensure the user has permission to make the change
        $this->verifyAccessLevel('employee');
        
        // Ensure the required parameters exist
        $this->requireParam('stockNumber');
        $this->requireParam('messageId');
        $body = $this->requestBody;

        $part = $this->inventoryDao->getPartByStockNumber($body['stockNumber']);
        $message = $this->messageDao->getMessageByID($body['messageId']);

        $mailer = New TekBotsMailer('tekbot-worker@engr.oregonstate.edu');
        $ok = $mailer->sendRecountEmail($part, $message);

        if (!$ok)
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to Send'));
        else
            $this->respond(new Response(Response::OK, 'Email Sent'));
    }

	public function handleAddType() {
        // Ensure the user has permission to make the change
        $this->verifyAccessLevel('employee');
        
        // Ensure the required parameters exist
        $this->requireParam('description');
        $body = $this->requestBody;

        $type = new InventoryType();

        $type->setDescription($body['description']);
        $type->setArchived(false);

        $ok = $this->inventoryDao->addType($type);

        if (!$ok)
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to Add'));
        else
            $this->respond(new Response(Response::OK, 'Type Added'));
            
    
    }
    public function handleUpdateTypeDescription() {
        // Ensure the user has permission to make the change
        $this->verifyAccessLevel('employee');
        
        // Ensure the required parameters exist
        $this->requireParam('id');
        $this->requireParam('description');
        $body = $this->requestBody;

        $type = $this->inventoryDao->getTypeById($body['id']);

        $type->setDescription($body['description']);
        $type->setDateUpdated(new \DateTime());

        $ok = $this->inventoryDao->updateType($type);

        if (!$ok)
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to Update'));
        else
            $this->respond(new Response(Response::OK, 'Type Updated'));
    }
	
	public function handleToggleArchiveType() {
        // Ensure the user has permission to make the change
        $this->verifyAccessLevel('employee');
        
        // Ensure the required parameters exist
        $this->requireParam('id');
        $body = $this->requestBody;

        $type = $this->inventoryDao->getTypeById($body['id']);

        $type->setArchived(!$type->getArchived());
        $type->setDateUpdated(new \DateTime());

        $ok = $this->inventoryDao->updateType($type);

        if (!$ok)
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to Archive'));
        else
            if($type->getArchived())
                $this->respond(new Response(Response::OK, 'Type Archived'));
            else
                $this->respond(new Response(Response::OK, 'Type Unarchived'));
    
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

            case 'updateLocation':
                $this->handleUpdateLocation();
				break;

			case 'addKitContents':
                $this->handleAddKitContents();
				break;

			case 'removeKitContents':
                $this->handleRemoveKitContents();
				break;

			case 'updateQuantity':
                $this->handleUpdateQuantity();
				break;

			case 'updateType':
                $this->handleUpdateType();
				break;
			
			case 'updateManufacturer':
                $this->handleUpdateManufacturer();
				break;
			
			case 'updateManufacturerNumber':
                $this->handleUpdateManufacturerNumber();
				break;
			
			case 'updateLastSupplier':
                $this->handleUpdateLastSupplier();
				break;
			
			case 'updateDescription':
                $this->handleUpdateDescription();
				break;
			
			case 'updateMarketPrice':
                $this->handleUpdateMarketPrice();
				break;
			
			case 'calculateMarketPrice':
                $this->handleCalculateMarketPrice();
				break;
			
			case 'calculateLastPrice':
                $this->handleCalculateLastPrice();
				break;
			
			case 'updateTouchnetId':
                $this->handleUpdateTouchnetId();
				break;
			
			case 'updateLastPrice':
                $this->handleUpdateLastPrice();
				break;

            case 'updatePartMargin':
                $this->handleUpdatePartMargin();
				break;
			
			case 'updateComment':
                $this->handleUpdateComment();
				break;

			case 'updateKitQuantity':
                $this->handleUpdateKitQuantity();
				break;

			case 'updateStocked':
                $this->handleUpdateStocked();
				break;

			case 'updateArchived':
                $this->handleUpdateArchived();
				break;

			case 'addPart':
                $this->handleAddPart();
				break;

			case 'addSupplierForPart':
                $this->handleAddSupplierForPart();
				break;

            case 'removeSupplierForPart':
                $this->handleRemoveSupplierForPart();
				break;

			case 'updatePublicDesc':
                $this->handleUpdatePublicDesc();
				break;

            case 'sendRecountEmail':
                $this->handleSendRecountEmail();
                break;

            case 'addType':
                $this->handleAddType();
                break;

            case 'toggleArchiveType':
                $this->handleToggleArchiveType();
                break;

            case 'updateTypeDescription':
                $this->handleUpdateTypeDescription();
                break;

            default:
                $this->respond(new Response(Response::BAD_REQUEST, 'Invalid action on Part resource'));
        }
    }

}
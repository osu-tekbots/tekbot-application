<?php
// Updated 11/5/2019
namespace Api;

use Model\Station;
use Model\StationContents;
use Model\StationEquipment;
use DataAccess\LabDao;

/**
 * Defines the logic for how to handle AJAX requests made to modify user information.
 */
class LabActionHandler extends ActionHandler {
    /** @var \DataAccess\* */
	private $labDao;

    public function __construct($labDao, $logger)
    {
        parent::__construct($logger);
		$this->labDao = $labDao;
    }

    public function handleUpdateEquipmentStatus() {
        // Ensure the user has permission to make the change
        $this->verifyAccessLevel('employee');
        
        $this->requireParam('id');
        $this->requireParam('status');

        $body = $this->requestBody;

        $ok = $this->labDao->updateEquipmentStatus($body['id'], $body['status']);

        if (!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Unable to Update Equipment Status'));
        }

        $this->respond(new Response(Response::OK, 'Successfully Updated Equipment Status'));
    }

    public function handleUpdateEquipmentType() {
        // Ensure the user has permission to make the change
        $this->verifyAccessLevel('employee');
        
        $this->requireParam('id');

        $body = $this->requestBody;

        $ok = $this->labDao->updateEquipmentType($body['id'], $body['type']);

        if (!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Unable to Update Type'));
        }

        $this->respond(new Response(Response::OK, 'Successfully Updated Equipment Type'));
    }

    public function handleUpdateEquipmentModel() {
        // Ensure the user has permission to make the change
        $this->verifyAccessLevel('employee');
        
        $this->requireParam('id');

        $body = $this->requestBody;

        $ok = $this->labDao->updateEquipmentModel($body['id'], $body['model']);

        if (!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Unable to Update Model'));
        }

        $this->respond(new Response(Response::OK, 'Successfully Updated Equipment Model'));
    }

    public function handleUpdateEquipmentManual() {
        // Ensure the user has permission to make the change
        $this->verifyAccessLevel('employee');
        
        $this->requireParam('id');

        $body = $this->requestBody;

        $ok = $this->labDao->updateEquipmentManual($body['id'], $body['manual']);

        if (!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Unable to Update Manual'));
        }

        $this->respond(new Response(Response::OK, 'Successfully Updated Equipment Manual'));
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

            case 'updateEquipmentStatus':
                $this->handleUpdateEquipmentStatus();
				break;
            case 'updateEquipmentType':
                $this->handleUpdateEquipmentType();
                break;
            case 'updateEquipmentModel':
                $this->handleUpdateEquipmentModel();
                break;
            case 'updateEquipmentManual':
                $this->handleUpdateEquipmentManual();
                break;
            default:
                $this->respond(new Response(Response::BAD_REQUEST, 'Invalid action on Ticket resource'));
        }
    }
}
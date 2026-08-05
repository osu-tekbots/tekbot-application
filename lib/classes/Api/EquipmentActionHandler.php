<?php
namespace Api;

use Model\EquipmentHealthLog;
use Model\EquipmentHealthOption;
use Model\EquipmentUnit;
use Model\EquipmentType;


/**
 * Defines the logic for how to handle AJAX requests made to modify equipment information.
 */
class EquipmentActionHandler extends ActionHandler {

    /** @var \DataAccess\EquipmentTypeDao */
    private $equipmentTypeDao;
    /** @var \DataAccess\EquipmentUnitDao */
    private $equipmentUnitDao;
    /** @var \DataAccess\EquipmentHealthDao */
    private $equipmentHealthDao;
    /** @var \Util\ConfigManager */
    private $config;
    
    /**
     * Constructs a new instance of the action handler for requests on equipment resources.
     *
     * @param \DataAccess\EquipmentTypeDao $equipmentTypeDao the data access object for equipment types
     * @param \DataAccess\EquipmentUnitDao $equipmentUnitDao the data access object for equipment units
     * @param \Util\ConfigManager $config the configuration manager providing access to site config
     * @param \Util\Logger $logger the logger to use for logging information about actions
     */
    public function __construct($equipmentTypeDao, $equipmentUnitDao, $equipmentHealthDao, $config, $logger) {
        parent::__construct($logger);
        $this->equipmentTypeDao = $equipmentTypeDao;
        $this->equipmentUnitDao = $equipmentUnitDao;
        $this->equipmentHealthDao = $equipmentHealthDao;
        $this->config = $config;
    }


    /**
     * Creates a new equipment entry in the database.
     *
     * @return void
     */
    public function handleCreateEquipment() {
        // Ensure the user has permission to make the change
        $this->verifyAccessLevel('employee');

        $title = $this->getFromBody('title');

        $equipment = new EquipmentType();
        $equipment->setName($title);
        $equipment->setDateCreated(new \DateTime());

        $ok = $this->equipmentTypeDao->addEquipment($equipment);
        if (!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to create new equipment'));
        }

        $this->respond(new Response(
            Response::CREATED, 
            'Successfully created new equipment', 
            array('id' => $equipment->getEquipmentID())
        ));
    }


    /**
     * Creates a new equipment unit entry in the database.
     * 
     * @return void
     */
    public function handleCreateEquipmentUnit() {
        // Ensure the user has permission to make the change
        $this->verifyAccessLevel('employee');

        $equipmentID = $this->getFromBody('equipmentID');

        $unit = new EquipmentUnit();
        $unit->setEquipmentID($equipmentID);
        $unit->setDateCreated(new \DateTime());

        $id = $this->equipmentUnitDao->addUnit($unit);
        if (!$id) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to create equipment unit'));
        }

        $this->respond(new Response(
            Response::CREATED, 
            'Successfully created equipment unit',
            array('id' => $id)
        ));
    }


    /**
     * Updates fields editable from the user interface in a equipment entry in the database.
     *
     * @return void
     */
    public function handleSaveEquipment() {
        // Ensure the user has permission to make the change
        $this->verifyAccessLevel('employee');

        $id = $this->getFromBody('equipmentID');
        $name = $this->getFromBody('name');
        $description = $this->getFromBody('description');
        $notes = $this->getFromBody('notes');
        $parts = $this->getFromBody('parts');
        $usageInstructions = $this->getFromBody('usageInstructions');
        $returnCheck = $this->getFromBody('returnCheck');
        $replacementCost = $this->getFromBody('replacementCost');
      
        $equipment = $this->equipmentTypeDao->getEquipment($id);
        if (empty($equipment)){
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Unable to obtain equipment from ID'));
        }

        $equipment->setName($name);
        $equipment->setDescription($description);
        $equipment->setNotes($notes);
        $equipment->setParts($parts);
        $equipment->setUsageInstructions($usageInstructions);
        $equipment->setReturnCheck($returnCheck);
        $equipment->setReplacementCost($replacementCost);
        $equipment->setDateUpdated(new \Datetime);

        $ok = $this->equipmentTypeDao->updateEquipment($equipment);
        if (!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to save equipment'));
        }

        $this->respond(new Response(
            Response::OK,
            'Successfully saved equipment'
        ));
    }


    /**
     * Updates the provided equipment unit records
     *
     * @return void
     */
    public function handleSaveEquipmentUnit() {
        // Ensure the user has permission to make the change
        $this->verifyAccessLevel('employee');

        $unitId = $this->getFromBody('unitID');
        $isPublic = $this->getFromBody('isPublic', false);
        $location = $this->getFromBody('location', false);
        $notes = $this->getFromBody('notes', false);

        $unit = $this->equipmentUnitDao->getUnit($unitId);
        if (!$unit){
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Unable to obtain unit from ID'));
        }

        if (isset($isPublic)) $unit->setIsPublic($isPublic);
        if (isset($location)) $unit->setLocation($location);
        if (isset($notes)) $unit->setNotes($notes);
        $unit->setDateUpdated(new \Datetime);

        $ok = $this->equipmentUnitDao->updateUnit($unit);
        if (!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to update unit'));
        }

        $this->respond(new Response(
            Response::OK,
            'Successfully updated unit'
        ));
    }


    /**
     * Creates a new health log entry for the given equipment unit
     * 
     * @return void
     */
    public function handleSetEquipmentUnitHealth() {
        
        // Ensure the user has permission to make the change
        $this->verifyAccessLevel('employee');

        $unitID = $this->getFromBody('unitID');
        $healthOption = $this->getFromBody('healthStatus');
        $notes = $this->getFromBody('notes');

        $healthLog = new EquipmentHealthLog();
        $healthLog->setUnitID($unitID);
        $healthLog->setHealthOption(new EquipmentHealthOption($healthOption));
        $healthLog->setNotes($notes);

        $ok = $this->equipmentHealthDao->addHealthLog($healthLog);
        if (!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to update unit health'));
        }

        $this->respond(new Response(Response::OK, 'Successfully updated unit health'));
    }


    /**
     * Handles updating the default image for a equipment in the database.
     *
     * @return void
     */
    public function handleSetDefaultImage() {
        // Ensure the user has permission to make the change
        $this->verifyAccessLevel('employee');
    
        $imageId = $this->getFromBody('imageID');

        $image = $this->equipmentTypeDao->getEquipmentImage($imageId);
        if (!$image){
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Unable to obtain image from ID'));
        }

        $ok = $this->equipmentTypeDao->updateDefaultEquipmentImage($imageId);
        if (!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to update default equipment image'));
        }

        $this->respond(new Response(
            Response::OK,
            'Successfully updated default equipment image',
            array('name' => $image->getFilename())
        ));
    }


    /**
     * Handles archiving an equipment type in the database.
     *
     * @return void
     */
    public function handleDeleteEquipment() {
        // Ensure the user has permission to make the change
        $this->verifyAccessLevel('employee');

        $equipmentID = $this->getFromBody('equipmentID');

        $ok = $this->equipmentTypeDao->deleteEquipment($equipmentID);
        if (!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to delete equipment'));
        }

        $this->respond(new Response(
            Response::OK, 
            'Successfully deleted equipment'
        ));
    }


    /**
     * Handles archiving an equipment unit in the database.
     * 
     * @return void
     */
    public function handleDeleteEquipmentUnit() {
        // Ensure the user has permission to make the change
        $this->verifyAccessLevel('employee');

        $unitID = $this->getFromBody('unitID');

        $ok = $this->equipmentUnitDao->deleteUnit($unitID);
        if (!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to delete equipment unit'));
        }

        $this->respond(new Response(
            Response::OK, 
            'Successfully deleted equipment unit'
        ));
    }


    /**
     * Handles unarchiving an equipment type in the database.
     *
     * @return void
     */
    public function handleRestoreEquipment() {
        // Ensure the user has permission to make the change
        $this->verifyAccessLevel('employee');

        $equipmentID = $this->getFromBody('equipmentID');

        $ok = $this->equipmentTypeDao->restoreEquipment($equipmentID);
        if (!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to restore equipment'));
        }

        $this->respond(new Response(
            Response::OK, 
            'Successfully restored equipment'
        ));
    }


    /**
     * Handles unarchiving an equipment unit in the database.
     * 
     * @return void
     */
    public function handleRestoreEquipmentUnit() {
        // Ensure the user has permission to make the change
        $this->verifyAccessLevel('employee');

        $unitID = $this->getFromBody('unitID');

        $ok = $this->equipmentUnitDao->restoreUnit($unitID);
        if (!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to restore equipment unit'));
        }

        $this->respond(new Response(
            Response::OK, 
            'Successfully restored equipment unit'
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

            case 'createEquipment':
                $this->handleCreateEquipment();

            case 'createEquipmentUnit':
                $this->handleCreateEquipmentUnit();

            case 'saveEquipment':
                $this->handleSaveEquipment();

            case 'saveEquipmentUnit':
                $this->handleSaveEquipmentUnit();

            case 'setEquipmentUnitHealth':
                $this->handleSetEquipmentUnitHealth();

            case 'setDefaultImage':
                $this->handleSetDefaultImage();

            case 'deleteEquipment':
                $this->handleDeleteEquipment();

            case 'deleteEquipmentUnit':
                $this->handleDeleteEquipmentUnit();

            case 'restoreEquipment':
                $this->handleRestoreEquipment();

            case 'restoreEquipmentUnit':
                $this->handleRestoreEquipmentUnit();


            default:
                $this->respond(new Response(Response::BAD_REQUEST, 'Invalid action on equipment resource'));
        }
    }
}

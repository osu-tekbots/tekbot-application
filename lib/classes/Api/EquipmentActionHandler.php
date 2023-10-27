<?php
namespace Api;

use Model\Equipment;
use Model\EquipmentCategory;
use Model\EquipmentHealth;
use DataAccess\QueryUtils;




/**
 * Defines the logic for how to handle AJAX requests made to modify equipment information.
 */
class EquipmentActionHandler extends ActionHandler {

    /** @var \DataAccess\equipmentDao */
    private $equipmentDao;
    /** @var \Email\ProjectMailer */
    //private $mailer;
    /** @var \Util\ConfigManager */
    private $config;
    
    /**
     * Constructs a new instance of the action handler for requests on equipment resources.
     *
     * @param \DataAccess\CapstoneProjectsDao $equipmentDao the data access object for equipments
     * @param \DataAccess\CapstoneProjectsDao $usersDao the data access object for users
     * @param \Email\ProjectMailer $mailer the mailer used to send equipment related emails
     * @param \Util\ConfigManager $config the configuration manager providing access to site config
     * @param \Util\Logger $logger the logger to use for logging information about actions
     */
    public function __construct($equipmentDao , $config, $logger) {
        parent::__construct($logger);
        $this->equipmentDao = $equipmentDao;
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

        // Ensure all the requred parameters are present
        $this->requireParam('title');

        $body = $this->requestBody;

        $equipment = new Equipment();
        $equipment->setEquipmentName($body['title']);
        $equipment->setDateCreated(new \DateTime());

        $ok = $this->equipmentDao->addNewEquipment($equipment);
        if (!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to create new equipment'));
        }

        $this->respond(new Response(
            Response::CREATED, 
            'Successfully created new equipment resource', 
            array('id' => $equipment->getEquipmentID())
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
        $name = $this->getFromBody('equipmentName');
        //$categoryID = $this->getFromBody('equipmentCategoryID');
        $healthID = $this->getFromBody('equipmentHealthID');
        $description = $this->getFromBody('equipmentDescription');
        $notes = $this->getFromBody('equipmentNotes');
        $numberparts = $this->getFromBody('equipmentNumberparts');
        $location = $this->getFromBody('equipmentLocation');
        $partslist = $this->getFromBody('equipmentPartlist');
        $usageinstructions = $this->getFromBody('equipmentUsage');
        $equipmentcheck = $this->getFromBody('equipmentCheck');
        $instances = $this->getFromBody('instances');
        $replacecost = $this->getFromBody('replacementCost');
    
      
        $equipment = $this->equipmentDao->getEquipment($id);
        if (empty($equipment)){
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Unable to obtain equipment from ID'));
        }

        $equipment->setEquipmentName($name);
        $equipment->setDescription($description);
        $equipment->setNotes($notes);
        $equipment->setNumberParts($numberparts);
        $equipment->setLocation($location);
        $equipment->setPartList($partslist);
        $equipment->setUsageInstructions($usageinstructions);
        $equipment->setReturnCheck($equipmentcheck);
        $equipment->setInstances($instances);
        $equipment->setReplacementCost($replacecost);

        $equipment->getHealthID()->setId($healthID);
        //$equipment->getCategoryID()->setId($categoryID);

        $equipment->setDateUpdated(new \Datetime);

        $ok = $this->equipmentDao->updateEquipment($equipment);
        if (!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to save equipment'));
        }

        $this->respond(new Response(
            Response::OK,
            'Successfully saved equipment'
        ));
    }

    /**
     * Sets is_public to true for equipment
     *
     * @return void
     */
    public function handleMakePublicEquipment() {
        // Not in use? -- noticed 8/28/23
        /* $id = $this->getFromBody('equipmentID');

        $equipment = $this->equipmentDao->getEquipment($id);
        if (empty($equipment)){
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Unable to obtain equipment from ID'));
        }

        $equipment->setIsPublic(TRUE);

        $ok = $this->equipmentDao->updateEquipment($equipment);
        if (!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to make equipment public'));
        }

        $this->respond(new Response(
            Response::OK,
            'Successfully made equipment public'
        )); */
    }

    /**
     * Sets is_public to false for equipment
     *
     * @return void
     */
    public function handleMakeHiddenEquipment() {
        // Ensure the user has permission to make the change
        $this->verifyAccessLevel('employee');

        $id = $this->getFromBody('equipmentID');

        $equipment = $this->equipmentDao->getEquipment($id);
        if (empty($equipment)){
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Unable to obtain equipment from ID'));
        }

        $equipment->setIsPublic(FALSE);
        $equipment->setDateUpdated(new \Datetime);

        $ok = $this->equipmentDao->updateEquipmentVisiblity($equipment);
        if (!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to hide equipment'));
        }

        $this->respond(new Response(
            Response::OK,
            'Successfully hid equipment'
        ));
    }

    /**
     * Sets is_public to true for equipment
     *
     * @return void
     */
    public function handleShowEquipment() {
        // Ensure the user has permission to make the change
        $this->verifyAccessLevel('employee');

        $id = $this->getFromBody('equipmentID');

        $equipment = $this->equipmentDao->getEquipment($id);
        if (empty($equipment)){
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Unable to obtain equipment from ID'));
        }

        $equipment->setIsPublic(TRUE);
        $equipment->setDateUpdated(new \Datetime);

        $ok = $this->equipmentDao->updateEquipmentVisiblity($equipment);
        if (!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to hide equipment'));
        }

        $this->respond(new Response(
            Response::OK,
            'Successfully made equipment public'
        ));
    }

    /**
     * Handles updating the default image for a equipment in the database.
     *
     * @return void
     */
    public function handleDefaultImageSelected() {
        $imageId = $this->getFromBody('imageID');

        $image = $this->equipmentDao->getEquipmentImage($imageId);
        if (empty($image)){
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Unable to obtain image from ID'));
        }

        $image->setIsDefault(true);

        $ok = $this->equipmentDao->updateDefaultEquipmentImage($image);
        if (!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to update default equipment image'));
        }

        $this->respond(new Response(
            Response::OK,
            'Successfully updated default equipment image',
            array('name' => $image->getImageName())
        ));
    }

 
    /**
     * Handles archiving an equipment in the database.
     *
     * @return void
     */
    public function handleArchiveEquipment() {
        // Ensure the user has permission to make the change
        $this->verifyAccessLevel('employee');

        $id = $this->getFromBody('equipmentID');

        $equipment = $this->equipmentDao->getEquipment($id);
        if (empty($equipment)){
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Unable to obtain equipment from ID'));
        }

        $equipment->setIsArchived(true);

        $ok = $this->equipmentDao->updateEquipmentVisiblity($equipment);
        if (!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to archive equipment'));
        }

        $this->respond(new Response(
            Response::OK,
            'Successfully removed equipment'
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

            case 'saveEquipment':
                $this->handleSaveEquipment();

            case 'makeEquipmentHidden':
                $this->handleMakeHiddenEquipment();

            case 'makeEquipmentShown':
                $this->handleShowEquipment();

            case 'makeEquipmentArchive':
                $this->handleArchiveEquipment();

            // case 'makeEquipmentPublic':
                // $this->handleMakePublicEquipment();

            case 'defaultImageSelected':
                $this->handleDefaultImageSelected();


            default:
                $this->respond(new Response(Response::BAD_REQUEST, 'Invalid action on equipment resource'));
        }
    }

    private function getAbsoluteLinkTo($path) {
        return $this->config->getBaseUrl() . $path;
    }
}

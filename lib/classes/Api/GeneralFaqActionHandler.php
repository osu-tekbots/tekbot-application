<?php
namespace Api;

use Model\Faq;
use DataAccess\QueryUtils;



/**
 * Defines the logic for how to handle AJAX requests made to modify equipment information.
 */
class GeneralFaqActionHandler extends ActionHandler {

    /** @var \DataAccess\equipmentDao */
    private $faqDao;
    /** @var \Util\ConfigManager */
    private $config;
    
    /**
     * Constructs a new instance of the action handler for requests on equipment resources.
     * @param \DataAccess\faqDao $usersDao the data access object for users
     * @param \Util\ConfigManager $config the configuration manager providing access to site config
     * @param \Util\Logger $logger the logger to use for logging information about actions
     */
    public function __construct($faqDao, $config, $logger) {
        parent::__construct($logger);
        $this->faqDao = $faqDao;
        $this->config = $config;
    }

   
    /**
     * Updates a faq enrollment
     *
     * @return void
     */
    public function handleUpdateGeneralFaq() {

        $body = $this->requestBody;
        $id = $body['id'];
        $category = $body['category'];
        $question = $body['question'];
        $answer = $body['answer'];

        $faq = $this->faqDao->getFaq($id);
        if (empty($faq)){
            $this->respond(new Response(Response::NOT_FOUND, 'Unable to obtain faq from ID'));
        }

        $faq->setCategory($category);
        $faq->setQuestion($question);
        $faq->setAnswer($answer);

        $ok = $this->faqDao->updateFAQ($faq);
        if (!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to update faq'));
        }

        $this->respond(new Response(
            Response::OK,
            'Successfully updated faq'
        ));
    }

     /**
     * Creates a new faq entry in the database.
     *
     * @return void
     */
    public function handleCreateGeneralFaq() {
        // Ensure all the requred parameters are present
        $body = $this->requestBody;
        $id = $body['id'];
        $category = $body['category'];
        $question = $body['question'];
        $answer = $body['answer'];

        $faq = new Faq();
        $faq->setCategory($category);
        $faq->setQuestion($question);
        $faq->setAnswer($answer);

        $ok = $this->faqDao->addNewFaq($faq);
        if (!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to create faq'));
        }

        $this->respond(new Response(
            Response::OK,
            'Successfully created faq'
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

            case 'createGeneralFaq':
                $this->handleCreateGeneralFaq();

            case 'updateGeneralFaq':
                $this->handleUpdateGeneralFaq();

            default:
                $this->respond(new Response(Response::BAD_REQUEST, 'Invalid action on equipment resource'));
        }
    }

    private function getAbsoluteLinkTo($path) {
        return $this->config->getBaseUrl() . $path;
    }
}

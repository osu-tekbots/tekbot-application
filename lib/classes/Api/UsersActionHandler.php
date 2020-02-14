<?php
// Updated 11/5/2019
namespace Api;
use Model\UserAccessLevel;

/**
 * Defines the logic for how to handle AJAX requests made to modify user information.
 */
class UsersActionHandler extends ActionHandler {

    /** @var \DataAccess\UsersDao */
    private $dao;

    /**
     * Constructs a new instance of the action handler for requests on user resources.
     *
     * @param \DataAccess\UsersDao $dao the data access object for users
     * @param \Util\Logger $logger the logger to use for logging information about actions
     */
    public function __construct($dao, $logger)
    {
        parent::__construct($logger);
        $this->dao = $dao;
    }

    /**
     * Updates profile information about a user in the database based on data in an HTTP request.
     * 
     * This function, after invocation is finished, will exit the script via the `ActionHandler\respond()` function.
     *
     * @return void
     */
    public function saveUserProfile() {
        // Ensure the required parameters exist
        $this->requireParam('uid');
        $this->requireParam('firstName');
        $this->requireParam('lastName');
        $this->requireParam('email');
        $this->requireParam('phone');

        $body = $this->requestBody;

        // Get the existing user. 
        // TODO: If it isn't found, send a NOT_FOUND back to the client
        $user = $this->dao->getUserByID($body['uid']);

        // Update the user
        $user->setFirstName($body['firstName']);
        $user->setLastName($body['lastName']);
        $user->setEmail($body['email']);
        $user->setPhone($body['phone']);

        $ok = $this->dao->updateUser($user);

        if(!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to save user profile information'));
        }

        $this->respond(new Response(Response::OK, 'Successfully saved profile information'));

    }

    /**
     * Request handler for updating the user type after a user has logged in for the first time.
     *
     * @return void
     */
    function handleUpdateUserType() {
        $uid = $this->getFromBody('uid');
        $admin = $this->getFromBody('admin');

        $user = $this->dao->getUserByID($uid);
        if ($admin) {
            $user->getAccessLevelID()->setId(UserAccessLevel::EMPLOYEE);
        } else {
            $user->getAccessLevelID()->setId(UserAccessLevel::STUDENT);
        }
        $ok = $this->dao->updateUser($user);
        if (!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to update user type'));
        }
        $this->respond(new Response(
            Response::OK,
            'Successfully updated user type'
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
        $this->requireParam('action');

        // Call the correct handler based on the action
        switch($this->requestBody['action']) {

            case 'saveProfile':
                $this->saveUserProfile();

            case 'updateUserType':
                $this->handleUpdateUserType();

            default:
                $this->respond(new Response(Response::BAD_REQUEST, 'Invalid action on user resource'));
        }
    }

}
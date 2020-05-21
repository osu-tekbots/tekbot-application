<?php
// Updated 11/5/2019
namespace Api;
use Model\VoucherCode;
use Model\CourseGroup;
use Model\CoursePrintAllowance;

/**
 * Defines the logic for how to handle AJAX requests made to modify user information.
 */
class PrintCutGroupActionHandler extends ActionHandler {

    /** @var \DataAccess\CoursePrintAllowanceDao */
    private $dao;

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

    /*
    *   Handles the adding of additional voucher codes
    *   Returns a list with the voucher codes that were successfully added to the database
    */
    public function handleAddVoucherCodes() {
        // Ensure the required parameters exist
        $this->requireParam('num');
        $this->requireParam('serviceID');
        $this->requireParam('date_expired');

        $body = $this->requestBody;
        $num = $body['num'];
        $dateExpired = new \DateTime($body['date_expired']);
        $serviceID = $body['serviceID'];

        $VoucherList = "";
        for ($i = 0; $i < $num; $i++){
            $voucher = new VoucherCode();
            $voucher->setDateExpired($dateExpired);
            $voucher->setServiceID($serviceID);
            $voucher->setDateCreated(new \DateTime());
            $ok = $this->dao->addNewVoucherCode($voucher);
            if ($ok){
                $VoucherList .= $voucher->getVoucherID() . '&#13;&#10;';
            }
        }

        if(!$VoucherList) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to add voucher data'));
        }

        
        $this->respond(new Response(Response::OK, $VoucherList));

    }

    /*
    *   Handles the adding of a new course
    *   
    */
    public function handleAddCourse() {
        // Ensure the required parameters exist
        $this->requireParam('courseName');
        $this->requireParam('numberallowedprints');
        $this->requireParam('numberallowedcuts');

        $body = $this->requestBody;

        

        // TODO: If it isn't found, send a NOT_FOUND back to the client
        $course = new CoursePrintAllowance();
        $course->setCourseName($body['courseName']);
        $course->setNumberAllowedPrints($body['numberallowedprints']);
        $course->setNumberAllowedCuts($body['numberallowedcuts']);


        $ok = $this->dao->addNewCoursePrintAllowance($course);

        if(!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to add new course'));
        }

        $this->respond(new Response(Response::OK, 'Successfully added course'));

    }

    /*
    *   Handles the adding of a new group within a course
    *   
    */
    public function handleAddCourseGroup() {
        // Ensure the required parameters exist
        $this->requireParam('groupName');
        $this->requireParam('allowanceID');
        $this->requireParam('academicYear');
        $this->requireParam('dateExpired');
        $this->requireParam('dateCreated');
        

        $body = $this->requestBody;

        $courseGroup = new CourseGroup();
        $courseGroup->setGroupName($body['groupName']);
        $courseGroup->setAllowanceID($body['allowanceID']);
        $courseGroup->setAcademicYear($body['academicYear']);
        $courseGroup->setDateExpiration($body['dateExpired']);
        $courseGroup->setDateCreated($body['dateCreated']);

        $ok = $this->dao->addNewCourseGroup($courseGroup);

        // TODO: If it isn't found, send a NOT_FOUND back to the client
        // $course = new CoursePrintAllowance();
        // $course->setCourseName($body['courseName']);
        // $course->setNumberAllowedPrints($body['numberallowedprints']);
        // $course->setNumberAllowedCuts($body['numberallowedcuts']);


        // $ok = $this->dao->addNewCoursePrintAllowance($course);

        if(!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to add new course group'));
        }

        $this->respond(new Response(Response::OK, 'Successfully added course group'));

    }

    function handleClearVouchers() {
        // $voucher->setDateCreated(new \DateTime());
        $date = new \DateTime();
        $ok = $this->dao->clearVouchers($date);
        if(!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to delete vouchers'));
        }

        $this->respond(new Response(Response::OK, 'Successfully deleted vouchers'));
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

            case 'addVoucherCodes':
                $this->handleAddVoucherCodes();

            case 'clearVouchers':
                $this->handleClearVouchers();

            case 'addCourse':
                $this->handleAddCourse();
            
            case 'addGroup':
                $this->handleAddCourseGroup();

            default:
                $this->respond(new Response(Response::BAD_REQUEST, 'Invalid action on cut print group resource'));
        }
    }

}
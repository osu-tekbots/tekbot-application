<?php

namespace Api;
use Model\Voucher;
// use Model\CourseGroup;
// use Model\CoursePrintAllowance;


// formerly PrintCutGroupActionHandler
/**
 * Defines the logic for how to handle AJAX requests made to modify user information.
 */
class VoucherActionHandler extends ActionHandler {

    /** @var \DataAccess\VoucherDao */
    private $dao;

    public function __construct($dao, $logger)
    {
        parent::__construct($logger);
        $this->dao = $dao;
    }

    /*
    *   Handles the adding of additional voucher codes
    *   Returns a list with the voucher codes that were successfully added to the database
    */
    public function handleAddVouchers() {
        // Ensure the required parameters exist
        $this->requireParam('num');
        $this->requireParam('accountCode'); // Needed for linking w/ a payment account
        $this->requireParam('serviceID');
        $this->requireParam('date_expired');

        $body = $this->requestBody;
        $num = $body['num'];
        $accountCode = $body['accountCode'];
        $dateExpired = new \DateTime($body['date_expired']);
        $serviceID = $body['serviceID'];

        $VoucherList = "";
        for ($i = 0; $i < $num; $i++){
            $voucher = new Voucher();
            $voucher->setLinkedAccount($accountCode);
            $voucher->setDateExpired($dateExpired);
            $voucher->setServiceID($serviceID);
            $voucher->setDateCreated(new \DateTime());
            $ok = $this->dao->addNewVoucher($voucher);
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
    // Removed 8/31/23 -- Never implemented this version of handling vouchers; just making sure nothing breaks before deleting entirely
    /* public function handleAddCourse() {
        // Ensure the required parameters exist
        $this->requireParam('courseName');
        $this->requireParam('numberallowedprints');
        $this->requireParam('numberallowedcuts');

        $body = $this->requestBody;

        $course = new CoursePrintAllowance();
        $course->setCourseName($body['courseName']);
        $course->setNumberAllowedPrints($body['numberallowedprints']);
        $course->setNumberAllowedCuts($body['numberallowedcuts']);


        $ok = $this->dao->addNewCoursePrintAllowance($course);

        if(!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to add new course'));
        }

        $this->respond(new Response(Response::OK, 'Successfully added course'));

    } */

    /*
    *   Handles the adding of a new group within a course
    *   
    */
    // Removed 8/31/23 -- Never implemented this version of handling vouchers; just making sure nothing breaks before deleting entirely
    /* public function handleAddCourseGroup() {
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
        if(!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to add new course group'));
        }

        $this->respond(new Response(Response::OK, 'Successfully added course group'));

    } */

    function handleClearVouchers() {
        // $voucher->setDateCreated(new \DateTime());
        $date = new \DateTime();
        $ok = $this->dao->clearVouchers($date);
        if(!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to delete vouchers'));
        }

        $this->respond(new Response(Response::OK, 'Successfully deleted vouchers'));
    }

    
    function handleClearPrintVouchers() {
        // $voucher->setDateCreated(new \DateTime());
        $date = new \DateTime();
        $ok = $this->dao->clearPrintVouchers($date);
        if(!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Failed to delete vouchers'));
        }

        $this->respond(new Response(Response::OK, 'Successfully deleted vouchers'));
    }

    
    function handleClearCutVouchers() {
        // $voucher->setDateCreated(new \DateTime());
        $date = new \DateTime();
        $ok = $this->dao->clearCutVouchers($date);
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
            case 'addVoucherCodes':
                $this->handleAddVouchers();
                break;
            case 'clearVouchers':
                $this->handleClearVouchers();
                break;
            case 'clearPrintVouchers':
                $this->handleClearVouchers();
                break;
            case 'clearCutVouchers':
                $this->handleClearVouchers();
                break;
            /* case 'addCourse':
                $this->handleAddCourse();
                break;
            case 'addGroup':
                $this->handleAddCourseGroup();
                break; */
            default:
                $this->respond(new Response(Response::BAD_REQUEST, 'Invalid action on cut print group resource'));
        }
    }

}
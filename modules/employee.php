<?php

use Util\Security;
use DataAccess\UsersDao;
use DataAccess\EquipmentDao;
use DataAccess\EquipmentCheckoutDao;
use DataAccess\EquipmentFeeDao;
use DataAccess\EquipmentReservationDao;

/**
 * Renders the HTML for the panel that displays options for reviewing a capstone project to admins.
 *
 * @param \Model\CapstoneProject $project the project being reviewed
 * @param \Model\CapstoneProjectCategory[] $categories an array of the available project categories
 * @return void
 */
function renderAdminReviewPanel($project, $categories) {

    $pId = $project->getId();
    $pStatusName = $project->getStatus()->getName();
    $pCategoryId = $project->getCategory()->getId();
    $pCategoryName = $project->getCategory()->getName();
    $pIsHidden = $project->getIsHidden();
    $pIsArchived = $project->getIsArchived();
    $pComments = $project->getProposerComments();

    $actions = array();
    if ($pStatusName == 'Pending Approval') {
        $actions[] = 'Project Review';
    }
    if ($pCategoryName == 'None') {
        $actions[] = 'Project Category Placement';
    }
    $visibility = $pIsHidden
					? 'Private Project (Not viewable on Browse Projects)' 
					: 'Public Project';
    $actionsHtmlContent = count($actions) > 0 
					? 'Action Required: ' . implode(' and ', $actions)
                    : 'No action required at this time';

    $commentsHtml = $pComments != '' 
					? "<h6><p style='color:red'>Proposer Comments: $pComments</p></h6>"
                    : '';
    $isArchived = $pIsArchived
                    ? 'Archived Project (Not longer Active)'
                    : '';

    $options = '';
    foreach ($categories as $c) {
        $id = $c->getId();
        $name = $c->getName();
        $selected = $id == $pCategoryId ? 'selected' : '';
        $options .= "<option $selected value='$id'>$name</option>";
    }

    $viewButtonStyle = $pIsHidden ? 'display: none;' : '';

    echo "
    <br/>
    <div class='row'>
        <div class='col-sm-3'></div>
        <div class='col-sm-6 border rounded border-dark' id='adminProjectStatusDiv'>
            <center><h4><p style='color: black;'>-- Admin Project Status Review --</p></h4></center>
            <h6><p style='color:red'>$actionsHtmlContent</p></h6>
            <h6><p style='color:red'>$visibility</p></h6>
            <h6><p style='color:red'>$isArchived</p></h6>
            $commentsHtml
            <h6><p style='color:black'>Current Project Status: $pStatusName</p></h6>
            <h6><p style='color:black'>Major Category: $pCategoryName</p></h6>
            <select class='form-control' id='projectCategorySelect' data-toggle='tooltip'
                data-placement='top' title=''>
                $options
            </select>
            <center>
                <a href='pages/viewSingleProject.php?id=$pId'>
                    <button class='btn btn-lg btn-primary admin-btn' type='button' style='$viewButtonStyle' 
                        id='adminViewProjectBtn'>
                        View Project &raquo
                    </button>
                </a>
                <button class='btn btn-lg btn-success admin-btn' type='button' 
                    id='adminApproveProjectBtn'>Approve Project</button>
                <button class='btn btn-lg btn-danger admin-btn' type='button' 
                    id='adminUnapproveProjectBtn'>Reject/Unapprove Project</button>
                <br/>
                <button class='btn btn-lg btn-outline-danger admin-btn' type='button' 
                    id='adminMakeProjectPrivateBtn'>Make Project Private</button>
                <button class='btn btn-lg btn-outline-info admin-btn' type='button' 
                    id='adminMakeProjectNotPrivateBtn'>Make Project Public</button>
                <button class='btn btn-lg btn-outline-danger admin-btn' type='button' 
                    id='adminMakeProjectArchivedBtn'>Archive Project</button>
                <a href='pages/adminProject.php'>
                    <button class='btn btn-lg btn-primary admin-btn' type='button' 
                        id='adminReturnBtn'>Return &raquo</button>
                </a>
            </center>
            <div id='approvedText' class='adminText' 
                style='color: green;'>Project Approved!</div>
            <div id='rejectedText' class='adminText' 
                style='color: red;'>Project Rejected!</div>
            <div id='privateText' class='adminText' 
                style='color: red;'>Project Now Private! (Will NOT show up in Browse Projects)</div>
            <div id='publicText' class='adminText' 
                style='color: blue;'>Project Now Public! (WILL show up in Browse Projects)</div>
            <div id='categoryText' class='adminText' 
                style='color: green;'>Category Changed!</div>
            <div id='archivedText' class='adminText' 
                style='color: blue;'>Project Archived! (No Longer Active)</div>
        </div>
    </div>
    <div class='col-sm-3'></div>
    ";
}

// Checks if the entry for student ID is valid
function isValidStudentID($sid){
    $ID = trim($sid);
    if (!$ID) {
        return 0;
    }
    if (strlen($ID) != 9){
        return 0;
    }
    if (!is_numeric($ID)){
        return 0;
    }

    return 1;
}


/**
 * Renders the HTML for the panel that displays options for reviewing a capstone project to admins.
 *
 * @param \Model\CapstoneProject $project the project being reviewed
 * @param \Model\CapstoneProjectCategory[] $categories an array of the available project categories
 * @return void
 */

 function renderEmployeeSidebar() {

     echo'
    <br><br>
     <!-- Sidebar -->
     <ul class="sidebar navbar-nav">
        <li class="nav-item dropdown">
             <a class="nav-link dropdown-toggle" href="#" id="pagesDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="fas fa-fw fa-tools"></i>
                <span>Equipment</span>
             </a>
             <div class="dropdown-menu" aria-labelledby="pagesDropdown">
                 <h6 class="dropdown-header">Reserved/Checked Out:</h6>
                 <a class="dropdown-item" href="pages/employeeEquipment.php">Checkout Equipment</a>
                 <div class="dropdown-divider"></div>
                 <h6 class="dropdown-header">Adjust Content:</h6>
                 <a class="dropdown-item" href="pages/employeeEquipmentList.php">Edit Equipment</a>
                 <a class="dropdown-item" href="blank.html">Info</a>
             </div>
        </li>

        <li class="nav-item dropdown">
         <a class="nav-link dropdown-toggle" href="#" id="pagesDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <i class="fas fa-fw fa-hand-rock"></i>
            <span>Kit Handout</span>
         </a>
         <div class="dropdown-menu" aria-labelledby="pagesDropdown">
             <h6 class="dropdown-header">Handout:</h6>
             <a class="dropdown-item" href="pages/employeeKitHandout.php">Kit Handout</a>
             <div class="dropdown-divider"></div>
             <h6 class="dropdown-header">View:</h6>
             <a class="dropdown-item" href="pages/employeeKitList.php">View Kit List</a>
             <a class="dropdown-item" href="pages/employeeInsertKitEnrollment.php">Add Kit Enrollments</a>
             <a class="dropdown-item" href="blank.html">Info</a>
         </div>
        </li>
        <li class="nav-item dropdown">
            <a style="color: lightblue;" class="nav-link dropdown-toggle" href="#" id="pagesDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="fas fa-fw fa-print"></i>
                <span>3D Printing</span>
            </a>
            <div class="dropdown-menu" aria-labelledby="pagesDropdown">
                <h6 class="dropdown-header">Print Jobs:</h6>
                <a class="dropdown-item" href="pages/employeePrintJobList.php">All Print Jobs</a>
                <a class="dropdown-item" href="pages/submit3DPrint.php">(Temp) Submission</a>
                <a class="dropdown-item" href="pages/employeePrinterVouchers.php">Print Vouchers</a>
                <div class="dropdown-divider"></div>
                <h6 class="dropdown-header">Modifying Print Tools:</h6>
				 <a class="dropdown-item" href="pages/employeePrinterMessages.php">Edit Messages</a>
                <a class="dropdown-item" href="pages/employeeAddPrinter.php">Printers/Print Types</a>
            </div>
        </li>
        <li class="nav-item dropdown">
            <a style="color: lightblue;" class="nav-link dropdown-toggle" href="#" id="pagesDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                 <i class="fas fa-fw fa-cut"></i>
                <span>Laser Cutting</span>
            </a>
            <div class="dropdown-menu" aria-labelledby="pagesDropdown">
                <h6 class="dropdown-header">Laser Cut Jobs:</h6>
                <a class="dropdown-item" href="pages/employeePrintJobList.php">All Cut Jobs</a>
                <a class="dropdown-item" href="pages/employeeCutVouchers.php">Cut Vouchers</a>
                <div class="dropdown-divider"></div>
                <h6 class="dropdown-header">Modifying Print Tools:</h6>
                <a class="dropdown-item" href="pages/employeeAddPrinter.php">Printers/Print Types</a>
            </div>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="pages/employeeFees.php">
                <i class="fas fa-fw fa-dollar-sign"></i>
                <span>Fees</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="pages/employeeUser.php">
                <i class="fas fa-fw fa-users"></i>
                <span>Users</span>
            </a>
        </li>
        <li class="nav-item dropdown">
         <a class="nav-link dropdown-toggle" href="#" id="pagesDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <i class="fas fa-fw fa-hand-rock"></i>
            <span>Inventory</span>
         </a>
         <div class="dropdown-menu" aria-labelledby="pagesDropdown">
             <a class="dropdown-item" href="pages/employeeInventory.php">Inventory List</a>
             <div class="dropdown-divider"></div>
             <h6 class="dropdown-header">Options:</h6>
             <a class="dropdown-item" href="pages/employeeInventoryMessages.php">Edit Messages</a>
         </div>
        </li>
        <li class="nav-item">
            <a style="color: lightblue;" target= "_blank" class="nav-link" href="https://trello.com/b/XUktYdsk/tekbots/">
                <i class="fab fa-fw fa-trello"></i>
                <span>Trello</span>
            </a>
        </li>
        <li class="nav-item">
            <a style="color: lightblue;" target= "_blank" class="nav-link" href="../../store/labs/index.php">
                <i class="fas fa-fw fa-ticket-alt"></i>
                <span>Lab Tickets</span>
            </a>
        </li>
        
		<li class="nav-item dropdown">
         <a class="nav-link dropdown-toggle" href="#" id="pagesDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <i class="fas fa-fw fa-hand-rock"></i>
            <span>Lockers</span>
         </a>
         <div class="dropdown-menu" aria-labelledby="pagesDropdown">
             <a class="dropdown-item" href="pages/employeeLockers.php">Lockers Page</a>
             <div class="dropdown-divider"></div>
             <h6 class="dropdown-header">Options:</h6>
             <a class="dropdown-item" href="pages/employeeLockersMessages.php">Edit Messages</a>
         </div>
        </li>
		
		<li class="nav-item dropdown">
         <a class="nav-link dropdown-toggle" href="#" id="pagesDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <i class="fas fa-fw fa-hand-rock"></i>
            <span>TekBox</span>
         </a>
         <div class="dropdown-menu" aria-labelledby="pagesDropdown">
             <a class="dropdown-item" href="pages/employeeBoxes.php">TekBoxes Page</a>
             <div class="dropdown-divider"></div>
             <h6 class="dropdown-header">Options:</h6>
             <a class="dropdown-item" href="pages/employeeBoxMessages.php">Edit Messages</a>
         </div>
        </li>
		
		<li class="nav-item">
            <a style="color: lightblue;" target= "_blank" class="nav-link" href="https://secure.touchnet.net/sso/login?service=https%3A%2F%2Fsecure.touchnet.net%2Fcentral%2F%3Fcmmserviceurltoken%3D9a7e7cf6-986d-4f2e-841d-11232a37763a">
                <i class="fas fa-fw fa-network-wired"></i>
                <span>TouchNet</span>
            </a>
        </li>
        <li class="nav-item">
            <a style="color: lightblue;" class="nav-link" href="https://secure.touchnet.net/C20159_ustores/web/classic/store_main.jsp?STOREID=8">
                <i class="fas fa-fw fa-store"></i>
                <span>OSU Marketplace</span>
            </a>
        </li>
        <li class="nav-item">
            <a style="color: lightblue;" class="nav-link" href="../../store/hweekend/index.php">
                <i class="fas fa-fw fa-kaaba"></i>
                <span>HWeekend</span>
            </a>
        </li>

     </ul>


     ';
    

 }

 function renderEmployeeBreadcrumb($section, $pagetitle){
     echo" 
        <!-- Breadcrumbs-->
        <ol class='breadcrumb'>
            <li class='breadcrumb-item'>
                <a>$section</a>
            </li>
            <li class='breadcrumb-item active'>$pagetitle</li>
        </ol>
     ";

 }

 function createEquipmentHideButton($equipmentID) {
	echo "
	<button class='btn btn-outline-info hideEquipmentBtn' id='hideEquipmentBtn$equipmentID' type='button' data-toggle='tooltip' data-placement='bottom' 
    title='Hide the equipment from public view.  This equipment will only be visible on the employee equipment page.  This can be used for archived items, or listings that you are still working on.'>
		Make Hidden
	</button>
	
	<script type='text/javascript'>
		$('#hideEquipmentBtn$equipmentID').on('click', function() {
			let res = confirm('You are hiding this equipment from public view. This can be changed later.');
			if(!res) return false;
			let equipmentID = '$equipmentID';
			let data = {
				action: 'makeEquipmentHidden',
                equipmentID: equipmentID,
			};
			api.post('/equipments.php', data).then(res => {
                snackbar(res.message, 'success');
                setTimeout(function(){
                    window.location.reload(1);
                 }, 2000);
			}).catch(err => {
				snackbar(err.message, 'error');
			});
		});
	</script>
	";
}

function createShowEquipmentButton($equipmentID) {
	echo "
	<button class='btn btn-outline-info capstone-nav-btn' id='showEquipmentBtn$equipmentID' type='button' data-toggle='tooltip' data-placement='bottom' 
    title='This will make the equipment visible to everyone on the browse equipment page.  By default, items are created as private so to make them visible you will need to hit this button.'>
		Make Public
	</button>
	
	<script type='text/javascript'>
		$('#showEquipmentBtn$equipmentID').on('click', function() {
			let res = confirm('You are making this equipment available for public viewing. This can be changed later.');
			if(!res) return false;
			let equipmentID = '$equipmentID';
			let data = {
				action: 'makeEquipmentShown',
                equipmentID: equipmentID,
			};
			api.post('/equipments.php', data).then(res => {
                snackbar(res.message, 'success');
                setTimeout(function(){
                    window.location.reload(1);
                 }, 2000);
			}).catch(err => {
				snackbar(err.message, 'error');
			});
		});
	</script>
	";
}

function createArchiveEquipmentButton($equipmentID){
	echo "
	<button class='btn btn-outline-danger capstone-nav-btn' id='archiveEquipmentBtn$equipmentID' type='button' data-toggle='tooltip' data-placement='bottom' 
    title='Removes the equipment for both employees and students'>
		Delete Equipment
	</button>
	
	<script type='text/javascript'>
		$('#archiveEquipmentBtn$equipmentID').on('click', function() {
			let res = confirm('You are deleting an equipment. Are you sure about this?.');
			if(!res) return false;
			let equipmentID = '$equipmentID';
			let data = {
				action: 'makeEquipmentArchive',
                equipmentID: equipmentID,
			};
			api.post('/equipments.php', data).then(res => {
                snackbar(res.message, 'success');
                setTimeout(function(){
                    history.go(-1);
                 }, 2000);
			}).catch(err => {
				snackbar(err.message, 'error');
			});
		});
	</script>
	";
}

function createAssignEquipmentFeesButton($checkoutID, $userID, $reservationID){
    global $dbConn, $logger;
    $feeDao = new EquipmentFeeDao($dbConn, $logger);
    $fee = $feeDao->getEquipmentFeeWithCheckoutID($checkoutID);
    if (empty($fee)){
        $buttonText = "Assign Fee";
    }
    else {
        // Checkout has been asssigned, change to view
        $buttonText = "View Fee";
    }
    return "
    <button class='btn btn-outline-danger capstone-nav-btn' type='button' data-toggle='modal' 
    data-target='#newFeeModal$checkoutID' id='openNewEquipmentFeeModalBtn'>$buttonText</button>
    
    <script type='text/javascript'>

     $('#assignEquipmentFees$checkoutID').on('click', function() {
        let reservationID = '$reservationID';
        let feeAmount = $('#feeAmount$checkoutID').val();
        let feeNotes = $('#feeNotes$checkoutID').val();
        let userID = '$userID';
        let checkoutID = '$checkoutID';
         let data = {
            action: 'assignEquipmentFees',
            checkoutID: checkoutID,
            reservationID: reservationID,
            feeAmount: feeAmount,
            userID: userID,
            feeNotes: feeNotes
         };
         api.post('/equipmentrental.php', data).then(res => {
             snackbar(res.message, 'success');
             setTimeout(function(){
                window.location.reload(1);
             }, 2000);
         }).catch(err => {
             snackbar(err.message, 'error');
         });
     });
     
 </script>
    
    ";
}

function createReservationHandoutButton($reservationID, $listNumber, $userID, $equipmentID){
     
     return "
     <button class='btn btn-outline-primary capstone-nav-btn' type='button' data-toggle='modal' 
     data-target='#newHandoutModal$reservationID' id='openNewHandoutModalBtn'>Handout</button>
    
    
     <script type='text/javascript'>

 		$('#handoutEquipmentBtn$reservationID').on('click', function() {
            let reservationID = '$reservationID';
            let contractID = $('#$reservationID').val();
            let userID = '$userID';
            let equipmentID = '$equipmentID';
 			let data = {
 				action: 'checkoutEquipment',
                reservationID: reservationID,
                contractID: contractID,
                userID: userID,
                equipmentID: equipmentID
 			};
 			api.post('/equipmentrental.php', data).then(res => {
 				$('#activeReservation$listNumber').remove();
                 snackbar(res.message, 'success');
                 setTimeout(function(){
                    window.location.reload(1);
                 }, 2000);
 			}).catch(err => {
 				snackbar(err.message, 'error');
 			});
         });
         
 	</script>

     ";

}

function createViewCheckoutButton($checkoutID){
     
    return "
    <button class='btn btn-outline-primary capstone-nav-btn' type='button' data-toggle='modal' 
    data-target='#viewCheckoutModal$checkoutID' id='openNewViewModalBtn'>View</button>
    ";

}

function createReserveAsEmployeeBtn($reservationID, $listNumber, $userID, $equipmentID){
     
    return "
    <button class='btn btn-outline-primary capstone-nav-btn' type='button' id='reserveAsEmployeeBtn$reservationID'>Recreate Reservation</button>
   
   
    <script type='text/javascript'>
        $('#reserveAsEmployeeBtn$reservationID').on('click', function() {
           let equipmentID = '$equipmentID';
           let userID = '$userID';
            let data = {
                action: 'createReservation',
               userID: userID,
               equipmentID: equipmentID
            };
            api.post('/equipmentrental.php', data).then(res => {
                $('#expiredReservation$listNumber').remove();
                snackbar(res.message, 'success');
                setTimeout(function(){
                   window.location.reload(1);
                }, 2000);
            }).catch(err => {
                snackbar(err.message, 'error');
            });
        });
        
    </script>

    ";

}


function renderEquipmentReturnButton($checkout){
    $checkoutID = $checkout->getCheckoutID();
    return "
    <button class='btn btn-outline-primary capstone-nav-btn' type='button' data-toggle='modal' 
    data-target='#newReturnModal$checkoutID' id='openNewReturnModalBtn'>Return</button>
   
   
    <script type='text/javascript'>
        $('#returnEquipmentBtn$checkoutID').on('click', function() {
           let checkoutID = '$checkoutID';
           let checkoutNotes = $('#checkoutNotes$checkoutID').val();
            let data = {
                action: 'returnEquipment',
               checkoutID: checkoutID,
               checkoutNotes: checkoutNotes,
            };
            api.post('/equipmentrental.php', data).then(res => {
                snackbar(res.message, 'success');
                setTimeout(function(){
                    window.location.reload(1);
                 }, 2000);
            }).catch(err => {
                snackbar(err.message, 'error');
            });
        });
        
    </script>

    ";
}

function renderEquipmentFeeApproveButton($feeID){
    return "
    <button class='btn btn-outline-primary capstone-nav-btn' type='button' data-toggle='modal' 
    data-target='#verifyFeeModal$feeID' id='verifyFeeModalBtn'>Verify</button>
   
   
    <script type='text/javascript'>
    $('#approveEquipmentFees$feeID').on('click', function() {
        let feeID = '$feeID';
         let data = {
            action: 'approveEquipmentFees',
            feeID: feeID
         };
         api.post('/equipmentrental.php', data).then(res => {
             snackbar(res.message, 'success');
             setTimeout(function(){
                 window.location.reload(1);
              }, 2000);
         }).catch(err => {
             snackbar(err.message, 'error');
         });
     });

     $('#denyEquipmentFees$feeID').on('click', function() {
        let feeID = '$feeID';
         let data = {
            action: 'rejectEquipmentFees',
            feeID: feeID
         };
         api.post('/equipmentrental.php', data).then(res => {
             snackbar(res.message, 'success');
             setTimeout(function(){
                 window.location.reload(1);
              }, 2000);
         }).catch(err => {
             snackbar(err.message, 'error');
         });
     });
     
    </script>

    ";


}




?>
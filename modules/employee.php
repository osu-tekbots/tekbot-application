<?php

use DataAccess\EquipmentFeeDao;

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
    echo <<< HTML
    <br><br>
    <!-- Sidebar -->
    <ul class="sidebar navbar-nav">
        <li class="nav-item dropdown">
            <a style="color: lightblue;" class="nav-link dropdown-toggle" href="#" id="pagesDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <i class="fas fa-fw fa-tools"></i>
            <span>Equipment</span>
            </a>
            <div class="dropdown-menu" aria-labelledby="pagesDropdown">
                <a class="dropdown-item" href="pages/employeeEquipment.php">Overview</a>
                <div class="dropdown-divider"></div>
                <h6 class="dropdown-header">Adjust Content:</h6>
                <a class="dropdown-item" href="pages/employeeEquipmentList.php">Edit Equipment</a>
                <a class="dropdown-item" href="pages/employeeEquipmentMessages.php">Edit Messages</a>
                <a class="dropdown-item" href="pages/employeeEquipmentLabels.php">Print Labels</a>
            </div>
        </li>

        <li class="nav-item dropdown">
            <a style="color: lightblue;" class="nav-link dropdown-toggle" href="#" id="pagesDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
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
                <a class="dropdown-item" href="pages/employeeLaserJobList.php">All Cut Jobs</a>
                <a class="dropdown-item" href="pages/employeeCutVouchers.php">Cut Vouchers</a>
                <div class="dropdown-divider"></div>
                <h6 class="dropdown-header">Modifying Laser Tools:</h6>
                <a class="dropdown-item" href="pages/employeeLaserMessages.php">Edit Messages</a>
                <a class="dropdown-item" href="pages/employeeAddLaser.php">Laser Cutters/Materials</a>
            </div>
        </li>
        <li class="nav-item dropdown">
            <a style="color: lightblue;" class="nav-link dropdown-toggle" href="#" id="pagesDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="fas fa-fw fa-warehouse"></i>
                <span>Inventory</span>
            </a>
            <div class="dropdown-menu" aria-labelledby="pagesDropdown">
                <a class="dropdown-item" href="pages/employeeInventory.php">Inventory List</a>
                <a class="dropdown-item" href="pages/employeeInventoryKits.php">Configure Kits</a>
                <a class="dropdown-item" href="pages/employeeInventoryOrderParts.php">Order Parts</a>
                <div class="dropdown-divider"></div>
                <h6 class="dropdown-header">Options:</h6>
                <a class="dropdown-item" href="pages/employeeInventoryMessages.php">Edit Messages</a>
                <a class="dropdown-item" href="pages/employeeInventoryLabels.php">Print Labels</a>
            </div>
        </li>
        <li class="nav-item dropdown">
            <a style="color: lightblue;" class="nav-link dropdown-toggle" href="#" id="pagesDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="fas fa-fw fa-door-closed"></i>
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
            <a style="color: lightblue;" class="nav-link dropdown-toggle" href="#" id="pagesDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="fas fa-fw fa-globe"></i>
                <span>Touchnet</span>
            </a>
            <div class="dropdown-menu" aria-labelledby="pagesDropdown">
                <a class="dropdown-item" href="https://secure.touchnet.net/central">Touchnet Admin</a>
                <a class="dropdown-item" href="https://secure.touchnet.net/C20159_ustores/web/classic/store_main.jsp?STOREID=8">Store Front</a>
            </div>
        </li>
		
		<li class="nav-item dropdown">
            <a style="color: lightblue;" class="nav-link dropdown-toggle" href="#" id="pagesDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="fas fa-fw fa-lock"></i>
                <span>TekBox</span>
            </a>
            <div class="dropdown-menu" aria-labelledby="pagesDropdown">
                <a class="dropdown-item" href="pages/employeeBoxes.php">TekBoxes Page</a>
                <div class="dropdown-divider"></div>
                <h6 class="dropdown-header">Options:</h6>
                <a class="dropdown-item" href="pages/employeeBoxMessages.php">Edit Messages</a>
            </div>
        </li>

        <!-- Turned Lab tickets into a drop down with employeeTicketList as the main and QR codes and edit equipement as options-->
        <li class="nav-item dropdown">
            <a style="color: lightblue;" class="nav-link dropdown-toggle" href="#" id="pagesDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="fas fa-fw fa-ticket-alt"></i>
                <span>Lab Tickets</span>
            </a>
            <div class="dropdown-menu" aria-labelledby="pagesDropdown">
                <a class="dropdown-item" href="pages/employeeTicketList.php">Ticket List</a>
                <div class="dropdown-divider"></div>
                <h6 class="dropdown-header">Options:</h6>
                <a class="dropdown-item" href="pages/employeeTicketLabels.php">QR Codes</a>
             <a class="dropdown-item" href="../../store/labs/ajax/equipment_page.php">Edit Equipment</a>
                <a class="dropdown-item" href="pages/employeeTicketMessages.php">Edit Messages</a>
            </div>
        </li>

        <!-- Internal Sales Page with Bill All implemented, still need to work on message editing-->
        <li class="nav-item dropdown">
            <a style="color: lightblue;" class="nav-link dropdown-toggle" href="#" id="pagesDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="fas fa-fw fa-money-check-alt"></i>
                <span>Internal Sales</span>
            </a>
            <div class="dropdown-menu" aria-labelledby="pagesDropdown">
                <a class="dropdown-item" href="pages/employeeInternalSales.php">Internal Sales Page</a>
                <div class="dropdown-divider"></div>
                <h6 class="dropdown-header">Options:</h6>
                <a class="dropdown-item" href="pages/employeeInternalSalesMessages.php">Edit Messages</a>
            </div>
        </li>

        <li class="nav-item">
            <a style="color: lightblue;" class="nav-link" href="pages/employeeFees.php">
                <i class="fas fa-fw fa-dollar-sign"></i>
                <span>Fees</span>
            </a>
        </li>
        <li class="nav-item">
            <a style="color: lightblue;" class="nav-link" href="pages/employeeUser.php">
                <i class="fas fa-fw fa-users"></i>
                <span>Users</span>
            </a>
        </li>
        <li class="nav-item">
            <a style="color: lightblue;" class="nav-link" href="pages/employeeEmail.php">
                <i class="far fa-fw fa-paper-plane"></i>
                <span>Send Email</span>
            </a>
        </li>
        <li class="nav-item">
            <a style="color: lightblue;" target= "_blank" class="nav-link" href="https://discord.gg/9YFafybAv6">
                <i class="fab fa-discord"></i>
                <span>Discord</span>
            </a>
        </li>
        <!--
        <li class="nav-item">
            <a style="color: lightblue;" target= "_blank" class="nav-link" href="https://trello.com/b/XUktYdsk/tekbots/">
                <i class="fab fa-fw fa-trello"></i>
                <span>Trello</span>
            </a>
        </li>
        -->
        
        <li class="nav-item">
            <a style="color: lightblue;" class="nav-link" href="../../store/hweekend/index.php">
                <i class="fas fa-fw fa-project-diagram"></i>
                <span>HWeekend</span>
            </a>
        </li>
        <li class="nav-item">
            <a style="color: lightblue;" target= "_blank" class="nav-link" href="https://docs.google.com/document/d/1ec17hL1cNvOtv9CpPJCzAewM0tlJ4FV-OH18Fo4_MXM">
                <i class="fas fa-book"></i>
                <span>Store Procedures</span>
            </a>
        </li>

     </ul>


HTML;
    

}

 /*
 Old function that's been deprecated as of 8/8/23
 */
 function renderEmployeeBreadcrumb($section, $pagetitle){
     /*echo" 
        <!-- Breadcrumbs-->
        <ol class='breadcrumb'>
            <li class='breadcrumb-item'>
                <a>$section</a>
            </li>
            <li class='breadcrumb-item active'>$pagetitle</li>
        </ol>
     ";*/

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
                equipmentID: equipmentID,
				messageID: 'wersspdohssfuj'
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
               equipmentID: equipmentID,
			   messageID: 'wersspdohssfuj'
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
			   messageID: 'fsrt56pdohssfuj'
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

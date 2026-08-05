<?php

use DataAccess\EquipmentFeeDaoOld;

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
        <!-- Home -->
        <li class="nav-item pt-2">
            <a style="color: lightblue;" class="nav-link" href="pages/employeeInterface.php">
                <i class="fas fa-fw fa-home"></i>
                <span>Employee Home</span>
            </a>
        </li>


        <!-- Section Description -->
        <div class="nav-label">
            Sales
        </div>

        <!-- Inventory -->
        <li class="nav-item dropdown">
            <a style="color: lightblue;" class="nav-link dropdown-toggle" href="#" id="pagesDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="fas fa-fw fa-warehouse"></i>
                <span>Inventory</span>
            </a>
            <div class="dropdown-menu" aria-labelledby="pagesDropdown">
                <a class="dropdown-item" href="pages/employeeInventory.php">Inventory List</a>
                <a class="dropdown-item" href="pages/employeeInventoryKits.php">Configure Kits</a>
                <a class="dropdown-item" href="pages/employeeInventoryOrderParts.php">Order Parts</a>
                <a class="dropdown-item" href="pages/employeeInventoryCarts.php">Access Carts</a>

                <div class="dropdown-divider"></div>
                <h6 class="dropdown-header">Options:</h6>
                <a class="dropdown-item" href="pages/employeeInventoryMessages.php">Edit Messages</a>
                <a class="dropdown-item" href="pages/employeeInventoryTypes.php">Edit Types</a>
                <a class="dropdown-item" href="pages/employeeInventoryLabels.php">Print Labels</a>
            </div>
        </li>
		
        <!-- Touchnet -->
		<li class="nav-item dropdown">
            <a style="color: lightblue;" class="nav-link dropdown-toggle" href="#" id="pagesDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="fas fa-fw fa-globe"></i>
                <span>Touchnet</span>
            </a>
            <div class="dropdown-menu" aria-labelledby="pagesDropdown">
                <a class="dropdown-item" href="https://secure.touchnet.net/central" target="_blank" rel="noopener noreferrer">Touchnet Admin</a>
                <a class="dropdown-item" href="https://secure.touchnet.net/C20159_ustores/web/classic/store_main.jsp?STOREID=8" target="_blank" rel="noopener noreferrer">Store Front</a>
            </div>
        </li>

        <!-- Kit Handout -->
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

        <!-- Internal Sales -->
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
		
        <!-- TekBoxes -->
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


        <!-- Section Description -->
        <div class="nav-label">
            Services
        </div>

        <!-- 3D Printing -->
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

        <!-- Laser Cutting -->
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

        <!-- Lab Tickets -->
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
                <!-- Commented out because this isn't the current site -->
                <!-- <a class="dropdown-item" href="../../store/labs/ajax/equipment_page.php">Edit Equipment</a> -->
                <a class="dropdown-item" href="pages/employeeTicketMessages.php">Edit Messages</a>
            </div>
        </li>

        <!-- Equipment -->
        <li class="nav-item dropdown">
            <a style="color: lightblue;" class="nav-link dropdown-toggle" href="#" id="pagesDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <i class="fas fa-fw fa-tools"></i>
            <span>Equipment</span>
            </a>
            <div class="dropdown-menu" aria-labelledby="pagesDropdown">
                <a class="dropdown-item" href="pages/employeeEquipmentCheckout.php">Checkout</a>
                <a class="dropdown-item" href="pages/employeeEquipmentList.php">Equipment List</a>
                <div class="dropdown-divider"></div>
                <h6 class="dropdown-header">Adjust Content:</h6>
                <a class="dropdown-item" href="pages/employeeEquipmentMessages.php">Edit Messages</a>
                <a class="dropdown-item" href="pages/employeeEquipmentLabel.php">Print Labels</a>
            </div>
        </li>

        <!-- Lockers -->
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


        <!-- Section Description -->
        <div class="nav-label">
            Other
        </div>

        <!-- Admin Control -->
        <li class="nav-item">
            <a style="color: lightblue;" class="nav-link" href="pages/employeeAdminControl.php">
                <i class="far fa-address-card"></i>
                <span> Admin Control</span>
            </a>
        </li>

        <!-- Send Email -->
        <li class="nav-item">
            <a style="color: lightblue;" class="nav-link" href="pages/employeeEmail.php">
                <i class="far fa-fw fa-paper-plane"></i>
                <span>Send Email</span>
            </a>
        </li>

        <!-- Users -->
        <li class="nav-item">
            <a style="color: lightblue;" class="nav-link" href="pages/employeeUser.php">
                <i class="fas fa-fw fa-users"></i>
                <span>Users</span>
            </a>
        </li>

        <!-- Teams -->
        <li class="nav-item">
            <a style="color: lightblue;" target= "_blank" class="nav-link" href="https://teams.microsoft.com/l/team/19%3AALCvuZCBAm9ngX8gyW5BLAK_XkC51sgHbQQ67gaZKwI1%40thread.tacv2/conversations">
                <i class="fas fa-comments"></i>
                <span>MS Teams</span>
            </a>
        </li>

        <!-- Store Procedures -->
        <li class="nav-item">
            <a style="color: lightblue;" target= "_blank" class="nav-link" href="https://docs.google.com/document/d/1awkeaImJgMPAy5k3k22faxHv0JKlVe6-37omVu_1Dp4/edit?tab=t.0#heading=h.z0agvft6ftd">
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

 function createEquipmentHideButton($itemID) {
	echo "
	<button class='btn btn-outline-info hideEquipmentBtn' id='hideEquipmentBtn$itemID' type='button' data-toggle='tooltip' data-placement='bottom' 
    title='Hide the unit from public view, making it only visible to employees. This can be used for archived items or listings that you are still working on.'>
		Make Hidden
	</button>
	
	<script type='text/javascript'>
		$('#hideEquipmentBtn$itemID').on('click', function() {
			let res = confirm('You are hiding this equipment from public view. This can be changed later.');
			if(!res) return false;
			let itemID = '$itemID';
			let data = {
				action: 'saveEquipmentItem',
                isPublic: false,
                itemID,
			};
			api.post('/equipment.php', data).then(res => {
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

function createShowEquipmentButton($itemID) {
	echo "
	<button class='btn btn-outline-info capstone-nav-btn' id='showEquipmentBtn$itemID' type='button' data-toggle='tooltip' data-placement='bottom' 
    title='This will make the unit visible to everyone on the Browse Equipment page.  By default, items are created as private, so you will need to hit this button to make them visible.'>
		Make Public
	</button>
	
	<script type='text/javascript'>
		$('#showEquipmentBtn$itemID').on('click', function() {
			let res = confirm('You are making this equipment available for public viewing. This can be changed later.');
			if(!res) return false;
			let itemID = '$itemID';
			let data = {
				action: 'saveEquipmentItem',
                isPublic: true,
                itemID,
			};
			api.post('/equipment.php', data).then(res => {
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
    title='Removes the equipment, though restoration by an employee is possible.'>
		Delete Equipment
	</button>
	
	<script type='text/javascript'>
		$('#archiveEquipmentBtn$equipmentID').on('click', function() {
			let res = confirm('You are deleting an equipment. Are you sure about this?.');
			if(!res) return false;
			let equipmentID = '$equipmentID';
			let data = {
				action: 'deleteEquipment',
                equipmentID: equipmentID,
			};
			api.post('/equipment.php', data).then(res => {
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

function createUnarchiveEquipmentButton($equipmentID){
	echo "
	<button class='btn btn-outline-danger capstone-nav-btn' id='unarchiveEquipmentBtn$equipmentID' type='button' data-toggle='tooltip' data-placement='bottom' 
    title='Restores the equipment, making it show up again on equipment lists.'>
		Restore Equipment
	</button>
	
	<script type='text/javascript'>
		$('#unarchiveEquipmentBtn$equipmentID').on('click', function() {
			let equipmentID = '$equipmentID';
			let data = {
				action: 'restoreEquipment',
                equipmentID: equipmentID,
			};
			api.post('/equipment.php', data).then(res => {
                snackbar(res.message, 'success');
                setTimeout(() => window.location.reload(), 1000);
			}).catch(err => {
				snackbar(err.message, 'error');
			});
		});
	</script>
	";
}

function createArchiveUnitButton($unitID){
	echo "
	<button class='btn btn-outline-danger capstone-nav-btn' id='deleteUnitBtn$unitID' type='button' data-toggle='tooltip' data-placement='bottom' 
    title='Removes this unit of the equipment, though restoration by an employee is possible.'>
		Delete Unit
	</button>
	
	<script type='text/javascript'>
		$('#deleteUnitBtn$unitID').on('click', function() {
			let res = confirm('You are deleting an unit. Are you sure about this?.');
			if(!res) return false;
			let itemID = '$unitID';
			let data = {
				action: 'deleteEquipmentItem',
                itemID
			};
			api.post('/equipment.php', data).then(res => {
                snackbar(res.message, 'success');
                setTimeout(() => window.location.reload(), 1000);
			}).catch(err => {
				snackbar(err.message, 'error');
			});
		});
	</script>
	";
}

function createUnarchiveUnitButton($unitID){
	echo "
	<button class='btn btn-outline-danger capstone-nav-btn' id='restoreUnitBtn$unitID' type='button' data-toggle='tooltip' data-placement='bottom' 
    title='Restores this unit of the equipment, making it show up on unit lists and making it available for checkout again.'>
		Restore Unit
	</button>
	
	<script type='text/javascript'>
		$('#restoreUnitBtn$unitID').on('click', function() {
			let itemID = '$unitID';
			let data = {
				action: 'restoreEquipmentItem',
                itemID
			};
			api.post('/equipment.php', data).then(res => {
                snackbar(res.message, 'success');
                setTimeout(() => window.location.reload(), 1000);
			}).catch(err => {
				snackbar(err.message, 'error');
			});
		});
	</script>
	";
}

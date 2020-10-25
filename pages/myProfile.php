<?php
include_once '../bootstrap.php';

use DataAccess\UsersDao;
use DataAccess\EquipmentFeeDao;
use DataAccess\EquipmentDao;
use DataAccess\EquipmentCheckoutDao;
use DataAccess\EquipmentReservationDao;
use DataAccess\PrinterFeeDao;
use DataAccess\PrinterDao;
use DataAccess\BoxDao;
use DataAccess\KitEnrollmentDao;
use Model\KitEnrollmentStatus;
use Model\EquipmentCheckoutStatus;
use Util\Security;

session_start();

include_once PUBLIC_FILES . '/lib/shared/authorize.php';

$isLoggedIn = isset($_SESSION['userID']) && !empty($_SESSION['userID']);
allowIf($isLoggedIn, $configManager->getBaseUrl() . 'pages/login.php');

$title = 'My Profile';
$css = array(
	'assets/css/admin.css',
	'https://cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css',
	'assets/Madeleine.js/src/css/Madeleine.css'
);

$js = array(
	'https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js',
	'assets/Madeleine.js/src/lib/stats.js',
	'assets/Madeleine.js/src/lib/detector.js',
	'assets/Madeleine.js/src/lib/three.min.js',
	'assets/Madeleine.js/src/Madeleine.js'
);
include_once PUBLIC_FILES . '/modules/header.php';
include_once PUBLIC_FILES . '/modules/newHandoutModal.php';
include_once PUBLIC_FILES . '/modules/employee.php';
include_once PUBLIC_FILES . '/modules/renderBrowse.php';

$usersDao = new UsersDao($dbConn, $logger);

$user = $usersDao->getUserByID($_SESSION['userID']);

if ($user){
	$uId = $user->getUserID();
	$uFirstName = $user->getFirstName();
	$uLastName = $user->getLastName();
	$uPhone = $user->getPhone();
	$uEmail = $user->getEmail();
	$uOnid = $user->getOnid();
	$uAccessLevel = $user->getAccessLevelID()->getName();
} else {
	echo "<h1>You are not in db. You shoudl never have seen this.</h1>";
	exit();
}

$checkoutFeeDao = new EquipmentFeeDao($dbConn, $logger);
$equipmentDao = new EquipmentDao($dbConn, $logger);
$userDao = new UsersDao($dbConn, $logger);
$checkoutDao = new EquipmentCheckoutDao($dbConn, $logger);
$reservationDao = new EquipmentReservationDao($dbConn, $logger);
$printerFeeDao = new PrinterFeeDao($dbConn, $logger);
$printerDao = new PrinterDao($dbConn, $logger);
$boxDao = new BoxDao($dbConn, $logger);
$kitsDao = new KitEnrollmentDao($dbConn, $logger);

$uID = $_SESSION['userID'];
$checkoutFees = $checkoutFeeDao->getFeesForUser($uID);
$printerFees = $printerFeeDao->getFeesForUser($uID);



$studentPrintJobs = $printerDao->getPrintJobsForUser($uID);


/*
This section prepares the 3D printing/Laser cutting tab.
*/

include_once PUBLIC_FILES . '/modules/customerPrintModal.php';


$printJobsHTML = '';
foreach($studentPrintJobs as $p) {
	$printJobID = $p->getPrintJobID();
	$userID = $p->getUserID();
	$user = $userDao->getUserByID($p->getUserID());
	$name = Security::HtmlEntitiesEncode($user->getFirstName()) . ' ' . Security::HtmlEntitiesEncode($user->getLastName());
	$printType = Security::HtmlEntitiesEncode($printerDao->getPrintTypesByID($p->getPrintTypeID())->getPrintTypeName());
	$printer = Security::HtmlEntitiesEncode($printerDao->getPrinterByID($p->getPrinterId())->getPrinterName());
	$dbFileName = $p->getDbFileName();
	$stlFileName = $p->getStlFileName();
	$dateCreated = $p->getDateCreated();
	$validPrintDate = $p->getValidPrintCheck();
	$userConfirm = $p->getUserConfirmCheck();
	$completePrintDate = $p->getCompletePrintDate();
	$customerNotes = $p->getCustomerNotes();
	$employeeNotes = $p->getEmployeeNotes();
	$pendingResponse = $p->getPendingCustomerResponse();


	$buttonScripts = "
	<script>
	$('#confirmPrint$printJobID').on('click', function() {
		if(confirm('Confirm print and allow employees to start printing?')) {
			let printJobID = '$printJobID';
			let data = {
				action: 'customerConfirmPrint',
				printJobID: printJobID,
			}
			api.post('/printers.php', data).then(res => {
				snackbar(res.message, 'success');
				setTimeout(function(){window.location.reload()}, 1000);
			}).catch(err => {
				snackbar(err.message, 'error');
		});
		}
	});

	$('#deletePrint$printJobID').on('click', function() {
		if(confirm('Confirm you would like to delete this print job and remove it from queue (irreversable)?')) {
			let printJobID = '$printJobID';
			let data = {
				action: 'deletePrintJob',
				printJobID: printJobID,
			}
			api.post('/printers.php', data).then(res => {
				snackbar(res.message, 'success');
				setTimeout(function(){window.location.reload()}, 1000);
			}).catch(err => {
				snackbar(err.message, 'error');
		});
		}
	});
	
	</script>
	";

	$status = "";

	if($validPrintDate) {
		$status = "<a data-toggle='tool-tip' data-placement='top' title='$validPrintDate'>Print Validated By Employee</a><br/>";
	} else {
		$status = "Waiting for Employee to Validate Print"; 
	}


	$confirmationButton = "<button id='confirmPrint$printJobID' class='btn btn-outline-primary capstone-nav-btn'>Confirm</button>";
	$denyButton = "<button id='deletePrint$printJobID' class='btn btn-outline-danger capstone-nav-btn'>Delete</button>";
	if($validPrintDate && $pendingResponse) {
		$status .= "Awaiting your confirmation:<br/>";
		$status .= $confirmationButton;
		$status .= $denyButton;	
	} elseif(!$completePrintDate && $validPrintDate) {
		$status .= "Currently in queue to Print";
	}

	if($completePrintDate) {
		$status = "Print has completed";
	}

	$printJobsHTML .= "
	<tr>
	<td>$dateCreated</td>
	<td>
		<button data-toggle='modal' data-target='#newReservationModal' data-whatever='$dbFileName' id='openNewReservationBtn'  class='btn btn-outline-primary capstone-nav-btn'>
			View
		</button>
		<a style='color:#0000FF;' href='./prints/$dbFileName'>$stlFileName</a>
	</td>
	<td>$customerNotes</td>
	<td>$status</td>

	</tr>
	$buttonScripts
	";
}

/*
This section prepares the Fees tab of the webpage

*/

$feeHTML = '';

$checkoutFeeCount = 0;
foreach ($checkoutFees as $f){
    $checkoutID = $f->getCheckoutID();
    $checkout = $checkoutDao->getCheckout($checkoutID);
    $feeNotes = $f->getNotes();
    $feeAmount = $f->getAmount();
    $feeCreated = $f->getDateCreated();
    $feeID = $f->getFeeID();

    $isPending = $f->getIsPending();
	$isPaid = $f->getIsPaid();
	// Makes sure fee is not pending or paid
	if ($isPending == FALSE && $isPaid == FALSE){
		$checkoutFeeCount++;
	}
    renderViewCheckoutModal($checkout);

    renderPayFeeModal($f);
    $payButton = $isPending ? "PENDING APPROVAL" : ($isPaid ? "PAID" : createPayButton($feeID));

    $feeHTML .= "
    <tr>
        <td><a href='' data-toggle='modal' 
		data-target='#viewCheckoutModal$checkoutID'>Checkout</a></td>
        <td>$feeNotes</td>
        <td>$feeAmount</td>
        <td>$payButton</td>
    </tr>
    ";
}
foreach ($printerFees as $fee){
	$feeID = $fee->getPrintFeeId();
	$feeNotes = $fee->getCustomerNotes();
	$feeAmount = $fee->getAmount();
	$isPaid = $fee->getIsPaid();
	$isPending = $fee->getIsPending();
	$dateCreated = $fee->getDateCreated();
	$dateUpdated = $fee->getDateUpdated();

	// Print is verified and prints needs to be paid for
	if ($fee->getIsVerified() == 1 && $fee->getIsPending() == 0) {
		$actions = 'Pay button';
	}
	// Print is verified and payment is submitted
	if ($fee->getIsVerified() == 1 && $fee->getIsPending() == 1) {
		$actions = 'Pending Employee Verification';
	}
	// Print needs to be verified by employee before charging student
	if ($fee->getIsVerified() == 0){
		$actions = 'Pending Print Check';
	}

	if ($fee->getIsPaid() == 1){
		$actions = 'PAID';
	}


	$feeHTML .= "
    <tr>
        <td>Related Print</td>
        <td>$feeNotes</td>
        <td>$feeAmount</td>
        <td>$actions</td>
    </tr>
    ";

}

/*
This section prepares the contents of the Equipment Tba.
This includes Reservations and Active Check-Outs

*/
$reservedEquipment = $reservationDao->getReservationsForUser($uID);
$checkedoutEquipment = $checkoutDao->getCheckoutsForUser($uID);


$reservedEquipmentCount = 0;
$reservedHTML = '';
$listNumber = 0;
if ($reservedEquipment){
	foreach ($reservedEquipment as $r){
			$reservationID = $r->getReservationID();
			$equipmentID = $r->getEquipmentID();
			$userID = $r->getUserID();
			$reservationTime = $r->getDatetimeReserved();
			$latestPickupTime = $r->getDatetimeExpired();
			$isActive = $r->getIsActive();
			$equipment = $equipmentDao->getEquipment($equipmentID);

			$equipmentName = Security::HtmlEntitiesEncode($equipment->getEquipmentName());
			$equipmentLocation = Security::HtmlEntitiesEncode($equipment->getLocation());
	
		
			
			if ($isActive){
				$active = "Active";
				//renderNewHandoutModal($r);
				//$handoutButton = createReservationHandoutButton($reservationID, $listNumber, $userID, $equipmentID);
				$cancelButton = createReservationCancelButton($reservationID, $listNumber);
				$tableIDName = "activeReservation$listNumber";
				$reservedEquipmentCount++;
				$reservedHTML .= "
					<tr id='$tableIDName'>
						<td>$reservationTime</td>
						<td>$latestPickupTime</td>
						<td>$equipmentName</td>
						<td>$active</td>
						<td>$cancelButton</td>
					</tr>
					";
				$listNumber++;
			}
			else { //Should not display, TODO: Remove
				$active = "Expired";
				//$handoutButton = createReserveAsEmployeeBtn($reservationID, $listNumber, $userID, $equipmentID);
				$handoutButton = "";
				$cancelButton = "";
				$tableIDName = "expiredReservation$listNumber";
			}
	}
	
	$headers = "<table class='table' id='equipmentReservations'>
			<thead>
				<tr>
					<th>Reservation Time</th>
					<th>Expiration Time</th>
					<th>Equipment</th>
					<th>Status</th>
					<th>Actions</th>
				</tr>
			</thead>
			<tbody>";
			
	$footers = "</tbody>
		</table>
		<script>
		$('#equipmentReservations').DataTable(
			{
				aaSorting: [[0, 'desc']]
			}
		);

		</script>";
		
	if ($reservedHTML != '')
		$reservedHTML = $headers . $reservedHTML . $footers;	
	
	
} else {
	$reservedHTML = "";
}

$checkedOutEquipmentLateCount = 0;
$checkedoutEquipmentCount = 0;
$checkoutHTML = '';
$activeHTML = '';
$oldHTML = '';
$listNumber = 0;
if ($checkedoutEquipment){
	foreach ($checkedoutEquipment as $c){
		$checkoutID = $c->getCheckoutID();
		$reservationID = $c->getReservationID();
		$userID = $c->getUserID();

		$pickupTime = $c->getPickupTime();
		$latestPickupTime = $c->getDeadlineTime();
		$returnedTime = $c->getReturnTime();
		$contractName = $c->getContractID();
		$status = $c->getStatusID()->getName();

		$statusID = $c->getStatusID()->getId();
		$reservation = $reservationDao->getReservation($reservationID);
		$equipmentID = $reservation->getEquipmentID();
		$equipment = $equipmentDao->getEquipment($equipmentID);

		$equipmentName = Security::HtmlEntitiesEncode($equipment->getEquipmentName());
		$equipmentLocation = Security::HtmlEntitiesEncode($equipment->getLocation());

		if ($statusID == "Late"){
			$checkedOutEquipmentLateCount++;
		}

		if ($statusID == "Returned" || $statusID == "Returned Late"){
			// If equipment has been returned
			renderViewCheckoutModal($c);
			renderEquipmentFeesModal($c);
			//$assignFeeButton = createAssignEquipmentFeesButton($checkoutID, $userID, $reservationID);
			$returnButton = createViewCheckoutButton($checkoutID);
			//TODO: View Checkout button
			$oldHTML .= "
			<tr id='checkout$listNumber'>
				<td>$pickupTime</td>
				<td>$latestPickupTime</td>
				<td>$returnedTime</td>
				<td>$equipmentName</td>
				<td>$status</td>
				<td>$returnButton</td>
			</tr>
			";
		} else {
			//renderEquipmentReturnModal($c);
			$checkedoutEquipmentCount++;
			$returnButton = "";
			$assignFeeButton = "";
			//TODO: Extend checkout button
			$activeHTML .= "
			<tr id='checkout$listNumber'>
				<td>$pickupTime</td>
				<td>$latestPickupTime</td>
				<td>$returnedTime</td>
				<td>$equipmentName</td>
				<td>$status</td>
				<td>$returnButton</td>
			</tr>
			";

		}

		$listNumber++;

	}
	$headers = "<table class='table' id='activeCheckouts'>
			<thead>
				<tr>
					<th>Pickup Time</th>
					<th>Deadline Time</th>
					<th>Returned Time</th>
					<th>Equipment</th>
					<th>Status</th>
					<th>Actions</th>
				</tr>
			</thead>
			<tbody>";
	$footers = "</tbody>
		</table>
		<script>
		$('#activeCheckouts').DataTable(
			{
				'paging':false, 
				'searching': false, 
				aaSorting: [[0, 'desc']]
			}
		);

		</script>";
	$checkoutHTML .= "<h3>Current Check-Outs</h3>" . $headers . $activeHTML . $footers;
	
	
	$headers = "<table class='table' id='oldCheckouts'>
			<thead>
				<tr>
					<th>Pickup Time</th>
					<th>Deadline Time</th>
					<th>Returned Time</th>
					<th>Equipment</th>
					<th>Status</th>
					<th>Actions</th>
				</tr>
			</thead>
			<tbody>";
	$footers = "</tbody>
		</table>
		<script>
		$('#oldCheckouts').DataTable(
			{
				'paging':false, 
				aaSorting: [[0, 'desc']]
			}
		);

		</script>";
	$checkoutHTML .= "<h3><BR>Previous Check-Outs</h3>" . $headers . $oldHTML . $footers;
	
} else {
	$checkoutHTML = "";
}

/*
This section creates lock/unlock cards for each TekBox currently checked out to the user
*/

$tekBoxHTML = '';
$boxes = $boxDao->getBoxByUser($uId);
$tekBoxHTML .= "<div class='card col-3' style='padding-top:1em;padding-bottom:1em;margin:1em;'>
					<h5 class'card-title'>TekBoxs</h5>
					<div class='card-body'>";
if (count($boxes) > 0){
	foreach ($boxes AS $b){
		$tekBoxHTML .= "<div class='row'><div class='col-9'>TekBox #: " . $b->getNumber() . "<BR>";
		$tekBoxHTML .= "Filled: " .date("l, M/d",strtotime($b->getFillDate())). "<BR>";
		
		if ($b->getLocked() == 0){
			$tekBoxHTML .= "Status: <span id='status".$b->getBoxKey()."'>Unlocked</span></div>";
			$tekBoxHTML .= "<div class='col-3'><button id='tekboxButton".$b->getBoxKey()."' class='btn btn-danger' onclick='lock(\"$uId\", \"".$b->getBoxKey()."\")'>Lock?</button></div></div>";
		} else {
			$tekBoxHTML .= "Status: <span id='status".$b->getBoxKey()."'>Locked</span></div>";
			$tekBoxHTML .= "<div class='col-3'><button id='tekboxButton".$b->getBoxKey()."' class='btn btn-success' onclick='unlock(\"$uId\", \"".$b->getBoxKey()."\")'>Unlock?</button></div></div>";	
		}
	}
	$tekBoxHTML .= "</div>
					</div>";
} else {
	$tekBoxHTML .= "You do not have any items in a TekBox.</div>
					</div>";
}

$tekBoxHTML .= "
<script type='text/javascript'>
function lock(uid, id){
	
	let content = {
		action: 'lock',
		boxId: id,
		uId: uid
	}
	
	api.post('/boxes.php', content).then(res => {
		snackbar(res.message, 'Box Locked');
		$('#tekboxButton'+id).hide();
		$('#status'+id).html('Locked');
	}).catch(err => {
		snackbar(err.message, 'error');
	});
}

function unlock(uid, id){
	
	let content = {
		action: 'unlock',
		boxId: id,
		uId: uid
	}
	
	api.post('/boxes.php', content).then(res => {
		snackbar(res.message, 'Box Unlocked');
		$('#tekboxButton'+id).hide();
		$('#status'+id).html('Unlocked');
	}).catch(err => {
		snackbar(err.message, 'error');
	});
}
</script>

";

/*
Checks if the user has kits to pick up.
*/

$kitsHTML = "";
$tempkits = $kitsDao->getKitEnrollmentsByOnid($uOnid);
$kits = Array();
foreach ($tempkits AS $t)
	if ($t->getKitStatusID()->getId() == 1) // KitEnrollmentStatus::READY = 1
		$kits[] = $t;
	
if (sizeof($kits) > 0){
	$kitsHTML .= "<div class='card col-3' style='padding-top:1em;padding-bottom:1em;margin:1em;'>
						<h5 class'card-title'>Class Kits for Pickup</h5>
						<div class='card-body'><p>Class kits can be picked up from KEC1110 during store hours.</p>Courses:<BR>";
	foreach ($kits AS $k){
		$kitsHTML .= $k->getCourseCode() . "<BR>";
	}
	$kitsHTML .= "</div>
				</div>";
}
/*
This section prepares the Dashboard contents for the Dashboard tab.
This includes Reservations and Active Check-Outs

*/

$dashboardHTML = "";
$dashboardHTML .= "
	<br><br><br><br>
	<section class='panel'>
		<div class='row'>";
		$dashboardHTML .= $kitsHTML;
		$dashboardHTML .= $tekBoxHTML;
		
		/*
		if ($checkoutFeeCount != 0){
			$dashboardHTML .= "<div class='card col-3' style='padding-top:1em;padding-bottom:1em;margin:1em;'>
						<h5 class'card-title'>Equipment Fees</h5>
						<div class='card-body'>You have unpaid equipment checkout fees!  Pay them as soon as possible.
						</div></div>";
		} else {
			$dashboardHTML .= "<div class='card col-3' style='padding-top:1em;padding-bottom:1em;margin:1em;'>
						<h5 class'card-title'>Equipment Fees</h5>
						<div class='card-body'>You do not have any unpaid equipment fees.
						</div></div>";
		}
		*/
		
		
		if ($reservedEquipmentCount != 0){
			$dashboardHTML .= "<div class='card col-3' style='padding-top:1em;padding-bottom:1em;margin:1em;'>
						<h5 class'card-title'>Equipment Reservations</h5>
						<div class='card-body'>You have an equipment reservation!  Go to TekBots (KEC 1110) to pick up your equipment before your reservation expires.
						</div></div>";
		} else {
			$dashboardHTML .= "<div class='card col-3' style='padding-top:1em;padding-bottom:1em;margin:1em;'>
						<h5 class'card-title'>Equipment Reservations</h5>
						<div class='card-body'>You do not have any equipment reservations.
						</div></div>";
		}
		
		
		if ($checkedOutEquipmentLateCount != 0){
			$dashboardHTML .= "<div class='card col-3' style='padding-top:1em;padding-bottom:1em;margin:1em;'>
						<h5 class'card-title'>Late Equipment</h5>
						<div class='card-body'>You have yet to return a checked out equipment!  Return the item to TekBots (KEC 1110) ASAP to prevent late fees and having your student account charged!
						</div></div>";
		} else {
			$dashboardHTML .= "<div class='card col-3' style='padding-top:1em;padding-bottom:1em;margin:1em;'>
						<h5 class'card-title'>Late Equipment</h5>
						<div class='card-body'>You do not have any late equipment currently checked out.
						</div></div>";
		}

		
		$dashboardHTML .= "<div class='card col-3' style='padding-top:1em;padding-bottom:1em;margin:1em;'>
						<h5 class'card-title'>Equipment Checked Out</h5>";
		if ($checkedoutEquipmentCount != 0){
			$dashboardHTML .= "<div class='card-body'>You currently have $checkedoutEquipmentCount equipment(s) checked out!  Make sure to keep track of the deadline time and return the item before then!</div>";
		} else {
			$dashboardHTML .= "<div class='card-body'>You do not have any equipment currently checked out.</div>";
		}
		$dashboardHTML .= "</div>";
		
$dashboardHTML .= "
		</div>
	</section>";


?>

<br>
<div class="stickytabs">
	<!-- TODO: Change defaultOpen -->
	<button class="tablink" onclick="openPage('Dashboard', this, '#A7ACA2')" id="defaultOpen">Dashboard</button>
	<button class="tablink" onclick="openPage('Jobs', this, '#A7ACA2')" >Print/Cut Jobs</button>
	<!-- <button class="tablink" onclick="openPage('Fees', this, '#A7ACA2')">My Fees</button> -->
	<button class="tablink" onclick="openPage('Equipment', this, '#A7ACA2')">Borrowed Equipment</button>
	<button class="tablink" onclick="openPage('Profile', this, '#A7ACA2')">Profile</button>
	
</div>



<div id="Jobs" class="tabcontent">
	<div class="container-fluid">
		
		<!-- Perform query for all print jobs of students-->
		<!-- Using table from print jobs: -->
			<!-- Students can see status (pending, printing, etc) -->
			<!-- Students can see a render of the print they submitted -->
			<?php
				echo "
			<br><br><br>
			<div class='admin-paper'>
			<h3>Print Jobs &nbsp&nbsp&nbsp&nbsp<a href='./pages/submit3DPrint.php'><button class='btn btn-info'>New 3D Print</button></a></h3>
				<table class='table' id='printJobs'>
					<thead>
						<tr>
							<th>Date Submitted</th>
							<th>File</th>
							<th>Your notes</th>
							<th>Status</th>

						</tr>
					</thead>
					<tbody>
						$printJobsHTML
					</tbody>
				</table>
				<script>
					$('#printJobs').DataTable({
						'searching': false, 
						'order':[[0, 'desc']]
						});
				</script>
			</div>
			<div class='admin-paper'>
			<h3>Laser Cuts &nbsp&nbsp&nbsp&nbsp<a href=''><button class='btn btn-info'>New Laser Cut</button></a></h3>
				<table class='table' id='laserCuts'>
					<thead>
						<tr>
							<th>Date Submitted</th>
							<th>File</th>
							<th>Your notes</th>
							<th>Status</th>

						</tr>
					</thead>
					<tbody>
						$laserCutsHTML
					</tbody>
				</table>
				<script>
					$('#laserCuts').DataTable({
						'searching': false, 
						'order':[[0, 'desc']]
						});
				</script>
			</div>
				
				";
			?>
	
		
			
	</div>
</div>


<div id="Dashboard" class="tabcontent">
	<?php echo $dashboardHTML;?>
</div>



<div id="Profile" class="tabcontent">
<form id="formUserProfile">
	<input type="hidden" name="uid" value="<?php echo $_SESSION['userID']; ?>" />
	<div class="">
		<br><br><br><br><br>
		<div class="container bootstrap snippets">
			<div class="row">
				<div class="col-sm-6">
					<div class="panel-heading">
						<h4 class="panel-title">User Info</h4>
					</div>
					<div class="panel-body">
						<div class="form-group">
							<label class="col control-label" for="firstNameText">First Name</label>
							<div class="col-sm-11">
								<textarea class="form-control" id="firstNameText" name="firstName" rows="1"
									required readonly><?php echo $uFirstName; ?></textarea>
							</div>
						</div>
						<div class="form-group">
							<label class="col control-label" for="lastNameText">Last Name</label>
							<div class="col-sm-11">
								<textarea class="form-control" id="lastNameText" name="lastName"
									rows="1" readonly><?php echo $uLastName; ?></textarea>
							</div>
						</div>
						<div class="container">
							<div class="row">

							</div>
						</div>
						<br>
						
						<div class="panel-body">
							<br>
							<div class="col-sm-11">
								<button class="btn btn-large btn-block btn-primary" id="saveProfileBtn"
									type="button">Save</button>
								<div id="successText" class="successText" style="display:none;">Success!</div>
							</div>
						</div>
					</div>
				</div>

				<div class="col-sm-6">
					<div class="panel-heading">
						<h4 class="panel-title">Contact Info</h4>
					</div>
					<div class="panel-body">
						<div class="form-group">
							<label class="col control-label" for="phoneText">Phone Number</label>
							<div class="col">
								<textarea class="form-control" id="phoneText" name="phone" rows="1"
									required><?php echo $uPhone; ?></textarea>
							</div>
						</div>
						<div class="form-group">
							<label class="col control-label" for="emailText">Email Address</label>
							<div class="col">
								<textarea class="form-control" id="emailText" name="email" rows="1"
									 readonly required><?php echo $uEmail; ?></textarea>
							</div>
						</div>
						<br>
						<div class="panel-heading">
							<h4 class="panel-title">Account info</h4>
						</div>
						<hr class="my-4">
						<div class="form-group">
							<p class="form-control-static">User Type: <?php echo $uAccessLevel; ?> </p>
							<div class="col">

							</div>
						</div>
						<div class="form-group">
							<?php
								// TODO: display projects here
								?>
							<div class="col-sm-11">

							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</form>
</div>


		
			<?php
/*
echo "<div id='Fees' class='tabcontent'>
	<div class='container-fluid'>
			<br><br><br><br><br>
			<div class='admin-paper'>
			<h3>Equipment Checkout Fees</h3>
				<table class='table' id='equipmentFees'>
				<caption>Equipment Checkout Fees</caption>
					<thead>
						<tr>
							<th>Related Transaction</th>
							<th>Notes</th>
							<th>Amount</th>
							<th>Actions</th>
						</tr>
					</thead>
					<tbody>
						$feeHTML
					</tbody>
				</table>
				<script>
					$('#equipmentFees').DataTable();
				</script>
			</div>
		</div>
	</div>	
				";
*/			
			?>
	
		
			


<div id="Equipment" class="tabcontent">
<?php
echo "
	<br><br><br><br><br>
	<div class='admin-paper'>
	<h3>Reserved Equipment</h3><a href='./pages/browseEquipment.php'><button class='btn btn-warning'>Browse Equipment</button></a>
	$reservedHTML
	</div>";
	
	echo "<br><br>";

	echo "<div class='admin-paper'>
	$checkoutHTML
	</div>";

	?>

</div>

<script defer type="text/javascript">

function openPage(pageName, elmnt, color) {
  // Hide all elements with class="tabcontent" by default */
  var i, tabcontent, tablinks;
  tabcontent = document.getElementsByClassName("tabcontent");
  for (i = 0; i < tabcontent.length; i++) {
    tabcontent[i].style.display = "none";
  }

  // Remove the background color of all tablinks/buttons
  tablinks = document.getElementsByClassName("tablink");
  for (i = 0; i < tablinks.length; i++) {
    tablinks[i].style.backgroundColor = "";
  }

  // Show the specific tab content
  document.getElementById(pageName).style.display = "block";

  // Add the specific color to the button used to open the tab content
  elmnt.style.backgroundColor = color;
}

// Get the element with id="defaultOpen" and click on it
document.getElementById("defaultOpen").click();

/**
 * Event handler for a click event on the 'Save' button for user profiles.
 */
function onSaveProfileClick() {

	let data = new FormData(document.getElementById('formUserProfile'));

	let body = {
		action: 'saveProfile'
	};
	for(const [key, value] of data.entries()) {
		body[key] = value;
	}

	api.post('/users.php', body).then(res => {
		snackbar(res.message, 'success');
	}).catch(err => {
		snackbar(err.message, 'error');
	});

}
$('#saveProfileBtn').on('click', onSaveProfileClick);
</script>


<?php include_once PUBLIC_FILES . '/modules/footer.php'; ?>
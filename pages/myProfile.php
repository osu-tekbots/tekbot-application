<?php
include_once '../bootstrap.php';

use DataAccess\UsersDao;
use DataAccess\EquipmentFeeDao;
use DataAccess\EquipmentDao;
use DataAccess\EquipmentCheckoutDao;
use DataAccess\EquipmentReservationDao;
use DataAccess\PrinterFeeDao;
use DataAccess\PrinterDao;
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
	'assets/Madeleine.js/src/Madeleine.js',
	// 'assets/StlViewer.js/stl_viewer.min.js',
	// 'assets/StlViewer.js/CanvasRenderer.js',
	// 'assets/StlViewer.js/load_stl.min.js',
	// 'assets/StlViewer.js/OrbitControls.js',
	// 'assets/StlViewer.js/parser.min.js',
	// 'assets/StlViewer.js/Projector.js',
	// 'assets/StlViewer.js/three.min.js',
	// 'assets/StlViewer.js/TrackballControls.js',
	// 'assets/StlViewer.js/webgl_detector.js',
);
include_once PUBLIC_FILES . '/modules/header.php';
include_once PUBLIC_FILES . '/modules/newHandoutModal.php';
include_once PUBLIC_FILES . '/modules/employee.php';
include_once PUBLIC_FILES . '/modules/renderBrowse.php';

$usersDao = new UsersDao($dbConn, $logger);

$user = $usersDao->getUserByID($_SESSION['userID']);


// TODO: handle the case where we aren't able to fetch the user
if ($user){
	$uId = $user->getUserID();
	$uFirstName = $user->getFirstName();
	$uLastName = $user->getLastName();
	$uPhone = $user->getPhone();
	$uEmail = $user->getEmail();
	$uAccessLevel = $user->getAccessLevelID()->getName();
}

$checkoutFeeDao = new EquipmentFeeDao($dbConn, $logger);
$equipmentDao = new EquipmentDao($dbConn, $logger);
$userDao = new UsersDao($dbConn, $logger);
$checkoutDao = new EquipmentCheckoutDao($dbConn, $logger);
$reservationDao = new EquipmentReservationDao($dbConn, $logger);
$printerFeeDao = new PrinterFeeDao($dbConn, $logger);
$printerDao = new PrinterDao($dbConn, $logger);


$uID = $_SESSION['userID'];
$checkoutFees = $checkoutFeeDao->getFeesForUser($uID);
$printerFees = $printerFeeDao->getFeesForUser($uID);
$reservedEquipment = $reservationDao->getReservationsForUser($uID);
$checkedoutEquipment = $checkoutDao->getCheckoutsForUser($uID);
$checkedoutEquipment = $checkoutDao->getCheckoutsForUser($uID);
$studentPrintJobs = $printerDao->getPrintJobsForUser($uID);


$printJobsHTML = '';


include_once PUBLIC_FILES . '/modules/customerPrintModal.php';

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


	</script>
	";

	$confirmationButton = "<button id='confirmPrint$printJobID' class='btn btn-outline-primary capstone-nav-btn'>Confirm</button>";

	$action = $validPrintDate ? ($userConfirm ? "" : $confirmationButton) : "";
	$currentStatus = $completePrintDate ? ("Print Completed") : ($validPrintDate ? ($userConfirm ? "Validated, in queue to print" : "Validated, awaiting your confirmation") : "Awaiting File Validation by Tekbots Employee");

	$printJobsHTML .= "
	<tr>
	<td>$dateCreated</td>
	<td><a href='./prints/$dbFileName'><button data-toggle='tool-tip' data-placement='top' title='$stlFileName' class='btn btn-outline-primary capstone-nav-btn'>Download</button></a>
	<button data-toggle='modal' data-target='#newReservationModal' data-whatever='$dbFileName' id='openNewReservationBtn'  class='btn btn-outline-primary capstone-nav-btn'>
		View
	</button></td>
	<td>$customerNotes</td>
	<td>$currentStatus</td>
	<td>$action</td>
	</tr>
	$buttonScripts
	";
}

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
			}
			else {
				$active = "Expired";
				//$handoutButton = createReserveAsEmployeeBtn($reservationID, $listNumber, $userID, $equipmentID);
				$handoutButton = "";
				$cancelButton = "";
				$tableIDName = "expiredReservation$listNumber";
			}
		



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
} else {
	$reservedHTML = "";
}

$checkedOutEquipmentLateCount = 0;
$checkedoutEquipmentCount = 0;
$checkoutHTML = '';
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
		} else {
			//renderEquipmentReturnModal($c);
			$checkedoutEquipmentCount++;
			$returnButton = "";
			$assignFeeButton = "";
			//TODO: Extend checkout button

		}

		$checkoutHTML .= "
		<tr id='checkout$listNumber'>
			<td>$pickupTime</td>
			<td>$latestPickupTime</td>
			<td>$returnedTime</td>
			<td>$equipmentName</td>
			<td>$status</td>
			<td>$returnButton</td>
		</tr>
		";
		$listNumber++;

	}
} else {
	$checkoutHTML = "";
}


?>

<br>
<div class="stickytabs">
	<!-- TODO: Change defaultOpen -->
	<button class="tablink" onclick="openPage('Dashboard', this, '#A7ACA2')" >Dashboard</button>
	<button class="tablink" onclick="openPage('Profile', this, '#A7ACA2')">Profile</button>
	<button class="tablink" onclick="openPage('Jobs', this, '#A7ACA2')" id="defaultOpen">Print/Cut Jobs</button>
	<button class="tablink" onclick="openPage('Fees', this, '#A7ACA2')">My Fees</button>
	<button class="tablink" onclick="openPage('Equipment', this, '#A7ACA2')">Equipment Reservations</button>
</div>



<div id="Jobs" class="tabcontent">
	<div class="container-fluid">
		
		<!-- Perform query for all print jobs of students-->
		<!-- Using table from print jobs: -->
			<!-- Students can see status (pending, printing, etc) -->
			<!-- Students can see a render of the print they submitted -->
			<?php
				echo "
			<br><br><br><br><br>
			<div class='admin-paper'>
			<h3>Print Jobs</h3>
				<table class='table' id='equipmentFees'>
				<caption>Equipment Checkout Fees</caption>
					<thead>
						<tr>
							<th>Date Submitted</th>
							<th>File</th>
							<th>Your notes</th>
							<th>Current Status</th>
							<th>Actions</th>
						</tr>
					</thead>
					<tbody>
						$printJobsHTML
					</tbody>
				</table>
				<script>
					$('#equipmentFees').DataTable({'order':[[0, 'desc']]});
				</script>
				
			</div>
				
				";
			?>
	
		
			
	</div>
</div>


<div id="Dashboard" class="tabcontent">
	<br><br><br><br>
	<section class="panel dashboard">
    <h2>Dashboard </h2>
	<ul>
		<?php 
		// If we have actionable items, show the action required
		$actionStyle = "style='color:red'";
		$dashboardText = "";
		if ($checkoutFeeCount != 0){
			$dashboardText .= 
			"
			<li $actionStyle>You have unpaid equipment checkout fees!  Pay them as soon as possible.</li>
			";
		}
		if ($reservedEquipmentCount != 0){
			$dashboardText .=
			"
			<li $actionStyle>You have an equipment reservation!  Go to TekBots (KEC 1110) to pick up your equipment before your reservation expires.</li>
			";
		}
		if ($checkedOutEquipmentLateCount != 0){
			$dashboardText .= "
			<li $actionStyle>You have yet to return a checked out equipment!  Return the item to TekBots (KEC 1110) ASAP to prevent late fees and having your student account charged!</li>
			";
		}
		if ($checkedoutEquipmentCount != 0){
			$dashboardText .= "
			<li>You currently have $checkedoutEquipmentCount equipment(s) checked out!  Make sure to keep track of the deadline time and return the item before then!</li>
			";
		}

		if (empty($dashboardText)){
			$dashboardText = "<li style='list-style-type:none;'>No Pending Items. Have a good day!</li>";
		}
		echo $dashboardText;
		?>
		

    </ul>
  </section>
 

</div>

<div id="Dashboard" class="tabcontent">
	<br><br><br><br>
	<section class="panel dashboard">
    <h2>Dashboard </h2>
	<ul>
		<?php 
		// If we have actionable items, show the action required
		$actionStyle = "style='color:red'";
		$dashboardText = "";
		if ($checkoutFeeCount != 0){
			$dashboardText .= 
			"
			<li $actionStyle>You have unpaid equipment checkout fees!  Pay them as soon as possible.</li>
			";
		}
		if ($reservedEquipmentCount != 0){
			$dashboardText .=
			"
			<li $actionStyle>You have an equipment reservation!  Go to TekBots (KEC 1110) to pick up your equipment before your reservation expires.</li>
			";
		}
		if ($checkedOutEquipmentLateCount != 0){
			$dashboardText .= "
			<li $actionStyle>You have yet to return a checked out equipment!  Return the item to TekBots (KEC 1110) ASAP to prevent late fees and having your student account charged!</li>
			";
		}
		if ($checkedoutEquipmentCount != 0){
			$dashboardText .= "
			<li>You currently have $checkedoutEquipmentCount equipment(s) checked out!  Make sure to keep track of the deadline time and return the item before then!</li>
			";
		}

		if (empty($dashboardText)){
			$dashboardText = "<li style='list-style-type:none;'>No Pending Items. Have a good day!</li>";
		}
		echo $dashboardText;
		?>
		

    </ul>
  </section>
 

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

<div id="Fees" class="tabcontent">
	<div class="container-fluid">
		
			<?php

				echo "
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
				
				";
			
			
				// File located inside modules/renderBrowse.php
			?>
	
		
			
	</div>
</div>

<div id="Equipment" class="tabcontent">
<?php
echo "
	<br><br><br><br><br>
	<div class='admin-paper'>
	<h3>Reserved Equipment</h3>
		<table class='table' id='equipmentReservations'>
		<caption>Reservations</caption>
			<thead>
				<tr>
					<th>Reservation Time</th>
					<th>Expiration Time</th>
					<th>Equipment</th>
					<th>Status</th>
					<th>Actions</th>
				</tr>
			</thead>
			<tbody>
				$reservedHTML
			</tbody>
		</table>
		<script>
		$('#equipmentReservations').DataTable(
			{
				lengthMenu: [[3, 5, 10, -1], [3, 5, 10, 'All']],
				aaSorting: [[0, 'desc']]
			}
		);

		</script>
	</div>
		
		";
		




		
	echo "
	<br><br>";

	
	echo "

	<div class='admin-paper'>
	<h3>Checked Out Equipment</h3>
		<table class='table' id='equipmentCheckouts'>
		<caption>Checkouts</caption>
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
			<tbody>
				$checkoutHTML
			</tbody>
		</table>
		<script>
		$('#equipmentCheckouts').DataTable(
			{
				lengthMenu: [[3, 5, 10, -1], [3, 5, 10, 'All']],
				aaSorting: [[0, 'desc']]
			}
		);

		</script>
	</div>
		
		";
	
		
		

		



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
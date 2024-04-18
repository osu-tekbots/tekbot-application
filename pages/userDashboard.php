<?php
include_once '../bootstrap.php';

use DataAccess\UsersDao;
use DataAccess\EquipmentFeeDao;
use DataAccess\EquipmentDao;
use DataAccess\EquipmentCheckoutDao;
use DataAccess\EquipmentReservationDao;
use DataAccess\PrinterFeeDao;
use DataAccess\PrinterDao;
use DataAccess\LaserDao;
use DataAccess\BoxDao;
use DataAccess\KitEnrollmentDao;
use Util\Security;

session_start();

include_once PUBLIC_FILES . '/lib/shared/authorize.php';

allowIf(verifyPermissions(['user', 'employee']), $configManager->getBaseUrl() . 'pages/login.php');

$title = 'My Dashboard';
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
	echo "<br><br><h1>You are not in the database. You should never have seen this.</h1>";
	echo "Please send us an email <a href='mailto:".$configManager->getWorkerMaillist()."'>here</a> to report the issue.";
	exit();
}

$checkoutFeeDao = new EquipmentFeeDao($dbConn, $logger);
$equipmentDao = new EquipmentDao($dbConn, $logger);
$userDao = new UsersDao($dbConn, $logger);
$checkoutDao = new EquipmentCheckoutDao($dbConn, $logger);
$reservationDao = new EquipmentReservationDao($dbConn, $logger);
$printerFeeDao = new PrinterFeeDao($dbConn, $logger);
$printerDao = new PrinterDao($dbConn, $logger);
$laserDao = new LaserDao($dbConn, $logger);
$boxDao = new BoxDao($dbConn, $logger);
$kitsDao = new KitEnrollmentDao($dbConn, $logger);

$uID = $_SESSION['userID'];
$checkoutFees = $checkoutFeeDao->getFeesForUser($uID);
$printerFees = $printerFeeDao->getFeesForUser($uID);



$studentPrintJobs = $printerDao->getPrintJobsForUser($uID);


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
Laser Cuts Notice
*/
$laserCutsHtml = '';
$unconfirmedCuts = $laserDao->getUnconfirmedLaserJobsForUser($uID);
$numUnconfirmedCuts = count($unconfirmedCuts);
if ($numUnconfirmedCuts > 0) {
	$laserCutsHtml = "<div class='card col-3' style='padding-top:1em;padding-bottom:1em;margin:1em;'>
	<h5 class'card-title'>You have $numUnconfirmedCuts unconfirmed cut(s)!</h5>
	<div class='card-body'>
	<a href='./pages/userCuts.php'><span class='lead mb-0'>Laser Cuts Webpage →</span></a>
	</div>
	</div>";
}

/*
3D Prints Notice
*/
$printsHtml = '';
$unconfirmedPrints = $printerDao->getUnconfirmedPrintJobsForUser($uID);
$numUnconfirmedPrints = count($unconfirmedPrints);
if ($numUnconfirmedPrints > 0) {
	$laserCutsHtml = "<div class='card col-3' style='padding-top:1em;padding-bottom:1em;margin:1em;'>
	<h5 class'card-title'>You have $numUnconfirmedPrints unconfirmed print(s)!</h5>
	<div class='card-body'>
	<a href='./pages/userPrints.php'><span class='lead mb-0'>3D Prints Webpage →</span></a>
	</div>
	</div>";
}

/*
TekBox Notice
*/
$tekBoxHTML = '';
$boxes = $boxDao->getBoxByUser($uId);
if (count($boxes) > 0)
$tekBoxHTML .= "<div class='card col-3' style='padding-top:1em;padding-bottom:1em;margin:1em;'>
			<h5 class'card-title'>You have an item to pickup from a TekBox!</h5>
			<div class='card-body'>
			<a href='./pages/userTekbox.php'><span class='lead mb-0'>TekBox Webpage →</span></a>
			</div>
			</div>";
			
/*
Kit Handout Notice
*/
$kitsHTML = "";
$tempkits = $kitsDao->getKitEnrollmentsByOnid($uOnid);
$kits = Array();
foreach ($tempkits AS $t)
	if ($t->getKitStatusID()->getId() == 1) // KitEnrollmentStatus::READY = 1
		$kits[] = $t;
	
if (sizeof($kits) > 0)
	$kitsHTML .= "<div class='card col-3' style='padding-top:1em;padding-bottom:1em;margin:1em;'>
			<h5 class'card-title'>You have a kit to pickup from TekBots!</h5>
			<div class='card-body'>
			<a href='./pages/userKits.php'><span class='lead mb-0'>Kit Pickup Webpage →</span></a>
			</div>
			</div>";


/*
Equipment Reservation Notice
*/
$reservationHTML = '';
if ($reservedEquipmentCount != 0)
	$reservationHTML = "<div class='card col-3' style='padding-top:1em;padding-bottom:1em;margin:1em;'>
			<h5 class'card-title'>You have an active equipment reservation!</h5>
			<div class='card-body'>
			<a href='./pages/publicEquipmentList.php'><span class='lead mb-0'>Borrowed Equipment Webpage →</span></a>
			</div>
			</div>";

/*
Late Equipment Notice
*/
$lateEquipmentHTML = '';						
if ($checkedOutEquipmentLateCount != 0)					
	$lateEquipmentHTML = "<div class='card col-3' style='padding-top:1em;padding-bottom:1em;margin:1em;'>
			<h5 class'card-title'>You have equipment to be returned that is now late!</h5>
			<div class='card-body'>
			<a href='./pages/publicEquipmentList.php'><span class='lead mb-0'>Borrowed Equipment Webpage →</span></a>
			</div>
			</div>";
					
/*
Checked Out Equipment Notice
*/
$checkedOutHTML = '';
if ($checkedoutEquipmentCount != 0)
	$checkedOutHTML = "<div class='card col-3' style='padding-top:1em;padding-bottom:1em;margin:1em;'>
			<h5 class'card-title'>You have equipment checked out.</h5>
			<div class='card-body'>
			<a href='./pages/publicEquipmentList.php'><span class='lead mb-0'>Borrowed Equipment Webpage →</span></a>
			</div>
			</div>";
?>

<br><br>
<a href="./pages/myProfile.php"><button class="btn btn-lg btn-outline-primary capstone-nav-btn" type="button">User Information</button></a>
<a href="./pages/userKits.php"><button class="btn btn-lg btn-outline-primary capstone-nav-btn" type="button">Course Kits</button></a>
<a href="./pages/userPrints.php"><button class="btn btn-lg btn-outline-primary capstone-nav-btn" type="button">3D Prints</button></a>
<a href="./pages/publicInventory.php"><button class="btn btn-lg btn-outline-primary capstone-nav-btn" type="button">TekBots Inventory</button></a>
<a href="./pages/userCuts.php"><button class="btn btn-lg btn-outline-primary capstone-nav-btn" type="button">Laser Cuts</button></a>
<a href="./pages/publicEquipmentList.php"><button class="btn btn-lg btn-outline-primary capstone-nav-btn" type="button">Equipment for Loan</button></a>
<a href="./pages/userTekbox.php"><button class="btn btn-lg btn-outline-primary capstone-nav-btn" type="button">TekBox Pickup System</button></a>
<a href="./pages/publicTicketSubmit.php"><button class="btn btn-lg btn-outline-primary capstone-nav-btn" type="button">Lab Ticket Submission</button></a>
<BR>
<?php echo $laserCutsHtml;?>
<?php echo $printsHtml;?>
<?php echo $tekBoxHTML;?>
<?php echo $kitsHTML;?>
<?php echo $reservationHTML;?>
<?php echo $lateEquipmentHTML;?>
<?php echo $checkedOutHTML;?>

<?php include_once PUBLIC_FILES . '/modules/footer.php'; ?>
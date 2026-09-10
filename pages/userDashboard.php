<?php
include_once '../bootstrap.php';

use DataAccess\UsersDao;
use DataAccess\EquipmentCheckoutDao;
use DataAccess\PrinterDao;
use DataAccess\LaserDao;
use DataAccess\BoxDao;
use DataAccess\KitEnrollmentDao;

if (PHP_SESSION_ACTIVE != session_status())
	session_start();

include_once PUBLIC_FILES . '/lib/shared/authorize.php';

allowIf(verifyPermissions(['user', 'employee'], $logger), $configManager->getBaseUrl() . 'pages/login.php');

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
include_once PUBLIC_FILES . '/modules/employee.php';

$usersDao = new UsersDao($dbConn, $logger);

$user = $usersDao->getUserByID($_SESSION['userID']);

if ($user){
	$uID = $_SESSION['userID'];
	$uFirstName = $user->getFirstName();
	$uLastName = $user->getLastName();
	$uOnid = $user->getOnid();
} else {
	echo "<br><br><h1>You are not in the database. You should never have seen this.</h1>";
	echo "Please send us an email <a href='mailto:".$configManager->getWorkerMaillist()."'>here</a> to report the issue.";
	exit();
}

$checkoutDao = new EquipmentCheckoutDao($dbConn, $logger);
$printerDao = new PrinterDao($dbConn, $logger);
$laserDao = new LaserDao($dbConn, $logger);
$boxDao = new BoxDao($dbConn, $logger);
$kitsDao = new KitEnrollmentDao($dbConn, $logger);


/*
Laser Cuts Notice
*/
$laserCutsHtml = '';
$unconfirmedCuts = $laserDao->getUnconfirmedLaserJobsForUser($uID);
$numUnconfirmedCuts = count($unconfirmedCuts);
if ($numUnconfirmedCuts > 0) {
	$laserCutsHtml = "
	<div class='card'>
		<div class='card-body'>
			<h5 class'card-title'>$numUnconfirmedCuts laser cut".($numUnconfirmedCuts > 1 ? 's' : '')." waiting for confirmation</h5>
			<a href='./pages/userCuts.php'>Laser Cuts Webpage →</a>
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
	$printsHtml = "
	<div class='card'>
		<div class='card-body'>
			<h5 class'card-title'>$numUnconfirmedPrints 3D print".($numUnconfirmedPrints > 1 ? 's' : '')." waiting for confirmation</h5>
			<a href='./pages/userPrints.php'>3D Prints Webpage →</a>
		</div>
	</div>";
}

// TODO: only show if locked
/*
TekBox Notice
*/
$tekBoxHTML = '';
$boxes = $boxDao->getBoxByUser($uID);
if (count($boxes) > 0) {
	$tekBoxHTML .= "
	<div class='card'>
		<div class='card-body'>
			<h5 class'card-title'>Items waiting for contactless pickup</h5>
			<a href='./pages/userTekbox.php'>TekBox Webpage →</a>
		</div>
	</div>";
}
			
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
	$kitsHTML .= "
	<div class='card'>
		<div class='card-body'>
			<h5 class'card-title'>Course kits ready to pick up from TekBots</h5>
			<a href='./pages/userKits.php'>Kit Pickup Webpage →</a>
		</div>
	</div>";


/*
Equipment Reservation Notice
*/
$reservedEquipmentCount = $checkoutDao->getReservationCountForUser($uID);
$reservationHTML = '';
if ($reservedEquipmentCount != 0)
	$reservationHTML = "
	<div class='card'>
		<div class='card-body'>
			<h5 class'card-title'>Equipment reserved for pickup</h5>
			<a href='./pages/publicEquipmentList.php'>Borrowed Equipment Webpage →</a>
		</div>
	</div>";
					
/*
Checked Out Equipment Notice
*/
$checkedOutEquipmentCount = $checkoutDao->getCheckoutCountForUser($uID);
$checkedOutHTML = '';
if ($checkedOutEquipmentCount != 0)
	$checkedOutHTML = "
	<div class='card'>
		<div class='card-body'>
			<h5 class'card-title'>Equipment checked out</h5>
			<a href='./pages/publicEquipmentList.php'>Borrowed Equipment Webpage →</a>
		</div>
	</div>";

/*
Late Equipment Notice: override checkout notice
*/
$checkedOutEquipmentLateCount = $checkoutDao->getLateCheckoutCountForUser($uID);
if ($checkedOutEquipmentLateCount != 0)
	$checkedOutHTML = "
	<div class='card'>
		<div class='card-body'>
			<h5 class'card-title'><span class='text-danger'>Overdue</span> equipment checked out</h5>
			<a href='./pages/publicEquipmentList.php'>Borrowed Equipment Webpage →</a>
		</div>
	</div>";
?>

<br><br>

<div class="container">
	<h1 class="h3 mt-3">Welcome<?= $_SESSION['newUser'] ? '' : ' back' ?>, <?= $uFirstName ?>!</h1>

	<div class="mt-4" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(max(14rem, calc(33% - 1rem)), 1fr)); gap: 1rem;">
		<?= $laserCutsHtml ?>
		<?= $printsHtml ?>
		<?= $tekBoxHTML ?>
		<?= $kitsHTML ?>
		<?= $reservationHTML ?>
		<?= $checkedOutHTML ?>
	</div>


	<div class="row mt-1 mb-4">
		<div class="col-sm mt-3">
			<h2 class="h5">Shop</h2>
			<a class="d-block" style="width: fit-content;" href="./pages/publicCart.php">My Cart</a>
			<a class="d-block" style="width: fit-content;" href="./pages/publicInventory.php">TekBots Inventory</a>
			<a class="d-block" style="width: fit-content;" href="./pages/publicEquipmentList.php">Equipment for Loan</a>
		</div>
		<div class="col-sm mt-3">
			<h2 class="h5">Fabrication</h2>
			<a class="d-block" style="width: fit-content;" href="./pages/userPrints.php">3D Prints</a>
			<a class="d-block" style="width: fit-content;" href="./pages/userCuts.php">Laser Cuts</a>
		</div>
		<div class="col-sm mt-3">
			<h2 class="h5">Lab Rooms</h2>
			<a class="d-block" style="width: fit-content;" href="./pages/publicTicketSubmit.php">Submit Ticket</a>
		</div>
		<div class="col-sm mt-3">
			<h2 class="h5">Personal</h2>
			<a class="d-block" style="width: fit-content;" href="./pages/myProfile.php">My Profile</a>
		</div>
	</div>
</div>

<?php include_once PUBLIC_FILES . '/modules/footer.php'; ?>
<?php
include_once '../bootstrap.php';

use DataAccess\UsersDao;
use DataAccess\KitEnrollmentDao;
use Model\KitEnrollmentStatus;
use Util\Security;

session_start();

include_once PUBLIC_FILES . '/lib/shared/authorize.php';

$isLoggedIn = isset($_SESSION['userID']) && !empty($_SESSION['userID']);
allowIf($isLoggedIn, $configManager->getBaseUrl() . 'pages/login.php');

$title = 'My Kits';
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

$userDao = new UsersDao($dbConn, $logger);
$kitsDao = new KitEnrollmentDao($dbConn, $logger);

$uID = $_SESSION['userID'];

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
?>
<br>
<?php echo $kitsHTML;?>


<?php include_once PUBLIC_FILES . '/modules/footer.php'; ?>
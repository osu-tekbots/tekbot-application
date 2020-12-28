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
include_once PUBLIC_FILES . '/modules/renderTermData.php';
include_once PUBLIC_FILES . '/modules/header.php';
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
$kits = $kitsDao->getKitEnrollmentsByOnid($uOnid);
$kitStatus = $kitsDao->getKitEnrollmentTypes();
	
if (sizeof($kits) > 0){
	$kitsHTML .= "<table class='table' id='oldCheckouts'>
			<thead>
				<tr>
					<th>Term</th>
					<th>Course</th>
					<th>Status</th>
				</tr>
			</thead>
			<tbody>";
			
	foreach ($kits AS $k)
		$kitsHTML .= "<tr><td>" .term2string($k->getTermID()) . "</td><td>" .$k->getCourseCode() . "</td><td>" . $k->getKitStatusID()->getName(). "</td></tr>";
	
	$kitsHTML .= "</tbody></table>";
	$kitsHTML .= "<script>
		$('#kits').DataTable(
			{
				'paging': false, 
				aaSorting: [[0, 'desc']]
			}	
		);

		</script>";
} else {
	$kitsHTML .= "<BR>No records of kits for this user.";
}
?>
<br><br>
<div class="alert">
	<h1>Course Kits</h1>
	<div class="row">
		<div class="col-7">
		<p class="lead mb-0">The TekBots group helps to distribute kits for courses in the School of EECS. This page lists the status of your kits.</p>
		<p class="lead mb-0"><b>COVID Update: </b>Normally all kits are available for pickup form KEC1110 during the store hours posted on our mainpage. To address concerns of COVID transmission an option for mailing kits or contactless pickup is available. These processes are described in an email / Canvas announcement that was sent to each course.</p>
		<BR><BR><?php echo $kitsHTML;?>
		</div>
		<div class="col-5"><img class="img-fluid rounded" src="./assets/img/rect2.png">
		</div>
	</div>
</div>


<?php include_once PUBLIC_FILES . '/modules/footer.php'; ?>
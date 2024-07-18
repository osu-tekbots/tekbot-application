<?php
include_once '../bootstrap.php';

use DataAccess\UsersDao;
use DataAccess\LaserDao;
use Util\Security;

session_start();

include_once PUBLIC_FILES . '/lib/shared/authorize.php';

allowIf(verifyPermissions(['user', 'employee'], $logger), $configManager->getBaseUrl() . 'pages/login.php');

$title = 'My Laser Cuts';
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
	echo "<h1>You are not in the database. You should never have seen this.</h1>";
	exit();
}


$userDao = new UsersDao($dbConn, $logger);
$laserDao = new LaserDao($dbConn, $logger);


$uID = $_SESSION['userID'];
$studentLaserJobs = $laserDao->getLaserJobsForUser($uID);


/*
This section prepares the 3D printing/Laser cutting tab.
*/

include_once PUBLIC_FILES . '/modules/customerPrintModal.php';


$printJobsHTML = '';
foreach($studentLaserJobs as $p) {
	$printJobID = $p->getLaserJobId();
	$userID = $p->getUserID();
	$user = $userDao->getUserByID($p->getUserID());
	$name = Security::HtmlEntitiesEncode($user->getFirstName()) . ' ' . Security::HtmlEntitiesEncode($user->getLastName());
	$dbFileName = $p->getDbFileName();
	$dxfFileName = $p->getDxfFileName();
	$dateCreated = $p->getDateCreated();
	$validPrintDate = $p->getValidCutDate();
	$userConfirm = $p->getUserConfirmDate();
	$completePrintDate = $p->getCompleteCutDate();
	$customerNotes = $p->getCustomerNotes();
	$employeeNotes = $p->getEmployeeNotes();
	$pendingResponse = $p->getPendingCustomerResponse();


	$buttonScripts = "
	<script>
	$('#confirmPrint$printJobID').on('click', function() {
		if(confirm('Confirm cut and allow employees to start cutting?')) {
			let printJobID = '$printJobID';
			let data = {
				action: 'customerConfirmCut',
				laserJobID: printJobID,
			}
			api.post('/lasers.php', data).then(res => {
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
				action: 'deleteCutJob',
				laserJobID: printJobID,
			}
			api.post('/lasers.php', data).then(res => {
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
		$status = "<a data-toggle='tool-tip' data-placement='top' title='$validPrintDate'>Cut Validated By Employee</a><br/>";
	} else {
		$status = "Waiting for Employee to Validate Laser Cut"; 
	}


	$confirmationButton = "<button id='confirmPrint$printJobID' class='btn btn-outline-primary capstone-nav-btn'>Confirm</button>";
	$denyButton = "<button id='deletePrint$printJobID' class='btn btn-outline-danger capstone-nav-btn'>Delete</button>";
	if($validPrintDate && $pendingResponse) {
		$status .= "Awaiting your confirmation:<br/>";
		$status .= $confirmationButton;
		$status .= $denyButton;	
	} elseif(!$completePrintDate && $validPrintDate) {
		$status .= "Currently in queue to Cut";
	}

	if($completePrintDate) {
		$status = "Cut has completed";
	}

	$printJobsHTML .= "
	<tr>
	<td>$dateCreated</td>
	<td>
		<a style='color:#0000FF;' href='./uploads/lasercuts/$dbFileName'>$dxfFileName</a>
	</td>
	<td>$customerNotes</td>
	<td>$status</td>

	</tr>
	$buttonScripts
	";
}
?>

<br>
	<div class="container-fluid">
		
		<!-- Perform query for all print jobs of students-->
		<!-- Using table from print jobs: -->
			<!-- Students can see status (pending, printing, etc) -->
			<!-- Students can see a render of the print they submitted -->
			<br><br>
			<!--
			<div class="alert alert-danger mx-2">
				<b>Note:</b> We are unable to process any further laser cuts until further notice. We apologize for the inconvenience.
				If you have any questions, please <a href="./pages/info.php">see the FAQ</a> or <a href="mailto:tekbot-worker@engr.oregonstate.edu">send us an email</a>.
			</div>
			-->
			<br>
			<div class='admin-paper'>
			<h3>Laser Cut Jobs &nbsp&nbsp&nbsp&nbsp<a href='./pages/userCutsSubmit.php'><button class='btn btn-info'>New Laser Cut</button></a></h3>
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
						<?= $printJobsHTML ?>
					</tbody>
				</table>
				<script>
					$('#printJobs').DataTable({
						'searching': false, 
						'order':[[0, 'desc']]
						});
				</script>
			</div>
	
		
			
	</div>


<?php include_once PUBLIC_FILES . '/modules/footer.php'; ?>
<?php
include_once '../bootstrap.php';

use DataAccess\UsersDao;
use DataAccess\PrinterFeeDao;
use DataAccess\PrinterDao;
use Util\Security;

session_start();

include_once PUBLIC_FILES . '/lib/shared/authorize.php';

allowIf(verifyPermissions(['user', 'employee']), $configManager->getBaseUrl() . 'pages/login.php');

$title = 'My 3D Prints';
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
	echo "<h1>You are not in db. You should never have seen this.</h1>";
	exit();
}


$userDao = new UsersDao($dbConn, $logger);
$printerFeeDao = new PrinterFeeDao($dbConn, $logger);
$printerDao = new PrinterDao($dbConn, $logger);


$uID = $_SESSION['userID'];
$printerFees = $printerFeeDao->getFeesForUser($uID);
$studentPrintJobs = $printerDao->getPrintJobsForUser($uID);


/*
This section prepares the 3D printing/Laser cutting tab.
*/

include_once PUBLIC_FILES . '/modules/view3dPrintModal.php';


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
		<button data-toggle='modal' data-target='#view3dModel' data-whatever='$dbFileName' id='openNewReservationBtn'  class='btn btn-outline-primary capstone-nav-btn'>
			View
		</button>
		<a style='color:#0000FF;' href='./uploads/prints/$dbFileName'>$stlFileName</a>
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
			<?php
				echo "
			<br><br><br>
			<div class='admin-paper'>
			<h3>Print Jobs &nbsp&nbsp&nbsp&nbsp<a href='./pages/userPrintsSubmit.php'><button class='btn btn-info'>New 3D Print</button></a></h3>
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
			
				";
			?>
	
		
			
	</div>


<?php include_once PUBLIC_FILES . '/modules/footer.php'; ?>
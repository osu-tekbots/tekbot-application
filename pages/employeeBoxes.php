<?php
include_once '../bootstrap.php';

use DataAccess\BoxDao;
use DataAccess\UsersDao;
use Util\Security;

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token');

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL); 


if (PHP_SESSION_ACTIVE != session_status())
    session_start();

// Make sure the user is logged in and allowed to be on this page
include_once PUBLIC_FILES . '/lib/shared/authorize.php';

allowIf(verifyPermissions('employee', $logger), 'index.php');


$title = 'Employee TekBoxes';
$css = array(
	'assets/css/sb-admin.css',
	'assets/css/admin.css',
	'https://cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css'
);
$js = array(
    'https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js'
);


if (isset($_REQUEST['key'])){
	$boxDao = new BoxDao($dbConn, $logger);
	$levels = $boxDao->getBatteryLevels($_REQUEST['key']);
	echo "<table><tr><th>Time</th><th>Reading</th></tr>";
	foreach ($levels AS $l)
		echo "<tr><td>" . $l['timestamp'] . "</td><td>" . $l['battery'] . "</td></tr>";
	echo "</table>";
	exit();
}

include_once PUBLIC_FILES . '/modules/header.php';
include_once PUBLIC_FILES . '/modules/employee.php';
include_once PUBLIC_FILES . '/modules/renderBrowse.php';

$boxDao = new BoxDao($dbConn, $logger);
$userDao = new UsersDao($dbConn, $logger);
$boxes = $boxDao->getBoxes();

$options = "<option value=''></option>";
$users = $userDao->getAllUsers();
foreach ($users as $user){
	$options .= "<option value='".$user->getUserID()."'>".$user->getLastName().", ".$user->getFirstName()."</option>";
}

?>
<script type='text/javascript'>
function fillBox(id){
	var userid = $('#name'+id).val();
	var contents = $('#contents'+id).val();
	
	if (userid != ''){
		let content = {
			action: 'fillBox',
			userId: userid,
			contents: contents,
			fillById: '<?php echo $_SESSION['userID'];?>',
			messageId: 'fji8486u6jmfai8w',
			boxId: id
		}
		
		api.post('/boxes.php', content).then(res => {
			snackbar(res.message, 'Order Filled');
			$('#row'+id).css({ opacity: 0 });
		}).catch(err => {
			snackbar(err.message, 'error');
		});
	}
}

function resetBox(id){
	
	let content = {
		action: 'resetBox',
		boxId: id
	}
	
	api.post('/boxes.php', content).then(res => {
		snackbar(res.message, 'info');
		$('#row'+id).css({ opacity: 0 });
	}).catch(err => {
		snackbar(err.message, 'error');
	});
}

function lockBox(id){
	
	let content = {
		action: 'lockAdmin',
		boxId: id
	}
	
	api.post('/boxes.php', content).then(res => {
		snackbar(res.message, 'info');
		$('#row'+id).css({ opacity: 0 });
	}).catch(err => {
		snackbar(err.message, 'error');
	});
}

function unlockBox(id){
	
	let content = {
		action: 'unlockAdmin',
		boxId: id
	}
	
	api.post('/boxes.php', content).then(res => {
		snackbar(res.message, 'Box Unlocked');
		$('#row'+id).css({ opacity: 0 });
	}).catch(err => {
		snackbar(err.message, 'error');
	});
}

function emptyBox(id){
	
	let content = {
		action: 'emptyBox',
		messageId: 'ojuetr7w87utmmvk',
		boxId: id
	}
	
	api.post('/boxes.php', content).then(res => {
		snackbar(res.message, 'Order Removed');
		$('#row'+id).css({ opacity: 0});
	}).catch(err => {
		snackbar(err.message, 'error');
	});
}

</script>

<br/>
<div id="page-top">

	<div id="wrapper">

	<?php 
		// Located inside /modules/employee.php
		renderEmployeeSidebar();
	?>

    <div class="admin-content" id="content-wrapper">
        <div class="container-fluid">
			<?php 
			echo "<div class='admin-paper'>";

			
			foreach ($boxes as $b) {
				$boxNumber = $b->getNumber();
				$boxKey = $b->getBoxKey();
				$fillDate = $b->getFillDate();
				$pickupDate = $b->getPickupDate();
				$locked = $b->getLocked();
				$userId = $b->getUserId();
				$contents = $b->getContents();
				$battery = $b->getBattery();
				$battery = (min(($battery - 1248)/(1790-1248),1))*100;
				if ($userId != '')
					$user = $userDao->getUserById($userId);
				$fillBy = $b->getFillBy();
				if ($fillBy != '')
					$fillByUser = $userDao->getUserById($fillBy);

			 
					echo '<div class="form-group row" id="row'.$boxKey.'" style="padding-left:4px;padding-right:4px;margin-top:4px;margin-bottom:4px;">
							<div class="col-sm-1" style="text-align:right;">
							<h2>'.$boxNumber.':</h2>
							<a href="./pages/employeeBoxes.php?key='.$boxKey.'"><div class="progress"><div class="progress-bar '.($battery < 25 ? 'bg-danger' :'bg-success').'" role="progressbar" style="width: '.$battery.'%" aria-valuenow="'.$battery.'" aria-valuemin="0" aria-valuemax="100">'.number_format($battery,0).'%</div>'.($battery < 25 ? '&nbsp;&nbsp;Low Battery' :'').'</div></a>
							</div>';
							
					if ($fillDate == '0000-00-00 00:00:00'){ //Box is available for use
						echo '<div class="form-group col-sm-4">User:<select id="name'.$boxKey.'" class="custom-select" >'.$options.'</select></div>';
						echo '<div class="form-group col-sm-4">Contents:<input type="text" class="form-control" id="contents'.$boxKey.'"></div>';
						echo '<button id="button'.$boxKey.'" onclick="fillBox(\''.$boxKey.'\')" class="btn btn-outline-success m-2 col-sm-1">Fill Box</button>';
					} else {
						if ($pickupDate != '0000-00-00 00:00:00'){ // This is likely available but we need to check.
							echo '<div class="form-group col-sm-4">User: '.$user->getLastName().", ".$user->getFirstName().'<BR><b>Picked Up: ' .$pickupDate.'</b><BR>Fullfilled By: '.$fillByUser->getLastName().", ".$fillByUser->getFirstName().'</div>';
							echo '<div class="form-group col-sm-4">Contents: '.$contents.'</div>';
							echo '<button onclick="resetBox(\''.$boxKey.'\')" class="btn btn-outline-primary m-2 col-sm-1">Reset Box</button>';
							if ($locked == 1)
								echo '<button onclick="unlockBox(\''.$boxKey.'\')" class="btn btn-outline-danger m-2 col-sm-1">Unlock Box</button>';
							else
								echo '<button onclick="lockBox(\''.$boxKey.'\')" class="btn btn-outline-danger m-2 col-sm-1">Lock Box</button>';
						} else { // Box still has something in it (for sure)
							echo '<div class="form-group col-sm-4">User: '.$user->getLastName().", ".$user->getFirstName().'<BR>Box Filled: '.((time() - strtotime($fillDate)) > (2*24*60*60) ? "<span style='font-weight: bold;color:red !important;'>$fillDate</span>" : $fillDate).'<BR>Fullfilled By: '.$fillByUser->getLastName().", ".$fillByUser->getFirstName().'</div>';
							echo '<div class="form-group col-sm-4">Contents: '.$contents.'</div>';
							echo '<button onclick="emptyBox(\''.$boxKey.'\')" class="btn btn-outline-danger m-2 col-sm-1">Empty Box</button>';
							if ($locked == 1)
								echo '<button onclick="unlockBox(\''.$boxKey.'\')" class="btn btn-outline-danger m-2 col-sm-1">Unlock Box</button>';
							else
								echo '<button onclick="lockBox(\''.$boxKey.'\')" class="btn btn-outline-danger m-2 col-sm-1">Lock Box</button>';
						}
					}
					echo '</div>';
				}
				echo "</div>";
			?>
		</div>
	</div>
</div>

<?php 
include_once PUBLIC_FILES . '/modules/footer.php' ; 
?>
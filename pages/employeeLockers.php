<?php
include_once '../bootstrap.php';

use DataAccess\LockerDao;
use DataAccess\UsersDao;
use Model\EquipmentCheckoutStatus;
use Util\Security;

if (!session_id()) {
    session_start();
}

// Make sure the user is logged in and allowed to be on this page
include_once PUBLIC_FILES . '/lib/shared/authorize.php';

allowIf(verifyPermissions('employee'), 'index.php');


$title = 'Employee Lockers';
$css = array(
	'assets/css/sb-admin.css',
	'assets/css/admin.css',
	'https://cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css'
);
$js = array(
    'https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js'
);

include_once PUBLIC_FILES . '/modules/header.php';
include_once PUBLIC_FILES . '/modules/employee.php';
include_once PUBLIC_FILES . '/modules/renderBrowse.php';

// Handout Modal Functionality
include_once PUBLIC_FILES . '/modules/newHandoutModal.php';

$lockerDao = new LockerDao($dbConn, $logger);
$userDao = new UsersDao($dbConn, $logger);
$lockers = $lockerDao->getLockers();


$options = "<option value=''></option>";
$users = $userDao->getAllUsers();
foreach ($users as $user){
	$options .= "<option value='".$user->getUserID()."'>".$user->getLastName().", ".$user->getFirstName()."</option>";
}


?>
<script type='text/javascript'>
	function renewLocker(id){
		var userid = $('#name'+id).attr("value");
		
		let content = {
			action: 'renewLocker',
			userId: userid,
			lockerId: id
		}
		
		api.post('/lockers.php', content).then(res => {
			snackbar(res.message, 'success');
			$('#row'+id).html('');
		}).catch(err => {
			snackbar(err.message, 'error');
		});
	}

	function remindLocker(id){
		var userid = $('#name'+id).attr("value");
		
		let content = {
			action: 'remindLocker',
			userId: userid,
			messageId: 'dsfipuwitpjnvnz7',
			lockerId: id
		}
		
		api.post('/lockers.php', content).then(res => {
			snackbar(res.message, 'success');
			$('#row'+id).html('');
		}).catch(err => {
			snackbar(err.message, 'error');
		});
	}

	function returnLocker(id){
		var userid = $('#name'+id).attr("value");
		
		let content = {
			action: 'returnLocker',
			userId: userid,
			messageId: 'ffhipohqwirytsre',
			lockerId: id
		}
		
		api.post('/lockers.php', content).then(res => {
			snackbar(res.message, 'success');
			$('#row'+id).html('');
		}).catch(err => {
			snackbar(err.message, 'error');
		});
	}

	function checkoutLocker(id){
		var userid = $('#name'+id).children(":selected").attr("value");
		
		let content = {
			action: 'checkoutLocker',
			userId: userid,
			messageId: 'oigahsgipeqrhglk',
			lockerId: id
		}
		
		api.post('/lockers.php', content).then(res => {
			snackbar(res.message, 'success');
			$('#row'+id).html('');
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

                foreach ($lockers as $l) {
                    $lockerId = $l->getLockerId();
					$lockerNumber = $l->getLockerNumber();
					$lockerRoomId = $l->getLockerRoomId();
					$status = $l->getStatus();
					$location = $l->getLocation();
					$free = $l->getFree();
					$userId = $l->getUserId();
					

                    if($free == 1 && $status == 1 && $userId == ''){
						echo '<div class="form-group row" id="row'.$lockerId.'" style="padding-left:4px;padding-right:4px;margin-top:4px;margin-bottom:4px;">
								<div class="col-lg-1" style="text-align:right;"><h2>'.$lockerNumber.':</h2></div>
								<div class="form-group col-sm-4"><select id="name'.$lockerId.'" class="custom-select" >'.$options.'</select></div>
								<div class="col-sm-4"><button onclick="checkoutLocker(\''.$lockerId.'\')" class="btn btn-outline-success">Check Out</button></div>
								</div>';
					}else{
						$user = $userDao->getUserByID($userId);
						if ($user != false){
							$email = $user->getEmail();
							$dueDate = $l->getDueDate()?->format("M. Y");
							echo '<div class="form-group row" id="row'.$lockerId.'" style="padding-left:4px;padding-right:4px;margin-top:4px;margin-bottom:4px;">
								<div class="col-lg-1" style="text-align:right;"><h2>'.$lockerNumber.':</h2></div>
								<div class="col-sm-2"><h2><a href="mailto:'.$email.'">'. Security::HtmlEntitiesEncode($user->getFirstName()). ' ' . Security::HtmlEntitiesEncode($user->getLastName()) . '</a></h2></div>
								<div class="d-none"><input type="hidden" id="name'.$lockerId.'" value="'.$userId.'"></div>
								<div class="col-sm-2"><h4>Due '.$dueDate.'</h4></div>
								<div class="col-sm">
									<button onclick="returnLocker('.$lockerId.')" class="btn btn-outline-danger">Return Locker</button>
									<button class="btn btn-outline-warning" onclick="remindLocker(\''.$lockerId.'\');">Send Renewal Reminder</button>
									<button class="btn btn-outline-success" onclick="renewLocker(\''.$lockerId.'\');">Renew Locker</button>
								</div>
								</div>';
						} else {
							echo '<div  class="row bg-danger"  id="row'.$lockerId.'" style="padding-left:4px;padding-right:4px;margin-top:4px;margin-bottom:4px;">
								Something went wrong. Locker is marked as checked out but user could not be found!
								</div>';
						}
					}

                }


            
                    echo "</div>";
                echo "</div>";
            ?>

        </div>
    </div>

<?php 
include_once PUBLIC_FILES . '/modules/footer.php' ; 
?>
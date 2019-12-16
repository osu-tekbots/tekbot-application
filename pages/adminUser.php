<?php
include_once '../bootstrap.php';

use DataAccess\EquipmentDao;
use DataAccess\EquipmentCheckoutDao;
use DataAccess\EquipmentReservationDao;
use DataAccess\UsersDao;
use Model\UserAccessLevel;
use Util\Security;

if (!session_id()) {
    session_start();
}

// Make sure the user is logged in and allowed to be on this page
include_once PUBLIC_FILES . '/lib/shared/authorize.php';

$isEmployee = isset($_SESSION['userID']) && !empty($_SESSION['userID']) 
	&& isset($_SESSION['userAccessLevel']) && $_SESSION['userAccessLevel'] == 'Employee';

allowIf($isEmployee, 'index.php');


$title = 'Employee Users View';
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

$equipmentDao = new EquipmentDao($dbConn, $logger);
$userDao = new UsersDao($dbConn, $logger);

$users = $userDao->getAllUsers();
$userHTML = '';
foreach ($users as $user){
	$userID = $user->getUserID();
	$email = Security::HtmlEntitiesEncode($user->getEmail());
	$name = Security::HtmlEntitiesEncode($user->getFirstName()) 
	. ' ' 
	. Security::HtmlEntitiesEncode($user->getLastName());
	$phone = Security::HtmlEntitiesEncode($user->getPhone());
	$onid =  Security::HtmlEntitiesEncode($user->getOnid());
	$accessLevelID = $user->getAccessLevelID()->getId();
	if ($accessLevelID == UserAccessLevel::EMPLOYEE){
		$userTypeBtn = "<button class='btn btn-sm btn-success btn-user-type' data-id='$userID' data-admin='true' 
		data-toggle='tooltip' data-placement='right' title='Demote to Student'>
		Employee
		</button>";
	} else {
		$userTypeBtn = "
		<button class='btn btn-sm btn-light btn-user-type' data-id='$userID' data-admin='false' 
            data-toggle='tooltip' data-placement='right' title='Promote to Employee'>
            Student
        </button>
		";
	}

	$userHTML .= "
	<tr>
		<td>$name</td>
		<td>$email</td>
		<td>$onid</td>
		<td>$phone</td>
		<td>$userTypeBtn</td>
	</tr>
	
	";

}






?>
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
					renderEmployeeBreadcrumb('Users', 'View');
					
					if (empty($users)){
						echo "
						<h4>Unable to find any users</h4>
						";
					} else {
						echo"
						
						<div class='admin-paper'>
						<h3>Current Users</h3>
						<p><strong>IMPORTANT</strong>: Do not give anyone the employee type unless they are currently working at TekBots.
						</p>
						<table class='table' id='currentUsers'>
						<thead>
							<tr>
								<th>Name</th>
								<th>Email</th>
								<th>Onid</th>
								<th>Phone</th>
								<th>Type</th>
							</tr>
						</thead>
						<tbody>
							$userHTML
						</tbody>
						</table>
						<script>
							$('#currentUsers').DataTable();
						</script>
					</div>
						
						
						
						
						
						
						
						
						";
					}

				

	
	
				?>




			</div>
		</div>
	</div>
</div>

<script>
/**
 * Handles a click on the user type button in the admin user table to promote/demote a user to/from admin status.
 */
function onUserTypeClick() {
    $btn = $(this);
    let uid = $btn.data('id');
    let isAdmin = $btn.data('admin');
	let willBeAdmin = !isAdmin;
    let body = {
        uid,
        action: 'updateUserType',
        admin: willBeAdmin
    };
    api.post('/users.php', body).then(res => {
        snackbar(res.message, 'success');
        $btn.data('admin', willBeAdmin);
        if(willBeAdmin) {
            $btn.removeClass('btn-light').addClass('btn-success');
            $btn.text('Employee');
            //$btn.tooltip('hide').attr('data-original-title', 'Demote to Student').tooltip('show');
        } else {
            $btn.removeClass('btn-success').addClass('btn-light');
            $btn.text('Student');
            //$btn.tooltip('hide').attr('data-original-title', 'Promote to Employee').tooltip('show');
        }
    }).catch(err => {
        snackbar(err.message, 'error');
    });
}
$('.btn-user-type').click(onUserTypeClick);

</script>

<?php 
include_once PUBLIC_FILES . '/modules/footer.php' ; 
?>

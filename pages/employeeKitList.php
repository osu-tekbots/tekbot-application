<?php
include_once '../bootstrap.php';

use DataAccess\KitEnrollmentDao;
use Model\KitEnrollmentStatus;
use Util\Security;

if (!session_id()) {
    session_start();
}

// Make sure the user is logged in and allowed to be on this page
include_once PUBLIC_FILES . '/lib/shared/authorize.php';

$isEmployee = isset($_SESSION['userID']) && !empty($_SESSION['userID']) 
	&& isset($_SESSION['userAccessLevel']) && $_SESSION['userAccessLevel'] == 'Employee';

allowIf($isEmployee, 'index.php');


$title = 'Employee Equipment View';
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

$kitEnrollmentDao = new KitEnrollmentDao($dbConn, $logger);
$kits = $kitEnrollmentDao->getKitsForAdmin();

$kitHTML = '';
$listNumber = 0;
foreach ($kits as $k){
		$kitID =$k->getKitEnrollmentID();
		$osuID = $k->getOsuID();
		$onid = $k->getOnid();
		$status = $k->getKitStatusID()->getName();
		$termID = $k->getTermID();
		$courseCode = $k->getCourseCode();
		$dUpdated = $k->getDateUpdated();
		$dCreated = $k->getDateCreated();
		$name = $k->getFirstMiddleLastName();
       
	



	$kitHTML .= "
	<tr id='$kitID'>
		<td>$osuID</td>
		<td>$onid</td>
		<td>$name</td>
		<td>$courseCode</td>
		<td>$termID</td>
		<td>$status</td>
	</tr>
	";
	$listNumber++;
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
                    renderEmployeeBreadcrumb('Equipment', 'Checkout');

		
				echo "
	
				<div class='admin-paper'>
				<h3>Kit Enrollments</h3>
					<table class='table' id='kitEnrollmentList'>
					<caption>Kit Enrollments</caption>
						<thead>
							<tr>
								<th>Student ID</th>
								<th>Onid</th>
								<th>Last, First Middle Name</th>
								<th>Course Code</th>
								<th>Term</th>
								<th>Status</th>
							</tr>
						</thead>
						<tbody>
							$kitHTML
						</tbody>
					</table>
					<script>
					$('#kitEnrollmentList').DataTable(
						{
							lengthMenu: [[20, 50, 100, -1], [20, 50, 100, 'All']],
						
						}
					);

					</script>
				</div>
					
					";
					
				?>




			</div>
		</div>
	</div>
</div>

<script>


</script>

<?php 
include_once PUBLIC_FILES . '/modules/footer.php' ; 
?>

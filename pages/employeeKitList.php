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
include_once PUBLIC_FILES . '/modules/renderTermData.php';

allowIf(verifyPermissions('employee', $logger), 'index.php');


$title = 'Employee Kit Listing';
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

if (isset($_REQUEST['term'])){
	$termfilter = $_REQUEST['term'];
} else {
	$termfilter = getCurrentTermId();
}
if (isset($_REQUEST['status'])){
	$statusfilter = $_REQUEST['status'];
} else {
	$statusfilter = 1;
}

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
	$term = term2string($termID);

	$statuses = $kitEnrollmentDao->getKitEnrollmentTypes();
	$selectHTML = '';
	foreach ($statuses as $s){
		$selectHTML .= "<option value='".$s->getId()."' ".($s->getName() == $status ?'selected':'').">".$s->getName()."</option>";
	}

	if ($statusfilter == 1) {
		$statusfilter = 'Ready';
	} else if ($statusfilter == 2) {
		$statusfilter = 'Handed Out';
	} else if ($statusfilter == 3) {
		$statusfilter = 'Refunded';
	} 

	if (($termfilter == $termID || $termfilter == 'All') && ($statusfilter == $status || $statusfilter == 'All')){
		$kitHTML .= "
		<tr id='row$kitID'>
			<td><button id='deleteKitEnrollmentButton$kitID' class='btn btn-outline-danger btn-sm' onclick='deleteSelectedKitEnrollment(\"$kitID\");'>Delete</button></td>
			<td>$osuID</td>
			<td>$onid</td>
			<td>$name</td>
			<td>$courseCode</td>
			<td>$term</td>
			<td>$status</td>
			<td><select id='status$kitID' onchange='updateStatus(\"$kitID\");'>$selectHTML</select></td>
		</tr>
		";
		$listNumber++; 
	}
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
		
				echo "
	
				<div class='admin-paper'>
				<h3>Kit Enrollments</h3>
				<div display: inline-block>
				<table>
					<tr>
					<td style='width:15%'>
					Select Status Filter: 
					</td>
					<td>
						<select id='statusFilterSelect' class='w-25 form-control input-sm'>
							<option selected disabled hidden>".$statusfilter."</option>
							<option value='All'>Show All</option>
							<option value='1'>Ready</option>
							<option value='2'>Handed Out</option>
							<option value='3'>Refunded</option>
						</select>
					</td>
					<tr>
					<td>
					Select Term Filter:
					</td>
					<td>
					<select id='termFilterSelect' class='w-25 form-control input-sm'>
						<option value='0'>".term2string($termfilter)."</option>
						<option value='All'>Show All</option>
					";

					$kitEnrollmentTerms = $kitEnrollmentDao->getKitEnrollmentTerms();
					foreach($kitEnrollmentTerms as $kitEnrollmentTerm){
						echo "<option value =".$kitEnrollmentTerm.">".term2string($kitEnrollmentTerm)."</option>";
					};

					echo "
					</td>
					</select>
				</table>
					<table class='table' id='kitEnrollmentList'>
					<caption>Kit Enrollments</caption>
						<thead>
							<tr>
								<th></th>
								<th>Student ID</th>
								<th>Onid</th>
								<th>Last, First Middle Name</th>
								<th>Course Code</th>
								<th>Term</th>
								<th>Status</th>
								<th></th>
							</tr>
						</thead>
						<tbody>
							$kitHTML
						</tbody>
					</table>
					
				</div>
					
					";
					
				?>




			</div>
		</div>
	</div>
</div>

<script type='text/javascript'>
function updateStatus(id){
	var status = $('#status'+id).val();
	
	let content = {
		action: 'updateHandoutKitEnrollments',
		kid: id,
		status: status
	}

	api.post('/kitenrollment.php', content).then(res => {
		snackbar(res.message, 'success');
//		$('#row'+id).html('');
	}).catch(err => {
		snackbar(err.message, 'error');
	});
}

function deleteSelectedKitEnrollment(id){
	let content = {
		action: 'deleteHandoutKitEnrollment',
		kid: id
	}

	api.post('/kitenrollment.php', content).then(res => {
		snackbar(res.message, 'success');
//		$('#row'+id).html('');
	}).catch(err => {
		snackbar(err.message, 'error');
	});
}

$('#statusFilterSelect').on('input', function() {
	let getStatusFilter = document.getElementById("statusFilterSelect").value;
    
    const params = new URLSearchParams(window.location.search);
    params.set('status', getStatusFilter);
    window.location.search = params;
})

$('#termFilterSelect').on('input', function() {
	let getTermFilter = document.getElementById("termFilterSelect").value;
    
    const params = new URLSearchParams(window.location.search);
    params.set('term', getTermFilter);
    window.location.search = params;
})

$('#kitEnrollmentList').DataTable(
	{
		"autoWidth": true,
		'scrollX':false, 
		'paging':false, 
		'order':[[5, 'desc'], [4, 'asc'], [3, 'asc']],
		"columns": [
			{ "orderable": false },
			{ "orderable": false },
			null,
			null,
			null,
			null,
			null,
			{ "orderable": false }
		  ]
						
	}
);
</script>

<?php 
include_once PUBLIC_FILES . '/modules/footer.php' ; 
?>

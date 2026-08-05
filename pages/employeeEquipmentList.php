<?php
include_once '../bootstrap.php';
use DataAccess\EquipmentDaoOld;
use DataAccess\EquipmentTypeDao;

if (PHP_SESSION_ACTIVE != session_status())
    session_start();

// Make sure the user is logged in and allowed to be on this page
include_once PUBLIC_FILES . '/lib/shared/authorize.php';

allowIf(verifyPermissions('employee', $logger), 'pages/index.php');


$title = 'Employee Equipment View';
$css = array(
	'assets/css/sb-admin.css',
	'assets/css/admin.css',
	'https://cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css'
);
$js = array(
    array(
        'defer' => 'true',
        'src' => 'assets/js/edit-equipment.js'
    ),
    array(
        'defer' => 'true',
        'src' => 'assets/js/admin-review.js'
    ),
    array(
        'defer' => 'true',
        'src' => 'assets/js/upload-image.js'
	),
	'https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js'
);
include_once PUBLIC_FILES . '/modules/header.php';
include_once PUBLIC_FILES . '/modules/employee.php';
include_once PUBLIC_FILES . '/modules/newEquipmentModal.php';

$showDeleted = isset($_GET['show_deleted']) && strtolower($_GET['show_deleted']) != 'false';


/* 
* Gets the various pieces of information for each item from the Equipment DAO
*/
$equipmentTypeDao = new EquipmentTypeDao($dbConn, $logger);

$equipment = $equipmentTypeDao->getEmployeeEquipment($showDeleted);

$equipmentHTML = "";
foreach ($equipment as $e){
	$equipmentID = $e->getEquipmentID();

	if (!empty($e->getImages())){
        $imagePath = "images/equipment/{$e->getImages()[0]->getImageID()}";
    } else {
        $imagePath = "assets/img/no-image.png";
    }
	
	$background = $e->getIsDeleted() ? 'background: rgb(255, 230, 230)' : '';

	$name = $e->getName();
	$replacementCost = $e->getReplacementCost();
	$notes = $e->getNotes();
	$parts = $e->getParts();
	

	// Allow editing deleted equipment to hit "restore" button, but not viewing
	$viewButton = $e->getIsDeleted() ? '' : createLinkButton("pages/publicEquipmentDetail.php?id=$equipmentID", 'View');
	$editButton = createLinkButton("pages/employeeEquipmentDetail.php?id=$equipmentID", 'Edit');

	$units = "<ul>";
	foreach ($e->getUnits() as $unit) {
		$status = $unit->getCheckoutStatus();
		if (! $unit->getIsPublic()) $status = "Hidden";

		if ($status == 'Available')        $statusColor = 'text-success';
		else if ($status == 'Reserved')    $statusColor = 'text-warning';
		else if ($status == 'Checked out') $statusColor = 'text-danger';
		else if ($status == 'Hidden')     $statusColor = 'text-primary';

		$location = $unit->getLocation();
		$health = $unit->getHealthStatus();

		$units .= "<li><b><span class='$statusColor'>$status</span> ($health):</b> $location</li>";
	}
	$units .= "</ul>";
	
	/**
	 * Creates a data table with the information populated from above. 
	 */
	$equipmentHTML .= "
	<tr style='$background'>
		<td>
			$name
			<br>
			<img height='200px;' src='$imagePath'>
		</td>
		<td>$units</td>
		<td>$notes</td>
		<td>$viewButton $editButton</td>
	</tr>
	";

}

?>
<br/>
<div id="page-top">
	<div id="wrapper">
		<?php renderEmployeeSidebar(); ?>

		<div id="content-wrapper">
			<div class="container-fluid">
				<button
					class="btn btn-lg btn-outline-primary capstone-nav-btn" type="button"
					data-toggle="modal" data-target="#newEquipmentModal" id="openNewEquipmentModalBtn"
				>
					Create New Equipment
				</button>
				
				<div class="admin-paper">
					<div class="d-flex justify-content-between align-items-end">
						<h1>Equipment for Rent</h1>
						<form class="form-group form-inline">
							<input
								name="show_deleted" id="deletedCheckbox" class='form-control mr-1' type="checkbox"
								onchange='this.form.submit()' <?php echo $showDeleted ? 'checked' : '' ?>
							>
							<label for="deletedCheckbox">Show deleted equipment</label>
						</form>
					</div>
					<table class="table" id="equipmentList">
						<caption>Employee Equipment List</caption>
						<thead>
							<tr>
								<th>Equipment</th>
								<th>Units</th>
								<th>Notes</th>
								<th></th>
							</tr>
						</thead>
						<tbody>
							<?= $equipmentHTML ?>
						</tbody>
					</table>
					<script>
					$('#equipmentList').DataTable(
						{
							lengthMenu: [[-1, 25, 50], ['All', 25, 50]]
						}
					);
					</script>
				</div>
			</div>
		</div>
	</div>
</div>

<script>
	$('#createEquipmentBtn').on('click', function () {
		// Capture the data we need
		let data = {
			action: 'createEquipment',
			title: $('#equipmentNameInput').val(),
		};

		// Send our request to the API endpoint
		api.post('/equipment.php', data).then(res => {
			window.location.replace('pages/employeeEquipmentDetail.php?id=' + res.content.id);
		}).catch(err => {
			snackbar(err.message, 'error');
		});
	});
</script>

<?php 
include_once PUBLIC_FILES . '/modules/footer.php' ; 
?>

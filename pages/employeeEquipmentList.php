<?php
include_once '../bootstrap.php';

use DataAccess\EquipmentDao;

if (!session_id()) {
    session_start();
}

// Make sure the user is logged in and allowed to be on this page
include_once PUBLIC_FILES . '/lib/shared/authorize.php';

$isEmployee = isset($_SESSION['userID']) && !empty($_SESSION['userID']) 
	&& isset($_SESSION['userAccessLevel']) && $_SESSION['userAccessLevel'] == 'Admin'|| $_SESSION['userAccessLevel'] == 'Employee';

allowIf($isEmployee, $configManager->getBaseUrl() . 'pages/index.php');


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
include_once PUBLIC_FILES . '/modules/renderBrowse.php';
include_once PUBLIC_FILES . '/modules/newEquipmentModal.php';


/* 
* Gets the various pieces of information for each item from the Equipment DAO,
* hides content if it is not public. 
*/
$dao = new EquipmentDao($dbConn, $logger);
$equipments = $dao->getAdminEquipment();
$equipmentItemHTML = "";
foreach ($equipments as $e){
	$equipmentID = $e->getEquipmentID();
	$defaultImage = $dao->getDefaultEquipmentImage($equipmentID);
	if (!empty($defaultImage)){
        $imageName = $defaultImage->getImageID();
        $imagePath = "images/equipment/$imageName";
    } else {
        $imageName = "no-image.png";
        $imagePath = "assets/img/$imageName";
    }
	$name = $e->getEquipmentName();
	$location = $e->getLocation();
	$replacementCost = $e->getReplacementCost();
	$notes = $e->getNotes();
	$parts = $e->getPartList();
	$units = $e->getInstances();
	$isPublic = $e->getIsPublic();
	if ($isPublic){
		$publicStatus = "Public";
	} else {
		$publicStatus = "Hidden";
	}
	$viewButton = createLinkButton("pages/publicEquipmentDetail.php?id=$equipmentID", 'View');
	$editButton = createLinkButton("pages/employeeEquipmentDetail.php?id=$equipmentID", 'Edit');
	
/* 
* Creates a data table with the information populated from above. 
*/
	$equipmentItemHTML .= "
	<tr>
		<td><img height='200px;' src='$imagePath'></td>
		<td>$name</td>
		<td>$publicStatus</td>
		<td>$location</td>
		<td>$units</td>
		<td>$replacementCost</td>
		<td>$notes</td>
		<td>$viewButton $editButton</td>
	</tr>
	";

}

?>
<br/>
<div id="page-top">

	<div id="wrapper">

	<?php 
		renderEmployeeSidebar();
	?>

		<div id="content-wrapper">

			<div class="container-fluid">

					<button class="btn btn-lg btn-outline-primary capstone-nav-btn" type="button" data-toggle="modal"
					data-target="#newEquipmentModal" id="openNewEquipmentModalBtn">Create New Equipment</button>
				<?php
				echo"
					<div class='admin-paper'>
					<h1>Equipment Rentals</h1>
						<table class='table' id='equipmentList'>
						<caption>Employee Equipment List</caption>
							<thead>
								<tr>
									<th>Image</th>
									<th>Name</th>
									<th>Visibility</th>
									<th>Location</th>
									<th>Units</th>
									<th>Cost</th>
									<th>Notes</th>
									<th></th>
								</tr>
							</thead>
							<tbody>
								$equipmentItemHTML
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
					";
				?>



          



				


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
				api.post('/equipments.php', data).then(res => {
					window.location.replace('pages/employeeEquipmentDetail.php?id=' + res.content.id);
				}).catch(err => {
					snackbar(err.message, 'error');
				});
		});



</script>

<?php 
include_once PUBLIC_FILES . '/modules/footer.php' ; 
?>

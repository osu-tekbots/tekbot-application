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
    'assets/css/sb-admin.css'
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
    )
);
include_once PUBLIC_FILES . '/modules/header.php';
include_once PUBLIC_FILES . '/modules/employee.php';
include_once PUBLIC_FILES . '/modules/renderBrowse.php';
include_once PUBLIC_FILES . '/modules/newEquipmentModal.php';

$dao = new EquipmentDao($dbConn, $logger);
$equipments = $dao->getAdminEquipment();

?>
<br/>
<div id="page-top">

	<div id="wrapper">

	<?php 
		renderEmployeeSidebar();
	?>

		<div id="content-wrapper">

			<div class="container-fluid">

				<!-- Breadcrumbs-->
				<?php
					renderEmployeeBreadcrumb("Employee", "Browse");
				?>
					<button class="btn btn-lg btn-outline-primary capstone-nav-btn" type="button" data-toggle="modal"
					data-target="#newEquipmentModal" id="openNewEquipmentModalBtn">Create New Equipment</button>
				<?php
					renderEmployeeEquipmentList($equipments);
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
					window.location.replace('pages/editEquipment.php?id=' + res.content.id);
				}).catch(err => {
					snackbar(err.message, 'error');
				});
		});



</script>

<?php 
include_once PUBLIC_FILES . '/modules/footer.php' ; 
?>

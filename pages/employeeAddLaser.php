<?php
include_once '../bootstrap.php';

use DataAccess\EquipmentDao;
use DataAccess\EquipmentCheckoutDao;
use DataAccess\EquipmentReservationDao;
use DataAccess\LaserDao;
use DataAccess\UsersDao;

if (!session_id()) {
    session_start();
}

// Make sure the user is logged in and allowed to be on this page
include_once PUBLIC_FILES . '/lib/shared/authorize.php';

$isEmployee = isset($_SESSION['userID']) && !empty($_SESSION['userID']) 
	&& isset($_SESSION['userAccessLevel']) && $_SESSION['userAccessLevel'] == 'Employee';

allowIf($isEmployee, 'index.php');


$title = 'Employee Add Laser Cutter';
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
$checkoutDao = new EquipmentCheckoutDao($dbConn, $logger);
$reservationDao = new EquipmentReservationDao($dbConn, $logger);
$reservedEquipment = $reservationDao->getReservationsForAdmin();
$checkedoutEquipment = $checkoutDao->getCheckoutsForAdmin();


$printerDao = new LaserDao($dbConn, $logger);
$printers = $printerDao->getLaserCutters();
$printTypes = $printerDao->getLaserCutMaterials();



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
				<div class='admin-paper' style='overflow-x:scroll'>
				<h3>
				This page allows Tekbot employees to update, remove, and add printers and print types.
				</h1>
				<br/>
				";
				


				$printerHTML = "";

				foreach ($printers as $p) {
					$printerID = $p->getLaserId();
					$printerName = $p->getLaserName();
					$printerDesc = $p->getDescription();
					$printerLoc = $p->getLocation();
					$editPrinterBtn = "<button id='editButton$printerID' onClick='editPrinter($printerID)'>Save</button>";
					$rmvPrinterBtn = "<button id='removeButton$printerID' onClick='removePrinter($printerID)'>Remove</button>"; 
					$printerHTML .= "
					<tr>
					
						<td>$editPrinterBtn $rmvPrinterBtn</td>
						<td><input id='printerName$printerID' value='$printerName'></td>
						<td><input id='printerDescription$printerID' value='$printerDesc'></td>
						<td><input id='printerLocation$printerID' value='$printerLoc'></td>
					</tr>
					
					";
				}

				$addPrinterRow= "
				<tr>
				<td><button id='addPrinterButt'>Add</button></td>
				<td><input id='addPrinterName'></td>
				<td><input id='addPrinterDescription'></td>
				<td><input id='addPrinterLocation'></td>
				</tr>
				";


				echo"
						
						<div class='admin-paper'>
						<h3>Laser Cutters</h3>
						<p>These are the available Tekbot Laser Cutters.</p>
						<table class='table' id='checkoutFees'>
						<caption>Information regarding Tekbot Laser Cutters</caption>
						<thead>
							<tr>
								<th></th>
								<th>Name</th>
								<th>Description</th>
								<th>Location</th>
							</tr>
						</thead>
						<tbody>
							$printerHTML
							$addPrinterRow
						</tbody>
						</table>
						<script>
							$('#checkoutFees').DataTable();
						</script>
					</div>";
				?>

		<!-- Printer table handler -->
<script>

function removePrinter(printerID) {
	let printerName = $("#printerName" + printerID).val();
	if(window.confirm("Are you sure you want to delete laser cutter: " + printerName + "?"))
	{
		let data = {
			action: 'removeLaserCutter',
			laserID: printerID
		};
		api.post('/lasers.php', data).then(res => {
		 snackbar(res.message, 'success');
		//  TODO Add timeout
		//  setTimeout(location.reload(), 3000);
		 location.reload();
	 }).catch(err => {
		 snackbar(err.message, 'error');
	 });
	}
}

function editPrinter(printerID) {
	let printName = $("#printerName" + printerID).val();
	let printDesc = $("#printerDescription" + printerID).val();
	let printLoc = $("#printerLocation" + printerID).val();
	// alert(printName + printDesc + printLoc).val();
	let data = {
		action: 'saveLaserCutter',
		laserId: printerID,
		laserName: printName,
		description: printDesc,
		location: printLoc
	};
	api.post('/lasers.php', data).then(res => {
		 snackbar(res.message, 'success');
		//  TODO Add timeout
		//  setTimeout(location.reload(), 3000);
		 location.reload();
	 }).catch(err => {
		 snackbar(err.message, 'error');
	 });
}

$("#addPrinterButt").click(function(){
	if($("#addPrinterName").val() == "")
	{
		alert("Laser Cutter must have a name!");
	}
	else
	{
		let printName = $("#addPrinterName").val();
		let printDescription = $("#addPrinterDescription").val();
		let printLocation = $("#addPrinterLocation").val();
		let data = {
			action: 'createLaserCutter',
			title: printName,
			description: printDescription,
			location: printLocation
		}
		api.post('/lasers.php', data).then(res => {
		 snackbar(res.message, 'success');
		//  TODO Add timeout
		//  setTimeout(location.reload(), 3000);
		 location.reload();
	 }).catch(err => {
		 snackbar(err.message, 'error');
	 });
	}
});
</script>


<?php

	$printTypeHTML = "
	<tr>
	<td><button id='addPrintTypeButt' onClick='addPrintType()'>Add</button></td>
	<td><input id='addPrintTypeName'></input></td>
	<td><input id='addPrintTypeDescription'></input></td>
	<td><input id='addPrintTypeCost'></input></td>
	</tr>
	";

	foreach ($printTypes as $p) {
		$printTypeID = $p->getLaserMaterialId();
		$printTypeName = $p->getLaserMaterialName();
		$description = $p->getDescription();
		$cost = $p->getCostPerSheet();

		$printTypeHTML .= "
		<tr>
		<td><button id='editButton$printTypeID' onClick='editPrintType($printTypeID)'>Save</button> <button id='removeButton$printTypeID' onClick='removePrintType($printTypeID)'>Remove</button>
		<td><input id='printTypeName$printTypeID' value='$printTypeName'></td>
		<td><input id='printTypeDescription$printTypeID' value='$description'></td>
		<td><input id='costPerGram$printTypeID' value='$cost'></td>
		</tr>
		";
	}



	echo"
						
	<div class='admin-paper' style='overflow-x:scroll'>
	<h3>Laser Cutting Materials</h3>
	<p>These are the available laser cutting materials</p>
	<table class='table' id='checkoutFees'>
	<caption>Information regarding Tekbot Laser Cutter Materials</caption>
	<thead>
		<tr>
			<th></th>
			<th>Cut Material Name</th>
			<th>Description</th>
			<th>Cost Per Sheet</th>
		</tr>
	</thead>
	<tbody>
		$printTypeHTML
	</tbody>
	</table>
	<script>
		$('#checkoutFees').DataTable();
	</script>
	</div>";
?>

			</div>
		</div>
	</div>
</div>


<!-- Print Types Table Handler -->
<script>

function removePrintType(printTypeID) {
	let printTypeName = $("#printTypeName" + printTypeID).val();
	if(window.confirm("Are you sure you want to delete Laser Material: " + printTypeName + "?"))
	{
		let data = {
			action: 'removeLaserMaterial',
			materialID: printTypeID
		};
		api.post('/lasers.php', data).then(res => {
		 snackbar(res.message, 'success');
		//  TODO Add timeout
		//  setTimeout(location.reload(), 3000);
		 location.reload();
	 }).catch(err => {
		 snackbar(err.message, 'error');
	 });
	}
}

function editPrintType(printTypeID) {
	let printerId = $("#printerSelect" + printTypeID).val();
	let printName = $("#printTypeName" + printTypeID).val();
	let printDesc = $("#printTypeDescription" + printTypeID).val();
	let cost = $("#costPerGram" + printTypeID).val();
	let data = {
		action: 'saveLaserMaterial',
		id: printTypeID,
		name: printName,
		description: printDesc,
		cost: cost
	};
	api.post('/lasers.php', data).then(res => {
		 snackbar(res.message, 'success');
		//  TODO Add timeout
		//  setTimeout(location.reload(), 3000);
		 location.reload();
	 }).catch(err => {
		 snackbar(err.message, 'error');
	 });
}

function addPrintType() {
	if($("#addPrintTypeName").val() == "") {
		alert("Laser Material must have a name!");
	}
	else {
		let printTypeName = $("#addPrintTypeName").val();
		let description = $("#addPrintTypeDescription").val();
		let cost = $("#addPrintTypeCost").val();

		let data = {
			action: 'createLaserMaterial',
			name: printTypeName,
			cost: cost,
			description: description
		}
		api.post('/lasers.php', data).then(res => {
		 snackbar(res.message, 'success');
		//  TODO Add timeout
		//  setTimeout(location.reload(), 3000);
		 location.reload();
	 }).catch(err => {
		 snackbar(err.message, 'error');
	 });
	}	
}
</script>

<?php 
include_once PUBLIC_FILES . '/modules/footer.php' ; 
?>

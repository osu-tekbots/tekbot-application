<?php
include_once '../bootstrap.php';

use DataAccess\EquipmentDao;
use DataAccess\EquipmentCheckoutDao;
use DataAccess\EquipmentReservationDao;
use DataAccess\PrinterDao;
use DataAccess\UsersDao;
use Model\EquipmentCheckoutStatus;
use Util\Security;

if (!session_id()) {
    session_start();
}

// Make sure the user is logged in and allowed to be on this page
include_once PUBLIC_FILES . '/lib/shared/authorize.php';

$isEmployee = isset($_SESSION['userID']) && !empty($_SESSION['userID']) 
	&& isset($_SESSION['userAccessLevel']) && $_SESSION['userAccessLevel'] == 'Employee';

allowIf($isEmployee, 'index.php');


$title = 'Employee Add Printer';
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


$printerDao = new PrinterDao($dbConn, $logger);
$printers = $printerDao->getPrinters();
$printTypes = $printerDao->getPrintTypes();



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
                    renderEmployeeBreadcrumb('Employee', 'Edit Printers');

		
				echo "
				<div class='admin-paper' style='overflow-x:scroll'>
				";


				$printerHTML = "";

				foreach ($printers as $p) {
					$printerID = $p->getPrinterId();
					$printerName = $p->getPrinterName();
					$printerDesc = $p->getDescription();
					$printerLoc = $p->getLocation();
					$editPrinterBtn = "<button id='editButton$printerID' onClick='editPrinter($printerID)'>Edit</button>";
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
						<h3>Fees</h3>
						<p><strong>IMPORTANT</strong>: You must process the order in touchnet before approving fees!</p>
						<p>Make sure to process any fees that are awaiting approval.  Some of them are tied to prints or cuts and need to be processed before you are able to cut/print.</p>
						<table class='table' id='checkoutFees'>
						<caption>Fees Relating to Equipment Checkout</caption>
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
	if(window.confirm("Are you sure you want to delete printer: " + printerName + "?"))
	{
		let data = {
			action: 'removeprinter',
			printerID: printerID
		};
		api.post('/printers.php', data).then(res => {
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
		action: 'saveprinter',
		printerId: printerID,
		printerName: printName,
		description: printDesc,
		location: printLoc
	};
	api.post('/printers.php', data).then(res => {
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
		alert("Printer must have a name!");
	}
	else
	{
		let printName = $("#addPrinterName").val();
		let printDescription = $("#addPrinterDescription").val();
		let printLocation = $("#addPrinterLocation").val();
		let data = {
			action: 'createprinter',
			title: printName,
			description: printDescription,
			location: printLocation
		}
		api.post('/printers.php', data).then(res => {
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

	$printTypeHTML = "";

	foreach ($printTypes as $p) {
		$printTypeID = $p->getPrintTypeId();
		$printerID = $p->getPrinterId();
		$selectPrinter = $printerDao->getPrinterByID($printerID);
		$selectedPrinterID = $selectPrinter->getPrinterId();
		$selectedPrinterName = $selectPrinter->getPrinterName();
		$printTypeName = $p->getPrintTypeName();
		$description = $p->getDescription();
		$headSize = $p->getHeadSize();
		$precision = $p->getPrecision();
		$plateSize = $p->getBuildPlateSize();
		$cost = $p->getCostPerGram();

		$printTypeHTML .= "
		<tr>
		<td><button id='editButton$printTypeID' onClick='editPrintType($printTypeID)'>Edit</button> <button id='removeButton$printTypeID' onClick='removePrintType($printTypeID)'>Remove</button>
		<td><input id='printTypeName$printTypeID' value='$printTypeName'></td>
		<td><select id='printerSelect$printTypeID'>
		<option value='$selectedPrinterID'>$selectedPrinterName</option>
		";

		foreach ($printers as $printer) {
			if($printer->getPrinterId() != $selectedPrinterID) {
				$printTypeHTML .= '<option value="' . $printer->getPrinterId() . '">' . $printer->getPrinterName() . '</option>';				
			}			
		}
		
		$printTypeHTML .= "
		</select></td>
		<td><input id='printTypeDescription$printTypeID' value='$description'></td>
		<td><input id='headSize$printTypeID' value='$headSize'></td>
		<td><input id='precision$printTypeID' value='$precision'></td>
		<td><input id='buildSize$printTypeID' value='$plateSize'></td>
		<td><input id='costPerGram$printTypeID' value='$cost'></td>
		</tr>
		";
	}

	$printTypeHTML .= "
	<tr>
	<td><button id='addPrintTypeButt' onClick='addPrintType()'>Add</button></td>
	<td><input id='addPrintTypeName'></input></td>
	<td><select id='addPrintTypePrinterSelect'>";

	foreach ($printers as $printer) {
		$printTypeHTML .= '<option value="' . $printer->getPrinterId() . '">' . $printer->getPrinterName() . '</option>';
	}

	$printTypeHTML .= "
	<td><input id='addPrintTypeDescription'></input></td>
	<td><input id='addPrintTypeHeadSize'></input></td>
	<td><input id='addPrintTypePrecision'></input></td>
	<td><input id='addPrintTypePlateSize'></input></td>
	<td><input id='addPrintTypeCost'></input></td>
	</tr>
	";

	echo"
						
	<div class='admin-paper'>
	<h3>Fees</h3>
	<p><strong>IMPORTANT</strong>: You must process the order in touchnet before approving fees!</p>
	<p>Make sure to process any fees that are awaiting approval.  Some of them are tied to prints or cuts and need to be processed before you are able to cut/print.</p>
	<table class='table' id='checkoutFees'>
	<caption>Fees Relating to Equipment Checkout</caption>
	<thead>
		<tr>
			<th></th>
			<th>Print Type Name</th>
			<th>Printer</th>
			<th>Description</th>
			<th>Head Size</th>
			<th>Precision</th>
			<th>Build Plate Size</th>
			<th>Cost Per Gram</th>
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
	if(window.confirm("Are you sure you want to delete printer: " + printTypeName + "?"))
	{
		let data = {
			action: 'removeprinttype',
			printTypeID: printTypeID
		};
		api.post('/printers.php', data).then(res => {
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
	let printHead = $("#headSize" + printTypeID).val();
	let precision = $("#precision" + printTypeID).val();
	let buildSize = $("#buildSize" + printTypeID).val();
	let cost = $("#costPerGram" + printTypeID).val();
	let data = {
		action: 'saveprinttype',
		id: printTypeID,
		printerId: printerId,
		name: printName,
		description: printDesc,
		head: printHead,
		precision: precision,
		build: buildSize,
		cost: cost
	};
	api.post('/printers.php', data).then(res => {
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
		alert("Printer Type must have a name!");
	}
	else {
		let printTypeName = $("#addPrintTypeName").val();
		let printerID = $("#addPrintTypePrinterSelect").val();
		let description = $("#addPrintTypeDescription").val();
		let headSize = $("#addPrintTypeHeadSize").val();
		let precision = $("#addPrintTypePrecision").val();
		let plateSize = $("#addPrintTypePlateSize").val();
		let cost = $("#addPrintTypeCost").val();

		let data = {
			action: 'createprinttype',
			name: printTypeName,
			printerID: printerID,
			headSize: headSize,
			precision: precision,
			plateSize: plateSize,
			cost: cost,
			description: description
		}
		api.post('/printers.php', data).then(res => {
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

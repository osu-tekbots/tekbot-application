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
				<div class='admin-paper'>
				";
				echo '<table id="printerTable">'; 
				echo '<tr>';
				echo '<td></td>';
				echo '<td>' . "<b> Printer Name </b>" . '</td>';
				echo '<td>' . "<b> Description </b>" . '</td>';
				echo '<td>' . "<b> Location </b>" . '</td>';
				echo '</tr>';
				foreach ($printers as $p) {
					$printerID = $p->getPrinterId();
					echo '<tr>';
					echo '<td>' . '<button id="editButton' . $printerID . '" onClick="editPrinter(' . $printerID . ')">Edit</button> <button id="removeButton' . $printerID . '" onClick="removePrinter(' . $printerID . ')">Remove</button>'.'</td>'; 
					echo '<td>' . '<input id="printerName' . $printerID . '" value="' . $p->getPrinterName() . '"></input>'. '</td>';
					echo '<td>' . '<input id="printerDescription' . $printerID . '" value="' . $p->getDescription() . '"></input>'. '</td>';
					echo '<td>' . '<input id="printerLocation' . $printerID . '" value="' . $p->getLocation() . '"></input>'. '</td>';
					echo '</tr>';
				
			}
			echo '<tr>';
			echo '<td>' . '<button id="addPrinterButt">Add</button>'. '</td>';
			echo '<td>' . '<input id="addPrinterName"></input>'. '</td>';
			echo '<td>' . '<input id="addPrinterDescription'. '"></input>'. '</td>'; //refactor
			echo '<td>' . '<input id="addPrinterLocation"></input>'. '</td>';
			echo '</tr>';
		
				echo '</table>';
				echo"</div>
				
				";
				?>

		<!-- Printer table handler -->
<script>

function removePrinter(printerID) {
	alert(printerID);
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
	echo "
	<div class='admin-paper'>
	";

	echo '<table id="printTypesTable">'; 
	echo '<tr>';
	echo '<td></td>';
	echo '<td>' . "<b> Print Type Name </b>" . '</td>';
	echo '<td>' . "<b> Printer </b>" . '</td>';
	echo '<td>' . "<b> Description </b>" . '</td>';
	echo '<td>' . "<b> Head Size </b>" . '</td>';
	echo '<td>' . "<b> Precision </b>" . '</td>';
	echo '<td>' . "<b> Build Plate Size </b>" . '</td>';
	echo '<td>' . "<b> Cost Per Gram </b>" . '</td>';
	echo '</tr>';


	// echo $printers[0]->getPrinterId();


	foreach ($printTypes as $p) {

		$printTypeID = $p->getPrintTypeId();
		$printerID = $p->getPrinterId();
		$selectPrinter = $printerDao->getPrinterByID($printerID);




		echo '<tr>';
		echo '<td>' . '<button id="editButton' . $printTypeID . '" onClick="editPrintType(' . $printTypeID . ')">Edit</button> <button id="removeButton' . $printTypeID . '" onClick="removePrintType(' . $printTypeID . ')">Remove</button>'.'</td>'; 
		echo '<td>' . '<input id="printTypeName' . $printTypeID . '" value="' . $p->getPrintTypeName() . '"></input>'. '</td>';

		echo '<td><select id="printerSelect' . $printTypeID . '">';
		echo '<option value="' . $selectPrinter->getPrinterId() . '">' . $selectPrinter->getPrinterName() . '</option>';
		foreach ($printers as $printer) {
			if($printer->getPrinterId() != $selectPrinter->getPrinterId())
			{
				echo '<option value="' . $printer->getPrinterId() . '">' . $printer->getPrinterName() . '</option>';
			}
		}
	
		echo "</select></td>";

		echo '<td>' . '<input id="printTypeDescription' . $printTypeID . '" value="' . $p->getDescription() . '"></input>'. '</td>';
		echo '<td>' . '<input id="headSize' . $printTypeID . '" value="' . $p->getHeadSize() . '"></input>'. '</td>';
		echo '<td>' . '<input id="precision' . $printTypeID . '" value="' . $p->getPrecision() . '"></input>'. '</td>';
		echo '<td>' . '<input id="buildSize' . $printTypeID . '" value="' . $p->getBuildPlateSize() . '"></input>'. '</td>';
		echo '<td>' . '<input id="costPerGram' . $printTypeID . '" value="' . $p->getCostPerGram() . '"></input>'. '</td>';
		echo '</tr>';


		// Note: Printer ID is not working in the ExtractFromRow for PrintTypes in DAO

	}
	echo '<tr>';
	echo '<td>' . '<button id="addPrintTypeButt">Add</button>'. '</td>';
	echo '<td>' . '<input id="addPrintTypeName"></input>'. '</td>';
	echo '<td><select id="addPrintTypePrinterSelect">';
	foreach ($printers as $printer) {
		echo '<option value="' . $printer->getPrinterId() . '">' . $printer->getPrinterName() . '</option>';
	}

	echo "</select></td>";
	echo '<td>' . '<input id="addPrintTypeDescription'. '"></input>'. '</td>'; //refactor
	echo '<td>' . '<input id="addPrintTypeHeadSize"></input>'. '</td>';
	echo '<td>' . '<input id="addPrintTypePrecision"></input>'. '</td>';
	echo '<td>' . '<input id="addPrintTypePlateSize"></input>'. '</td>';
	echo '<td>' . '<input id="addPrintTypeCost"></input>'. '</td>';
	echo '</tr>';

	echo '</table>';
	echo"</div>";
?>

			</div>
		</div>
	</div>
</div>


<!-- Print Types Table Handler -->
<script>

function removePrintType(printTypeID) {
	alert(printTypeID);
}

function editPrintType(printTypeID) {
	let printerId = $("#printerSelect" + printTypeID).val();
	let printName = $("#printTypeName" + printTypeID).val();
	let printDesc = $("#printTypeDescription" + printTypeID).val();
	let printHead = $("#headSize" + printTypeID).val();
	let precision = $("#precision" + printTypeID).val();
	let buildSize = $("#buildSize" + printTypeID).val();
	let cost = $("#costPerGram" + printTypeID).val();
	// alert(printName + printDesc + printHead + precision + buildSize + cost).val();
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

$("#").click(function(){
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
			action: 'addPrintType',
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
include_once PUBLIC_FILES . '/modules/footer.php' ; 
?>

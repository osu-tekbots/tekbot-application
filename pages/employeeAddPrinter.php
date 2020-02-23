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
					echo '<tr>';
					echo '<td>' . '<button id=editButton' . $p->getPrinterId() . '>Edit</button> <button id=removeButton' . $p->getPrinterId() . '>Remove</button>'.'</td>'; 
					echo '<td>' . '<input id="printerName' . $p->getPrinterId() . '" value="' . $p->getPrinterName() . '"></input>'. '</td>';
					echo '<td>' . '<input id="printerDescription' . $p->getPrinterId() . '" value="' . $p->getDescription() . '"></input>'. '</td>';
					echo '<td>' . '<input id="printerLocation' . $p->getPrinterId() . '" value="' . $p->getLocation() . '"></input>'. '</td>';
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


<?php
	echo "
	<div class='admin-paper'>
	";

	echo '<table id="printTypesTable">'; 
	echo '<tr>';
	echo '<td></td>';
	echo '<td>' . "<b> Print Type Name </b>" . '</td>';
	echo '<td>' . "<b> Description </b>" . '</td>';
	echo '<td>' . "<b> Head Size </b>" . '</td>';
	echo '<td>' . "<b> Precision </b>" . '</td>';
	echo '<td>' . "<b> Build Plate Size </b>" . '</td>';
	echo '<td>' . "<b> Cost Per Gram </b>" . '</td>';
	echo '</tr>';
	foreach ($printTypes as $p) {
		echo '<tr>';
		echo '<td>' . '<button id=editButton' . $p->getPrintTypeId() . '>Edit</button> <button id=removeButton' . $p->getPrintTypeId() . '>Remove</button>'.'</td>'; 
		echo '<td>' . '<input id="printTypeName' . $p->getPrintTypeId() . '" value="' . $p->getPrintTypeName() . '"></input>'. '</td>';
		// description not part of Model yet
		echo '<td>' . '<input id="printTypeDescription' . $p->getPrintTypeId() . '" value=""></input>'. '</td>';
		echo '<td>' . '<input id="headSize' . $p->getPrintTypeId() . '" value="' . $p->getHeadSize() . '"></input>'. '</td>';
		echo '<td>' . '<input id="precision' . $p->getPrintTypeId() . '" value="' . $p->getPrecision() . '"></input>'. '</td>';
		echo '<td>' . '<input id="buildSize' . $p->getPrintTypeId() . '" value="' . $p->getBuildPlateSize() . '"></input>'. '</td>';
		echo '<td>' . '<input id="costPerGram' . $p->getPrintTypeId() . '" value="' . $p->getCostPerGram() . '"></input>'. '</td>';
		echo '</tr>';

		// Note: Printer ID is not working in the ExtractFromRow for PrintTypes in DAO

	}
	echo '<tr>';
	echo '<td>' . '<button id="addPrintTypeButt">Add</button>'. '</td>';
	echo '<td>' . '<input id="addPrintTypeName"></input>'. '</td>';
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

<!-- TODO: Ask Symon how to grab each edit and remove button for script -->
<!-- Adding printer table handler -->
<script>
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
			//  Insert delay for reload
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

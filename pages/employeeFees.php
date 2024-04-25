<!-- This file is used for processing fees associated with equipment rentals. -->
<!-- NOTE: Assigning payments should send users an email, but is not implemented -->
<!-- NOTE: Verifying payments requires a call to an uncreated mailing function -->

<?php
include_once '../bootstrap.php';

use DataAccess\EquipmentDao;
use DataAccess\EquipmentCheckoutDao;
use DataAccess\EquipmentReservationDao;
use DataAccess\UsersDao;
use DataAccess\EquipmentFeeDao;
use DataAccess\PrinterFeeDao;
use Model\UserAccessLevel;
use Model\EquipmentFee;
use Util\Security;

if (!session_id()) {
    session_start();
}

// Make sure the user is logged in and allowed to be on this page
include_once PUBLIC_FILES . '/lib/shared/authorize.php';

allowIf(verifyPermissions('employee', $logger), 'index.php');


$title = 'Employee Fees';
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

$equipmentCheckoutDao = new EquipmentCheckoutDao($dbConn, $logger);
$userDao = new UsersDao($dbConn, $logger);
$equipmentFeeDao = new EquipmentFeeDao($dbConn, $logger);
$printFeeDao = new PrinterFeeDao($dbConn, $logger);

$equipmentFees = $equipmentFeeDao->getAdminFees();
$printerFees = $printFeeDao->getAdminFees();
$feeHTML = '';
foreach ($equipmentFees as $fee){
	$feeID = $fee->getFeeID();
	$checkoutID = $fee->getCheckoutID();
	$checkout = $equipmentCheckoutDao->getCheckout($checkoutID);
	$userID = $fee->getUserID();
	$user = $userDao->getUserByID($userID);
	$feeNotes = $fee->getNotes();
	$feeAmount = $fee->getAmount();
	$isPaid = $fee->getIsPaid();
	$isPending = $fee->getIsPending();
	$dateCreated = $fee->getDateCreated();
	$dateUpdated = $fee->getDateUpdated();

	$email = Security::HtmlEntitiesEncode($user->getEmail());
	$name = Security::HtmlEntitiesEncode($user->getFirstName()) 
	. ' ' 
	. Security::HtmlEntitiesEncode($user->getLastName());

	if ($isPaid){
		$status = 'Complete';
		// No action
		$button = '';
	}
	else if ($isPending){
		$status = 'Awaiting Approval';
		renderApproveEquipmentFeeModal($fee);
		$button = renderEquipmentFeeApproveButton($feeID);
		// Approve or decline with notes modal
	}
	else {
		$status = 'Waiting For Payment';
		// Email reminder 
		$button = '';
	}

	


	$feeHTML .= "
	<tr>
	
		<td>$name</td>
		<td>checkoutView</td>
		<td>$feeAmount</td>
		<td>$feeNotes</td>
		<td>$status</td>
		<td>$button</td>
	</tr>
	
	";

}

foreach ($printerFees as $fee){
	$feeID = $fee->getPrintFeeId();
	//$checkoutID = $fee->getCheckoutID();
	//getPrintJobId
	//$checkout = $equipmentCheckoutDao->getCheckout($checkoutID);
	$userID = $fee->getUserId();
	$user = $userDao->getUserByID($userID);
	$feeNotes = $fee->getCustomerNotes();
	$feeAmount = $fee->getAmount();
	$isPaid = $fee->getIsPaid();
	$isPending = $fee->getIsPending();
	$dateCreated = $fee->getDateCreated();
	$relatedPrintID = $fee->getPrintJobId();
	//getPaymentInfo
	$dateUpdated = $fee->getDateUpdated();

	$email = Security::HtmlEntitiesEncode($user->getEmail());
	$name = Security::HtmlEntitiesEncode($user->getFirstName()) 
	. ' ' 
	. Security::HtmlEntitiesEncode($user->getLastName());

	if ($isPaid){
		$status = 'Complete';
		// No action
		$button = '';
	}
	else if ($isPending){
		$status = 'Awaiting Approval';

		// Approve or decline with notes modal
	}
	else {
		$status = 'Waiting For Payment';
		// Email reminder 
		$button = '';
	}

	$view = "<div id='$relatedPrintID' class='printViewClick' style='color: blue;'>Print View</div>";


	$feeHTML .= "
	<tr>
	
		<td>$name</td>
		<td>$view</td>
		<td>$feeAmount</td>
		<td>$feeNotes</td>
		<td>$status</td>
		<td>Actions</td>
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
						echo"
						
						<div class='admin-paper'>
						<h3>Fees</h3>
						<p><strong>IMPORTANT</strong>: You must process the order in touchnet before approving fees!</p>
						<p>Make sure to process any fees that are awaiting approval.  Some of them are tied to prints or cuts and need to be processed before you are able to cut/print.</p>
						<table class='table' id='checkoutFees'>
						<caption>Fees Relating to Equipment Checkout</caption>
						<thead>
							<tr>
								<th>Name</th>
								<th>Transaction</th>
								<th>Amount($)</th>
								<th>Notes</th>
								<th>Status</th>
								<th>Actions</th>
							</tr>
						</thead>
						<tbody>
							$feeHTML
						</tbody>
						</table>
						<script>
							$('#checkoutFees').DataTable();
						</script>
					</div>
						
						
						
						
						
						
						
						
						";
					

				

	
	
				?>




			</div>
		</div>
	</div>
</div>

<script>

$( ".printViewClick" ).click(function() {
	alert($(this).attr("id"));

});


</script>

<?php 
include_once PUBLIC_FILES . '/modules/footer.php' ; 
?>

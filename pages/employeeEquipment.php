<?php
include_once '../bootstrap.php';

use DataAccess\EquipmentDao;
use DataAccess\EquipmentCheckoutDao;
use DataAccess\EquipmentReservationDao;
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

$equipmentDao = new EquipmentDao($dbConn, $logger);
$userDao = new UsersDao($dbConn, $logger);
$checkoutDao = new EquipmentCheckoutDao($dbConn, $logger);
$reservationDao = new EquipmentReservationDao($dbConn, $logger);
$reservedEquipment = $reservationDao->getReservationsForAdmin();
$checkedoutEquipment = $checkoutDao->getCheckoutsForAdmin();

$reservedHTML = '';
$listNumber = 0;
foreach ($reservedEquipment as $r){
		$reservationID = $r->getReservationID();
        $equipmentID = $r->getEquipmentID();
        $userID = $r->getUserID();
        $reservationTime = $r->getDatetimeReserved();
        $latestPickupTime = $r->getDatetimeExpired();
		$isActive = $r->getIsActive();
		$user = $userDao->getUserByID($userID);
		$equipment = $equipmentDao->getEquipment($equipmentID);

        $equipmentName = Security::HtmlEntitiesEncode($equipment->getEquipmentName());
        $equipmentLocation = Security::HtmlEntitiesEncode($equipment->getLocation());
 
        $email = Security::HtmlEntitiesEncode($user->getEmail());
        $name = Security::HtmlEntitiesEncode($user->getFirstName()) 
        . ' ' 
		. Security::HtmlEntitiesEncode($user->getLastName());
		
		if ($isActive){
			$active = "Active";
            renderNewHandoutModal($r);
            $handoutButton = createReservationHandoutButton($reservationID, $listNumber, $userID, $equipmentID);
            $cancelButton = createReservationCancelButton($reservationID, $listNumber);
            $tableIDName = "activeReservation$listNumber";
        }
        else {
			$active = "Expired";
			//$handoutButton = createReserveAsEmployeeBtn($reservationID, $listNumber, $userID, $equipmentID);
			$handoutButton = "";
            $cancelButton = "";
            $tableIDName = "expiredReservation$listNumber";
        }
	



	$reservedHTML .= "
	<tr id='$tableIDName'>
		<td>$email</td>
		<td>$name</td>
		<td>$reservationTime</td>
		<td>$latestPickupTime</td>
		<td>$equipmentName</td>
		<td>$active</td>
		<td>$handoutButton $cancelButton</td>
	</tr>
	";
	$listNumber++;
}

$checkoutHTML = '';
$listNumber = 0;
foreach ($checkedoutEquipment as $c){
	$checkoutID = $c->getCheckoutID();
	$reservationID = $c->getReservationID();
	$userID = $c->getUserID();

	$pickupTime = $c->getPickupTime();
	$latestPickupTime = $c->getDeadlineTime();
	$returnedTime = $c->getReturnTime();
	$contractName = $c->getContractID();
	$status = $c->getStatusID()->getName();

	$statusID = $c->getStatusID()->getId();
	$reservation = $reservationDao->getReservation($reservationID);
	$equipmentID = $reservation->getEquipmentID();
	$user = $userDao->getUserByID($userID);
	$equipment = $equipmentDao->getEquipment($equipmentID);

	$equipmentName = Security::HtmlEntitiesEncode($equipment->getEquipmentName());
	$equipmentLocation = Security::HtmlEntitiesEncode($equipment->getLocation());

	$email = Security::HtmlEntitiesEncode($user->getEmail());
	$name = Security::HtmlEntitiesEncode($user->getFirstName()) 
	. ' ' 
	. Security::HtmlEntitiesEncode($user->getLastName());


	if ($statusID == "Returned" || $statusID == "Returned Late"){
		// If equipment has been returned
		renderViewCheckoutModal($c);
		renderEquipmentFeesModal($c);
		$assignFeeButton = createAssignEquipmentFeesButton($checkoutID, $userID, $reservationID);
		$returnButton = createViewCheckoutButton($checkoutID);
		//TODO: View Checkout button
	} else {
		renderEquipmentReturnModal($c);
		$returnButton = renderEquipmentReturnButton($c);
		$assignFeeButton = "";
		//TODO: Extend checkout button

	}

	$checkoutHTML .= "
	<tr id='checkout$listNumber'>
		<td>$email</td>
		<td>$name</td>
		<td>$pickupTime</td>
		<td>$latestPickupTime</td>
		<td>$returnedTime</td>
		<td>$equipmentName</td>
		<td>$status</td>
		<td>$returnButton $assignFeeButton</td>
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
                    renderEmployeeBreadcrumb('Employee', 'Equipment Checkout');

		
				echo "
	
				<div class='admin-paper'>
				<h3>Reserved Equipment</h3>
					<table class='table' id='equipmentReservations'>
					<caption>Reservations</caption>
						<thead>
							<tr>
								<th>Email Address</th>
								<th>Name</th>
								<th>Reservation Time</th>
								<th>Deadline Time</th>
								<th>Equipment</th>
								<th>Status</th>
								<th>Actions</th>
							</tr>
						</thead>
						<tbody>
							$reservedHTML
						</tbody>
					</table>
					<script>
					$('#equipmentReservations').DataTable(
						{
							lengthMenu: [[5, 10, 20, -1], [5, 10, 20, 'All']],
							aaSorting: [[2, 'desc']]
						}
					);

					</script>
				</div>
					
					";
					

		


					
				echo "
				<br><br>";
			
				
				echo "
	
				<div class='admin-paper'>
				<h3>Checked Out Equipment</h3>
					<table class='table' id='equipmentCheckouts'>
					<caption>Checkouts</caption>
						<thead>
							<tr>
								<th>Email Address</th>
								<th>Name</th>
								<th>Pickup Time</th>
								<th>Deadline Time</th>
								<th>Returned Time</th>
								<th>Equipment</th>
								<th>Status</th>
								<th>Actions</th>
							</tr>
						</thead>
						<tbody>
							$checkoutHTML
						</tbody>
					</table>
					<script>
					$('#equipmentCheckouts').DataTable(
						{
							lengthMenu: [[5, 10, 20, -1], [5, 10, 20, 'All']],
							aaSorting: [[2, 'desc']]
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

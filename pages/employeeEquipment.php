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

allowIf(verifyPermissions('employee'), 'index.php');


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



/* 
 * Retrieves the information corresponding to the user and their choice
 * and concats this in the option tag in the HTML
 */
$user_option = "<option value=''></option>";
$users = $userDao->getAllUsers();
foreach ($users as $user){
	$user_option .= "<option value='".$user->getUserID()."'>".$user->getLastName().", ".$user->getFirstName()."</option>";
}

$equipment_option = "<option value=''></option>";
$availableEquipment = $equipmentDao->getAdminEquipment();
foreach ($availableEquipment as $e){
	$equipment_option .= "<option value='".$e->getEquipmentID()."'>".$e->getEquipmentName()."</option>";
}


/* 
 * Creates the HTMl for a new reservation, and populates this
 * using the option HTML from above
 */
$newreservationHTML = '';
$newreservationHTML .= "<form class='form-inline'>

					<label for='user' class='d-inline-block'>User:</label><select id='user' class='form-control d-inline-block'>$user_option</select>
					<label for='equipment' class='d-inline-block'>Equipment:</label><select id='equipment' class='form-control d-inline-block'>$equipment_option</select>
					<button class='form-control' onclick='reserveEquipment();'>Make Reservation</button>
				
				</form>";
				

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
		
		/* 
		* If there are any active reservations, this section displays them under the
		* reserved equipment section. -ADD ADDITIONAL NOTES
		*/
		if ($isActive){
			$active = "Active";
            renderNewHandoutModal($r);
			$handoutButton = createReservationHandoutButton($reservationID, $listNumber, $userID, $equipmentID);
			//$handoutButton = "<button class='handoutBtn btn btn-outline-primary capstone-nav-btn' id='$reservationID' type='button'>Handout</button>";
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
	


	if ($isActive){
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
}

/* 
* This section of code displays the "Checked Out Equipment" section
* Gets the details of each checked out item from the checkedoutEquipment DAO object,
* then populates a data table with this information.
*/

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


/* 
* If the entry has been returned, creates the "assign fee" and "view" buttons.
* View creates a modal with transaction details.
*/
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
				echo "
	
				<div class='admin-paper'>
				<h3>Reserved Equipment</h3>";
				if ($reservedHTML == '')
					echo "<p>There are no active equipment reservations.</p>$newreservationHTML</div>";
				else
					echo "
				<p>After a customer reserves an equipment on the portal, it will appear here.  Once they arrive at the store, hit 'Handout' to rent out the item to the student.  The reservation will expire and now it will show up as a 'Checked Out Equipment' in the table below.</p>
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
					$newreservationHTML
				</div>
					
					";
					

		


					

				
				echo "
				<div class='admin-paper'>
				<h3>Checked Out Equipment</h3>
				<p>When a student brings back the rented equipment, hit the 'Return' button next to their checkout.  Write any necessary notes in the notes section (scratches, broken handle).  The student can see the notes you put here.  If there are any fees that need to be assigned (late fees, damaged item), you can assign them fees by pressing the 'Assign fee' button.</p>
					<table class='table' id='equipmentCheckouts'>
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
							lengthMenu: [[10, 25, 50, -1], [10, 25, 50, 'All']],
							aaSorting: [[4, 'asc']]
						}
					);

					</script>
				</div>
					
					";
				
					
					

					



				?>

					<div id="modal">
					</div>


			</div>
		</div>
	</div>
</div>

<script>
/*
$('.handoutBtn').on('click', function() {
	let reservationID = $(this).attr("id");
	let data = {
		action: 'equipmentModalHandout',
		reservationID: reservationID
	};

	api.post('/equipmentrental.php', data).then(res => {
		//snackbar(res.message, 'success');
		console.log(res.message);
		document.getElementById("modal").innerHTML = res.message;
		let modalID = "#newHandoutModal" + reservationID;
		$(modalID).modal();
		//$(this).toggle();
	}).catch(err => {
		snackbar(err.message, 'error');
	});
		
});


$('select').on('change', function() {
	let reservationID = $(this).attr('id');
	let contract = $(this).val();
	let deadlineID = '#deadline' + reservationID;
	let data = {
		action: 'updateDeadlineText',
		contractID: contract
	};
	api.post('/equipmentrental.php', data).then(res => {
		$(deadlineID).html(res.message);
	}).catch(err => {
		snackbar(err.message, 'error');
	});

});
*/
function reserveEquipment(){
	var user = $('#user').val();
	var equipment = $('#equipment').val();
	
	let content = {
		action: 'createReservation',
		userID: user,
		equipmentID: equipment,
		messageID: 'wersdhwrujhssfuj'
	}
	
	api.post('/equipmentrental.php', content).then(res => {
		snackbar(res.message, 'info');
	}).catch(err => {
		snackbar(err.message, 'error');
	});	
}




</script>

<?php 
include_once PUBLIC_FILES . '/modules/footer.php' ; 
?>

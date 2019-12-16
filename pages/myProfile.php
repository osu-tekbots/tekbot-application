<?php
include_once '../bootstrap.php';

use DataAccess\UsersDao;
use DataAccess\EquipmentFeeDao;
use DataAccess\EquipmentDao;
use DataAccess\EquipmentCheckoutDao;
use DataAccess\EquipmentReservationDao;
use Model\EquipmentCheckoutStatus;
use Util\Security;

session_start();

include_once PUBLIC_FILES . '/lib/shared/authorize.php';

$isLoggedIn = isset($_SESSION['userID']) && !empty($_SESSION['userID']);
allowIf($isLoggedIn, $configManager->getBaseUrl() . 'pages/login.php');

$title = 'My Profile';
$css = array(
	'assets/css/admin.css',
	'https://cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css'
);

$js = array(
    'https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js'
);
include_once PUBLIC_FILES . '/modules/header.php';
include_once PUBLIC_FILES . '/modules/newHandoutModal.php';
include_once PUBLIC_FILES . '/modules/employee.php';
include_once PUBLIC_FILES . '/modules/renderBrowse.php';

$usersDao = new UsersDao($dbConn, $logger);

$user = $usersDao->getUserByID($_SESSION['userID']);


// TODO: handle the case where we aren't able to fetch the user
if ($user){
	$uId = $user->getUserID();
	$uFirstName = $user->getFirstName();
	$uLastName = $user->getLastName();
	$uPhone = $user->getPhone();
	$uEmail = $user->getEmail();
	$uAccessLevel = $user->getAccessLevelID()->getName();
}

$checkoutFeeDao = new EquipmentFeeDao($dbConn, $logger);
$equipmentDao = new EquipmentDao($dbConn, $logger);
$userDao = new UsersDao($dbConn, $logger);
$checkoutDao = new EquipmentCheckoutDao($dbConn, $logger);
$reservationDao = new EquipmentReservationDao($dbConn, $logger);


$uID = $_SESSION['userID'];
$checkoutFees = $checkoutFeeDao->getFeesForUser($uID);
$reservedEquipment = $reservationDao->getReservationsForUser($uID);
$checkedoutEquipment = $checkoutDao->getCheckoutsForUser($uID);
$checkoutFeeHTML = '';

foreach ($checkoutFees as $f){
    $checkoutID = $f->getCheckoutID();
    $checkout = $checkoutDao->getCheckout($checkoutID);
    $feeNotes = $f->getNotes();
    $feeAmount = $f->getAmount();
    $feeCreated = $f->getDateCreated();
    $feeID = $f->getFeeID();

    $isPending = $f->getIsPending();
    $isPaid = $f->getIsPaid();
    renderViewCheckoutModal($checkout);

    renderPayFeeModal($f);
    $payButton = $isPending ? "PENDING APPROVAL" : ($isPaid ? "PAID" : createPayButton($feeID));

    $checkoutFeeHTML .= "
    <tr>
        <td><a href='' data-toggle='modal' 
		data-target='#viewCheckoutModal$checkoutID'>Checkout</a></td>
        <td>$feeNotes</td>
        <td>$feeAmount</td>
        <td>$payButton</td>
    </tr>
    ";
}

$reservedHTML = '';
$listNumber = 0;
if ($reservedEquipment){
	foreach ($reservedEquipment as $r){
			$reservationID = $r->getReservationID();
			$equipmentID = $r->getEquipmentID();
			$userID = $r->getUserID();
			$reservationTime = $r->getDatetimeReserved();
			$latestPickupTime = $r->getDatetimeExpired();
			$isActive = $r->getIsActive();
			$equipment = $equipmentDao->getEquipment($equipmentID);

			$equipmentName = Security::HtmlEntitiesEncode($equipment->getEquipmentName());
			$equipmentLocation = Security::HtmlEntitiesEncode($equipment->getLocation());
	
		
			
			if ($isActive){
				$active = "Active";
				//renderNewHandoutModal($r);
				//$handoutButton = createReservationHandoutButton($reservationID, $listNumber, $userID, $equipmentID);
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
			<td>$reservationTime</td>
			<td>$latestPickupTime</td>
			<td>$equipmentName</td>
			<td>$active</td>
			<td>$cancelButton</td>
		</tr>
		";
		$listNumber++;
	}
} else {
	$reservedHTML = "";
}

$checkoutHTML = '';
$listNumber = 0;
if ($checkedoutEquipment){
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
		$equipment = $equipmentDao->getEquipment($equipmentID);

		$equipmentName = Security::HtmlEntitiesEncode($equipment->getEquipmentName());
		$equipmentLocation = Security::HtmlEntitiesEncode($equipment->getLocation());



		if ($statusID == "Returned" || $statusID == "Returned Late"){
			// If equipment has been returned
			renderViewCheckoutModal($c);
			renderEquipmentFeesModal($c);
			//$assignFeeButton = createAssignEquipmentFeesButton($checkoutID, $userID, $reservationID);
			$returnButton = createViewCheckoutButton($checkoutID);
			//TODO: View Checkout button
		} else {
			//renderEquipmentReturnModal($c);
			$returnButton = "";
			$assignFeeButton = "";
			//TODO: Extend checkout button

		}

		$checkoutHTML .= "
		<tr id='checkout$listNumber'>
			<td>$pickupTime</td>
			<td>$latestPickupTime</td>
			<td>$returnedTime</td>
			<td>$equipmentName</td>
			<td>$status</td>
			<td>$returnButton</td>
		</tr>
		";
		$listNumber++;

	}
} else {
	$checkoutHTML = "";
}

?>
<br>
<div class="stickytabs">
	<button class="tablink" onclick="openPage('Profile', this, 'red')">Profile</button>
	<button class="tablink" onclick="openPage('Fees', this, 'green')">My Fees</button>
	<button class="tablink" onclick="openPage('Equipment', this, 'blue')" id="defaultOpen">Equipment Reservations</button>
</div>

<div id="Profile" class="tabcontent">
<form id="formUserProfile">
	<input type="hidden" name="uid" value="<?php echo $_SESSION['userID']; ?>" />
	<div class="jumbotron jumbotron-fluid">
		<br><br>
		<h2>My Profile</h2>
		<hr class="my-4">
		<div class="container bootstrap snippets">
			<div class="row">
				<div class="col-sm-6">
					<div class="panel-heading">
						<h4 class="panel-title">User Info</h4>
					</div>
					<div class="panel-body">
						<div class="form-group">
							<label class="col control-label" for="firstNameText">First Name</label>
							<div class="col-sm-11">
								<textarea class="form-control" id="firstNameText" name="firstName" rows="1"
									required readonly><?php echo $uFirstName; ?></textarea>
							</div>
						</div>
						<div class="form-group">
							<label class="col control-label" for="lastNameText">Last Name</label>
							<div class="col-sm-11">
								<textarea class="form-control" id="lastNameText" name="lastName"
									rows="1" readonly><?php echo $uLastName; ?></textarea>
							</div>
						</div>
						<div class="container">
							<div class="row">

							</div>
						</div>
						<br>
						
						<div class="panel-body">
							<br>
							<div class="col-sm-11">
								<button class="btn btn-large btn-block btn-primary" id="saveProfileBtn"
									type="button">Save</button>
								<div id="successText" class="successText" style="display:none;">Success!</div>
							</div>
						</div>
					</div>
				</div>

				<div class="col-sm-6">
					<div class="panel-heading">
						<h4 class="panel-title">Contact Info</h4>
					</div>
					<div class="panel-body">
						<div class="form-group">
							<label class="col control-label" for="phoneText">Phone Number</label>
							<div class="col">
								<textarea class="form-control" id="phoneText" name="phone" rows="1"
									required><?php echo $uPhone; ?></textarea>
							</div>
						</div>
						<div class="form-group">
							<label class="col control-label" for="emailText">Email Address</label>
							<div class="col">
								<textarea class="form-control" id="emailText" name="email" rows="1"
									 readonly required><?php echo $uEmail; ?></textarea>
							</div>
						</div>
						<br>
						<div class="panel-heading">
							<h4 class="panel-title">Account info</h4>
						</div>
						<hr class="my-4">
						<div class="form-group">
							<p class="form-control-static">User Type: <?php echo $uAccessLevel; ?> </p>
							<div class="col">

							</div>
						</div>
						<div class="form-group">
							<?php
								// TODO: display projects here
								?>
							<div class="col-sm-11">

							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</form>
</div>

<div id="Fees" class="tabcontent">
	<div class="container-fluid">
		
			<?php

				echo "
			<br><br><br><br><br>
			<div class='admin-paper'>
			<h3>Equipment Checkout Fees</h3>
				<table class='table' id='equipmentFees'>
				<caption>Equipment Checkout Fees</caption>
					<thead>
						<tr>
							<th>Related Checkout</th>
							<th>Notes</th>
							<th>Amount</th>
							<th>Actions</th>
						</tr>
					</thead>
					<tbody>
						$checkoutFeeHTML
					</tbody>
				</table>
				<script>
					$('#equipmentFees').DataTable(
						{
							lengthMenu: [[5, 10, 20, -1], [5, 10, 20, 'All']]
						}
					);
				</script>
			</div>
				
				";
			
			
				// File located inside modules/renderBrowse.php
			?>
	
		
			
	</div>
</div>

<div id="Equipment" class="tabcontent">
<?php
echo "
	<br><br><br><br><br>
	<div class='admin-paper'>
	<h3>Reserved Equipment</h3>
		<table class='table' id='equipmentReservations'>
		<caption>Reservations</caption>
			<thead>
				<tr>
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
				lengthMenu: [[3, 5, 10, -1], [3, 5, 10, 'All']],
				aaSorting: [[0, 'desc']]
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
				lengthMenu: [[3, 5, 10, -1], [3, 5, 10, 'All']],
				aaSorting: [[0, 'desc']]
			}
		);

		</script>
	</div>
		
		";
	
		
		

		



	?>

</div>


<script defer type="text/javascript">

function openPage(pageName, elmnt, color) {
  // Hide all elements with class="tabcontent" by default */
  var i, tabcontent, tablinks;
  tabcontent = document.getElementsByClassName("tabcontent");
  for (i = 0; i < tabcontent.length; i++) {
    tabcontent[i].style.display = "none";
  }

  // Remove the background color of all tablinks/buttons
  tablinks = document.getElementsByClassName("tablink");
  for (i = 0; i < tablinks.length; i++) {
    tablinks[i].style.backgroundColor = "";
  }

  // Show the specific tab content
  document.getElementById(pageName).style.display = "block";

  // Add the specific color to the button used to open the tab content
  elmnt.style.backgroundColor = color;
}

// Get the element with id="defaultOpen" and click on it
document.getElementById("defaultOpen").click();

/**
 * Event handler for a click event on the 'Save' button for user profiles.
 */
function onSaveProfileClick() {

	let data = new FormData(document.getElementById('formUserProfile'));

	let body = {
		action: 'saveProfile'
	};
	for(const [key, value] of data.entries()) {
		body[key] = value;
	}

	api.post('/users.php', body).then(res => {
		snackbar(res.message, 'success');
	}).catch(err => {
		snackbar(err.message, 'error');
	});

}
$('#saveProfileBtn').on('click', onSaveProfileClick);
</script>


<?php include_once PUBLIC_FILES . '/modules/footer.php'; ?>
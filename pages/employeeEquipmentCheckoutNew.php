<?php
include_once '../bootstrap.php';

use DataAccess\EquipmentCheckoutDao;
use DataAccess\EquipmentHealthDao;
use DataAccess\EquipmentItemDao;
use DataAccess\EquipmentTypeDao;
use DataAccess\UsersDao;
use Util\Security;

if (PHP_SESSION_ACTIVE != session_status())
    session_start();

// Make sure the user is logged in and allowed to be on this page
include_once PUBLIC_FILES . '/lib/shared/authorize.php';

allowIf(verifyPermissions('employee', $logger), 'index.php');


$title = 'Employee Equipment Checkout';
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

$checkoutDao = new EquipmentCheckoutDao($dbConn, $logger);
$equipmentHealthDao = new EquipmentHealthDao($dbConn, $logger);
$equipmentItemDao = new EquipmentItemDao($dbConn, $logger);
$equipmentTypeDao = new EquipmentTypeDao($dbConn, $logger);
$userDao = new UsersDao($dbConn, $logger);


$reservedEquipment = $checkoutDao->getEmployeeReservations();
$checkedOutEquipment = $checkoutDao->getEmployeeCheckouts();
$healthOptions = $equipmentHealthDao->getAllHealthOptions();

// Builds <select> options for all users
$user_option = "<option value=''></option>";
$users = $userDao->getAllUsers();
foreach ($users as $user){
	$user_option .= "<option value='".$user->getUserID()."'>".$user->getLastName().", ".$user->getFirstName()."</option>";
}

// Builds <select> options for all equipment
$equipment_option = "<option value=''></option>";
$availableEquipment = $equipmentTypeDao->getEmployeeEquipment();
foreach ($availableEquipment as $e){
	$equipment_option .= "<option value='".$e->getEquipmentID()."'>".$e->getName()."</option>";
}
				

$reservedHTML = '';
foreach ($reservedEquipment as $r){
	$reservationID = $r->getReservationID();

	$dateReserved = $r->getDateReserved();

	$equipment = $equipmentTypeDao->getEquipmentByItemID($r->getItemID());
	$user = $userDao->getUserByID($r->getUserID());
	
	$reservedHTML .= "
	<tr id='reservation$reservationID'>
		<td>" . Security::HtmlEntitiesEncode($user->getEmail()) . "</td>
		<td>" . Security::HtmlEntitiesEncode($user->getFirstName())." ".Security::HtmlEntitiesEncode($user->getLastName()) . "</td>
		<td>{$dateReserved->format('Y-m-d H:i:s')}</td>
		<td>" . Security::HtmlEntitiesEncode($equipment->getName()) . "</td>
		<td>{$r->getItemID()}</td>
		<td>
			<button
				data-toggle='modal' data-target='#handoutModal'
				data-equipment-id='{$equipment->getEquipmentID()}' data-user-id='{$user->getUserID()}' data-reservation-id='$reservationID' data-reserved-item-id='{$r->getItemID()}'
				class='btn btn-outline-primary capstone-nav-btn' type='button'
			>
				Handout
			</button>
			<button onclick='cancelReservation($reservationID);' class='btn btn-outline-danger deleteProjectBtn' type='button'>
				Cancel
			</button>
		</td>
	</tr>
	";
}

/* 
 * This section of code displays the "Checked Out Equipment" section.
 * Gets the details of each checked out item, then populates a data table with this
 * information.
 */
$checkoutHTML = '';
foreach ($checkedOutEquipment as $c){
	$checkoutID = $c->getCheckoutID();

	$equipment = $equipmentTypeDao->getEquipmentByItemID($c->getItemID());
	$item = $equipmentItemDao->getItem($c->getItemID());
	$user = $userDao->getUserByID($c->getUserID());

	if ($c->getDateReturned()) {
		if ($c->getDateReturned() < $c->getDateDue())
			$status = 'Returned';
		else
			$status = 'Returned Late';
	} else {
		if (new DateTime() < $c->getDateDue())
			$status = 'Checked Out';
		else
			$status = 'Late';
	}

	$button = '';
	if ($status == "Checked Out" || $status == "Late"){
		$button = "<button
			class='btn btn-outline-primary capstone-nav-btn' type='button'
			data-toggle='modal' data-target='#returnModal'
			data-checkout-id='$checkoutID'
			data-equipment-name='{$equipment->getName()}'
			data-equipment-notes='{$equipment->getNotes()}'
			data-equipment-parts='{$equipment->getParts()}'
			data-equipment-return-check='{$equipment->getReturnCheck()}'
			data-user-name='{$user->getFirstName()} {$user->getLastName()}'
			data-user-onid='{$user->getOnid()}'
			data-user-email='{$user->getEmail()}'
			data-item-id='{$c->getItemID()}'
			data-item-location='{$item->getLocation()}'
			data-item-health='{$item->getHealthStatus()}'
		>
			Return
		</button>";
	}

	$checkoutHTML .= "
	<tr id='checkout$checkoutID'>
		<td>" . Security::HtmlEntitiesEncode($user->getEmail()) . "</td>
		<td>" . Security::HtmlEntitiesEncode($user->getFirstName()).' '.Security::HtmlEntitiesEncode($user->getLastName()) . "</td>
		<td>{$c->getDateCheckedOut()->format('Y-m-d H:i:s')}</td>
		<td>{$c->getDateDue()->format('Y-m-d H:i:s')}</td>
		<td>{$c->getDateReturned()?->format('Y-m-d H:i:s')}</td>
		<td>" . Security::HtmlEntitiesEncode($equipment->getName()) . "</td>
		<td>{$c->getItemID()}</td>
		<td>$status</td>
		<td>$button</td>
	</tr>
	";
}
?>

<br/>
<div id="page-top">
	<div id="wrapper">
		<?php renderEmployeeSidebar(); ?>

		<div class="admin-content" id="content-wrapper">
			<div class="container-fluid">
				<div class='admin-paper'>
					<h3>Reserved Equipment</h3>
					<?php
						if ($reservedHTML == '') {
							echo "<p>There are no active equipment reservations.</p>";
						} else {
							echo "<p>After a customer reserves an equipment on the portal, it will appear here. Once they arrive at the store, hit 'Handout' to rent out the item to the student. The reservation will expire and now it will show up as a 'Checked Out Equipment' in the table below.</p>
							<table class='table' id='equipmentReservations'>
								<thead>
									<tr>
										<th>Email Address</th>
										<th>Name</th>
										<th>Reservation Time</th>
										<th>Equipment</th>
										<th>Unit ID</th>
										<th>Actions</th>
									</tr>
								</thead>
								<tbody>
									$reservedHTML
								</tbody>
							</table>
							<script>
								$('#equipmentReservations').DataTable({
									lengthMenu: [[5, 10, 20, -1], [5, 10, 20, 'All']],
									aaSorting: [[2, 'desc']]
								});
							</script>";
						}
					?>
				</div>
				<div class="admin-paper">
					<h3>Spontaneous Handout</h3>
					<form class="form-inline">
						<div class="input-group mx-1">
							<div class="input-group-prepend"><label for="user" class="input-group-text">User</label></div>
							<select id="user" class="custom-select">
								<?= $user_option ?>
							</select>
						</div>
						<div class="input-group mx-1">
							<div class="input-group-prepend"><label for="equipment" class="input-group-text">Equipment</label></div>
							<select id="equipment" class="custom-select">
								<?= $equipment_option ?>
							</select>
						</div>
						<button data-toggle='modal' data-target='#newReservationModal' class="form-control btn btn-outline-primary mx-1" type="button">
							Handout Without Reservation
						</button>
					</form>
				</div>
				<div class='admin-paper'>
					<h3>Checked Out Equipment</h3>
					<p>When a student brings back the rented equipment, hit the 'Return' button next to their checkout. Write any necessary notes in the notes section (scratches, broken handle). The student can see the notes you put here. If there are any fees that need to be assigned (late fees, damaged item), you can assign them fees by pressing the 'Assign fee' button.</p>
					<table class='table' id='equipmentCheckouts'>
						<thead>
							<tr>
								<th>Email Address</th>
								<th>Name</th>
								<th>Pickup Time</th>
								<th>Deadline Time</th>
								<th>Returned Time</th>
								<th>Equipment</th>
								<th>Unit ID</th>
								<th>Status</th>
								<th>Actions</th>
							</tr>
						</thead>
						<tbody>
							<?= $checkoutHTML ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- Equipment checkout modal -->
<div class="modal" id="handoutModal">
	<div class="modal-dialog modal-dialog-centered modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title">Hand out <span class="equipmentName2"></span> to <span class="userName"></span></h4>
				<button type="button" class="close" data-dismiss="modal">&times;</button>
			</div>
	
			<div class="modal-body">
				<h4 class="d-flex justify-content-between">
					<span class="equipmentName2"></span>
					<span class="itemID uninitialized"></span>
				</h4>
				<p class="equipmentNotes"></p>

				<input type="hidden" id="handoutReservationId">
				<input type="hidden" id="handoutUserId">

				<div class='input-group mb-2'>
					<div class='input-group-prepend'><label for="handoutItemSelect" class='input-group-text'>Unit</label></div>
					<select id="handoutItemSelect" class="custom-select"></select>
				</div>
				
				<div class="row">
					<div class="col">
						<p><b>Location:</b> <span class="itemLocation uninitialized"></span></p>
						<p><b>Employee Notes:</b> <span class="itemNotes uninitialized"></span></p>
					</div>
					<div class="col">
						<p><b>Health:</b> <span class="itemHealthStatus uninitialized"></span></p>
						<p><b>Visibility:</b> <span class="publicStatus uninitialized"></span></p>
					</div>
				</div>

				<div class='input-group mb-2'>
					<div class='input-group-prepend'><label for="handoutDueDate" class='input-group-text'>Return Deadline</label></div>
					<input id="handoutDueDate" type="date" class="form-control">
				</div>

				<h4 class="userName"></h4>
				<p><b>ONID:</b> <span class="userOnid"></span></p>
				<p><b>Email:</b> <span class="userEmail"></span></p>
			</div>

			<div class="modal-footer">
				<button type="button" class="btn btn-success" data-dismiss="modal" onClick="handoutEquipment();">Handout</button>
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
			</div>
		</div>
	</div>
</div>

<!-- Equipment return modal -->
<div class="modal fade" id="returnModal">
	<div class="modal-dialog modal-dialog-centered modal-lg">
		<div class="modal-content">
			<!-- Modal Header -->
			<div class="modal-header">
				<h4 class="modal-title">Take back <span class="equipmentName2"></span> from <span class="userName"></span></h4>
				<button type="button" class="close" data-dismiss="modal">&times;</button>
			</div>

			<!-- Modal body -->
			<div class="modal-body">
				<h4 class="d-flex justify-content-between">
					<span class="equipmentName2"></span>
					<span class="itemID"></span>
				</h4>

				<p><b>Equipment Notes:</b> <span class="equipmentNotes"></span></p>
				<p><b>Equipment Parts:</b> <span class="equipmentParts"></span></p>
				<p><b>Return Steps:</b><br> <span class="equipmentReturnCheck"></span></p>

				<h4 class="userName"></h4>
				<p><b>ONID:</b> <span class="userOnid"></span></p>
				<p><b>Email:</b> <span class="userEmail"></span></p>

				<input type="hidden" id="returnCheckoutID">

				<h4>Return Details</h4>
				<div class="input-group mb-2">
					<div class="input-group-prepend"><label for="returnLocation" class="input-group-text">Location</label></div>
					<input id="returnLocation" type="text" class="form-control">
				</div>
				
				<div class="input-group mb-2">
					<div class="input-group-prepend"><label for="returnHealth" class="input-group-text">Health Status</label></div>
					<select id="returnHealth" type="text" class="custom-select">
						<?php
							foreach ($healthOptions as $option) {
								echo "<option value='{$option->getOptionID()}'>
									{$option->getName()}
								</option>";
							}
						?>
					</select>
				</div>
				<div class="form-group">
					<label for="returnNotes">Return Notes</label>
					<textarea id="returnNotes" class="form-control" rows="4"></textarea>
				</div>
			</div>

			<!-- Modal footer -->
			<div class="modal-footer">
				<button type='button' class='btn btn-success' data-dismiss='modal' onclick='returnEquipment();'>Return</button>
				<button type='button' class='btn btn-secondary' data-dismiss='modal'>Cancel</button>
			</div>
		</div>
	</div>
</div>

<!-- Equipment rental contract modal -->
<?php include_once PUBLIC_FILES . '/modules/reserveEquipmentModal.php'; ?>

<script>
	// 
	// Webpage interactivity
	// 

	$('#equipmentCheckouts').DataTable({
		lengthMenu: [[10, 25, 50, -1], [10, 25, 50, 'All']],
		aaSorting: [[4, 'asc']]
	});

	// Makes reservation contract modal open checkout modal when contract accepted
	$('#createReservationBtn').attr('data-toggle', 'modal');
	$('#createReservationBtn').attr('data-target', '#handoutModal');

	// Populates the handout modal with equipment and user details, allowing easy
	// verification that the correct item is handed out to the correct user
	$('#handoutModal').on('show.bs.modal', event => {
		const modal = $('#handoutModal'), button = $(event.relatedTarget);
		const equipmentId = button.data('equipment-id') ?? $('#equipment').val(),
			userId = button.data('user-id') ?? $('#user').val(),
			reservationId = button.data('reservation-id'),
			reservedItemId = button.data('reserved-item-id');

		const content = {
			action: 'getCheckoutDetails',
			equipmentID: equipmentId,
			reservedItemID: reservedItemId,
			userID: userId
		};

		api.post('/equipment-checkout.php', content).then(res => {
			modal.find('.equipmentName2').text(res.content.equipment.name);
			modal.find('.equipmentNotes').text(res.content.equipment.notes);
			modal.find('.userName').text(res.content.user.name);
			modal.find('.userOnid').text(res.content.user.onid);
			modal.find('.userEmail').text(res.content.user.email);

			modal.find('.uninitialized').html('<i>Select an item</i>');

			modal.find('#handoutUserId').val(userId);
			modal.find('#handoutReservationId').val(reservationId);
			modal.find('#handoutItemSelect').empty();
			modal.find('#handoutItemSelect').append($('<option>', {selected: Boolean(reservedItemId)}));
			for (const item of res.content.items) {
				modal.find('#handoutItemSelect').append($('<option>', {
					value: item.id,
					text: `${item.id} -${item.isPublic ? '' : ' HIDDEN -'} ${item.location} (${item.healthStatus})`,
					selected: item.id == reservedItemId,
					'data-location': item.location,
					'data-health-status': item.healthStatus,
					'data-notes': item.notes,
					'data-is-public': item.isPublic,
				}));
			}

			if (reservedItemId) {
				const item = res.content.items.find(i => i.id == reservedItemId)
				populateCheckoutModalItemFields(item.id, item.location, item.notes, item.healthStatus, item.isPublic);
			} else {
				clearCheckoutModalItemFields();
			}
		}).catch(err => {
			snackbar(err.message, 'error');
			modal.modal('hide');
		});
	});

	// Populates modal fields with item details to easily verify the correct item was selected
	$('#handoutItemSelect').on('change', () => {
		const selected = $('#handoutItemSelect').find(':selected');

		if (selected.val() == '') 
			clearCheckoutModalItemFields();
		else
			populateCheckoutModalItemFields(
				selected.val(),
				selected.data('location'),
				selected.data('notes'),
				selected.data('health-status'),
				selected.data('is-public')
			);
	});

	function clearCheckoutModalItemFields() {
		const modal = $('#handoutModal');

		modal.find('.uninitialized').html('<i>Select a unit</i>');
		
		modal.find('.itemHealthStatus')
			.removeClass('text-success').removeClass('text-warning').removeClass('text-danger')
			.removeClass('font-weight-bold');
		modal.find('.publicStatus')
			.removeClass('text-success').removeClass('text-danger')
			.removeClass('font-weight-bold')
	}

	function populateCheckoutModalItemFields(id, location, notes, healthStatus, isPublic) {
		const modal = $('#handoutModal');

		modal.find('.itemID').text(`Unit ${id}`);
		modal.find('.itemLocation').text(location);
		modal.find('.itemNotes').text(notes);

		modal.find('.itemHealthStatus').text(healthStatus);
		switch (healthStatus) {
			case 'Fully Functional':
				modal.find('.itemHealthStatus')
					.addClass('text-success').removeClass('text-warning').removeClass('text-danger')
					.removeClass('font-weight-bold');
				break;

			case 'Partially Functional':
				modal.find('.itemHealthStatus')
					.addClass('text-warning').removeClass('text-success').removeClass('text-danger')
					.addClass('font-weight-bold');
				break;
			
			default:
				modal.find('.itemHealthStatus')
					.addClass('text-danger').removeClass('text-success').removeClass('text-warning')
					.addClass('font-weight-bold');
		}

		if (isPublic)
			modal.find('.publicStatus').text('Publicly listed')
				.addClass('text-success').removeClass('text-danger')
				.removeClass('font-weight-bold');
		else
			modal.find('.publicStatus').text('Not publicly listed')
				.addClass('text-danger').removeClass('text-success')
				.addClass('font-weight-bold');
	}

	// Populates the return modal with equipment and user details
	$('#returnModal').on('show.bs.modal', event => {
		const modal = $('#returnModal'), button = $(event.relatedTarget);
		const checkoutId = button.data('checkout-id'),
			equipmentName = button.data('equipment-name'),
			equipmentNotes = button.data('equipment-notes'),
			equipmentParts = button.data('equipment-parts'),
			equipmentReturnCheck = button.data('equipment-return-check'),
			userName = button.data('user-name'),
			userOnid = button.data('user-onid'),
			userEmail = button.data('user-email'),
			itemId = button.data('item-id'),
			itemLocation = button.data('item-location'),
			itemHealth = button.data('item-health');

		modal.find('.equipmentName2').text(equipmentName);
		modal.find('.equipmentNotes').text(equipmentNotes);
		modal.find('.equipmentParts').html(equipmentParts);
		modal.find('.equipmentReturnCheck').text(equipmentReturnCheck);
		modal.find('.userName').text(userName);
		modal.find('.userOnid').text(userOnid);
		modal.find('.userEmail').text(userEmail);
		modal.find('.itemID').text(`Unit ${itemId}`);
		modal.find('#returnCheckoutID').val(checkoutId);
		modal.find('#returnLocation').val(itemLocation);
		modal.find('#returnHealth option').prop('selected', false);
		modal.find('#returnHealth option').filter(function() {console.log($(this).text()); return $(this).text() == itemHealth}).prop('selected', true);
		modal.find('#returnNotes').val('');
	});

	
	// 
	// Action handlers
	// 
	
	function reserveEquipment() {
		const user = $('#user').val();
		const equipment = $('#equipment').val();
		
		const content = {
			action: 'createReservation',
			userID: user,
			equipmentID: equipment
		}
		
		api.post('/equipment-checkout.php', content).then(res => {
			snackbar(res.message, 'info');
		}).catch(err => {
			snackbar(err.message, 'error');
		});
	}

	function cancelReservation(reservationId) {
		let res = confirm('You are about to cancel a reservation. This action cannot be undone.');
		if(!res) return false;
		
		let data = {
			action: 'cancelReservation',
			reservationID: reservationId
		};
		api.post('/equipment-checkout.php', data).then(res => {
			$(`#reservation${reservationId}`).remove();
			snackbar(res.message, 'success');
		}).catch(err => {
			snackbar(err.message, 'error');
		});
	}

	function handoutEquipment() {
		const userId = $('#handoutUserId').val(),
			itemId = $('#handoutItemSelect').val(),
		 	reservationId = $('#handoutReservationId').val()
			dateDue = $('#handoutDueDate').val();

		const content = {
			action: 'checkoutEquipment',
			userID: userId,
			itemID: itemId,
			reservationID: reservationId,
			dateDue
		};

		api.post('/equipment-checkout.php', content).then(res => {
			snackbar(res.message, 'info');
		}).catch(err => {
			snackbar(err.message, 'error');
		});
	}

	function returnEquipment() {
		const checkoutId = $('#returnCheckoutID').val(),
			returnLocation = $('#returnLocation').val(),
		 	returnHealth = $('#returnHealth').val(),
			returnNotes = $('#returnNotes').val();

		const content = {
			action: 'returnEquipment',
			checkoutID: checkoutId,
			location: returnLocation,
			healthStatus: returnHealth,
			notes: returnNotes
		};

		api.post('/equipment-checkout.php', content).then(res => {
			snackbar(res.message, 'info');
		}).catch(err => {
			snackbar(err.message, 'error');
		});
	}
</script>

<?php include_once PUBLIC_FILES . '/modules/footer.php'; ?>

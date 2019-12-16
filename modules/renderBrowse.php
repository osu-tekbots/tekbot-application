<?php
use Util\Security;
use DataAccess\UsersDao;
use DataAccess\EquipmentDao;
use DataAccess\EquipmentReservation;
use DataAccess\EquipmentCheckoutDao;

include_once PUBLIC_FILES . "/modules/button.php";

// Keeps track of how many listings have been generated. This is available globally.
$numListingsCreated = 0;

function renderEmployeeEquipmentList($equipments) {
    global $numListingsCreated;

    if(!$equipments || count($equipments) == 0) {
		return;
    }
    $count = count($equipments);


    foreach ($equipments as $e){
        $equipmentID = $e->getEquipmentID();
        $name = Security::HtmlEntitiesEncode($e->getEquipmentName());
        if (strlen($name) > 60) {
            // Restrict the name length
            $name = substr($name, 0, 60) . "...";
        }
        $location = Security::HtmlEntitiesEncode($e->getLocation());
        $health = $e->getHealthID()->getName();
        renderEmployeeEquipmentItem($equipmentID, $name, $location, $health);
       
    }
  
    $count++;

}

function renderUserCheckoutFeesOwedTable($fees){
    global $dbConn, $logger;
    $equipmentCheckoutDao = new EquipmentCheckoutDao($dbConn, $logger);
    
    echo"
    <table>
        <tr>
            <th>Related Checkout</th>
            <th>Notes</th>
            <th>Amount</th>
            <th>Actions</th>
        </tr>
    ";
    foreach ($fees as $f){
        $checkoutID = $f->getCheckoutID();
        $checkout = $equipmentCheckoutDao->getCheckout($checkoutID);
        $feeNotes = $f->getNotes();
        $feeAmount = $f->getAmount();
        $feeCreated = $f->getDateCreated();
        renderEquipmentReturnModal($checkout);

        echo "
        <tr>
            <td><a href='' data-toggle='modal' 
            data-target='#newReturnModal$checkoutID'>Checkout</a></td>
            <td>$feeNotes</td>
            <td>$feeAmount</td>
            <td></td>
        </tr>
        ";


    }

    echo "</table>";

}



function renderEmployeeEquipmentItem($equipmentID, $name, $location, $health){
    $healthColor = ($health == 'Broken') ? 'red' : (($health == 'Partial Functionality') ? '#ffcc00' : 'green');
    $viewButton = createLinkButton("pages/viewEquipment.php?id=$equipmentID", 'View');
    $editButton = createLinkButton("pages/editEquipment.php?id=$equipmentID", 'Edit');

    echo "
    <div class='container'>
        <div class='row equipmentItemOutline'>
            <div class='col-3'>
                <img class='equipmentPreviewImage' src='../tekbot/assets/img/loginImage.jpg'>
            </div>
            <div class='col textColumn'>
                <p class='equipmentName'>$name</p> 
                <p>Health: <div style='$healthColor'>$health</div></p>
                <p>$location</p>
                $viewButton
                $editButton
            </div>
        </div>
    </div>
    ";
}


function renderEquipmentList($equipments, $isLoggedIn){
    global $dbConn, $logger;
    global $numListingsCreated;
    if(!$equipments || count($equipments) == 0) {
		return;
    }
    $count = count($equipments);
    $equipmentReservationDao = new EquipmentReservationDao($dbConn, $logger);

    foreach ($equipments as $e){
        $equipmentID = $e->getEquipmentID();
        $isAvailable = $equipmentReservationDao->getEquipmentAvailableStatus($equipmentID);
        $name = Security::HtmlEntitiesEncode($e->getEquipmentName());
        if (strlen($name) > 60) {
            // Restrict the name length
            $name = substr($name, 0, 60) . "...";
        }
        $health = $e->getHealthID()->getName();
        $description = Security::HtmlEntitiesEncode($e->getDescription());
        if (strlen($description) > 318) {
            // Restrict the description length
            $description = substr($description, 0, 318) . "...";
        }

        renderEquipmentItem($equipmentID, $name, $description, $health, $isLoggedIn);
        $count++;

    }

}

function renderEquipmentItem($equipmentID, $name, $description, $health, $isLoggedIn){
    $healthColor = ($health == 'Broken') ? 'red' : (($health == 'Partial Functionality') ? '#ffcc00' : 'green');


    $viewButton = createLinkButton("pages/viewEquipment.php?id=$equipmentID", 'View');
    if ($isLoggedIn){
        // Check to see if item can be reserved or not
            $reserveButton = '
            <button class="btn btn-outline-primary capstone-nav-btn" type="button" data-toggle="modal" 
            data-target="#newReservationModal" id="openNewApplicationModalBtn">
            Reserve
            </button>
            ';
    } else {
        $reserveButton = '';
    }

    echo "
    <div class='container'>
        <div class='row equipmentItemOutline' style='box-shadow: 2px 2px ;'>
            <div class='col-3'>
                <img class='equipmentPreviewImage' src='../tekbot/assets/img/loginImage.jpg'>
            </div>
            <div class='col textColumn'>
                <p class='equipmentName'>$name</p> 
                <p class='equipmentStatusHealth'>Health: <div style='color:$healthColor; display:inline;'>  $health</div></p>
                <p class='equipmentAvailability'>Status: <div style='color:$statusColor; display:inline;'>  $status</div></p>
                <p>$description</p>
                $viewButton
            </div>
        </div>
    </div>
    ";

}


function createReservationCancelButton($reservationID, $listNumber){
    // $checkoutID = $checkout->getCheckoutID();
    // $reservationID = $reservation->getReservationID();
    
    return "
	<button class='btn btn-outline-danger deleteProjectBtn' id='cancelReservationBtn$reservationID' type='button'>
		Cancel
	</button>
	
    <script type='text/javascript'>
        
		$('#cancelReservationBtn$reservationID').on('click', function() {
			let res = confirm('You are about to cancel a reservation. This action cannot be undone.');
			if(!res) return false;
            let reservationID = '$reservationID';
			let data = {
				action: 'cancelReservation',
                reservationID: reservationID
			};
			api.post('/equipmentrental.php', data).then(res => {
                $('#activeReservation$listNumber').remove();
                snackbar(res.message, 'success');
			}).catch(err => {
				snackbar(err.message, 'error');
			});
		});
	</script>
	";
}

function createPayButton($feeID){
    return "
    <button class='btn btn-outline-primary capstone-nav-btn' type='button' data-toggle='modal' 
    data-target='#feeModal$feeID' id='openNewApplicationModalBtn'>
    Pay
    </button>
	
    <script type='text/javascript'>
        
		$('#markPaid$feeID').on('click', function() {
			let res = confirm('You are about to mark this fee as paid. This will be verified by an employee. Would you like to continue?');
			if(!res) return false;
            let feeID = '$feeID';
            let touchnetID = $('#touchnetID$feeID').val();
            if(touchnetID == '')
            {
                alert('You did not input the touchnetID');
                return;
            }
			let data = {
				action: 'payEquipmentFees',
                feeID: feeID,
                touchnetID: touchnetID
			};
			api.post('/equipmentrental.php', data).then(res => {
                snackbar(res.message, 'success');
                setTimeout(function(){
                    window.location.reload(1);
                 }, 2000);
			}).catch(err => {
				snackbar(err.message, 'error');
			});
		});
	</script>
	";
}



?>
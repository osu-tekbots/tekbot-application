<?php
use Util\Security;
use DataAccess\QueryUtils;
use DataAccess\UsersDao;
use DataAccess\EquipmentDao;
use DataAccess\EquipmentCheckoutDao;
use DataAccess\EquipmentReservationDao;
use DataAccess\EquipmentFeeDao;
use DataAccess\ContractDao;
use Model\EquipmentReservation;
use Model\EquipmentFee;

/**
 * Renders the HTML for the modal that will start the handout process for handing out an equipment to a student
 * project.
 *
 * @param \Model\CapstoneProject $project
 * @return void
 */
function renderNewHandoutModal($reservation) {
	global $dbConn, $logger;
	$userDao = new UsersDao($dbConn, $logger);
	$equipmentDao = new EquipmentDao($dbConn, $logger);
	$contractDao = new ContractDao($dbConn, $logger);
	
	$hours = QueryUtils::timeAddToCurrent("24:00:00");
	$reservationID = $reservation->getReservationID();
	$equipmentID = $reservation->getEquipmentID();
	$userID = $reservation->getUserID();
	$contracts = $contractDao->getEquipmentCheckoutContracts();
	
	$user = $userDao->getUserByID($userID);
	$equipment = $equipmentDao->getEquipment($equipmentID);

	$equipmentName = Security::HtmlEntitiesEncode($equipment->getEquipmentName());
	$equipmentLocation = Security::HtmlEntitiesEncode($equipment->getLocation());
	$equipmentNotes = Security::HtmlEntitiesEncode($equipment->getNotes());
	$equipmentHealth = $equipment->getHealthID()->getName();

	$email = Security::HtmlEntitiesEncode($user->getEmail());
	$name = Security::HtmlEntitiesEncode($user->getFirstName()) 
	. ' ' 
	. Security::HtmlEntitiesEncode($user->getLastName());
	$phoneNumber = Security::HtmlEntitiesEncode($user->getPhone()); 
	$onid = Security::HtmlEntitiesEncode($user->getOnid());

    echo "
		<div class='modal fade' id='newHandoutModal$reservationID'>
			<br><br><br><br>
		<div class='modal-dialog modal-lg'>
			<div class='modal-content'>

					<!-- Modal Header -->
					<div class='modal-header'>
					<h4 class='modal-title'>Hand out $equipmentName to $name</h4>
					<button type='button' class='close' data-dismiss='modal'>&times;</button>
					</div>
		
					<!-- Modal body -->
					<div class='modal-body'>
						<h4 id='projectNameApplicationHeader'>$equipmentName</h4>
						<p><b>Location:</b> $equipmentLocation</p>
						<p><b>Health:</b> $equipmentHealth</p>
						";
						if (!empty($equipmentNotes)){
							echo "<p><b>Notes:</b> $equipmentNotes</p>";
						}
						echo "
						<h4 id='projectNameApplicationHeader'>$name</h4>
						<p><b>ONID:</b> $onid</p>
						<p><b>Email:</b> $email</p>
						<br>
						<select class='contract' id='$reservationID'>";
							foreach($contracts as $c){
								$contractID = $c->getContractID();
								$contractTitle = $c->getTitle();
								echo "<option value='$contractID'>$contractTitle</option>";
							}
						echo "
						</select>
						<h5>Deadline Time:  <div style='display:inline-block;' id='deadline$reservationID'>$hours</div></h5>
						<h6 class='text-secondary'>Weekends are accounted for, holidays are not.</h6>
					</div>

					<!-- Modal footer -->
					<div class='modal-footer'>
					<button type='button' class='btn btn-success' data-dismiss='modal' id='handoutEquipmentBtn$reservationID'>Handout</button>
					<button type='button' class='btn btn-secondary' data-dismiss='modal'>Cancel</button>
					</div>

				</div>
			</div>
		</div>

	";
}


/**
 * Renders the HTML for the modal that will start the handin process for turning back in an equipment
 * project.
 *
 * @param \Model\CapstoneProject $project
 * @return void
 */
function renderEquipmentReturnModal($checkout) {
	global $dbConn, $logger;
	$userDao = new UsersDao($dbConn, $logger);
	$equipmentDao = new EquipmentDao($dbConn, $logger);
	$reservationDao = new EquipmentReservationDao($dbConn, $logger);
	
	$checkoutID = $checkout->getCheckoutID();
	$reservationID = $checkout->getReservationID();
	$reservation = $reservationDao->getReservation($reservationID);
	$equipmentID = $reservation->getEquipmentID();
	$userID = $checkout->getUserID();
	$checkoutNotes = $checkout->getNotes();

	$user = $userDao->getUserByID($userID);
	$equipment = $equipmentDao->getEquipment($equipmentID);

	$equipmentName = Security::HtmlEntitiesEncode($equipment->getEquipmentName());
	$equipmentLocation = Security::HtmlEntitiesEncode($equipment->getLocation());
	$equipmentNotes = Security::HtmlEntitiesEncode($equipment->getNotes());
	$equipmentHealth = $equipment->getHealthID()->getName();

	$equipmentReturnCheck = Security::HtmlEntitiesEncode($equipment->getReturnCheck());
	$equipmentPartList = Security::HtmlEntitiesEncode($equipment->getPartList());
	$equipmentNumberParts = Security::HtmlEntitiesEncode($equipment->getNumberParts());


	$email = Security::HtmlEntitiesEncode($user->getEmail());
	$name = Security::HtmlEntitiesEncode($user->getFirstName()) 
	. ' ' 
	. Security::HtmlEntitiesEncode($user->getLastName());
	$phoneNumber = Security::HtmlEntitiesEncode($user->getPhone()); 
	$onid = Security::HtmlEntitiesEncode($user->getOnid());

	$buttons = "<button type='button' class='btn btn-success' data-dismiss='modal' id='returnEquipmentBtn$checkoutID'>Return</button>
	<button type='button' class='btn btn-secondary' data-dismiss='modal'>Cancel</button>";
	$readonly = "";
	$title = "Take back $equipmentName from $name";


    echo "
		<div class='modal fade' id='newReturnModal$checkoutID'>
			<br><br><br><br>
		<div class='modal-dialog modal-lg'>
			<div class='modal-content'>

					<!-- Modal Header -->
					<div class='modal-header'>
					<h4 class='modal-title'>$title</h4>
					<button type='button' class='close' data-dismiss='modal'>&times;</button>
					</div>
		
					<!-- Modal body -->
					<div class='modal-body'>
						<h4 id='projectNameApplicationHeader'>$equipmentName</h4>
						<p><b>Location:</b> $equipmentLocation</p>
						<p><b>Health:</b> $equipmentHealth</p>";
						if (!empty($equipmentNotes)){
							echo "<p><b>Equipment Notes:</b> $equipmentNotes</p>";
						}
						echo "
						<p><b>Equipment Parts List:</b> $equipmentPartList</p>
						<p><b>Number of parts:</b> $equipmentNumberParts</p>
						<p><b>Return Steps:</b><br> $equipmentReturnCheck</p>

						<h4 id='projectNameApplicationHeader'>$name</h4>
						<p><b>ONID:</b> $onid</p>
						<p><b>Email:</b> $email</p>
						<h5>Checkout Notes:</h5>
						<textarea id='checkoutNotes$checkoutID' rows='4' cols='50' $readonly>$checkoutNotes</textarea>
					</div>

					<!-- Modal footer -->
					<div class='modal-footer'>
					$buttons
					</div>

				</div>
			</div>
		</div>

	";
}

function renderEquipmentFeesModal($checkout) {
	global $dbConn, $logger;
	$equipmentFeeDao = new EquipmentFeeDao($dbConn, $logger);
	$equipmentDao = new EquipmentDao($dbConn, $logger);
	$reservationDao = new EquipmentReservationDao($dbConn, $logger);
	$checkoutID = $checkout->getCheckoutID();

	$fee = $equipmentFeeDao->getEquipmentFeeWithCheckoutID($checkoutID);
	if (!empty($fee)){
		$feeAmount = $fee->getAmount();
		$feeNotes = $fee->getNotes();
		// Check if fee is paid or not
		if ($fee->getIsPaid() == 1){
			$feeStatus = "<h4 style='color: green;'>FEE ASSIGNED - PAID</h4>";
			
		} else {
			$feeStatus = "<h4 style='color: red;'>FEE ASSIGNED - NOT YET PAID</h4>";
		}
		$readonly = "readonly";
		$buttons = "<button type='button' class='btn btn-secondary' data-dismiss='modal'>Close</button>";
	}
	else {
		$feeStatus = "";
		$readonly = "";
		$feeAmount = "0.00";
		$feeNotes = "";
		$buttons = "<button type='button' class='btn btn-success' data-dismiss='modal' id='assignEquipmentFees$checkoutID'>Assign Fee</button>
		<button type='button' class='btn btn-secondary' data-dismiss='modal'>Cancel</button>";
	}

	$reservationID = $checkout->getReservationID();
	$reservation = $reservationDao->getReservation($reservationID);
	$equipmentID = $reservation->getEquipmentID();
	$userID = $checkout->getUserID();


    echo "
		<div class='modal fade' id='newFeeModal$checkoutID'>
			<br><br><br><br>
		<div class='modal-dialog modal-lg'>
			<div class='modal-content'>

					<!-- Modal Header -->
					<div class='modal-header'>
					<h4 class='modal-title'>Assign Fees For Checkout</h4>
					<button type='button' class='close' data-dismiss='modal'>&times;</button>
					</div>
		
					<!-- Modal body -->
					<div class='modal-body'>
						$feeStatus
						<h4 id='projectNameApplicationHeader'>Fee Assignment Guidelines</h4>
						<p><b>First Late Return:</b> Warning</p>
						<p><b>Consistent Late Return:</b> $5 per late day</p>
						<p><b>Broken Item:</b> Cost of the item </p>
						<p><b>Broken Part:</b> Cost of the Part </p>

						<b style='color:red;'>*Both fields are required when assigning a fee</b>
						<h5>Amount Owed ($)</h5>
						<input id='feeAmount$checkoutID' type='number' value='$feeAmount' step='0.1' $readonly>
						<br>
						<h5>Fee Notes (What are they paying for):</h5>
						<textarea id='feeNotes$checkoutID' rows='4' cols='50' $readonly >$feeNotes</textarea>
					</div>

					<!-- Modal footer -->
					<div class='modal-footer'>
					$buttons
					</div>

				</div>
			</div>
		</div>

	";
}

function renderViewCheckoutModal($checkout){
	global $dbConn, $logger;
	$userDao = new UsersDao($dbConn, $logger);
	$equipmentDao = new EquipmentDao($dbConn, $logger);
	$reservationDao = new EquipmentReservationDao($dbConn, $logger);

	// Update for view
	$checkoutID = $checkout->getCheckoutID();
	$reservationID = $checkout->getReservationID();
	$reservation = $reservationDao->getReservation($reservationID);
	$equipmentID = $reservation->getEquipmentID();
	$userID = $checkout->getUserID();
	$contractID = $checkout->getContractID();


	$checkoutNotes = $checkout->getNotes();
	$dc = $checkout->getDateCreated();
	$dateCreated = $dc->format(QueryUtils::DATE_STR);
	$dateReturned = $checkout->getReturnTime();
	
	$dateUpdated = $checkout->getDateUpdated();
	
	$deadlineTime = $checkout->getDeadlineTime();

	$status = $checkout->getStatusID()->getName();

	$user = $userDao->getUserByID($userID);
	$equipment = $equipmentDao->getEquipment($equipmentID);

	$equipmentName = Security::HtmlEntitiesEncode($equipment->getEquipmentName());

	$name = Security::HtmlEntitiesEncode($user->getFirstName()) 
	. ' ' 
	. Security::HtmlEntitiesEncode($user->getLastName());
	$email = Security::HtmlEntitiesEncode($user->getEmail());
	$onid = Security::HtmlEntitiesEncode($user->getOnid());

	$buttons = "<button type='button' class='btn btn-secondary' data-dismiss='modal'>Close</button>";
	$title = "Checkout Details: $checkoutID";


    echo "
		<div class='modal fade' id='viewCheckoutModal$checkoutID'>
			<br><br><br><br>
		<div class='modal-dialog modal-lg'>
			<div class='modal-content'>

					<!-- Modal Header -->
					<div class='modal-header'>
					<h4 class='modal-title'>$title</h4>
					<button type='button' class='close' data-dismiss='modal'>&times;</button>
					</div>
		
					<!-- Modal body -->
					<div class='modal-body'>
						<h4 id='projectNameApplicationHeader'>$equipmentName</h4>
						<p><b>Checked Out On: </b> $dateCreated</p>
						<p><b>Number of Contract Days: </b> $contractID</p>
						<p><b>Deadline Time: </b> $deadlineTime</p>
						<p><b>Return Time: </b> $dateReturned</p>
						<p><b>Status: </b> $status</p>
					";
					if (!empty($checkoutNotes)){
						echo "<p><b>Checkout Notes: </b> $checkoutNotes</p>";
					}
					echo"	
						
						<h4 id='projectNameApplicationHeader'>Renter: $name</h4>
						<p><b>ONID:</b> $onid</p>
						<p><b>Email:</b> $email</p>
					</div>

					<!-- Modal footer -->
					<div class='modal-footer'>
					$buttons
					</div>

				</div>
			</div>
		</div>

	";
}


function renderApproveEquipmentFeeModal($fee){
	global $dbConn, $logger;
	$equipmentFeeDao = new EquipmentFeeDao($dbConn, $logger);
	$userDao = new UsersDao($dbConn,$logger);
	
	$feeID = $fee->getFeeID();

	$fee = $equipmentFeeDao->getEquipmentFee($feeID);
	if (!empty($fee)){
		$userID = $fee->getUserID();
		$feeAmount = $fee->getAmount();
		$paymentInfo = $fee->getPaymentInfo();
		$feeNotes = $fee->getNotes();
		$buttons = "<button type='button' class='btn btn-success' data-dismiss='modal' id='approveEquipmentFees$feeID'>Approve</button>
		<button type='button' class='btn btn-danger' data-dismiss='modal' id='denyEquipmentFees$feeID'>Reject</button>
		<button type='button' class='btn btn-secondary' data-dismiss='modal'>Cancel</button>";
		$user = $userDao->getUserByID($userID);
		$name = Security::HtmlEntitiesEncode($user->getFirstName()) 
		. ' ' 
		. Security::HtmlEntitiesEncode($user->getLastName());

		echo "
		<div class='modal fade' id='verifyFeeModal$feeID'>
		<br><br><br><br>
		<div class='modal-dialog modal-lg'>
			<div class='modal-content'>

					<!-- Modal Header -->
					<div class='modal-header'>
					<h4 class='modal-title'>Approve Fee Payment</h4>
					<button type='button' class='close' data-dismiss='modal'>&times;</button>
					</div>
		
					<!-- Modal body -->
					<div class='modal-body'>
						<h4 id='projectNameApplicationHeader'>Process Touchnet Order For $name</h4>
						<p><b>Payment Information: </b> $paymentInfo</p>

						<b style='color:red;'>*Make sure amount in touchnet matches the amount listed here!</b>
						<p><b>Price: </b> $feeAmount</p>
						
						<h5>Fee Notes (Update If Necessary):</h5>
						<textarea id='feeNotes$feeID' rows='4' cols='50' >$feeNotes</textarea>
					</div>

					<!-- Modal footer -->
					<div class='modal-footer'>
					$buttons
					</div>

				</div>
			</div>
		</div>

	";
	}  
}

function renderPayFeeModal($fee){
	$feeID = $fee->getFeeID();

	if (!empty($fee)){
		$userID = $fee->getUserID();
		$feeAmount = $fee->getAmount();
		$paymentInfo = $fee->getPaymentInfo();
		$feeNotes = $fee->getNotes();

		$buttons = "<button type='button' class='btn btn-success' data-dismiss='modal' id='markPaid$feeID'>Mark as Paid</button>
		<button type='button' class='btn btn-secondary' data-dismiss='modal'>Cancel</button>";

		echo "
		<div class='modal fade' id='feeModal$feeID'>
		<br><br><br><br>
		<div class='modal-dialog modal-lg'>
			<div class='modal-content'>

					<!-- Modal Header -->
					<div class='modal-header'>
					<h4 class='modal-title'>Approve Fee Payment</h4>
					<button type='button' class='close' data-dismiss='modal'>&times;</button>
					</div>
		
					<!-- Modal body -->
					<div class='modal-body'>
						<h4 id='projectNameApplicationHeader'>Fee Information</h4>
						<p><b>Notes:</b> $feeNotes</p>
						

						<h4>Pay for your fee at the following link:</h4> <a target='_blank'href='https://secure.touchnet.net/C20159_ustores/web/classic/product_detail.jsp?PRODUCTID=2387'>https://secure.touchnet.net/C20159_ustores/web/classic/product_detail.jsp?PRODUCTID=2387</a><br>
						<ol>
							<li>In the Quantity, put equal to the amount that you have been charged.  For example, if you are charged $15, put 15 in the quantity.</li>
							<b style='color:red;'>*Make sure the quantity in touchnet matches the one here!</b>
							<p><b>Price: </b>$$feeAmount | Quantity: $feeAmount</p>
							<li>Hit 'Add to Cart'</li>
							<li>Hit 'Checkout'</li>
							<li>Go through the prompts and fill out your phone and email and additional information needed.</li>
							<li>After filling out your card information and hitting 'Continue', hit 'Submit Order'</li>
							<li>Right under the Print and Continue Shopping buttons, you will see an order number.  Put that order number into the form here</li>
						</ol>

				
						
						<h5>After paying, please input the touchnet Order # here<b style='color:red;'>*</b></h5>
						<textarea id='touchnetID$feeID' rows='1' cols '20'></textarea>

					</div>

					<!-- Modal footer -->
					<div class='modal-footer'>
					$buttons
					</div>

				</div>
			</div>
		</div>

	";
	}
}


?>

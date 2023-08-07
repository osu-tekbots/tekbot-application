<?php
include_once '../bootstrap.php';

use DataAccess\EquipmentDao;
use DataAccess\EquipmentCheckoutDao;
use DataAccess\EquipmentReservationDao;
use DataAccess\UsersDao;
use DataAccess\QueryUtils;
use Model\EquipmentCheckoutStatus;
use Util\Security;
use Email\TekBotsMailer;

if (!session_id()) {
    session_start();
}

// Make sure the user is logged in and allowed to be on this page
include_once PUBLIC_FILES . '/lib/shared/authorize.php';


$title = 'Cronjob';
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


$equipmentDao = new EquipmentDao($dbConn, $logger);
$userDao = new UsersDao($dbConn, $logger);
$checkoutDao = new EquipmentCheckoutDao($dbConn, $logger);
$reservationDao = new EquipmentReservationDao($dbConn, $logger);
$reservedEquipment = $reservationDao->getReservationsForAdmin();
$checkedoutEquipment = $checkoutDao->getCheckoutsForAdmin();
$messageDao = new MessageDao($dbConn, $logger);
$mailer = new EquipmentRentalMailer($configManager->get('email.from_address'), $configManager->get('email.subject_tag'));

$reservedHTML = '';
$listNumber = 0;
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


		$user = $r->getUser(); 
        $email = Security::HtmlEntitiesEncode($user->getEmail());
        $name = Security::HtmlEntitiesEncode($user->getFirstName()) 
        . ' ' 
		. Security::HtmlEntitiesEncode($user->getLastName());

		// If active
		if ($isActive == 1){
			if (QueryUtils::isLate($latestPickupTime)){
				// Mark as inactive
				$r->setIsActive(FALSE);
				$ok = $reservationDao->updateReservation($r);
			} 
		} 

}

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

	// If checked out
	if ($statusID == "Checked Out"){
		if (QueryUtils::isLate($latestPickupTime)){
			$c->setStatusID(EquipmentCheckoutStatus::LATE);
			$ok = $checkoutDao->updateCheckout($c);
			if ($ok){
				$mailer->sendEquipmentLateEmail($c, $user, $equipmentName);
			}
			// Expiration - send email - mark as inactive
			$notes = "Needs to expire";
		} else {
			$notes = "Active";
		}
	} else {
		$notes = "Already Returned";
	}




}



?>
<br/>
<div id="page-top">


<?php 
include_once PUBLIC_FILES . '/modules/footer.php' ; 
?>

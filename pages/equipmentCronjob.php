<?php
include_once '../bootstrap.php';

use DataAccess\EquipmentDao;
use DataAccess\EquipmentCheckoutDao;
use DataAccess\EquipmentReservationDao;
use DataAccess\UsersDao;
use DataAccess\QueryUtils;
use Model\EquipmentCheckoutStatus;
use Util\Security;
use Email\EquipmentRentalMailer;

$equipmentDao = new EquipmentDao($dbConn, $logger);
$userDao = new UsersDao($dbConn, $logger);
$checkoutDao = new EquipmentCheckoutDao($dbConn, $logger);
$reservationDao = new EquipmentReservationDao($dbConn, $logger);
$reservedEquipment = $reservationDao->getReservationsForAdmin();
$checkedoutEquipment = $checkoutDao->getCheckoutsForAdmin();
// $messageDao = new MessageDao($dbConn, $logger);
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
				// $ok = $reservationDao->updateReservation($r);
			} 
		} 

}

$emailsSent = 0;
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
				$ok = $mailer->sendEquipmentLateEmail($c, $user, $equipmentName);
				if($ok) 
					$emailsSent++;
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
<?php
include_once '../bootstrap.php';

use DataAccess\EquipmentCheckoutDao;
use DataAccess\EquipmentTypeDao;
use DataAccess\MessageDao;
use DataAccess\UsersDao;
use Email\TekBotsMailer;

$checkoutDao = new EquipmentCheckoutDao($dbConn, $logger);
$equipmentDao = new EquipmentTypeDao($dbConn, $logger);
$messageDao = new MessageDao($dbConn, $logger);
$userDao = new UsersDao($dbConn, $logger);

$mailer = new TekBotsMailer($configManager->getWorkerMaillist(), $configManager->getBounceEmail(), null, $logger);


$overdueEquipment = $checkoutDao->getLateCheckoutsForEmployee();
$overdueMessage = $messageDao->getMessageByID('vwbF4elQwhGP8TQm');

// Send a reminder email for each overdue equipment unit
$emailsSent = 0;
foreach ($overdueEquipment as $c){
	$user = $userDao->getUserByID($c->getUserID());
	$equipment = $equipmentDao->getEquipmentByUnitID($c->getUnitID());

	$ok = $mailer->sendEquipmentEmail($user, $c, $equipment, $overdueMessage);
	
	if ($ok) {
		$logger->info("Sent overdue equipment reminder email to {$user->getUserID()} for {$c->getUnitID()}");
		$emailsSent++;
	} else {
		$logger->error("Failed to send overdue equipment reminder email to {$user->getUserID()} for {$c->getUnitID()}");
	}
}

// `$emailsSent` is set as "return" value for `include`ing this script

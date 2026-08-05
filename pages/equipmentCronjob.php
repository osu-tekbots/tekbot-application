<?php
include_once '../bootstrap.php';

use DataAccess\EquipmentCheckoutDao;
use DataAccess\EquipmentDao;
use DataAccess\MessageDao;
use DataAccess\UsersDao;
use Email\TekBotsMailer;

$checkoutDao = new EquipmentCheckoutDao($dbConn, $logger);
$equipmentDao = new EquipmentDao($dbConn, $logger);
$messageDao = new MessageDao($dbConn, $logger);
$userDao = new UsersDao($dbConn, $logger);

$mailer = new TekBotsMailer($configManager->getWorkerMaillist(), $configManager->getBounceEmail(), null, $logger);


$overdueEquipment = $checkoutDao->getOverdueCheckoutsForAdmin();
$overdueMessage = $messageDao->getMessageByID('vwbF4elQwhGP8TQm');

// Send a reminder email for each overdue equipment item
foreach ($overdueEquipment as $c){
	$user = $userDao->getUserByID($c->getUserID());
	$equipment = $equipmentDao->getEquipmentByItemID($c->getItemID());

	$ok = $mailer->sendEquipmentEmail($user, $c, $equipment, $overdueMessage);
	
	if (!$ok) {
		$logger->error("Failed to send overdue equipment reminder email to {$user->getUserID()} for {$c->getItemID()}");
	}
}

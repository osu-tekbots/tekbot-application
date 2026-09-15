<?php
include_once '../bootstrap.php';

use DataAccess\EquipmentCheckoutDao;
use DataAccess\EquipmentTypeDao;
use DataAccess\InventoryDao;
use DataAccess\MessageDao;
use DataAccess\UsersDao;
use Email\TekBotsMailer;

$checkoutDao = new EquipmentCheckoutDao($dbConn, $logger);
$equipmentDao = new EquipmentTypeDao($dbConn, $logger);
$inventoryDao = new InventoryDao($dbConn, $logger);
$messageDao = new MessageDao($dbConn, $logger);
$userDao = new UsersDao($dbConn, $logger);

$mailer = new TekBotsMailer($configManager->getWorkerMaillist(), $configManager->getBounceEmail(), null, $logger);


// //////////////////////////////////////////////////////////////////////////////////// //
// ////////////////////////////////// PUBLIC FUNCTIONS //////////////////////////////// //
// //////////////////////////////////////////////////////////////////////////////////// //


/**
 * Sends automatic reminder emails if they haven't been sent yet today
 * 
 * @return int|bool How many reminder emails were sent or false if emails were already sent today
 */
function sendCronEmailsIfNeeded($checkoutDao, $configurationDao, $equipmentDao, $messageDao, $userDao, $configManager, $mailer, $logger) {
    $configuration = $configurationDao->getConfiguration();

    // Don't do anything if emails were already sent today
    if (checkDaysSinceCronEmails($configuration, $configManager)) {
        return false;
    }

    $emailsSent = sendOverdueEquipmentEmails($checkoutDao, $equipmentDao, $messageDao, $userDao, $mailer, $logger);

    // Update last email sent time
    $configuration->setLastCronEmailTime(new DateTime());
    $configurationDao->updateConfiguration($configuration);
    
    return $emailsSent;
}


/**
 * Deletes any carts that haven't been accessed for 90 days, if that hasn't already
 * happened today
 * 
 * @return int|false How many carts were purged or false if carts were already purged today
 */
function purgeOldCartsIfNeeded($configurationDao, $inventoryDao, $configManager) {
    $configuration = $configurationDao->getConfiguration();

    // Don't do anything if carts were already purged today
    if (checkDaysSinceCartPurge($configuration, $configManager)) {
        return false;
    }
    
    $cartsPurged = $inventoryDao->deleteOldCarts();

    // Update last cart purge time
    $configuration->setLastCartPurgeTime(new DateTime());
    $configurationDao->updateConfiguration($configuration);
    
    return $cartsPurged;
}


// //////////////////////////////////////////////////////////////////////////////////// //
// //////////////////////////////// PRIVATE FUNCTIONS ///////////////////////////////// //
// //////////////////////////////////////////////////////////////////////////////////// //

/**
 * Uses the ConfigurationDao to check when the last cron emails were sent, & if it's time to send more
 * 
 * @param Model\Configuration $configuration  The config object from the DB
 * 
 * @return bool If emails still need to be sent today
 */
function checkDaysSinceCronEmails($configuration, $configManager) {
    $today = new DateTime("today");
    $lastSent = new DateTime($configuration->getLastCronEmailTime() ?? '0-0-0 0:0:0');
    $lastSent->setTime(0, 0, 0); // Set time part to midnight for accurate comparison with `DateTime("today")`

    $daysSinceLastSent = (int) $lastSent->diff($today)->format("%R%a");

    return $daysSinceLastSent < (int)$configManager->get('email.cron_frequency');
}

/**
 * Uses the ConfigurationDao to check when the last cart purge happened, & if it's time to send more
 * 
 * @param Model\Configuration $configuration  The config object from the DB
 * 
 * @return bool If carts still need to be purged today
 */
function checkDaysSinceCartPurge($configuration, $configManager) {
    $today = new DateTime("today");
    $lastPurge = new DateTime($configuration->getLastCartPurgeTime() ?? '0-0-0 0:0:0');
    $lastPurge->setTime(0, 0, 0); // Set time part to midnight for accurate comparison with `DateTime("today")`

    $daysSinceLastPurge = (int) $lastPurge->diff($today)->format("%R%a");

    return $daysSinceLastPurge < (int)$configManager->get('email.cron_frequency');
}


function sendOverdueEquipmentEmails($checkoutDao, $equipmentDao, $messageDao, $userDao, $mailer, $logger) {
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
    
    return $emailsSent;
}

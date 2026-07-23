<?php
/**
 * This page handles client requests to modify or fetch projecgt-related data. All requests made to this page should
 * be a POST request with a corresponding `action` field in the request body.
 */
include_once '../bootstrap.php';


use Api\Response;
use DataAccess\EquipmentCheckoutDaoOld;
use DataAccess\EquipmentReservationDaoOld;
use DataAccess\UsersDao;
use DataAccess\ContractDao;
use DataAccess\EquipmentFeeDaoOld;
use DataAccess\EquipmentDaoOld;
use Api\EquipmentRentalActionHandler;
use Email\TekBotsMailer;
use DataAccess\MessageDao;

if (PHP_SESSION_ACTIVE != session_status())
    session_start();

// Setup our data access and handler classes
$equipmentCheckoutDao = new EquipmentCheckoutDaoOld($dbConn, $logger);
$equipmentReservationDao = new EquipmentReservationDaoOld($dbConn, $logger);
$usersDao = new UsersDao($dbConn, $logger);
$contractDao = new ContractDao($dbConn, $logger);
$equipmentFeeDao = new EquipmentFeeDaoOld($dbConn, $logger);
$equipmentDao = new EquipmentDaoOld($dbConn, $logger);
$mailer = new TekBotsMailer($configManager->getWorkerMaillist(), $configManager->getBounceEmail());
$messageDao = new MessageDao($dbConn, $logger);

$handler = new EquipmentRentalActionHandler($equipmentCheckoutDao, $equipmentReservationDao, $contractDao, $usersDao, $equipmentFeeDao, $equipmentDao , $mailer, $configManager, $logger, $messageDao);

// Handle the request
$handler->handleRequest();

?>
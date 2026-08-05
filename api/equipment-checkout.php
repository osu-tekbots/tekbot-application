<?php
/**
 * This page handles client requests to modify or fetch project-related data. All requests made to this page should
 * be a POST request with a corresponding `action` field in the request body.
 */
include_once '../bootstrap.php';


use Api\EquipmentCheckoutActionHandler;
use Api\Response;
use DataAccess\EquipmentCheckoutDao;
use DataAccess\EquipmentHealthDao;
use DataAccess\EquipmentUnitDao;
use DataAccess\EquipmentTypeDao;
use DataAccess\MessageDao;
use DataAccess\UsersDao;
use Email\TekBotsMailer;

if (PHP_SESSION_ACTIVE != session_status())
    session_start();

// Setup our data access and handler classes
$equipmentCheckoutDao = new EquipmentCheckoutDao($dbConn, $logger);
$equipmentHealthDao = new EquipmentHealthDao($dbConn, $logger);
$equipmentUnitDao = new EquipmentUnitDao($dbConn, $logger);
$equipmentTypeDao = new EquipmentTypeDao($dbConn, $logger);
$messageDao = new MessageDao($dbConn, $logger);
$usersDao = new UsersDao($dbConn, $logger);
$mailer = new TekBotsMailer($configManager->getWorkerMaillist(), $configManager->getBounceEmail());

$handler = new EquipmentCheckoutActionHandler($equipmentCheckoutDao, $equipmentHealthDao, $equipmentUnitDao, $equipmentTypeDao, $messageDao, $usersDao, $mailer, $configManager, $logger);

// Handle the request
$handler->handleRequest();

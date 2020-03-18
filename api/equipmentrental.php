<?php
/**
 * This page handles client requests to modify or fetch projecgt-related data. All requests made to this page should
 * be a POST request with a corresponding `action` field in the request body.
 */
include_once '../bootstrap.php';


use Api\Response;
use DataAccess\EquipmentCheckoutDao;
use DataAccess\EquipmentReservationDao;
use DataAccess\UsersDao;
use DataAccess\ContractDao;
use DataAccess\EquipmentFeeDao;
use DataAccess\EquipmentDao;
use Api\EquipmentRentalActionHandler;
use Email\EquipmentRentalMailer;

session_start();

// Setup our data access and handler classes
$equipmentCheckoutDao = new EquipmentCheckoutDao($dbConn, $logger);
$equipmentReservationDao = new EquipmentReservationDao($dbConn, $logger);
$usersDao = new UsersDao($dbConn, $logger);
$contractDao = new ContractDao($dbConn, $logger);
$equipmentFeeDao = new EquipmentFeeDao($dbConn, $logger);
$equipmentDao = new EquipmentDao($dbConn, $logger);
$mailer = new EquipmentRentalMailer($configManager->get('email.from_address'), $configManager->get('email.subject_tag'));
$handler = new EquipmentRentalActionHandler($equipmentCheckoutDao, $equipmentReservationDao, $contractDao, $usersDao, $equipmentFeeDao, $equipmentDao , $mailer, $configManager, $logger);

// Authorize the request
if (isset($_SESSION['userID']) && !empty($_SESSION['userID'])) {
    // Handle the request
    $handler->handleRequest();
} else {
    $handler->respond(new Response(Response::UNAUTHORIZED, 'You do not have permission to access this resource'));
}

?>
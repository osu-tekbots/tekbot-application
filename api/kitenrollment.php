<?php
/**
 * This page handles client requests to modify or fetch projecgt-related data. All requests made to this page should
 * be a POST request with a corresponding `action` field in the request body.
 */
include_once '../bootstrap.php';

use Api\Response;
use DataAccess\KitEnrollmentDao;
use Api\KitEnrollmentActionHandler;
use Email\EquipmentRentalMailer;

session_start();

// Setup our data access and handler classes
$kitEnrollmentDao = new KitEnrollmentDao($dbConn, $logger);
$mailer = new EquipmentRentalMailer($configManager->get('email.from_address'), $configManager->get('email.subject_tag'));
$handler = new KitEnrollmentActionHandler($kitEnrollmentDao, $mailer, $configManager, $logger);

// Authorize the request
if (isset($_SESSION['userID']) && !empty($_SESSION['userID'])) {
    // Handle the request
    $handler->handleRequest();
} else {
    $handler->respond(new Response(Response::UNAUTHORIZED, 'You do not have permission to access this resource'));
}

?>
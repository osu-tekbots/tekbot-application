<?php
/**
 * This page handles client requests to modify or fetch project-related data. All requests made to this page should
 * be a POST request with a corresponding `action` field in the request body.
 */
include_once '../bootstrap.php';

use Api\Response;
use DataAccess\PrinterDao;
use DataAccess\PrinterFeeDao;
use DataAccess\UsersDao;
use Api\PrinterActionHandler;
use Email\PrinterMailer;

session_start();

// Setup our data access and handler classes
$printerDao = new PrinterDao($dbConn, $logger);
$printerFeeDao = new PrinterFeeDao($dbConn, $logger);
$userDao = new UsersDao($dbConn, $logger);
$mailer = new PrinterMailer($configManager->get('email.from_address'), $configManager->get('email.subject_tag'));
$handler = new PrinterActionHandler($printerDao, $printerFeeDao, $userDao, $mailer, $configManager, $logger);


// Authorize the request
if (isset($_SESSION['userID']) && !empty($_SESSION['userID'])) {
    // Handle the request
    $handler->handleRequest();
} else {
    $handler->respond(new Response(Response::UNAUTHORIZED, 'You do not have permission to access this resource'));
}

?>
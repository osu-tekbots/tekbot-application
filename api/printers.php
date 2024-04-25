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
use DataAccess\VoucherDao;
use Api\PrinterActionHandler;
use Email\TekBotsMailer;
use DataAccess\MessageDao;

session_start();

// Setup our data access and handler classes
$printerDao = new PrinterDao($dbConn, $logger);
$printerFeeDao = new PrinterFeeDao($dbConn, $logger);
$voucherDao = new VoucherDao($dbConn, $logger);
$userDao = new UsersDao($dbConn, $logger);
$mailer = new TekBotsMailer($configManager->getWorkerMaillist(), $configManager->getBounceEmail(), null, $logger);
$messageDao = new MessageDao($dbConn, $logger);

$handler = new PrinterActionHandler($printerDao, $printerFeeDao, $voucherDao, $userDao, $mailer, $messageDao, $configManager, $logger);

// Authorize the request
if (verifyPermissions(['user', 'employee'], $logger)) {
    // Handle the request
    $handler->handleRequest();
} else {
    $handler->respond(new Response(Response::UNAUTHORIZED, 'You do not have permission to access this resource'));
}

?>
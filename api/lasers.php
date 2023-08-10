<?php
/**
 * This page handles client requests to modify or fetch project-related data. All requests made to this page should
 * be a POST request with a corresponding `action` field in the request body.
 */
include_once '../bootstrap.php';

use Api\Response;
use DataAccess\LaserDao;
use DataAccess\UsersDao;
use DataAccess\CoursePrintAllowanceDao;
use Api\LaserActionHandler;
use Email\TekBotsMailer;
use DataAccess\MessageDao;

session_start();

// Setup our data access and handler classes
$laserDao = new LaserDao($dbConn, $logger);
$coursePrintAllowanceDao = new CoursePrintAllowanceDao($dbConn, $logger);
$userDao = new UsersDao($dbConn, $logger);
$mailer = new TekBotsMailer('tekbot-worker@engr.oregonstate.edu', null, $logger);
$messageDao = new MessageDao($dbConn, $logger);

$handler = new LaserActionHandler($laserDao, $coursePrintAllowanceDao, $userDao, $mailer, $messageDao, $configManager, $logger);

// Authorize the request
if (isset($_SESSION['userID']) && !empty($_SESSION['userID'])) {
    // Handle the request
    $handler->handleRequest();
} else {
    $handler->respond(new Response(Response::UNAUTHORIZED, 'You do not have permission to access this resource'));
}

?>
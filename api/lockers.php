<?php
/**
 * This page handles client requests to modify or fetch user-related data. All requests made to this page should be a 
 * POST request with a corresponding `action` field in the request body.
 */
include_once '../bootstrap.php';

use DataAccess\LockerDao;
use DataAccess\UsersDao;
use DataAccess\MessageDao;
use Api\LockerActionHandler;
use Api\Response;

if(!session_id()) {
    session_start();
}

// Setup our data access and handler classes
$lockerDao = new LockerDao($dbConn, $logger);
$userDao = new UsersDao($dbConn, $logger);
$messageDao = new MessageDao($dbConn, $logger);
$handler = new LockerActionHandler($lockerDao, $userDao, $messageDao, $logger);

// Authorize the request -- done within each ActionHandler method as of 9/1/23
// if (verifyPermissions(['user', 'employee'])) {
    // Handle the request
    $handler->handleRequest();
// } else {
    // $handler->respond(new Response(Response::UNAUTHORIZED, 'You do not have permission to access this resource'));
// }

<?php
/**
 * This page handles client requests to modify or fetch user-related data. All requests made to this page should be a 
 * POST request with a corresponding `action` field in the request body.
 */
include_once '../bootstrap.php';

use DataAccess\TaskDao;
use DataAccess\UsersDao;
use DataAccess\MessageDao;
use Api\TaskActionHandler;
use Api\Response;

if(!session_id()) {
    session_start();
}

// Setup our data access and handler classes
$taskDao = new TaskDao($dbConn, $logger);
$userDao = new UsersDao($dbConn, $logger);
$messageDao = new MessageDao($dbConn, $logger);
$handler = new TaskActionHandler($taskDao, $userDao, $messageDao, $logger);

// Handle the request
$handler->handleRequest();

?>

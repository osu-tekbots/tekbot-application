<?php
/**
 * This page handles client requests to modify or fetch user-related data. All requests made to this page should be a 
 * POST request with a corresponding `action` field in the request body.
 */
include_once '../bootstrap.php';

use DataAccess\LockerDao;
use DataAccess\UsersDao;
use DataAccess\MessageDao;
use Email\TekBotsMailer;
use Api\LockerActionHandler;
use Api\Response;

if(!session_id()) {
    session_start();
}

// Setup our data access and handler classes
$lockerDao = new LockerDao($dbConn, $logger);
$userDao = new UsersDao($dbConn, $logger);
$messageDao = new MessageDao($dbConn, $logger);
$mailer = new TekBotsMailer($configManager->getWorkerMaillist(), $configManager->getBounceEmail(), null, $logger);
$handler = new LockerActionHandler($lockerDao, $userDao, $messageDao, $mailer, $logger);

// Handle the request
$handler->handleRequest();

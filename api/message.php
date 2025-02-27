<?php
/**
 * This page handles client requests to modify or fetch user-related data. All requests made to this page should be a 
 * POST request with a corresponding `action` field in the request body.
 */
include_once '../bootstrap.php';

use DataAccess\MessageDao;
use Email\TekBotsMailer;
use Api\MessageActionHandler;
use Api\Response;

if (PHP_SESSION_ACTIVE != session_status())
    session_start();

// Setup our data access and handler classes
$dao = new MessageDao($dbConn, $logger);
$mailer = new TekBotsMailer($configManager->getWorkerMaillist(), $configManager->getBounceEmail(), null, $logger);
$handler = new MessageActionHandler($dao, $mailer, $logger);

// Handle the request
$handler->handleRequest();

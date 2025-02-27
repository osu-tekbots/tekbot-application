<?php
/**
 * This page handles client requests to modify or fetch projecgt-related data. All requests made to this page should
 * be a POST request with a corresponding `action` field in the request body.
 */
include_once '../bootstrap.php';

use Api\Response;
use DataAccess\FaqDao;
use Api\GeneralFaqActionHandler;

if (PHP_SESSION_ACTIVE != session_status())
    session_start();

// Setup our data access and handler classes
$dao = new FaqDao($dbConn, $logger);
$handler = new GeneralFaqActionHandler($dao, $configManager, $logger);

// Handle the request
$handler->handleRequest();

?>
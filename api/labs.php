<?php

/**
 * This page handles client requests to modify or fetch user-related data. All requests made to this page should be a 
 * POST request with a corresponding `action` field in the request body.
 */
include_once '../bootstrap.php';

use DataAccess\LabDao;
use Api\LabActionHandler;

if (PHP_SESSION_ACTIVE != session_status())
    session_start();

// Setup our data access and handler classes
$labDao = new LabDao($dbConn, $logger);
$handler = new LabActionHandler($labDao, $logger);

// Handle the request
$handler->handleRequest();

?>
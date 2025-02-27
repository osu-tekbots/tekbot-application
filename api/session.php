<?php
/**
 * This page handles client requests to modify session variables. All requests made to this page should be a 
 * POST request with a corresponding `action` field in the request body.
 */
include_once '../bootstrap.php';

use Api\SessionActionHandler;

if (PHP_SESSION_ACTIVE != session_status())
    session_start();

// Setup our data access and handler classes
$handler = new SessionActionHandler($logger);

// Handle the request
$handler->handleRequest();

<?php
/**
 * This page handles client requests to modify or fetch user-related data. All requests made to this page should be a 
 * POST request with a corresponding `action` field in the request body.
 */
include_once '../bootstrap.php';

use DataAccess\VoucherDao;
use Api\VoucherActionHandler;
use Api\Response;

if (PHP_SESSION_ACTIVE != session_status())
    session_start();

// Setup our data access and handler classes
$dao = new VoucherDao($dbConn, $logger);
$handler = new VoucherActionHandler($dao, $logger);

// Handle the request
$handler->handleRequest();

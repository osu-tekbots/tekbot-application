<?php
/**
 * This page handles client requests to modify or fetch user-related data. All requests made to this page should be a 
 * POST request with a corresponding `action` field in the request body.
 */
include_once '../bootstrap.php';

use DataAccess\InventoryDao;
use DataAccess\UsersDao;
use DataAccess\TaskDao;
use Api\InventoryActionHandler;
use Api\Response;

if(!session_id()) {
    session_start();
}

// Setup our data access and handler classes
$inventoryDao = new InventoryDao($dbConn, $logger);
$userDao = new UsersDao($dbConn, $logger);
$taskDao = new TaskDao($dbConn, $logger);
$handler = new InventoryActionHandler($inventoryDao, $userDao, $taskDao, $logger);

// Handle the request
$handler->handleRequest();

?>

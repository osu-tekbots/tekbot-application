<?php
/**
 * This page handles client requests to modify or fetch projecgt-related data. All requests made to this page should
 * be a POST request with a corresponding `action` field in the request body.
 */
include_once '../bootstrap.php';

use Api\Response;
use DataAccess\EquipmentDaoOld;
use DataAccess\EquipmentCheckoutDaoOld;
use Api\EquipmentActionHandler;
use Email\ProjectMailer;

if (PHP_SESSION_ACTIVE != session_status())
    session_start();

// Setup our data access and handler classes
$equipmentDao = new EquipmentDaoOld($dbConn, $logger);
$handler = new EquipmentActionHandler($equipmentDao, $configManager, $logger);

// Handle the request
$handler->handleRequest();

?>
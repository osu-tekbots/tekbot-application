<?php
/**
 * This page handles client requests to modify or fetch projecgt-related data. All requests made to this page should
 * be a POST request with a corresponding `action` field in the request body.
 */
include_once '../bootstrap.php';

use Api\Response;
use DataAccess\EquipmentDao;
use DataAccess\EquipmentCheckoutDao;
use Api\EquipmentActionHandler;
use Email\ProjectMailer;

if(!session_id()) session_start();

// Setup our data access and handler classes
$equipmentDao = new EquipmentDao($dbConn, $logger);
$handler = new EquipmentActionHandler($equipmentDao, $configManager, $logger);

// Handle the request
$handler->handleRequest();

?>
<?php
/**
 * This page handles client requests to modify or fetch equipment-related data. All requests made to this page should
 * be a POST request with a corresponding `action` field in the request body.
 */
include_once '../bootstrap.php';

use Api\EquipmentActionHandler;
use Api\Response;
use DataAccess\EquipmentTypeDao;
use DataAccess\EquipmentItemDao;
use DataAccess\EquipmentHealthDao;
use Email\ProjectMailer;

if (PHP_SESSION_ACTIVE != session_status())
    session_start();

// Setup our data access and handler classes
$equipmentTypeDao = new EquipmentTypeDao($dbConn, $logger);
$equipmentItemDao = new EquipmentItemDao($dbConn, $logger);
$equipmentHealthDao = new EquipmentHealthDao($dbConn, $logger);
$handler = new EquipmentActionHandler($equipmentTypeDao, $equipmentItemDao, $equipmentHealthDao, $configManager, $logger);

// Handle the request
$handler->handleRequest();

?>
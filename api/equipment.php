<?php
/**
 * This page handles client requests to modify or fetch equipment-related data. All requests made to this page should
 * be a POST request with a corresponding `action` field in the request body.
 */
include_once '../bootstrap.php';

use Api\EquipmentActionHandler;
use Api\Response;
use DataAccess\EquipmentTypeDao;
use DataAccess\EquipmentUnitDao;
use DataAccess\EquipmentHealthDao;
use Email\ProjectMailer;

if (PHP_SESSION_ACTIVE != session_status())
    session_start();

// Setup our data access and handler classes
$equipmentTypeDao = new EquipmentTypeDao($dbConn, $logger);
$equipmentUnitDao = new EquipmentUnitDao($dbConn, $logger);
$equipmentHealthDao = new EquipmentHealthDao($dbConn, $logger);
$handler = new EquipmentActionHandler($equipmentTypeDao, $equipmentUnitDao, $equipmentHealthDao, $configManager, $logger);

// Handle the request
$handler->handleRequest();

?>
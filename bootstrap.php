<?php

// Uncomment the lines below to display errors before configuration has been loaded
//ini_set('display_errors', 1);
//error_reporting(E_ALL);

define('PUBLIC_FILES', __DIR__);
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token');

include PUBLIC_FILES . '/lib/shared/autoload.php';

// Load configuration
$configManager = new Util\ConfigManager(PUBLIC_FILES);

$dbConn = DataAccess\DatabaseConnection::FromConfig($configManager->getDatabaseConfig());

try {
    $logger = new Util\Logger($configManager->getLogFilePath(), $configManager->getLogLevel());
} catch (\Exception $e) {
    $logger = null;
}

// Set $_SESSION variables to be for this site
include PUBLIC_FILES . '/lib/shared/authenticate.php';

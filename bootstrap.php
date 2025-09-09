<?php

// Uncomment the lines below to display errors before configuration has been loaded
ini_set('display_errors', 1);
error_reporting(E_ALL);

define('PUBLIC_FILES', __DIR__);
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token');

include PUBLIC_FILES . '/lib/shared/autoload.php';

if (PHP_SESSION_ACTIVE != session_status()) {
  $ok = @session_start();
  if(!$ok){
    session_regenerate_id(true); // replace the Session ID
    session_start(); 
  }
}

// Load configuration
$_SESSION['configManager'] = new Util\ConfigManager(PUBLIC_FILES);
$configManager = $_SESSION['configManager'];

try {
    $dbConn = DataAccess\DatabaseConnection::FromConfig($configManager->getDatabaseConfig());
} catch (\Exception $e) {
    echo 'There is an irresolvable issue with our database connection right now. Please try again later.';
    die();
}

try {
    $logFileName = $configManager->getLogFilePath() . date('MY') . ".log";
    $logger = new Util\Logger($logFileName, $configManager->getLogLevel());
} catch (\Exception $e) {
    $logger = null;
}

// Add handlers for uncaught errors/exceptions
include PUBLIC_FILES . '/lib/handleUncaught.php';

// Set $_SESSION variables to be for this site
include PUBLIC_FILES . '/lib/authenticate.php';

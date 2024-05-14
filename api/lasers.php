<?php
/**
 * This page handles client requests to modify or fetch project-related data. All requests made to this page should
 * be a POST request with a corresponding `action` field in the request body.
 */
include_once '../bootstrap.php';

use Api\Response;
use DataAccess\LaserDao;
use DataAccess\UsersDao;
use DataAccess\VoucherDao;
use Api\LaserActionHandler;
use Email\TekBotsMailer;
use DataAccess\MessageDao;

session_start();

// Setup our data access and handler classes
$laserDao = new LaserDao($dbConn, $logger);
$voucherDao = new VoucherDao($dbConn, $logger);
$userDao = new UsersDao($dbConn, $logger);
$mailer = new TekBotsMailer($configManager->getWorkerMaillist(), $configManager->getBounceEmail(), null, $logger);
$messageDao = new MessageDao($dbConn, $logger);

$handler = new LaserActionHandler($laserDao, $voucherDao, $userDao, $mailer, $messageDao, $configManager, $logger);

// Handle the request
$handler->handleRequest();

?>
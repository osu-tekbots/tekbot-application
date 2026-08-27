<?php
/**
 * This page handles client requests to modify or fetch user-related data. All requests made to this page should be a 
 * POST request with a corresponding `action` field in the request body.
 */
include_once '../bootstrap.php';

use Api\TransactionActionHandler;
use DataAccess\MessageDao;
use DataAccess\InventoryDao;
use DataAccess\TransactionDao;
use Email\TekBotsMailer;

if (PHP_SESSION_ACTIVE != session_status())
    session_start();

// Setup our data access and handler classes
$messageDao = new MessageDao($dbConn, $logger);
$inventoryDao = new InventoryDao($dbConn, $logger);
$transactionDao = new TransactionDao($dbConn, $logger);
$mailer = new TekBotsMailer($configManager->getWorkerMaillist(), $configManager->getBounceEmail(), null, $logger);
$handler = new TransactionActionHandler($inventoryDao, $transactionDao, $messageDao, $mailer, $configManager, $logger);

// Handle the request
$handler->handleRequest();

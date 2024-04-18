<?php

/**
 * Temporary tool for letting Gareth send an email to customers
 */

include_once '../bootstrap.php';

use Api\EmailActionHandler;
use Api\Response;
use Email\Mailer;

if(!session_id()) {
    session_start();
}

$mailer = new Mailer($configManager->getWorkerMaillist(), $configManager->getBounceEmail(), 'TekBots', $logger);

$handler = new EmailActionHandler($mailer, $configManager, $logger);

$handler->handleRequest();
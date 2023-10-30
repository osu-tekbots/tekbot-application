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

$mailer = new Mailer('tekbot-worker@engr.oregonstate.edu', 'TekBots', $logger);

$handler = new EmailActionHandler($mailer, $logger);

$handler->handleRequest();
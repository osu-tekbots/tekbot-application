<?php
/**
 * This page handles client requests to modify or fetch user-related data. All requests made to this page should be a 
 * POST request with a corresponding `action` field in the request body.
 */
include_once '../bootstrap.php';

use DataAccess\TicketDao;
use DataAccess\LabDao;
use DataAccess\MessageDao;
use Api\TicketsActionHandler;
use Api\Response;
use Email\TekBotsMailer;

if(!session_id()) {
    session_start();
}

// Setup our data access and handler classes
$ticketDao = new TicketDao($dbConn, $logger);
$labDao = new LabDao($dbConn, $logger);
$messageDao = new MessageDao($dbConn, $logger);
$mailer = new TekBotsMailer('tekbot-worker@engr.oregonstate.edu', null, $logger);
$handler = new TicketsActionHandler($ticketDao, $labDao, $messageDao, $mailer, $logger);
$handler->handleRequest();

// Authorize the request
// if (isset($_SESSION['userID']) && !empty($_SESSION['userID'])) {
//     // Handle the request
    
// } else {
//     $handler->respond(new Response(Response::UNAUTHORIZED, 'You do not have permission to access this resource'));
// }
?>
<?php
/**
 * This page handles client requests to modify or fetch user-related data. All requests made to this page should be a 
 * POST request with a corresponding `action` field in the request body.
 */
include_once '../bootstrap.php';

/*
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL); 
*/

use DataAccess\BoxDao;


if(!session_id()) {
    session_start();
}

if (isset($_REQUEST['box_key']) && !empty($_REQUEST['box_key'])) {
	$boxDao = new BoxDao($dbConn, $logger);
	if ($boxDao->boxStatus($_REQUEST['box_key'])) { //if true, locker should be openable
		$boxDao->pickupBox($_REQUEST['box_key']); //Set Pickup Date in DB so we know
		echo '{"message":"open"}';
	} else {
		echo '{"message":"Invalid Request"}';
	}
} else {
    echo '{"message":"Invalid Request"}';
}

<?php

use DataAccess\UsersDao;
$usersDao = new UsersDao($dbConn, $logger);

if (!session_id()) session_start();

$user = NULL;

// Get user & set $_SESSION user variables for this site
if(isset($_SESSION['site']) && $_SESSION['site'] == 'tekbot') {
    // $_SESSION["site"] is this one! User info should be correct
} else {
    if(isset($_SESSION['auth']['method'])) {
        switch($_SESSION['auth']['method'] == 'onid') {
            case 'onid':
                // Logged in with ONID on another site; transferring to this one

                $user = $usersDao->getUserByONID($_SESSION['auth']['id']);
                
                $_SESSION['site'] = 'tekbot';
                $_SESSION['userID'] = $user->getUserId();
                $_SESSION['userAccessLevel'] = $user->getAccessLevelID()->getName();
                
                break;
            
            default:
                // Logged in with something not valid for this site; setting as not logged in
                $logger->info('Authentication provider is '.$_SESSION['auth']['method'].', not something this site recognizes');

                $_SESSION['site'] = NULL; // Done to prevent cross-site $_SESSION pollution for user verification,
                                          // ie running this script, logging into a site that doesn't set 
                                          // $_SESSION['site'] (so it's still 'tekbot') & then rerunning this script
                                          // (so the $_SESSION[userID] is trusted)
                $_SESSION['userID'] = NULL;
                $_SESSION['userAccessLevel'] = NULL;
        }
    } else {
        // Not logged in
    }
}

// Set global variables for scripts to use
// $userId = $_SESSION['userId'];
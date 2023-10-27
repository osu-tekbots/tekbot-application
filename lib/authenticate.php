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
        switch($_SESSION['auth']['method']) {
            case 'onid':
                // Logged in with ONID on another site; storing this site's user info in $_SESSION...

                $user = $usersDao->getUserByONID($_SESSION['auth']['id']);
                
                $_SESSION['site'] = 'tekbot';
                $_SESSION['userID'] = $user->getUserId();
                $_SESSION['userAccessLevel'] = $user->getAccessLevelID()->getName();
                
                break;
            
            default:
                // Logged in with something not valid for this site; setting as not logged in
                $logger->info('Authentication provider is '.$_SESSION['auth']['method'].', not something this site recognizes');

                $_SESSION['site'] = NULL;
                $_SESSION['userID'] = NULL;
                $_SESSION['userAccessLevel'] = NULL;
        }
    } else {
        // Not logged in; best to make sure everything's clear
        $_SESSION['site'] = NULL;
        $_SESSION['userID'] = NULL;
        $_SESSION['userAccessLevel'] = NULL;
    }
}

/**
 * Checks if the person who initiated the current request has one of the given access levels
 * 
 * @param string|string[] $allowedAccessLevels  The access level(s) that should be accepted. Options are:
 *      * "public"
 *      * "user"
 *      * "employee"
 * 
 * @return bool Whether the person who initiated the current request has one of the given access levels
 */
function verifyPermissions($allowedAccessLevels) {
    try {
        $isLoggedIn = isset($_SESSION['userID']) && !empty($_SESSION['userID']);
        $isEmployee = $isLoggedIn && isset($_SESSION['userAccessLevel']) && $_SESSION['userAccessLevel'] == 'Employee';

        $allowPublic    = (gettype($allowedAccessLevels)=='string') ? $allowedAccessLevels=='public'   : in_array('public',   $allowedAccessLevels);
        $allowUsers     = (gettype($allowedAccessLevels)=='string') ? $allowedAccessLevels=='user'     : in_array('user',     $allowedAccessLevels);
        $allowEmployees = (gettype($allowedAccessLevels)=='string') ? $allowedAccessLevels=='employee' : in_array('employee', $allowedAccessLevels);
        
        if($allowPublic) {
            return true;
        }
        if($allowUsers && $isLoggedIn && !$isEmployee) {
            return true;
        }
        if($allowEmployees && $isEmployee) {
            return true;
        }
    } catch(\Exception $e) {
        $logger->error('Failure while verifying user permissions: '.$e->getMessage());
    } 
    
    return false;
}
<?php
include_once '../bootstrap.php';

use DataAccess\UsersDao;
use Model\User;
use Model\UserAccessLevel;

/**
 * Uses ONID to authenticate the user. 
 * 
 * When the function returns, the user will have been authenticated and the SESSION variable will have been set
 * accordingly.
 *
 * @return void
 */
function authenticateStudent() {
    global $dbConn, $logger;

    include_once PUBLIC_FILES . '/auth/onidfunctions.php';
    $onid = authenticateWithONID();

    $dao = new UsersDao($dbConn, $logger);

    $u = $dao->getUserByONID($onid);
    if ($u) {
        $_SESSION['site'] = 'tekbot';
        $_SESSION['userID'] = $u->getUserID();
        $_SESSION['userAccessLevel'] = $u->getAccessLevelID()->getName();
        $_SESSION['newUser'] = false;
        $u->setDateLastLogin(new DateTime());
        $dao->updateUser($u);
    } else {
        $u = new User();
        $u->setAccessLevelID(new UserAccessLevel(UserAccessLevel::STUDENT, 'Student'));
        $u->setOnid($onid);
        $u->setFirstName($_SESSION['auth']['firstName']);
        $u->setLastName($_SESSION['auth']['lastName']);
        $u->setEmail($_SESSION['auth']['email']);
        $u->setDateLastLogin(new DateTime());
        $ok = $dao->addNewUser($u);
        // TODO: handle error

        $_SESSION['userID'] = $u->getUserID();
        $_SESSION['userAccessLevel'] = $u->getAccessLevelID()->getName();
        $_SESSION['newUser'] = true;
    }
    return true;
}

<?php
/**
 * This file is password protected on the Apache Web Server. It allows for local development of an authenticated
 * test user without the need for CAS or other OAuth authentication services, since these services do not permit
 * the use of localhost URLs.
 * 
 * Essentially, we are masquerading as another user while we do development offline.
 */
include_once '../bootstrap.php';
include_once '../modules/renderTermData.php';

use DataAccess\UsersDao;

if (PHP_SESSION_ACTIVE != session_status())
    session_start();

// Make sure the user is allowed on this page
if ($configManager->getEnvironment() != 'dev') {
    // Make sure the user is an employee
    include_once PUBLIC_FILES . '/lib/shared/authorize.php';
    allowIf(verifyPermissions('employee', $logger), '../pages/index.php');
}

$dao = new UsersDao($dbConn, $logger);

$redirect = "<script>location.replace('../pages/index.php')</script>";

$masquerading = isset($_SESSION['masq']);
if ($masquerading) {
    $user = $dao->getUserByID($_SESSION['userID']);
}

$action = isset($_POST['action']) ? $_POST['action'] : '';

switch ($action) {
    case 'start':
        $onid = $_POST['onid'];
        if ($onid . '' != '') {
            $user = $dao->getUserByOnid($onid);
            if ($user) {
                stopMasquerade();
                startMasquerade($user);
                echo $redirect;
                die();
            }
            $message = 'User with the provided ONID not found';
        }
        break;
        
    case 'stop':
        stopMasquerade();
        echo $redirect;
        die();

    default:
        break;
}

/**
 * Stops the current masquerade (if there is one) and restores the original user session variables.
 *
 * @return void
 */
function stopMasquerade() {
    if (isset($_SESSION['masq'])) {
        unset($_SESSION['userID']);
        unset($_SESSION['accessLevel']);
        unset($_SESSION['newUser']);
        unset($_SESSION['tekbotSiteTerm']);
        if (isset($_SESSION['masq']['savedPreviousUser'])) {
            $_SESSION['userID'] = $_SESSION['masq']['userID'];
            $_SESSION['accessLevel'] = $_SESSION['masq']['accessLevel'];
            $_SESSION['newUser'] = $_SESSION['masq']['newUser'];
            $_SESSION['site'] = $_SESSION['masq']['site'];
            $_SESSION['auth'] = $_SESSION['masq']['auth'];
            $_SESSION['tekbotSiteTerm'] = $_SESSION['masq']['tekbotSiteTerm'];
        }
        unset($_SESSION['masq']);
    }
}

/**
 * Starts to masquerade as the provided user
 *
 * @param \Model\User $user the user to masquerade as
 * @return void
 */
function startMasquerade($user) {
    $_SESSION['masq'] = array('active' => true);
    if (isset($_SESSION['userID'])) {
        $_SESSION['masq']['savedPreviousUser'] = true;
        $_SESSION['masq']['userID'] = $_SESSION['userID'];
        $_SESSION['masq']['accessLevel'] = $_SESSION['accessLevel'];
        $_SESSION['masq']['newUser'] = $_SESSION['newUser'];
        $_SESSION['masq']['site'] = $_SESSION['site'];
        $_SESSION['masq']['auth'] = $_SESSION['auth'];
        $_SESSION['masq']['tekbotSiteTerm'] = $_SESSION['tekbotSiteTerm'];
    }
    $_SESSION['userID'] = $user->getUserID();
    $_SESSION['userAccessLevel'] = $user->getAccessLevelID()->getName();
    $_SESSION['newUser'] = false;
    $_SESSION['site'] = 'tekbot';
    $_SESSION['auth'] = [
        'method' => 'onid',
        'id' => strtolower($user->getOnid()),
        'firstName' => $user->getFirstName(), 
        'lastName' => $user->getLastName(),
        'email' => $user->getEmail()
    ];
    $_SESSION['tekbotSiteTerm'] = getCurrentTermId();
}
?>

<h1>Senior Design Capstone: Masquerade as Another User</h1>

<?php if ($masquerading): ?>
    <p>Currently masquerading as <strong><?php echo $user->getFirstName() . ' ' . $user->getLastName(); ?></strong></p>
<?php endif; ?>

<?php if (isset($message)): ?>
    <p><?php echo $message ?></p>
<?php endif; ?>

<h3>Masquerade as Existing</h3>
<form method="post" spellcheck="false">
    <input type="hidden" name="action" value="start" />
    <label for="onid">ONID</label>
    <input required type="text" id="eonid" name="onid" autocomplete="off" />
    <button type="submit">Start Masquerading</button>
</form>

<h3>Stop Masquerading</h3>
<form method="post">
    <input type="hidden" name="action" value="stop" />
    <button type="submit">Stop</button>
</form>




<?php
include_once '../bootstrap.php';

if (PHP_SESSION_ACTIVE != session_status())
    session_start();

unset($_SESSION['site']);
unset($_SESSION['userID']);
unset($_SESSION['accessLevel']);
unset($_SESSION['newUser']);
unset($_SESSION['tekbotSiteTerm']);
session_unset();
session_destroy();

$redirect = $configManager->getBaseUrl();
echo "<script>window.location.replace('$redirect');</script>";
die();

<?php
include_once '../bootstrap.php';

if(!isset($_SESSION)) {
    @session_start();
}


$isLoggedIn = verifyPermissions(['user', 'employee'], $logger);
if ($isLoggedIn) {
    // Redirect to their profile page
    $redirect = $configManager->getBaseUrl() . 'pages/myProfile.php';
    echo "<script>window.location.replace('$redirect');</script>";
    die();
}

$title = 'Login';
include_once PUBLIC_FILES . '/modules/header.php';

?>

<br><br><br>
<div class="container">
<div class="row">
    <div class="col">
        <br>
        <hr class="my-4">
        <h4 class="text-center">Student Login</h4>
        <a class="login" href="auth/index.php?provider=onid" style="text-decoration:none;">
            <button id="onidBtn" class="btn btn-lg btn-warning btn-block text-uppercase" type="submit">
                <i class="fas fa-book mr-2"></i> Student Login (ONID)
            </button>
        </a>
        <hr class="my-4">
    </div>
</div>
</div>

<?php
include_once PUBLIC_FILES . '/modules/footer.php';
?>

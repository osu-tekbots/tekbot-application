<?php
/**
 * This header module should be included in all PHP files under the `pages/` directory. It includes all the necessary
 * JavaScript and CSS files and creates the header navigation bar.
 * 
 * Before including the header file, you can specify a `$js` or `$css` variable to add additional JavaScript files
 * and CSS stylesheets to be included when the page loads in the browser. These additional files will be included
 * **after** the default scripts and styles already included in the header.
 */
include_once PUBLIC_FILES . '/modules/button.php';

if (PHP_SESSION_ACTIVE != session_status()) {
  $ok = @session_start();
  if(!$ok){
    session_regenerate_id(true); // replace the Session ID
    session_start(); 
  }
}

$baseUrl = $configManager->getBaseUrl();

$title = isset($title) ? $title : 'TekBots | OSU';

// JavaScript to include in the page. If you provide a JS reference as an associative array, the keys are the
// atributes of the <script> tag. If it is a string, the string is assumed to be the src.
if (!isset($js)) {
    $js = array();
}
$js = array_merge( 
    // Scripts to use on all pages
    array(
        'assets/js/jquery-3.3.1.min.js',
        'assets/js/popper.min.js',
        'assets/js/bootstrap.min.js',
        'assets/js/moment.min.js',
        'assets/js/tempusdominus-bootstrap-4.min.js',
        'assets/js/jquery-ui.js',
        'assets/js/platform.js',
        'assets/js/slick.min.js',
        'assets/js/jquery.canvasjs.min.js',
        'assets/js/image-picker.min.js',
        'assets/shared/js/api.js',
        'assets/js/splitting.min.js',
        'assets/shared/js/snackbar.js'
    ), $js
);

// CSS to include in the page. If you provide a CSS reference as an associative array, the keys are the
// atributes of the <link> tag. If it is a string, the string is assumed to be the href.
if (!isset($css)) {
    $css = array();
}
$css = array_merge(
    array(
        // Stylesheets to use on all pages
        array(
            'href' => 'https://use.fontawesome.com/releases/v5.7.1/css/all.css',
            'integrity' => 'sha384-fnmOCqbTlWIlj8LyTjo7mOUStjsKC4pOpQbqyi7RrhN7udi9RwhKkMHpvLbHG9Sr',
            'crossorigin' => 'anonymous'
        ),
        'assets/css/bootstrap.min.css',
        'assets/css/tempusdominus-bootstrap-4.min.css',
        'assets/css/slick.css',
        'assets/css/slick-theme.css',
        'assets/css/jquery-ui.css',
        'assets/css/image-picker.css',
        'assets/css/capstone.css',
        'assets/shared/css/snackbar.css',
        'assets/css/splitting.css',
        'assets/css/splitting-cells.css',
        array(
            'media' => 'screen and (max-width: 768px)', 
            'href' => 'assets/css/capstoneMobile.css'
        ),
    ),
    $css
);

if ($logger) {
    $loggedIn = verifyPermissions(['user', 'employee'], $logger);
} else {
    $loggedIn = False;
}



// Setup the buttons to use in the header
// All users
$buttons = array(
    // 'Reserve<BR>Equipment' => 'pages/publicEquipmentList.php'
);
// Signed in users
if ($loggedIn) {
	//All signed in users types can view these pages
    $buttons['My TekBots'] = './pages/userDashboard.php';

    
    // Employee only
    if (verifyPermissions('employee', $logger)) {
        $buttons['Employee'] = 'pages/employeeInterface.php';
    }
}

// All users
$buttons['FAQ'] = 'pages/info.php';

//$buttons['Laser Cutting'] = 'pages/laserCutting.php';

if ($loggedIn) {
    $buttons['Logout'] = 'pages/logout.php';
} else {
    if($configManager->getEnvironment() == 'dev')
        $buttons['Login'] = 'masq/index.php';
    else
        $buttons['Login'] = 'pages/login.php';
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link href="https://fonts.googleapis.com/css?family=Roboto:900|Abel|Heebo:700" rel="stylesheet">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <base href="<?php echo $baseUrl ?>" />
    <link href="https://eecs.engineering.oregonstate.edu/education/tekbotSuite/tekbot/assets/img/tekbots.ico" rel="icon" type="image/x-icon">
    <title><?php echo $title; ?></title>

    <?php
    // Include the JavaScript files
    foreach ($js as $script) {
        if (!is_array($script)) {
            echo "<script type=\"text/javascript\" src=\"$script\"></script>";
        } else {
            $link = '<script type="text/javascript" ';
            foreach ($script as $attr => $value) {
                $link .= $attr . '="' . $value . '" ';
            }
            $link .= '></script>';
            echo $link;
        }
    }

    // Include the CSS Stylesheets
    foreach ($css as $style) {
        if (!is_array($style)) {
            echo "<link rel=\"stylesheet\" href=\"$style\" />";
        } else {
            $link = '<link rel="stylesheet" ';
            foreach ($style as $attr => $value) {
                $link .= $attr . '="' . $value . '" ';
            }
            $link .= '/>';
            echo $link;
        }
    } 
	
	?>

</head>
<body>

    <header id="header" class="dark" style="z-index:1001">
        <a class="header-main-link" href="">
            <div class="logo">
                <img class="logo" src="assets/img/osu-logo-orange.png" />
                <h1><span id="projectPrefix">TEKBOTS </span> </h1>
            </div>
        </a>
		<nav class="navigation">
            <ul>
            <?php 
                foreach ($buttons as $title => $link) {
                    echo createHeaderButton($link, $title);
                }
            ?>
            </ul>
        </nav>
    </header>

    <main>

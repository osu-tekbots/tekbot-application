<?php
include_once '../bootstrap.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

use DataAccess\InventoryDao;
use DataAccess\UsersDao;
use Util\Security;

if (PHP_SESSION_ACTIVE != session_status())
    session_start();

// Make sure the user is logged in and allowed to be on this page
include_once PUBLIC_FILES . '/lib/shared/authorize.php';

allowIf(verifyPermissions('employee', $logger), 'index.php');


$inventoryDao = new InventoryDao($dbConn, $logger);

$items = array_keys($_GET);
if (count($items) < 2) {
    echo '<h1>No items selected</h1>';
    die();
}

$labelsHTML = '';
if ($_GET['labeltype'] == 1) { // Larger Labels
    $j = 0;
    $pageCSS = 'size: letter;';
    $labelsHTML .= "<div class='printpagelarge'>";
    foreach ($items AS $i){
        if ($i != 'labeltype'){
            $p = $inventoryDao->getPartByStocknumber($i);
            if ($j == 10){
                $labelsHTML .= '</div><div class="printpagelarge">';
                $j=0;
            }
            
            
            
            $labelsHTML .= '
            <div class="printlabellarge">
                <div style="float:left;width:45%;min-height:130px;margin-top:.5em;"><BR>
                    <img style="height:1.25in;display:block;margin-left: auto;margin-right: auto;" src="createqr.php?data=https://eecs.engineering.oregonstate.edu/education/store/Inventory/mobile.php?stocknumber=' . $i . '">
                </div>
                <div style="float:right;width:55%;min-height:130px;margin-top:.5em;">
                    <BR>
                    <img src="../../../inventory_images/'.$p->getImage().'" style="max-width:1.5in;height:10em;">
                    <div style="float:right;bottom: 0;">
                        <span style="font-size:5em;margin-right:1em;margin-top:1em;">' . $p->getLocation() . '</span>
                    </div>
                    <BR>
                    <BR>
                    <BR>
                    <div style="position: relative;">
                        <div style="float:left;">
                            <span style="font-size:1.5em">' . $i . '</span>
                        </div>
                    </div>
                </div>
                <div style="padding-top:1em;margin-top:130px;">
                    <span style="font-size:2em;margin-left:2em;">' . substr($p->getType() . ': ' . $p->getName(),0,37) . '</span>
                </div>
            </div>';
            $j++;
        }
    }
    $labelsHTML .= "</div>";
} else if ($_GET['labeltype'] == 2) { //Small Labels
    $j = 0;
    $pageCSS = 'size: letter;';
    $labelsHTML .= '<div class="printpagesmall">';
    foreach ($items AS $i){
        if ($i != 'labeltype'){
            $p = $inventoryDao->getPartByStocknumber($i);
            if ($j == 32){
                $labelsHTML .= '</div><div class="printpagesmall">';
                $j=0;
            }		

            $j++;
            $labelsHTML .= "<div class='printlabelsmall'>
                    <div style='float:left;width:55%;'>
                        <img style='height:1in;display:block;margin-left: auto;margin-right: auto;' src='createqr.php?data=https://eecs.engineering.oregonstate.edu/education/store/Inventory/mobile.php?stocknumber=" . $i . "'>
                    </div>
                    <div style='float:left;width:40%;'>
                        <BR>
                        <BR>" . $p->getType() . 
                        "<BR>" . substr($p->getName(),0,30) . 
                        "<BR>" . $i . 
                        "<BR><span style='font-size:2em;'>" . $p->getLocation() . "</span>
                    </div>
                </div>";
            
        }
    }
    $labelsHTML .= "</div>";
} else if ($_GET['labeltype'] == 3) { //Ordering Labels
    $j = 0;
    $pageCSS = 'size: letter;';
    $labelsHTML .= "<div class='printpagelarge'>";
    foreach ($items AS $i){
        if ($i != 'labeltype'){
            $p = $inventoryDao->getPartByStocknumber($i);
            if ($j == 10){
                $labelsHTML .= '</div><div class="printpagelarge">';
                $j=0;
            }
            $labelsHTML .= '
                <div class="printlabellarge">
                    <div>
                        <div style="float:left;width:50%;min-height:130px;margin-top:1em;">
                            <BR>
                            '.($p->getTouchnetId() != '' ? '<img style="height:1.4in;display:block;margin-left: auto;margin-right: auto;margin-top:-.2in" src="createqr.php?data=https://secure.touchnet.net/C20159_ustores/web/product_detail.jsp?PRODUCTID=' . $p->getTouchnetId() . '">' : '').'
                        </div>
                        <div style="float:right;width:50%;min-height:130px;margin-top:1em;">
                            <img src="../../../inventory_images/'.$p->getImage().'" style="max-width:1.5in;height:13em;">
                        </div>
                    </div>
                    <div style="padding-top:1em;margin-top:110px;">
                        <span style="font-size:1.5em;margin-left:1em;">' . substr($p->getName(),0,50) . '</span>
                    </div>
                </div>';
            $j++;
        }
    }
    $labelsHTML .= "</div>";
} else if ($_GET['labeltype'] == 4) { //Simple Kit Labels
    foreach ($items AS $i){
        if ($i != 'labeltype'){
            $pageCSS = 'size: letter;';
            $labelsHTML .= '<div class="printpagesmall">';
            $p = $inventoryDao->getPartByStocknumber($i);
            for($j=0;$j<32;$j++){	
                $labelsHTML .= "<div class='printlabelsmall'>
                                    <div class='kit-label-sm'>
                                        <b>" . $p->getName() . "</b>
                                        <BR>" . date('m-d-Y',time()) . "
                                    </div>
                                </div>";
            }
            $labelsHTML .= "</div>";
        }
    }
    
} else if ($_GET['labeltype'] == 5) { //Detailed Kit Labels
    foreach ($items AS $i){
        if ($i == 'labeltype') continue;

        $kit = $inventoryDao->getPartByStocknumber($i);
        $contents = $inventoryDao->getKitContentsByStocknumber($i);
        $date = date('m-d-Y', time());

        // To reduce errors when handing out similarly-named kits, ecampus kits will have
        // the kit name's color changed to Beaver Orange.
        $is_ecampus = preg_match('/\be-?campus\b/i', $kit->getName()) === 1;

        $pageCSS = 'size: letter landscape;';
        $labelsHTML .= '<div class="printpagexl">';
        for($j = 0; $j < 6; $j++) {
            $labelsHTML .= "<div class='printlabelxl'>
                <div style='display:flex;'>
                    <div class='kit-label-lg' style='flex-grow:1;'>
                        <h1" . ($is_ecampus ? " style='color:#d73f09'" : "") . ">{$kit->getName()}</h1>
                        <p style='font-size:2em;'>Kitted on $date</p>
                        <p style='font-size:1.5em;'>https://tekbots.com</p>
                    </div>
                    <img class='kit-label-qr' src='createqr.php?data=https://eecs.engineering.oregonstate.edu/education/tekbotSuite/tekbot/publicInventoryPart.php?stocknumber=$i'>
                </div>
                <img class='kit-label-logo' src='https://eecs.engineering.oregonstate.edu/education/tekbotSuite/tekbot/assets/img/resize_tekbots.png'>
                <div style='display:flex;'>
                    <ul class='kit-label-list'>";
            $k = 1;

            // List all contents of the kit, except the kit label itself and the bag it's
            // on (or anything else in the "no-print" category). If this would overflow
            // the label, adds a note referring the user to the TekBots website for the
            // full contents list.
            foreach ($contents as $kit_part) {
                if (! $kit_part['ShowOnLabel']) continue;
                
                if ($k > 19 && count($contents) > 20) { // NOTE: hardcoded to vertical limit
                    $labelsHTML .= "<li>(and more; see all contents with QR)</li>";
                    break;
                }
            
                $p = $inventoryDao->getPartByStocknumber($kit_part['StockNumber']);
                $labelsHTML .= "<li><b>{$kit_part['Quantity']} &ndash;</b> {$p->getName()}</li>";
                $k++;
            }
            $labelsHTML .= "</ul>
                    <div style='width:.8in; flex-shrink:0;'></div>
                </div>
            </div>";
        }
        $labelsHTML .= "</div>";
    }
}

?>

<html>
	<head>
		<title>Inventory Labels</title>
		<link href="assets/css/label-print.css" rel="stylesheet">
        <style>
            @page {
                <?= $pageCSS ?>
            }
        </style>
	</head>
	<body>

    <?php echo $labelsHTML ?>

    <script>
        alert('When printing, you must select \'No Margin\' for correct scaling.');
    </script>
</html>

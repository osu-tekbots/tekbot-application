<?php
//This page is linked to https://beav.es/ScG

include_once '../bootstrap.php';

use DataAccess\TicketDao;
use DataAccess\LabDao;

if (!session_id()) {
    session_start();
}

$title = "Lab Room";

$css = array(
    'assets/css/sb-admin.css',
    'assets/css/admin.css',
    'https://cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css'
);
$js = array(
    'https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js'
);


include_once PUBLIC_FILES . '/modules/header.php';

?>

<br>

<div class="container-fluid">
    <div class="row pt-5">
        <div class="col text-center">
            <h1>Coming soon!</h1>
        </div>
    </div>
</div>
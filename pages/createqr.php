<?php

    include('../assets/phpqrcode/qrlib.php');
    
    if(isset($_GET['data'])) {
        $param = $_GET['data']; 
    } else {
        $param = 'Invalid Data Supplied';
    }
    
    // outputs image directly into browser, as PNG stream
    QRcode::png($param);
?>
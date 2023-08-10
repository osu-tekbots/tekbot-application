<?php

// This is not working, ask Jack
    // $mysqli = new mysqli($server, $user, $password, $databaseName, 3307);
    // if ($mysqli->connect_errno) {
    //     printf("Connection Failed, <B>Error: ".mysql_error()."</B><p>Contact <a HREF=\"mailto::support@engr.orst.edu\">COE Support</A></p>Connect failed: %s\n</BODY></HTML>", $mysqli->connect_error);
    //     echo "fail";
    //     exit();
    // }

    $idExists = (isset($_REQUEST['id']) && $_REQUEST['id'] != '');
    $actionExists = (isset($_REQUEST['action']) && $_REQUEST['action'] != '');
    if($idExists && $actionExists) {
        $id = $_REQUEST['id'];
        $action = $_REQUEST['action'];
        if($action == 'approve'){
            // $query = "SELECT * FROM 3d_jobs WHERE id = $id";
            // $query = "UPDATE `3d_jobs` SET `pending_customer_response` = 0, `user_confirm_date` = now(), WHERE `3d_job_id` = " . $id;
            // $result = $mysqli->query($query);
            // echo $result;
            echo "Update 3d_jobs where id=$id <br/>";
            echo "Set `pending_customer_response` = 0, `user_confirm_date` = now()";


        }
    }
?>
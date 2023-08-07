<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
require_once('../../../includes/config.php');
session_start();
if (isset($_POST['selectData'])) {
    if  ($_POST['action'] == 'roomChange'){
        $roomid = var_dump($_POST['selectData']); // $_POST['selectData'] is the selected value
        // query here
        // and you can return the result if you want to do some things cool ;)
        //query(SELECT name FROM table WHERE id = value)
        $query = "SELECT * FROM labs_stations WHERE labs_stations.roomid = :roomid";
        //echo $query;
        $result = $mysqli->query($query);
        //$stationrow = $result->fetch_assoc();
        $stationOptions = "<option value=''></option>";
					//$rooms = $labDao->getRooms();

					foreach ($stationsInRoom as $s){
						
					}
        while($row = $result->fetch_assoc()) {
            $stationOptions .= "<option value='".$s->getStationId()."'>".$s->getStationName()."</option>";
        }
    }
    elseif ( ($_POST['action'] == 'changeStation')) {
        echo '<p></p>';
    }
}
?>
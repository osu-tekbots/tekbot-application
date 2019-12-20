<?php
include_once '../bootstrap.php';

use Util\Security;
use DataAccess\EquipmentDao;
use DataAccess\EquipmentReservationDao;

$title = 'Browse Equipment';
$css = array(
	'https://cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css'
);
$js = array(
    'https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js'
);
include_once PUBLIC_FILES . '/modules/header.php';
include_once PUBLIC_FILES . '/modules/renderBrowse.php';
include_once PUBLIC_FILES . '/modules/reserveEquipmentModal.php';


$dao = new EquipmentDao($dbConn, $logger);
$equipmentReservationDao = new EquipmentReservationDao($dbConn, $logger);
$isLoggedIn = isset($_SESSION['userID']) && $_SESSION['userID'] . ''  != '';
$equipments = $dao->getBrowsableEquipment();

$equipmentItemHTML = "";
foreach ($equipments as $e){
    $equipmentID = $e->getEquipmentID();
    $image = $dao->getDefaultEquipmentImage($equipmentID);
    if (!empty($image)){
        $imageName = $image->getImageID();
        $imagePath = "images/equipment/$imageName";
        
    } else {
        $imageName = "loginImage.jpg";
        $imagePath = "assets/img/$imageName";
    }

    $viewButton = createLinkButton("pages/viewEquipment.php?id=$equipmentID", 'View');
    $isAvailable = $equipmentReservationDao->getEquipmentAvailableStatus($equipmentID);
    if ($isAvailable){
        $status = "Available";
    }
    else {
        $status = "Not Available";
    }
    $name = Security::HtmlEntitiesEncode($e->getEquipmentName());
    if (strlen($name) > 60) {
        // Restrict the name length
        $name = substr($name, 0, 60) . "..."; 
    }
    $health = $e->getHealthID()->getName();
    $description = Security::HtmlEntitiesEncode($e->getDescription());
    if (strlen($description) > 318) {
        // Restrict the description length
        $description = substr($description, 0, 318) . "...";
    }
    
    $equipmentItemHTML .= "
    <tr>
        <td><img height='200px;' src='$imagePath'></td>
        <td>$name</td>
        <td>$description</td>
        <td>$health</td>
        <td>$status</td>
        <td>$viewButton</td>
    </tr>
  
    ";
}
?> 
<br /><br />
<div class="container-fluid">
        <?php
        $version = phpversion();
        print $version;
        echo "
        <div class='admin-paper'>
        <h1>Equipment Rentals</h1>
            <table class='table' id='equipmentList'>
            <caption>Equipments For Rental</caption>
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Health</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    $equipmentItemHTML
                </tbody>
            </table>
            <script>
            $('#equipmentList').DataTable();

            </script>
        </div>
        ";
            // File located inside modules/renderBrowse.php
            //renderEquipmentList($equipments, $isLoggedIn);
        ?>
    



<?php
include_once PUBLIC_FILES . '/modules/footer.php';
?>


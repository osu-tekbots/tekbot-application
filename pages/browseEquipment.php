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
if ($isLoggedIn){
    $isEmployee = isset($_SESSION['userID']) && !empty($_SESSION['userID']) && isset($_SESSION['userAccessLevel']) && $_SESSION['userAccessLevel'] == 'Admin'|| $_SESSION['userAccessLevel'] == 'Employee';
} else {
    $isEmployee = FALSE; 
}
$equipments = $dao->getBrowsableEquipment();
$equipmentItemHTML = "";
foreach ($equipments as $e){
    $equipmentID = $e->getEquipmentID();
    $image = $dao->getDefaultEquipmentImage($equipmentID);
    if (!empty($image)){
        $imageName = $image->getImageID();
        $imagePath = "images/equipment/$imageName";
        
    } else {
        $imageName = "no-image.png";
        $imagePath = "assets/img/$imageName";
    }

    $viewButton = createLinkButton("pages/viewEquipment.php?id=$equipmentID", 'View');
    $editButton = createLinkButton("pages/editEquipment.php?id=$equipmentID", 'Edit');
    if ($isEmployee){
        $actions = "$viewButton $editButton";
    } else {
        $actions = "$viewButton";
    }
	
    $isAvailable = $equipmentReservationDao->getEquipmentAvailableStatus($equipmentID);
    if ($isAvailable){
        $status = "Available";
    }
    else {
        $status = "Not Available";
    }
	
//	$status .= "<BR>" . $e->getInstances() . " Unit(s) Total";
	
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
        <td><a href='pages/viewEquipment.php?id=$equipmentID'><img height='150px;' src='$imagePath'></a></td>
        <td>$name</td>
        <td>$description</td>
        <td>$status</td>
        <td>$actions</td>
    </tr>
  
    ";
}
?> 
<br><br>
<div class="container-fluid">
	<div class='admin-paper'>
        <div class="alert">
		<h1>Equipment Available to Borrow</h1>
		<div class="row">
			<div class="col-7">
			<p class="lead mb-0">OSU students, employees, and staff can borrow a variety of equipment from the TekBots store in KEC1110. Equipment is available on a varying number of day loans. To pick up equipment, browse the listing below and make a reservation for the equipment you want, A reservation is good for an hour so travel quickly to pickup your item from KEC1110. IN the event that you can not make it to our hours, it maybe possible to leave the item for you in one of our TekBox lockers. Contact us for more details.</p>
			</div>
			<div class="col-5"><img class="img-fluid rounded" src="./assets/img/rect1.png">
			</div>
		</div>
		</div>
		<table class='table' id='equipmentList'>
        <caption>Equipments For Rental</caption>
			<thead>
				<tr>
					<th>Image</th>
					<th>Name</th>
					<th>Description</th>
					<th>Status</th>
					<th></th>
				</tr>
			</thead>
			<tbody>
				<?php echo $equipmentItemHTML; ?>
			</tbody>
		</table>
	</div>
</div>
	
<script type='text/javascript'>
$('#equipmentList').DataTable(
	{
		'order':[[1, 'asc']],
		lengthMenu: [[-1, 5, 10, 20, 50], ['All', 5, 10, 20, 50]]
	}
);

</script>




<?php
include_once PUBLIC_FILES . '/modules/footer.php';
?>


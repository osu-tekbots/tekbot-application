<?php
include_once '../bootstrap.php';

use DataAccess\EquipmentDao;
use DataAccess\EquipmentCheckoutDao;
use DataAccess\EquipmentReservationDao;
use DataAccess\UsersDao;
use Model\EquipmentCheckoutStatus;
use Util\Security;

if (PHP_SESSION_ACTIVE != session_status())
    session_start();

// Make sure the user is logged in and allowed to be on this page
include_once PUBLIC_FILES . '/lib/shared/authorize.php';

allowIf(verifyPermissions('employee', $logger), 'index.php');


$title = 'Employee Inventory List';
$css = array(
	'assets/css/sb-admin.css',
	'assets/css/admin.css',
	'https://cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css'
);
$js = array(
    'https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js'
);


$items = array_keys($_POST);
$labelsHTML = "";
if (count($items) > 0) { //Need to render labels
	$labelsHTML .= "
	<style>
	body
	{
		font-family: 'Arial' , monospace;
		font-size:6pt;
	}

	header
	{
		display: none;
	}

	footer
	{
		display: none;
	}
	div.printpagelarge{
		page-break-inside: avoid;
		width:8.35in; 
		height:10.04in; 
		padding-left: .35in; 
		padding-right: 0 in; 
		padding-top: .45in; 
		padding-bottom: .48in; 
		float: none;
		/*padding: .5in .3in .5in .2in;*/
		box-sizing: border-box;
	}

	div.printlabellarge{
		width:4 in;
		min-width: 4in;
		height:2in;
		min-height: 2in;
		float:left;
		padding-left:.2 in;
		padding-right:.2 in;
		padding-top:.1 in;
		padding-bottom:.1 in;
		border-style:solid;
		border-color: white;
		box-sizing: border-box;
		font-size:6pt;
		background-color: #ffffff;
	}

	</style>";
	
	
$equipmentDao = new EquipmentDao($dbConn, $logger);
$userDao = new UsersDao($dbConn, $logger);
	
/* 
* -DOUBLECHECK- This section creates a page of large labels to print.
* If there are 10 items in teh page, page is completed, otherwise continues to fill
*/
		$j = 0;
		$labelsHTML .= "<div class='printpagelarge'>";
		foreach ($items AS $i){
			if ($i != 'labeltype'){
				$e = $equipmentDao->getEquipment($i);
				$defaultImage = $equipmentDao->getDefaultEquipmentImage($i);
				if (!empty($defaultImage)){
					$imageName = $defaultImage->getImageID();
					$imagePath = "images/equipment/$imageName";
				} else {
					$imageName = "no-image.png";
					$imagePath = "assets/img/$imageName";
				}
				
				if ($j == 10){
					$labelsHTML .= '</div><div class="printpagelarge">';
					$j=0;
				}
				$labelsHTML .= '
				<div class="printlabellarge">
					<div style="width:1.6in;height:1.8in;float:left;" ><img style="width:100%;height:100%;object-fit:contain;" src="../'.$imagePath.'"></div>
					<div style="float:left;width:2.1in;height:1.8in;padding-left:.05in;padding-top:.1in;">
						<div style="height:1.5in;"><span style="font-size:2em;">'.$e->getEquipmentName().'<BR></span>Contents:<BR>'.nl2br($e->getPartList()).'</div>
						<div style="height:.2in;">Location:'.$e->getLocation().'</div>
					</div>
				</div>';
				$j++;
			}
		}
/* 
* If the page is not full, continues to fill with the last label
*/
		if ($j != 10){
			while ($j != 10){
				$labelsHTML .= '
				<div class="printlabellarge">
					<div style="width:1.6in;height:1.8in;float:left;" ><img style="width:100%;height:100%;object-fit:contain;" src="../'.$imagePath.'"></div>
					<div style="float:left;width:2.1in;height:1.8in;padding-left:.05in;padding-top:.1in;">
						<div style="height:1.5in;"><span style="font-size:2em;">'.$e->getEquipmentName().'<BR></span>Contents:<BR>'.nl2br($e->getPartList()).'</div>
						<div style="height:.2in;">Location:'.$e->getLocation().'</div>
					</div>
				</div>';
				$j++;
			}
		}
		$labelsHTML .= "</div>";
	
	
	echo $labelsHTML;
	echo "<script>alert('When printing, you must select \'No Margin\' for correct scaling.');</script>";
	exit();
}

include_once PUBLIC_FILES . '/modules/header.php';
include_once PUBLIC_FILES . '/modules/employee.php';
include_once PUBLIC_FILES . '/modules/renderBrowse.php';

$equipmentDao = new EquipmentDao($dbConn, $logger);
$equipment = $equipmentDao->getAdminEquipment();

$options = "";
$options .= "<div class='form-row'>
				<div class='form-group col-sm-3'>
					<button class='btn btn-info' type='submit' form='mainform'>Get Selected Labels</button>
				</div>
			</div>";

$formHTML = "<form method='post' target='_blank' id='mainform'>
				$options
				<table class='table' id='EquipmentTable'>
                <thead>
                    <tr>
						<th></th>
                        <th>Name</th>
                        <th>Description</th>
						<th>Location</th>
                    </tr>
                </thead>
				<tbody>";
				
/* 
* Creates data table that populates from the equipment DAO
*/
				
foreach ($equipment as $e) {
	$id = $e->getEquipmentID();
	$name = $e->getEquipmentName();
	$description = $e->getDescription();
	$location = $e->getLocation();
	
	$formHTML .= "<tr>
	<td><input type='checkbox' id='checkbox$id' name='$id'></td>
	<td>$name</td>
	<td>$description</td>
	<td>$location</td>
	</tr>";
}

$formHTML .= "</tbody>
			</table>
			</form>";

?>
<script type='text/javascript'>

/*TODO - Did not see this functionality within the page?*/

function updateLocation(id){
	var location = $('#location'+id).val();
	
	let content = {
		action: 'updateLocation',
		stockNumber: id,
		location: location
	}

	api.post('/inventory.php', content).then(res => {
		snackbar(res.message, 'Updated');
		$('#row'+id).html('');
	}).catch(err => {
		snackbar(err.message, 'error');
	});
}

function updateQuantity(id){
	var amount = $('#quantity'+id).val();
	
	let content = {
		action: 'updateQuantity',
		stockNumber: id,
		amount: amount
	}
	
	api.post('/inventory.php', content).then(res => {
		snackbar(res.message, 'Updated');
		$('#row'+id).html('');
	}).catch(err => {
		snackbar(err.message, 'error');
	});
}

</script>

<br/>
<div id="page-top">

	<div id="wrapper">

	<?php 
		// Located inside /modules/employee.php
		renderEmployeeSidebar();
	?>

    <div class="admin-content" id="content-wrapper">
        <div class="container-fluid">
			<div class='admin-paper'>
            <?php 
				echo $formHTML;   
				echo $labelsHTML;
				
            ?>                
			</div>
        </div>
    </div>

<script>




function toggleArchived(){
	
	var archivedItems = document.getElementsByClassName('archived');
	var checkBox = document.getElementById("archived_checkbox");
	
	if (checkBox.checked == true){
		for (var i = 0; i < archivedItems.length; i ++) {
			archivedItems[i].style.display = '';
		}
	} else {
		for (var i = 0; i < archivedItems.length; i ++) {
			archivedItems[i].style.display = 'none';
		}
	} 
		
}

function toggleStocked(){
	
	var nonstockItems = document.getElementsByClassName('nonstock');
	var checkBox = document.getElementById("nonstock_checkbox");
	
	if (checkBox.checked == true){
		for (var i = 0; i < nonstockItems.length; i ++) {
			nonstockItems[i].style.display = '';
		}
	} else {
		for (var i = 0; i < nonstockItems.length; i ++) {
			nonstockItems[i].style.display = 'none';
		}
	} 
		
}


$('#EquipmentTable').DataTable({
		'scrollX':true, 
		'paging':false, 
		'order':[[1, 'asc']],
		"columns": [
			{ "orderable": false },
			null,
			null,
			null
		  ]
		});


</script>

<?php 
include_once PUBLIC_FILES . '/modules/footer.php' ; 
?>
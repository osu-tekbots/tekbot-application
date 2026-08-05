<?php
include_once '../bootstrap.php';

use DataAccess\InventoryDao;
use DataAccess\UsersDao;
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


if (isset($_REQUEST['location'])|| $_SESSION["location"] == 'all') {
	if ($_REQUEST['location'] == 'all')
		unset($_SESSION['location']);
	else
		$_SESSION['location'] = $_REQUEST['location'];
}

include_once PUBLIC_FILES . '/modules/header.php';
include_once PUBLIC_FILES . '/modules/employee.php';

$inventoryDao = new InventoryDao($dbConn, $logger);
$userDao = new UsersDao($dbConn, $logger);

if (isset($_SESSION['location']))
	$parts = $inventoryDao->getInventoryByLocation($_SESSION['location']);
else
	$parts = $inventoryDao->getInventory();

//Setting up basic dropdown for locations
$locationsHTML = "";
$locationsHTML .= "<div class='form-row'>
				<div class='form-group col-sm-3'>
					<label for = 'locationType'> Select Location: <select name='locationType' id='locationType' class='form-control' onChange = updateLocation(locationType.value) >
					</label>";

$locationsArray = $inventoryDao->getAllUniquePartLocations();

$selectedString = '';
if(!isset($_SESSION["location"]) || $_SESSION["location"] == 'all') {
	$selectedString = 'selected';
}
$locationsHTML .= "<option value = 'all' id = 'all' ".$selectedString.">All</option>";

//Adds every option with unique locatiomn
foreach($locationsArray AS $location) {
	$selectedBool = (isset($_SESSION['location']) && $location == $_SESSION['location']);
	$selectedString = '';
	if($selectedBool) {
		$selectedString = 'selected';
	}
	$locationsHTML .= "<option id = ".$location." value='".$location."' ".$selectedString.">".$location."</option>";
}


//finishes location dropdown
$locationsHTML .= "</select>
				</div>
			</div>";
			

$options = "<div class='form-row'>
				<div class='form-group col-sm-3'>
					<select name='labeltype' id='labeltype' class='form-control'>
						<option value='1'>Large Inventory Label</option>
						<option value='2'>Small Inventory Label</option>
						<option value='3'>Touchnet Ordering Label</option>
						<option value='4'>Simple Kit Label</option>
						<option value='5'>Detailed Kit Label</option>
					</select>
				</div>
				<div class='form-group col-sm-1'>
					<button class='btn btn-info' type='submit' form='mainform'>Get Selected Labels</button>
				</div>
			</div>";
$selectAllHTML = "<label for='selectAll'>
                     <input 
                       type='checkbox' 
                       id='selectAll' 
                       onclick='handleSelectAllClick(this)'
                     > Select&nbsp;All
                   </label>";

$formHTML = "<form action='employeeInventoryLabelsPrint.php' method='get' target='_blank' id='mainform'>
				$options
				<table class='table' id='InventoryTable'>
                <caption>Current Inventory</caption>
                <thead>
                    <tr>
						<th>".$selectAllHTML."</th>
                        <th>Type</th>
                        <th>Description</th>
						<th>Touchnet ID</th>
						<th>Last Updated</th>
                        <th>Location</th>
                    </tr>
                </thead>
                <tbody>";
				
foreach ($parts as $p) {
	$stocknumber = $p->getStocknumber();
	$type = $p->getType();
	$description = $p->getName();
	$lastUpdated = date('Y-m-d', strtotime($p->getLastCounted()));
	$location = $p->getLocation();
	$touchnetId = $p->getTouchnetId();
	
	if ($p->getArchive() == 0)
		$formHTML .= "<tr>
		<td><input type='checkbox' id='checkbox$stocknumber' name='$stocknumber' class = 'selectButtons'></td>
		<td>$type</td>
		<td>Stock: $stocknumber<BR>$description</td>
		<td>$touchnetId</td>
		<td>$lastUpdated</td>
		<td>$location</td>
		</tr>";
}

$formHTML .= "</tbody>
			</table>
			</form>";

?>
<script type='text/javascript'>
	function handleSelectAllClick(selectAll) {
		console.log("IN HANDLE SELECT ALL");
		const elements = document.getElementsByClassName('selectButtons');
		Array.from(elements).forEach(element => {
			element.checked = selectAll.checked;
		});
	}

	function updateLocation(location) {
		let data = {
			location: location,
			action: 'updateLocationState'
		};
		
		api.post('/inventory.php', data).then(res => {
            snackbar(res.message, 'info');
			setTimeout(() => window.location.reload(), 500);
			console.log(document.getElementByValue(location).selected);
			document.getElementById(location).selected = true;
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
				echo $locationsHTML;
				
				echo $formHTML;   
				echo $labelsHTML;
				
            ?>                
			</div>
        </div>
    </div>

<script>

$('#InventoryTable').DataTable({
		"autoWidth": true,
		'scrollX':false, 
		'paging':false, 
		'order':[[1, 'asc'], [2, 'asc']],
		"columns": [
			{ "orderable": false },
			null,
			null,
			null,
			null,
			null
		  ]
		});


</script>

<?php 
include_once PUBLIC_FILES . '/modules/footer.php' ; 
?>
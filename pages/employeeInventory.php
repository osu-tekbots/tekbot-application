<?php
include_once '../bootstrap.php';

use DataAccess\InventoryDao;
use DataAccess\UsersDao;
use Util\Security;

if (!session_id()) {
    session_start();
}

// Make sure the user is logged in and allowed to be on this page
include_once PUBLIC_FILES . '/lib/shared/authorize.php';

allowIf(verifyPermissions('employee'), 'index.php');


$time_start = microtime(true);
$log ='';

function time_point(){
	global $time_start;
	
	$exec_time = number_format((microtime(true) - $time_start) * 1000,2);
	$time_start = microtime(true);
	return $exec_time;
}

function studentPrice($price){
	$markup = .15;
	if ($price == 0)
		return ('$0.00');
		
	$price = (($price * $markup) > .1 ? (1+$markup) * $price : $price + .1) ;
	
	if ( (1 / $price ) < 1 )
		return ('$' . ceil($price) . '.00');
	else if (intval((1 / $price )) == 1 )
		return ('$1.00');
	else if ((1 / $price ) < 2 )
		return ('2 for $1');
	else if ((1 / $price ) < 3 )
		return ('3 for $1');
	else if ((1 / $price ) < 4 )
		return ('4 for $1');
	else 
		return ('Free for one / 10 for $1');
	
}


$title = 'Employee Inventory List';
$css = array(
	'assets/css/sb-admin.css',
	'assets/css/admin.css',
	'assets/css/jquery.dataTables.min.css'
);
$js = array(
    'assets/js/jquery.dataTables.min.js'
);


include_once PUBLIC_FILES . '/modules/header.php';
include_once PUBLIC_FILES . '/modules/employee.php';
include_once PUBLIC_FILES . '/modules/renderBrowse.php';

$log .= "Time to Load Headers: " . time_point() . " mS<BR>";

$inventoryDao = new InventoryDao($dbConn, $logger);
$userDao = new UsersDao($dbConn, $logger);
$types = $inventoryDao->getTypes();

if (isset($_REQUEST['archive']))
	$_SESSION['archive'] = $_REQUEST['archive'];

if (!isset($_SESSION['archive']))
	$_SESSION['archive'] = 1;


if (isset($_REQUEST['typeid']))
	$_SESSION['typeid'] = $_REQUEST['typeid'];

if (!isset($_SESSION['typeid']))
	$_SESSION['typeid'] = 0;

$log .= "<BR>Session Archive = " . $_SESSION['archive'] . "<BR>";

if ($_SESSION['typeid'] == 0){
	if ($_SESSION['archive'] == 0)
		$parts = $inventoryDao->getInventory(0);
	else
		$parts = $inventoryDao->getInventory();
} else {
	if ($_SESSION['archive'] == 0)
		$parts = $inventoryDao->getInventoryByTypeId($_SESSION['typeid'], 0);
	else
		$parts = $inventoryDao->getInventoryByTypeId($_SESSION['typeid']);
}

$log .= "Time to Load Records: " . time_point() . " mS<BR>";

$types_select = '';
foreach ($types as $t){
	if ($_SESSION['typeid'] == $t['typeId'])
		$types_select .= "<option selected value='".$t['typeId']."'>".$t['type']."</option>";
	else
		$types_select .= "<option value='".$t['typeId']."'>".$t['type']."</option>";
}

$filterDiv = "";
if ($_SESSION['archive'] == 1)
	$filterDiv .= '<div><input type="checkbox" id="archived_checkbox" onchange="toggleArchived();" checked>';
else
	$filterDiv .= '<div><input type="checkbox" id="archived_checkbox" onchange="toggleArchived();">';
$filterDiv .= ' Display Archived? | ';
$filterDiv .= '<input type="checkbox" id="nonstock_checkbox" onchange="toggleStocked();" checked>';
$filterDiv .= ' Display Non-Stocked? | ';
$filterDiv .= 'Select Category: <select id="categorySelect" onchange="loadCategory();"><option value = "0">All</option>'.$types_select.'</select></div>';

$types_select = '';
foreach ($types as $t){
	if($t['archived'])
		continue;
	$types_select .= "<option value='".$t['typeId']."'>".$t['type']."</option>";
}

$addpartHTML = "";
$addpartHTML .= "<div class='form-row'>
					<div class='col'><select class='custom-select' id='addtype'><option value='0'></option>$types_select</select></div>
					<div class='col'><input type='text' id='adddescription' class='form-control' placeholder='Part/Kit Description'></div>
					<button id='addpart' class='btn btn-primary' onclick='addpart();'>Add Part/Kit</button>
				  </div><BR>";

function printKitsUsedInt($kits) {
	$kitStr = '';
	foreach($kits as $k){
		$kitStr .= $k['kitNames'];
	}
	return $kitStr;
}
?>


<br/>
<div id="page-top">

	<div id="wrapper">

	<?php 
		// Located inside /modules/employee.php
		renderEmployeeSidebar();
	?>

    <div class="admin-content" id="content-wrapper">
        <div class="container-fluid">
			<div class='admin-paper' style="overflow-x: scroll">
            <?php 

               echo $addpartHTML;
			   
				$inventoryHTML = '';
				
				$log .= "Time to Prepare for Loop: " . time_point() . " mS<BR>";
				
                foreach ($parts as $p) {
					$stocknumber = $p->getStocknumber();
					$type = $p->getType();
					$description = $p->getName();
					$lastPrice = $p->getLastPrice();
					$location = $p->getLocation();
					$quantity = $p->getQuantity();
					$image = $p->getImage();
					$datasheet = $p->getDatasheet();
					$kitsUsedIn = $inventoryDao->getKitsUsedInByStocknumber($stocknumber);
					$kitsUsedInStr = '';
					foreach($kitsUsedIn as $k){
						//$kitsUsedInStr .= $k['kitNames'];
						$kitsUsedInStr .= "".$k['kitNames']." ";
					}
					$dateUpdated = $p->getLastUpdated();
					
					$inventoryHTML .= "<tr class='".($p->getArchive() == 1 ?'archived ':'')." ".($p->getStocked() == 1 ?'':'nonstock ')."' style='".($p->getArchive() == 1 ?'background-color: rgb(255, 230, 230);':'')."'>
						<td>$type</td>
						<td>$description<BR>Stock: $stocknumber</td>
						<td>".($image != '' ?"<a target='_blank' href='../../inventory_images/$image'>Image</a>":'')."</td>
						<td>".($datasheet != '' ?"<a target='_blank' href='../../inventory_datasheets/$datasheet'>Datasheet</a>":'')."</td>
						<td>".($p->getArchive() == 1 ?'Archived':'')."</td>
						<td>"."-".$kitsUsedInStr."</td>
						<td>\$".number_format(floatval($lastPrice),2)."</td>
						<td>".studentPrice($lastPrice,2)."</td>
						<td><input class='form-control' type='text' id='loc$stocknumber' value='$location' onchange='updateLocation(\"$stocknumber\")'></td>
						<td><input class='form-control' type='number' id='quantity$stocknumber' value='$quantity' onchange='updateQuantity(\"$stocknumber\")'></td>
						<td>".$dateUpdated."</td>
						<td><a href='./pages/employeeInventoryPart.php?stocknumber=$stocknumber'>Edit</a></td></tr>";
                }
				
				$log .= "Time spent in Loop: " . time_point() . " mS<BR>";
				

			

echo $filterDiv;			
            ?>
			<table class='table' id='InventoryTable'>
                <caption>Current Inventory</caption>
                <thead>
                    <tr>
						<th>Type</th>
                        <th>Description</th>
						<td></td>
						<td></td>
						<td></td>
						<th>Kits<BR>Used In</th>
                        <th>Last Price</th>
                        <th>Student<BR>Price</th>
                        <th>Location</th>
                        <th>Quantity</th>
						<th>Last Updated</th>
						<th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php echo $inventoryHTML;?>
                </tbody>
            </table>
			 <?php echo $log;?>
			</div>
        </div>
    </div>


<script type='text/javascript'>
function addpart(){
	
	let type = $('#addtype').val().trim();
	let desc =  $('#adddescription').val().trim();
	let data = {
		type: type,
		desc: desc,
		action: 'addPart'
	};

	if (type != 0 && desc != ''){
		api.post('/inventory.php', data).then(res => {
			//console.log(res.message);
			snackbar(res.message, 'info');
			location.reload();
		}).catch(err => {
			snackbar(err.message, 'error');
		});
	} else {
		alert('Description field is empty or type is not selected. No changes made');
	}
}

function updateLocation(id){
	var loc = $('#loc'+id).val();
	
	let content = {
		action: 'updateLocation',
		stockNumber: id,
		location: loc
	}

	api.post('/inventory.php', content).then(res => {
		snackbar(res.message, 'info');
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
		snackbar(res.message, 'info');
	}).catch(err => {
		snackbar(err.message, 'error');
	});
}

function loadCategory(){	
	var category = $('#categorySelect').val();
	window.location.href = "https://eecs.engineering.oregonstate.edu/education/tekbotSuite/tekbot/pages/employeeInventory.php?typeid=" + category;
}

function toggleArchived(){
	var checkBox = document.getElementById("archived_checkbox");
	
	if (checkBox.checked == true){
		window.location.href = "https://eecs.engineering.oregonstate.edu/education/tekbotSuite/tekbot/pages/employeeInventory.php?archive=1";
	} else {
		window.location.href = "https://eecs.engineering.oregonstate.edu/education/tekbotSuite/tekbot/pages/employeeInventory.php?archive=0";
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


$('#InventoryTable').DataTable({
		"autoWidth": true,
		'scrollX':false, 
		'paging':false, 
		'order':[[0, 'asc'], [1, 'asc']],
		"columns": [
			null,
			null,
			{ "orderable": false },
			{ "orderable": false },
			{ "orderable": false },
			null,
			null,
			null,
			{ "orderable": false },
			{ "orderable": false },
			null,
			{ "orderable": false }
		  ]
		});


</script>

<?php 
include_once PUBLIC_FILES . '/modules/footer.php' ; 
?>
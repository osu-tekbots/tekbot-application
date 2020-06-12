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
	else if ((1 / $price ) < 5 )
		return ('5 for $1');
	else
		return ('Free for one / ' . number_format($price,2) . ' ea.');
return $price;
}

$isEmployee = isset($_SESSION['userID']) && !empty($_SESSION['userID']) 
	&& isset($_SESSION['userAccessLevel']) && $_SESSION['userAccessLevel'] == 'Employee';

allowIf($isEmployee, 'index.php');


$title = 'Employee Inventory List';
$css = array(
	'assets/css/sb-admin.css',
	'assets/css/admin.css',
	'https://cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css'
);
$js = array(
    'https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js'
);

include_once PUBLIC_FILES . '/modules/header.php';
include_once PUBLIC_FILES . '/modules/employee.php';
include_once PUBLIC_FILES . '/modules/renderBrowse.php';

$inventoryDao = new InventoryDao($dbConn, $logger);
$userDao = new UsersDao($dbConn, $logger);
$parts = $inventoryDao->getInventory();


?>
<script type='text/javascript'>
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
                renderEmployeeBreadcrumb('Employee', 'Inventory');

               
				$inventoryHTML = '';
				
                foreach ($parts as $p) {
					$stocknumber = $p->getStocknumber();
					$type = $p->getType();
					$description = $p->getName();
					$lastPrice = $p->getLastPrice();
					$location = $p->getLocation();
					$quantity = $p->getQuantity();
					$image = $p->getImage();
					$datasheet = $p->getDatasheet();
					
					$inventoryHTML .= "<tr class='".($p->getArchive() == 1 ?'archived':'')." ".($p->getStocked() == 1 ?'':'nonstock')."' style='".($p->getArchive() == 1 ?'background-color: rgb(255, 230, 230);':'')."'>
						<td>$type</td><td>Stock: $stocknumber<BR>$description</td><td>".($image != '' ?"<a target='_blank' href='../../inventory_images/$image'>Image</a>":'')."</td><td>".($datasheet != '' ?"<a target='_blank' href='../../inventory_datasheets/$datasheet'>Datasheet</a>":'')."</td>
						<td>".($p->getArchive() == 1 ?'Archived':'')."</td><td>".$inventoryDao->getKitsUsedInByStocknumber($stocknumber)."</td><td>\$".number_format($lastPrice,2)."</td><td>".studentPrice($lastPrice,2)."</td>
						<td><input class='form-control' type='text' id='location$stocknumber' value='$location' onchange='updateLocation(\"$stocknumber\")'></td>
						<td><input class='form-control' type='number' id='quantity$stocknumber' value='$quantity' onchange='updateQuantity(\"$stocknumber\")'></td><td><a href='./pages/employeeInventoryPart.php?stocknumber=$stocknumber'>Edit</a></td></tr>";
                }
            ?>
			<div><p>Display Archived? <input type="checkbox" id="archived_checkbox" onchange="toggleArchived();" checked> | Display Non-Stocked? <input type="checkbox" id="nonstock_checkbox" onchange="toggleStocked();" checked></p></div>
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
						<th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php echo $inventoryHTML;?>
                </tbody>
            </table>
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


$('#InventoryTable').DataTable({
		'scrollX':true, 
		'paging':false, 
		'order':[[0, 'asc']],
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
			{ "orderable": false }
		  ]
		});


</script>

<?php 
include_once PUBLIC_FILES . '/modules/footer.php' ; 
?>
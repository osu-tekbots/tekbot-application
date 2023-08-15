<?php
include_once '../bootstrap.php';

use DataAccess\InventoryDao;
use DataAccess\UsersDao;
use Util\Security;


// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL); 


if (!session_id()) {
    session_start();
}

// Make sure the user is logged in and allowed to be on this page
include_once PUBLIC_FILES . '/lib/shared/authorize.php';

allowIf(verifyPermissions('employee'), 'index.php');

$inventoryDao = new InventoryDao($dbConn, $logger);
$userDao = new UsersDao($dbConn, $logger);


if(isset($_REQUEST['action'])){
	if ($_REQUEST['action'] == 'loadDescriptionSelect'){
		$names = $inventoryDao->getInventoryByTypeId($_REQUEST['typeId']);
		$namesOptions = '';
		foreach ($names as $n)
			if ($n->getArchive() == 0) //Only non archived items
				$namesOptions .= "<option value='".$n->getStocknumber()."'>".$n->getName()."</option>";
		echo $namesOptions;
		exit();
	}
	if ($_REQUEST['action'] == 'loadAddImage'){
		$part = $inventoryDao->getPartByStocknumber($_REQUEST['stockNumber']);
		$image = $part->getImage();
		echo "../../inventory_images/" . ($image != '' ? $image : 'noimage.jpg');
		exit();
	}
}


$title = 'Employee Inventory Order Parts';
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

$types = $inventoryDao->getTypes();
$typeOptions = "<option value=''>---</option>";
foreach ($types as $t){
	$typeOptions .= "<option value='".$t['typeId']."'>".$t['type']."</option>";
}
$typeOptions .= "</select>";


$item = array();
$i = 0;
for ($i = 0; $i < 20; $i++){ //Max of 20 items in one order
	$key='item' . $i;
	$value = 'qty' . $i;
	if (isset($_REQUEST[$key])){
		if (array_key_exists($_REQUEST[$key], $item))
			$item[$_REQUEST[$key]] += $_REQUEST[$value];
		else
			$item[$_REQUEST[$key]] = $_REQUEST[$value];
		}
}


$formHTML = "";
//added column labels 
$formHTML .= "<div class='row'>
				<div class='col-sm-4'> Part Type:</div>
				<div class='col-sm-4'> Part Name:</div>
				<div class='col-sm-2'> Quantity Needed:</div>
			</div>";

$formHTML .= "<form action=''>";
for($i=1;$i<11;$i++){
	$formHTML .= "
	<div class='form-row'>
	<div class='form-group col-sm-4'><select id='typeselect$i' onchange='updateNames($i);'>$typeOptions</select></div>
	<div class='form-group col-sm-4'><select id='nameselect$i' name='item$i'></select></div>
	<div class='form-group col-sm-2'><input name='qty$i' type='text'></div>
	</div>";
}			
$formHTML .= "<div class='form-group col-sm-2'><button type='submit' class='btn btn-warning'>Submit</button></div></form>";

$orderHTML = "";
$addHTML = "";
$log = "";
if (count($item) > 0){ // Order to generate
	/*
	//Add in low stock items
	$query = "SELECT `tekbots_inventory`.*, (tekbots_parts.partMargin - tekbots_inventory.Quantity) AS need FROM `tekbots_inventory` INNER JOIN `tekbots_parts` ON tekbots_inventory.StockNumber = tekbots_parts.StockNumber WHERE tekbots_parts.partMargin > tekbots_inventory.Quantity GROUP BY tekbots_inventory.StockNumber";
	$result = $mysqli->query($query);
	while ($row = mysqli_fetch_assoc($result)){
		if (array_key_exists($row['StockNumber'], $item))
			$item[$row['StockNumber']] += $row['need'];
		else
			$item[$row['StockNumber']] = $row['need'];
	}
	*/
	
	
	$orderHTML .= '<h2>Ordering parts for:</h2>';
	foreach ($item AS $key => $value){
			$p = $inventoryDao->getPartByStocknumber($key);
			$orderHTML .= '<BR>' . $p->getType() . ' - ' . $p->getName() . ' => Buying: ' . ($value-$p->getQuantity());	
	}
	
	$order = Array();
	
	foreach ($item AS $key => $need) {
		$part = $inventoryDao->getPartByStocknumber($key);
		if ($part->getTypeId() == 1){ //Is a Kit
			if (array_key_exists($key, $order))
				$order[$key] += $need;
			else
				$order[$key] = $need - $part->getQuantity();		
			$log .= "$key is a kit. We have ".$part->getQuantity(). " and need ".($need)."<BR>";
			$value1 = $need - $part->getQuantity();
			
			if ($order[$key] > 0) { // We need to make some of these kits
				$contents1 = $inventoryDao->getKitContentsByStocknumber($key);
				foreach ($contents1 AS $key1 => $need1){
					$part1 = $inventoryDao->getPartByStocknumber($key1);
					if ($part1->getTypeId() == 1){ //Is a Kit
						if (array_key_exists($key1, $order))
							$order[$key1] += $need1*$value1;
						else
							$order[$key1] = $need1*$value1 - $part1->getQuantity();	
						$log .= "$key1 is a KIT. We have ".$part1->getQuantity()." and need $need1 * $value1 = ".($need1*$value1)."<BR>";
						
						$value2 = ($need1*$value1) - $part1->getQuantity();
						if ($order[$key1] > 0) { // We need to make some of these kits
							$contents2 = $inventoryDao->getKitContentsByStocknumber($key1);
							foreach ($contents2 AS $key2 => $need2){
								$part2 = $inventoryDao->getPartByStocknumber($key2);
								if ($part2->getTypeId() == 1){ //Is a Kit
									if (array_key_exists($key2, $order))
										$order[$key2] += $need2*$value2;
									else
										$order[$key2] = $need2*$value2 - $part2->getQuantity();
									$log .= "$key2 is a kit. We have ".$part2->getQuantity()." and need ".($need2*$value2)."<BR>";
									$value3 = ($need2*$value2) - $part2->getQuantity();
									
									if ($order[$key2] > 0) { // We need to make some of these kits
										$contents3 = $inventoryDao->getKitContentsByStocknumber($key2);
										foreach ($contents3 AS $key3 => $need3){			
												$log .= "3: $key3 is a part. We have ".$part2->getQuantity()." and need $need3 * $value3 = ".($need3*$value3)."<BR>";
												if (array_key_exists($key3, $order))
													$order[$key3] += $need3*$value3;
												else
													$order[$key3] = $need3*$value3 - $part2->getQuantity();							
										}
									}
								} else {
									$log .= "2: ".$part2->getName()." is a part. We have ".$part2->getQuantity()." and need $need2 * $value2 = ".($need2*$value2)."<BR>";
									if (array_key_exists($key2, $order))
										$order[$key2] += $need2*$value2;
									else
										$order[$key2] = $need2*$value2- $part2->getQuantity();	
								}				
							}
						}
					} else {
						$log .= "1: ".$part1->getName()." is a part. We have ".$part1->getQuantity()." and need ".($need1*$value1)."<BR>";
						if (array_key_exists($key1, $order))
							$order[$key1] += $need1*$value1;
						else
							$order[$key1] = $need1*$value1 - $part1->getQuantity();	
					}				
				}
			}
		} else{
			$log .= "0: $key is a part. We have ".$part->getQuantity()." and need ".($need)."<BR>";
			if (array_key_exists($key, $order))
				$order[$key] += $need;
			else
				$order[$key] = $need - $part->getQuantity();
			}
	}
	
	$orderHTML .= "<p>Display Non-Order? <input type='checkbox' id='nonorder_checkbox' onchange='toggleNonOrder();' checked></p>
					<table class='table' id='OrderTable'>";
	$orderHTML .= "<thead>
                    <tr>
						<th>Type</th>
                        <th>Description</th>
						<th>To Order</th>
						<th>Location</th>
						<th>Supplier Info</th>
						<th>Stock</th>
						<th>Price Paid</th>
                    </tr>
                </thead>";
	foreach($order AS $key => $need){
		$p = $inventoryDao->getPartByStocknumber($key);
		$supplier = $inventoryDao->getSuppliersByStocknumber($key);
		$supplierHTML = "";
		foreach($supplier AS $s){
			if ($s['SupplierContact'] != '')
				$supplierHTML .= $s['SupplierName'] . ": <a target='_blank' href='". $s['SupplierContact'] . $s['SupplierPart'] . "'>" . $s['SupplierPart'] . "</a><BR>";
			else
				$supplierHTML .= "<a target='_blank' href='" . ($s['link'] != '' ? $s['link'] : $s['SupplierPart']) . "'>" . $s['SupplierName'] . "</a><BR>";
		}
			$orderHTML .= "<tr class='".($need < 0 ?'nonorder':'')."'><td>".$p->getType()."</td>";
			$orderHTML .= "<td>".$p->getName()."</td>";
			$orderHTML .= "<td>Order: $need</td>";
			$orderHTML .= "<td>".$p->getLocation()."</td>";
			$orderHTML .= "<td>".($need > 0 ? $supplierHTML : '')."</td>";
			$orderHTML .= "<td><input class='form-control' type='number' id='quantity$key' value='".$p->getQuantity()."' onchange='updateQuantity(\"$key\")'></td>";
			$orderHTML .= "<td>$ <input = type='text' id='lastprice$key' onchange='updateLastPrice(\"$key\");' value='".number_format($p->getLastPrice(),2)."'></td></tr>";	
	}
	$orderHTML .= "</table>";
	
	
	
	
	/*
	$kit = $inventoryDao->getPartByStocknumber($stocknumber);
	$description = $kit->getName();//
	//$lastPrice = $kit->getLastPrice();//
	$image = $kit->getImage();//
		
	$contents = $inventoryDao->getKitContentsByStocknumber($stocknumber);// Get the list of stocknumbers/quantity of each in the kit
	
	$contentsHTML = "<h4>Contents</h4><table id='ContentsTable' style='width:100%;'>
                <thead>
                    <tr>
						<th>Type</th>
                        <th >Description</th>
						<th>Quantity</th>
						<th></th>
                    </tr>
                </thead>
                <tbody>";
	foreach ($contents AS $key => $value){
		$p = $inventoryDao->getPartByStocknumber($key);
		
		$contentsHTML .= "<tr><td>".$p->getType()."</td><td><a href='./pages/employeeInventoryKits.php?stocknumber=$key'>".$p->getName()."</a></td><td><input type='text' value='$value' id='quantity$stocknumber' onchange='updateKitQuantity(\"$stocknumber\",\"$key\");'></td><td><button class='btn btn-warning' onclick='removeKitContents(\"$stocknumber\",\"$key\");'>Remove</button></td></tr>";	
	}
	$contentsHTML .= "</tbody></table>";
	
	if ($kit->getTypeId() == 1){ //This is a kit
	
	$addHTML = "<form><div style='padding-left:4px;padding-right:4px;margin-top:4px;margin-bottom:4px;'><div class='form-row'>
						<div class='form-group col-sm-9'>
						<HR><h4>Add Item</h4><table>";
	$addHTML .= "<tr><td>$typeSelect</td><td id='nameselect'></td><td><input type='text' id='newquantity' placeholder='Quantity in Kit'></td><td><button class='btn btn-success' onclick='addKitContents(\"$stocknumber\");'>Add</button></td></tr>";	
	$addHTML .= "</table></div><div class='col-sm-3'><img src='' class='img-fluid rounded-lg' id='addImage'></div></div></div></form>";
	}
	
	$kitHTML .= "<h3>Stock Number: $stocknumber</h3>
				<form>
				<div style='padding-left:4px;padding-right:4px;margin-top:4px;margin-bottom:4px;'>
					<div class='form-row'>
						<div class='form-group col-sm-9'>
						<h3>Kit: $description</h3>
						$contentsHTML
						</div>
						<div class='col-sm-3'>
							<div class='form-group'><label for='partImage' >Image <a href='../../inventory_images/".($image != '' ? $image : 'noimage.jpg')."' target='_blank'>".($image != '' ? $image : '')."</a></label><img src='../../inventory_images/".($image != '' ? $image : 'noimage.jpg')."' class='img-fluid rounded-lg' id='partImage'></div>
						</div>
					</div>
				</div>
				</form>
				
				";
	
	*/
} 

?>
<script type='text/javascript'>
function toggleNonOrder(){
	
	var nonorderItems = document.getElementsByClassName('nonorder');
	var checkBox = document.getElementById("nonorder_checkbox");
	
	if (checkBox.checked == true){
		for (var i = 0; i < nonorderItems.length; i ++) {
			nonorderItems[i].style.display = '';
		}
	} else {
		for (var i = 0; i < nonorderItems.length; i ++) {
			nonorderItems[i].style.display = 'none';
		}
	} 
		
}

function updateNames(id){
	var typeid = $('#typeselect'+id).val();
	//Since we only have one Ajax call on this whole page, I will include it in this page at the top.
	//Add Ajax call for name select. Select shoudl use stock numbers and be called #newdescription
	$.ajax({
		type: 'POST',
		url: './pages/employeeInventoryOrderParts.php',
		dataType: 'html',
		data: {typeId: typeid,
				action: 'loadDescriptionSelect'},
		success: function(result)
				{
				$('#nameselect'+id).fadeOut('fast', function() {
					$('#nameselect'+id).html(result);
				});
				$('#nameselect'+id).fadeIn('fast');
				},
		error: function (xhr, ajaxOptions, thrownError) {
					alert(xhr.status);
					alert(xhr.responseText);
					alert(thrownError);
				}
			});
}

function loadAddImage(){
	var stockNumber = $('#newdescription').val();
	//Since we only have one Ajax call on this whole page, I will include it in this page at the top.
	//Add Ajax call for name select. Select shoudl use stock numbers and be called #newdescription
	$.ajax({
		type: 'POST',
		url: './pages/employeeInventoryKits.php',
		dataType: 'html',
		data: {stockNumber: stockNumber,
				action: 'loadAddImage'},
		success: function(result)
				{
				$('#addImage').attr("src",result);
				$('#addImage').show();
				},
		error: function (xhr, ajaxOptions, thrownError) {
					alert(xhr.status);
					alert(xhr.responseText);
					alert(thrownError);
				}
			});
}

function updateLastPrice(id){
	var lastprice = $('#lastprice'+id).val();
	
	let content = {
		action: 'updateLastPrice',
		stockNumber: id,
		lastPrice: lastprice
	}

	api.post('/inventory.php', content).then(res => {
		snackbar(res.message, 'Updated');
		var inputF = document.getElementById('lastprice'+id);
		inputF.value = (lastprice*1).toFixed(2);
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
			
            <?php echo $formHTML .'<br>'.$orderHTML.'<br>'.$log;?>
			
			

			</div>
        </div>
    </div>
	
<script>

$(document).ready(function() {
	$('#addImage').hide();

	$('#OrderTable').DataTable({
			"autoWidth": false,
			"searching": false,
			'scrollX':true, 
			'paging':false, 
			'order':[[0, 'asc']],
			"columns": [
				null,
				null,
				{ "orderable": false },
				null,
				{ "orderable": false },
				{ "orderable": false },
				{ "orderable": false }					
			  ]
			});
});
</script>

<?php 
include_once PUBLIC_FILES . '/modules/footer.php' ; 
?>
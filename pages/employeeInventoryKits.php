<?php
include_once '../bootstrap.php';

use DataAccess\InventoryDao;
use DataAccess\UsersDao;
use Util\Security;

/*
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL); 
*/

if (!session_id()) {
    session_start();
}

// Make sure the user is logged in and allowed to be on this page
include_once PUBLIC_FILES . '/lib/shared/authorize.php';

$isEmployee = isset($_SESSION['userID']) && !empty($_SESSION['userID']) 
	&& isset($_SESSION['userAccessLevel']) && $_SESSION['userAccessLevel'] == 'Employee';

allowIf($isEmployee, 'index.php');

$inventoryDao = new InventoryDao($dbConn, $logger);
$userDao = new UsersDao($dbConn, $logger);

if(isset($_REQUEST['action'])){
	if ($_REQUEST['action'] == 'loadDescriptionSelect'){
		$names = $inventoryDao->getInventoryByTypeId($_REQUEST['typeId']);
		$namesSELECT = '<select id="newdescription" onchange="loadAddImage();">';
		foreach ($names as $n)
			$namesSELECT .= "<option value='".$n->getStocknumber()."' ".($n->getArchive() == 1 ? " style='color:red;'" :'').">".($n->getArchive() == 1 ?'ARCHIVED: ':'').$n->getName()."</option>";
		$namesSELECT .= "</select>";
		echo $namesSELECT;
		exit();
	}
	if ($_REQUEST['action'] == 'loadAddImage'){
		$part = $inventoryDao->getPartByStocknumber($_REQUEST['stockNumber']);
		$image = $part->getImage();
		echo "../../inventory_images/" . ($image != '' ? $image : 'noimage.jpg');
		exit();
	}
}


$title = 'Employee Inventory Configure Kits';
$css = array(
	'assets/css/sb-admin.css',
	'assets/css/admin.css',
	'https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css',
	'https://cdn.datatables.net/buttons/1.6.2/css/buttons.dataTables.min.css'
	
    
);
$js = array(
    'https://code.jquery.com/jquery-3.5.1.js',
	'https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js',
	'https://cdn.datatables.net/buttons/1.6.2/js/dataTables.buttons.min.js',
	'https://cdn.datatables.net/buttons/1.6.2/js/buttons.print.min.js'
);


if (isset($_REQUEST['stocknumber']))
	if (isset($_REQUEST['stocknumber']) && $_REQUEST['stocknumber'] != ''){
		$stocknumber = $_REQUEST['stocknumber'];
	}

$types = $inventoryDao->getTypes();
$typeSelect = "<select id='typeselect' onchange='updateAddContents();'><option value=''>---</option>";
foreach ($types as $t){
	$typeSelect .= "<option value='".$t['typeId']."'>".$t['type']."</option>";
}
$typeSelect .= "</select>";



$kitsSELECT = "<form action=''><div class='form-row print-hide'><div class='form-group col-sm-9'><select name='stocknumber'>";
$tempkits = $inventoryDao->getInventoryByTypeId(1); //Gets Kits
$kits = array();
$kitsarchived = array();
foreach ($tempkits AS $k)
	if ($k->getArchive() == 0)
		$kits[] = $k;
	else
		$kitsarchived[] = $k;
	
foreach ($kits AS $k){
	$stock = $k->getStocknumber();//
	$description = $k->getName();//
	
	if (isset($stocknumber))
		$kitsSELECT .= "<option value='$stock' ".($stocknumber == $stock ? 'selected' : '' ).">$description</option>";
	else
		$kitsSELECT .= "<option value='$stock'>$description</option>";
}
foreach ($kitsarchived AS $k){
	$stock = $k->getStocknumber();//
	$description = $k->getName();//
	
	if (isset($stocknumber))
		$kitsSELECT .= "<option value='$stock' ".($stocknumber == $stock ? 'selected' : '' )." style='color:red;'>ARCHIVED: $description</option>";
	else
		$kitsSELECT .= "<option value='$stock' style='color:red;'>ARCHIVED: $description</option>";
}


$kitsSELECT .= "</select></div><div class='form-group col-sm-3'><button class='btn btn-warning' type='submit' value=''>Load</button></div></div></form>";

$kitHTML = "";
$addHTML = "";
if (isset($stocknumber)){ // Display single kit information 
	$kit = $inventoryDao->getPartByStocknumber($stocknumber);
	
	$title = $kit->getName() . " Parts List";
	$description = $kit->getName();//
	//$lastPrice = $kit->getLastPrice();//
	$image = $kit->getImage();//
		
	$contents = $inventoryDao->getKitContentsByStocknumber($stocknumber);// Get the list of stocknumbers/quantity of each in the kit

	$contentsHTML = "<h4>Contents as of ". date("m-d-y",time()) . "</h4><table id='ContentsTable'>
                <thead>
                    <tr>
						<th>Type</th>
                        <th>Description</th>
						<th>Location</th>
						<th>Cost (each)</th>
						<th>Quantity<BR>per Kit</th>
						<th>Stock</th>
						<th></th>
                    </tr>
                </thead>
                <tbody>";
	foreach ($contents AS $key => $value){
		$p = $inventoryDao->getPartByStocknumber($key);
		
		$contentsHTML .= "<tr><td>".$p->getType()."</td>
		<td><a href='./pages/employeeInventoryKits.php?stocknumber=$key'>".$p->getName()."</a></td>
		<td>".$p->getLocation()."</td><td>$".number_format($p->getLastPrice(),2)."</td>
		<td><input type='text' value='$value' id='quantity$stocknumber' onchange='updateKitQuantity(\"$stocknumber\",\"$key\");'></td>
		<td><input class='form-control' type='number' id='stock$key' value='".$p->getQuantity()."' onchange='updateStock(\"$key\")'></td>
		<td><button type='button' class='btn btn-warning print-hide' onclick='removeKitContents(\"$stocknumber\",\"$key\");'>Remove</button></td></tr>";	
	}
	$contentsHTML .= "</tbody></table>";
	
	if ($kit->getTypeId() == 1){ //This is a kit
	
	$addHTML = "<form><div style='padding-left:4px;padding-right:4px;margin-top:4px;margin-bottom:4px;'><div class='form-row print-hide'>
						<div class='form-group col-sm-9'>
						<HR><h4>Add Item</h4><table>";
	$addHTML .= "<tr><td>$typeSelect</td><td id='nameselect'></td><td><input type='text' id='newquantity' placeholder='Quantity in Kit'></td><td><button class='btn btn-success' onclick='addKitContents(\"$stocknumber\");'>Add</button></td></tr>";	
	$addHTML .= "</table></div><div class='col-sm-3'><img src='' class='img-fluid rounded-lg' id='addImage'></div></div></div></form>";
	}
	
	$kitHTML .= "<h3>Stock Number: $stocknumber</h3>
				
				<form>
				<div style='padding-left:4px;padding-right:4px;margin-top:4px;margin-bottom:4px;'>
					<div class='form-row'>
						<div class='form-group col-sm-9 content-div'>
						<h3>Kit: $description</h3>
						$contentsHTML
						</div>
						<div class='col-sm-3 print-hide'>
							<div class='form-group'><label for='partImage' >Image <a href='../../inventory_images/".($image != '' ? $image : 'noimage.jpg')."' target='_blank'>".($image != '' ? $image : '')."</a></label><img src='../../inventory_images/".($image != '' ? $image : 'noimage.jpg')."' class='img-fluid rounded-lg' id='partImage'></div>
						</div>
					</div>
				</div>
				</form>
				
				";
	
	
} 

include_once PUBLIC_FILES . '/modules/header.php';
include_once PUBLIC_FILES . '/modules/employee.php';
include_once PUBLIC_FILES . '/modules/renderBrowse.php';


?>

<style>
@media print {
	
body {
	font-size: small;
}

header, nav, .sidebar, footer {
	display: none;
	}
	
.print-hide {
	display: none;
	}

.content-div {
	width: 100% !important;
	}
	
table {
	font-size: small;
	}

}
</style>

<script type='text/javascript'>
function updateStock(id){
	var amount = $('#stock'+id).val();
	
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

function updateAddContents(){
	var typeid = $('#typeselect').val();
	//Since we only have one Ajax call on this whole page, I will include it in this page at the top.
	//Add Ajax call for name select. Select shoudl use stock numbers and be called #newdescription
	$.ajax({
		type: 'POST',
		url: './pages/employeeInventoryKits.php',
		dataType: 'html',
		data: {typeId: typeid,
				action: 'loadDescriptionSelect'},
		success: function(result)
				{
				$('#nameselect').fadeOut('fast', function() {
					$('#nameselect').html(result);
				});
				$('#nameselect').fadeIn('fast');
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

function updateKitQuantity(id,childid){
	var quantity = $('#quantity'+id).val();
	
	let content = {
		action: 'updateKitQuantity',
		stockNumber: id,
		quantity: quantity,
		childid: childid
	}

	api.post('/inventory.php', content).then(res => {
		snackbar(res.message, 'Updated');
	}).catch(err => {
		snackbar(err.message, 'error');
	});
}

function addKitContents(id){
	var childid = $('#newdescription').val();
	var quantity = $('#newquantity').val();
	
	let content = {
		action: 'addKitContents',
		stockNumber: id,
		childid: childid,
		quantity: quantity
	}

	api.post('/inventory.php', content).then(res => {
		snackbar(res.message, 'Added');
		location.reload();
	}).catch(err => {
		snackbar(err.message, 'error');
	});
}

function removeKitContents(id, childid){
	if (confirm("Are you sure you want to remove this item?") == true){
		let content = {
			action: 'removeKitContents',
			stockNumber: id,
			childid: childid
		}

		api.post('/inventory.php', content).then(res => {
			snackbar(res.message, 'Removed');
			window.location.reload();
		}).catch(err => {
			snackbar(err.message, 'error');
		});
	} 
}

function updatePartImage(id){
	var content = new FormData();
		content.append('action', 'updatePartImage');
		content.append('stockNumber', id);
		content.append('imageFile', $('#imageFile').prop('files')[0]);

	$.ajax({
		type:'POST',
		url: 'api/inventory-files.php',
		data: content,
		cache: false,
		contentType: false,
		processData: false,
		success:function(data){
			snackbar(data.message, 'Updated');
			location.reload();
		},
		error: function(data){
			console.log("error");
			console.log(data);
		}
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
                renderEmployeeBreadcrumb('Employee', 'Configure Kits');
            ?>
			
            <?php echo $kitsSELECT . $kitHTML . $addHTML;?>

			</div>
        </div>
    </div>
	
<script>


/*
 * explain the funcationality - include version of DT that works with this. 
*/
var printButtonExtension = {
    exportOptions: {
        format: {
            body: function ( data, row, column, node ) {
                //check if type is input using jquery
                return node.firstChild.tagName === "INPUT" ?
                        node.firstElementChild.value :
                        data;

            }
        }
    }
};


$(document).ready(function() {
	$('#addImage').hide();

	$('#ContentsTable').DataTable({
			'dom': 'Bft',
			'buttons': [
				$.extend( true, {}, printButtonExtension, {
            extend: 'print'
        } )
			], 
			"autoWidth": true,
			"searching": false,
			'scrollX': false,
			'paging': false,

			'order':[[0, 'asc'],[1, 'asc']],
			"columns": [
				null,
				null,
				null,
				{ "orderable": false },
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
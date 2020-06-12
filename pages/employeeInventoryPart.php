<?php
include_once '../bootstrap.php';

use DataAccess\InventoryDao;
use DataAccess\UsersDao;
use Util\Security;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL); 

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


$title = 'Employee Inventory Part Update';
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


if (isset($_REQUEST['stocknumber']) && $_REQUEST['stocknumber'] != ''){
	$stocknumber = $_REQUEST['stocknumber'];
}

$inventoryDao = new InventoryDao($dbConn, $logger);
$userDao = new UsersDao($dbConn, $logger);
$part = $inventoryDao->getPartByStocknumber($stocknumber);
$types = $inventoryDao->getTypes();

$stocknumber = $part->getStocknumber();//
$description = $part->getName();//
$lastPrice = $part->getLastPrice();//
$location = $part->getLocation();//
$quantity = $part->getQuantity();//
$image = $part->getImage();
$datasheet = $part->getDatasheet();
$touchnetId = $part->getTouchnetId();//
$originalImage = $part->getOriginalImage();
$lastSupplier = $part->getLastSupplier();//
$typeId = $part->getTypeId();//
$manufacturer = $part->getManufacturer();//
$manufacturerNumber = $part->getManufacturerNumber();//
$partMargin = $part->getPartMargin();//
$stocked = $part->getStocked();//
$archive = $part->getArchive();//
$marketPrice = $part->getMarketPrice();//
$comment = $part->getComment();
$lastUpdated = $part->getLastUpdated();
$lastCounted = $part->getLastCounted();


$types_select = '';
foreach ($types as $t){
	$types_select .= "<option value='".$t['typeId']."' ".($t['typeId'] == $typeId ?'selected':'').">".$t['type']."</option>";
}

$partHTML = '';





$partHTML .= "<h3>Stock Number: $stocknumber</h3>
			<form>
			<div style='padding-left:4px;padding-right:4px;margin-top:4px;margin-bottom:4px;'>
				<div class='form-row'>
					<div class='form-group col-sm-3'>
					<label for='type'>Type</label><select onchange='updateType(\"$stocknumber\");' id='type$stocknumber' class='form-control'>$types_select</select></div>
					<div class='form-group col-sm-9'>
					<label for='description' >Description</label><input type='text' class='form-control' onchange='updateDescription(\"$stocknumber\");' id='description$stocknumber' value='".Security::HtmlEntitiesEncode($description)."'></div>
				</div>
				<div class='form-row'>
					<div class='form-group col-sm-3'><label for='lastSupplier' >Last Supplier</label><input type='text' class='form-control' onchange='updateLastSupplier(\"$stocknumber\");' id='lastSupplier$stocknumber' value='".Security::HtmlEntitiesEncode($lastSupplier)."'></div>
					<div class='form-group col-sm-3'><label for='manufacturer'>Manufacturer</label><input type='text' class='form-control' onchange='updateManufacturer(\"$stocknumber\");' id='manufacturer$stocknumber' value='".Security::HtmlEntitiesEncode($manufacturer)."'></div>
					<div class='form-group col-sm-3'><label for='manufacturerNumber'>Manufacturer Number</label><input type='text' class='form-control' onchange='updateManufacturerNumber(\"$stocknumber\");' id='manufacturerNumber$stocknumber' value='".Security::HtmlEntitiesEncode($manufacturerNumber)."'></div>
				</div>
				<div class='form-row'>
					<div class='form-group col-sm-3'><label for='lastPrice'>Last Price</label><input type='text' class='form-control' onchange='updateLastPrice(\"$stocknumber\");' id='lastprice$stocknumber' value='".number_format($lastPrice,2)."'></div>
					<div class='form-group col-sm-3'><label for='marketPrice'>Touchnet Price <span onclick='calculateMarketPrice(\"$stocknumber\");'></a>(Update)</a></span></label><input type='text' class='form-control' onchange='updateMarketPrice(\"$stocknumber\");' id='marketPrice$stocknumber' value='".number_format($marketPrice,2)."'></div>
					<div class='form-group col-sm-3'><label for='touchnetid'>Touchnet ID</label><input type='text' class='form-control' onchange='updateTouchnetId(\"$stocknumber\");' id='touchnetid$stocknumber' value='".Security::HtmlEntitiesEncode($touchnetId)."'></div>
					<div class='form-group col-sm-3'><label for='studentprice'>Student Price</label><input type='text' class='form-control' id='studentprice' value='".studentPrice($lastPrice)."' disabled></div>
				</div>
				<div class='form-row'>
				<div class='col-sm-7'>
				<div class='form-row'>
					<div class='form-group col-sm-6'><label for='partMargin'>Part Margin</label><input type='text' class='form-control' onchange='updatePartMargin(\"$stocknumber\")'id='partMargin$stocknumber' value='".Security::HtmlEntitiesEncode($partMargin)."'></div>
					<div class='form-group col-sm-3'><div class='form-check'><input type='checkbox' class='form-check-input' id='stocked' ".($stocked == 1 ? 'checked' : '' )."><label for='stocked' class='form-check-label'>Stocked</label></div></div>
					<div class='form-group col-sm-3'><div class='form-check'><input type='checkbox' class='form-check-input' id='archived' ".($archive == 1 ? 'checked' : '' )."><label for='archived' class='form-check-label'>Archived</label></div></div>
				</div>
				<div class='form-row'>
					<div class='form-group col-sm-6'><label for='location$stocknumber'>Location</label><input class='form-control' type='text' id='location$stocknumber' value='$location' onchange='updateLocation(\"$stocknumber\")'></div>
					<div class='form-group col-sm-6'><label for='quantity$stocknumber' >Quantity</label><input class='form-control' type='number' id='quantity$stocknumber' value='$quantity' onchange='updateQuantity(\"$stocknumber\")'></div>
				</div>
				<div class='form-row'>
					<div class='form-group col-sm-9'><label for='datasheet'>Datasheet <a href='../../inventory_datasheets/$datasheet' target='_blank'>$datasheet</a></label><input class='form-control' type='file' id='datasheet' value='' onchange='updateDatasheet(\"$stocknumber\")'></div>
				</div>
				<div class='form-row'>
					<div class='form-group col-sm-12'><label for='comment$stocknumber'>Comment</label><textarea class='form-control' id='comment$stocknumber' onchange='updateComment(\"$stocknumber\")'>$comment</textarea></div>
				</div>
				</div>
				
				<div class='col-sm-3'>
					<div class='form-group'><label for='quantity$stocknumber' >Image <a href='../../inventory_images/".($image != '' ? $image : 'noimage.jpg')."' target='_blank'>".($image != '' ? $image : '')."</a></label><img src='../../inventory_images/".($image != '' ? $image : 'noimage.jpg')."' class='img-fluid rounded-lg' id='partImage'><input class='form-control' type='file' id='imageFile' value='' onchange='updatePartImage(\"$stocknumber\");'></div>
				</div>
				
				</div>
			</div>
			</form>";

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
	}).catch(err => {
		snackbar(err.message, 'error');
	});
}

function updateDescription(id){
	var description = $('#description'+id).val();
	
	let content = {
		action: 'updateDescription',
		stockNumber: id,
		description: description
	}

	api.post('/inventory.php', content).then(res => {
		snackbar(res.message, 'Updated');
	}).catch(err => {
		snackbar(err.message, 'error');
	});
}

function updateLastSupplier(id){
	var lastSupplier = $('#lastSupplier'+id).val();
	
	let content = {
		action: 'updateLastSupplier',
		stockNumber: id,
		lastSupplier: lastSupplier
	}

	api.post('/inventory.php', content).then(res => {
		snackbar(res.message, 'Updated');
	}).catch(err => {
		snackbar(err.message, 'error');
	});
}

function updateManufacturer(id){
	var manufacturer = $('#manufacturer'+id).val();
	
	let content = {
		action: 'updateManufacturer',
		stockNumber: id,
		manufacturer: manufacturer
	}

	api.post('/inventory.php', content).then(res => {
		snackbar(res.message, 'Updated');
	}).catch(err => {
		snackbar(err.message, 'error');
	});
}

function updateManufacturerNumber(id){
	var manufacturerNumber = $('#manufacturerNumber'+id).val();
	
	let content = {
		action: 'updateManufacturerNumber',
		stockNumber: id,
		manufacturerNumber: manufacturerNumber
	}

	api.post('/inventory.php', content).then(res => {
		snackbar(res.message, 'Updated');
	}).catch(err => {
		snackbar(err.message, 'error');
	});
}

function updateTouchnetId(id){
	var touchnetid = $('#touchnetid'+id).val();
	
	let content = {
		action: 'updateTouchnetId',
		stockNumber: id,
		touchnetId: touchnetid
	}

	api.post('/inventory.php', content).then(res => {
		snackbar(res.message, 'Updated');
	}).catch(err => {
		snackbar(err.message, 'error');
	});
}

function updateMarketPrice(id){
	var marketPrice = $('#marketPrice'+id).val();
	
	let content = {
		action: 'updateMarketPrice',
		stockNumber: id,
		marketPrice: marketPrice
	}

	api.post('/inventory.php', content).then(res => {
		snackbar(res.message, 'Updated');
		var inputF = document.getElementById('marketPrice'+id);
		inputF.value = (marketPrice*1).toFixed(2);
	}).catch(err => {
		snackbar(err.message, 'error');
	});
}

function calculateMarketPrice(id){
//	event.preventDefault();
	var lastprice = $('#lastprice'+id).val();
	
	let content = {
		action: 'calculateMarketPrice',
		stockNumber: id,
		lastPrice: lastprice
	}

	api.post('/inventory.php', content).then(res => {
		snackbar(res.message, 'Updated');
		var inputF = document.getElementById('marketPrice'+id);
		inputF.value = (lastprice*1.25).toFixed(2);
	}).catch(err => {
		snackbar(err.message, 'error');
	});
	
	
	return false;
}

function updateType(id){
	var typeid = $('#type'+id).val();
	
	let content = {
		action: 'updateType',
		stockNumber: id,
		typeId: typeid
	}

	api.post('/inventory.php', content).then(res => {
		snackbar(res.message, 'Updated');
		$('#row'+id).html('');
	}).catch(err => {
		snackbar(err.message, 'error');
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
	}).catch(err => {
		snackbar(err.message, 'error');
	});
}

function updatePartMargin(id){
	var partMargin = $('#partMargin'+id).val();
	
	let content = {
		action: 'updatePartMargin',
		stockNumber: id,
		partMargin: partMargin
	}
	
	api.post('/inventory.php', content).then(res => {
		snackbar(res.message, 'Updated');
	}).catch(err => {
		snackbar(err.message, 'error');
	});
}

function updateComment(id){
	var comment = $('#comment'+id).val();
	
	let content = {
		action: 'updateComment',
		stockNumber: id,
		comment: comment
	}
	
	api.post('/inventory.php', content).then(res => {
		snackbar(res.message, 'Updated');
	}).catch(err => {
		snackbar(err.message, 'error');
	});
}
</script>

<script type='text/javascript'>

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
                renderEmployeeBreadcrumb('Employee', 'Update Part');
            ?>
			
            <?php echo $partHTML;?>

			</div>
        </div>
    </div>

<?php 
include_once PUBLIC_FILES . '/modules/footer.php' ; 
?>
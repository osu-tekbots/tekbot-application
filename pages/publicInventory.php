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
	else 
		return ('Free for one / 10 for $1');

return $price;
}



$title = 'Public Inventory List';
$css = array(
	'assets/css/sb-admin.css',
	'assets/css/admin.css',
	'https://cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css'
);
$js = array(
    'https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js'
);


include_once PUBLIC_FILES . '/modules/header.php';
$inventoryDao = new InventoryDao($dbConn, $logger);
$parts = $inventoryDao->getInventory();
$types = $inventoryDao->getTypes();
?>


<br/>
<div id="page-top">

	<div id="wrapper">

	<div class="admin-content" id="content-wrapper">
        <div class="container-fluid">
			<div class='admin-paper'>
			<div class='row'><div class='col-9'>
			<h3>TekBots Inventory</h3>
			<p>Welcome to our inventory. You can find all parts available form the TekBots store from the list below. These parts can be purchased via the TekBots Marketplace. Some parts are inexpensive enough that you only need to stop into the store to pick one up for free.</p>
			</div><div class='col-3'></div>
			</div>
            <?php 
				$inventoryHTML = '';
                foreach ($parts as $p) {
					if ($p->getArchive() == 0){
					$stocknumber = $p->getStocknumber();
					$type = $p->getType();
					$description = $p->getName();
					$lastPrice = $p->getLastPrice();
					$marketPrice = $p->getMarketPrice();
					$location = $p->getLocation();
					$quantity = $p->getQuantity();
					$image = $p->getImage();
					$datasheet = $p->getDatasheet();
					# add get touchnet page link
					$touchnetId = $p->getTouchnetId();
					
					$inventoryHTML .= "<tr><td>$type</td>
						<td>$description<BR>Stock: $stocknumber</td>
						<td>".($image != '' ?"<a target='_blank' href='../../inventory_images/$image'>Image</a>":'')."</td>
						<td>".($datasheet != '' ?"<a target='_blank' href='../../inventory_datasheets/$datasheet'>Datasheet</a>":'')."</td>
						<td>".($marketPrice == 0 ? studentPrice($lastPrice) : "$".number_format($marketPrice,2))."</td>
						<td>$quantity</td>
						<td>".($touchnetId != '' ?"<a target ='_blank' href='https://secure.touchnet.net/C20159_ustores/web/product_detail.jsp?PRODUCTID=$touchnetId'>Purchase Item</a>":'')."</td>
						<td><a href='./pages/publicInventoryPart.php?stocknumber=$stocknumber'>More Info</a></td></tr>";
					}
				}
            ?>
			
			<table class='table' id='InventoryTable' style='margin: 0 auto !important;'>
                <caption>Current Inventory</caption>
                <thead>
                    <tr>
						<th>Type</th>
                        <th>Description</th>
						<td></td>
						<td></td>
						<th>Student<BR>Price</th>
                        <th>Current<BR>Stock</th>
						<th>Purchase<BR>Link <!-- Added touchnet links by Travis Hudson 10/5/2022-->
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


<script type='text/javascript'>
function addpart(){
	
	let type = $('#addtype').val().trim();
	let desc =  $('#adddecription').val().trim();
	let data = {
		type: type,
		desc: desc,
		action: 'addPart'
	};

	if (type != 0 && desc != ''){
		api.post('/inventory.php', data).then(res => {
			//console.log(res.message);
			snackbar(res.message, 'Part Added');
			location.reload();
		}).catch(err => {
			snackbar(err.message, 'error');
		});
	} else {
		alert('Description field is empty or type is not selected. No changes made');
	}
}

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
		"autoWidth": true,
		'scrollX':false, 
		'paging':false, 
		'order':[[0, 'asc'], [1, 'asc']],
		"columns": [
			null,
			null,
			{ "orderable": false },
			{ "orderable": false },
			null,
			{ "orderable": false },
			{ "orderable": false }, //added row for puchase link
			{ "orderable": false }
		  ]
		});
</script>

<?php 
include_once PUBLIC_FILES . '/modules/footer.php' ; 
?>
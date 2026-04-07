<?php
include_once '../bootstrap.php';

use DataAccess\InventoryDao;
use DataAccess\UsersDao;
use Util\Security;


if (PHP_SESSION_ACTIVE != session_status()) {
    session_start();
}

// Make sure the user is logged in and allowed to be on this page
include_once PUBLIC_FILES . '/lib/shared/authorize.php';

$inventoryDao = new InventoryDao($dbConn, $logger);
$parts = $inventoryDao->getInventory();

//Cart feature cookie and id storage:

if (!isset($_SESSION['cart']) || ($_SESSION['cart'] === false)) {
	if (isset($_COOKIE['cartId'])) {
		$_SESSION['cart'] = $inventoryDao -> getCartByID($_COOKIE['cartId']); // Sync cookie to session
		$inventoryDao -> refreshCartInDatabase($_SESSION['cart']); 
		// Refresh the cart's date last accessed in the database
	} else {
		//create a new cart obj and get it     
		$_SESSION['cart'] = $inventoryDao -> createCartInDatabase();
		//Store in cookie (30 days)
		setcookie('cartId', $_SESSION['cart'] -> getIdKey(), time() + (86400 * 30), "/");
	}
}
$cart = $_SESSION['cart'];

/*
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
*/

$title = 'Public Inventory List';
$css = array(
	'assets/css/sb-admin.css',
	'assets/css/admin.css',
	'assets/css/jquery.dataTables.min.css');
$js = array(
    'assets/js/jquery.dataTables.min.js'

);

include_once PUBLIC_FILES . '/modules/header.php';
include_once PUBLIC_FILES . '/modules/inventoryFunctions.php';
include_once PUBLIC_FILES . '/modules/renderBrowse.php';

?>


<br/>
<div id="page-top">

	<div id="wrapper">

	<div class="admin-content" id="content-wrapper">
        <div class="container-fluid">
			<div class='admin-paper'>
			<div class='row'>

				<div class="d-flex justify-content-between align-items-center mb-2 col-12">
					<h3 class="mb-0">TekBots Inventory</h3>
					<a href="./pages/publicCart.php" class="btn btn-outline-secondary">
						<i class="fas fa-shopping-cart"></i> Visit Cart
					</a>
				</div>
				<div class='col-9'>
					<p>Welcome to our inventory. You can find all parts available form the TekBots store from the list below. These parts can be purchased via the TekBots Marketplace. Some parts are inexpensive enough that you only need to stop into the store to pick one up for free.</p>
				</div>
				
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
					//$marketPrice == 0 ? studentPrice($lastPrice) : "$".number_format($marketPrice,2)
					$inventoryHTML .= "<tr><td>$type</td>
						<td><a href='./publicInventoryPart.php?stocknumber=$stocknumber' >$description<BR>Stock: $stocknumber </a></td> 
						<td>".($marketPrice != 0 ? numberToDollarString($marketPrice):getStudentPrice($lastPrice))."</td>
						<td>$quantity</td>".
						(($cart -> getEditableStatus() == 0)? "":"<td><i class='fas fa-cart-plus cart-icon' style='font-size: 26px;' title='Add to Cart' onclick='addToCart(\"{$cart -> getIdKey() }\", \"$stocknumber\")'></i></td>")
						."<td>".($image != '' ?"<a target='_blank' href='../../inventory_images/$image'>Image</a>":'')."</td>
						<td>".($datasheet != '' ?"<a target='_blank' href='../../inventory_datasheets/$datasheet'>Datasheet</a>":'')."</td>
						<td>".($touchnetId != '' ?"<a target ='_blank' href='https://secure.touchnet.net/C20159_ustores/web/product_detail.jsp?PRODUCTID=$touchnetId'>Purchase Item</a>":'')."</td>
						";
					}
				}
            ?>
			
			<div class='row'>
				<div class='col-12 col-md-12 table-responsive'>
					<?php echo ($cart -> getEditableStatus() == 1)?(""):
						"<h4 style = 'caption-side: top;' class = 'text-warning fw-bold'>Your Cart is Locked</h4>"
					?>
					<table class='table' id='InventoryTable' style='width: 100%; max-width: 100%;'>
						<thead>
							<tr>
								<th>Type</th>
								<th>Description</th>
								<th>Student<BR>Price</th>
								<th>Current<BR>Stock</th>
								<?php echo (($cart -> getEditableStatus() == 0)?(""):"<th>Add To Cart</th>")?>
								<td></td><!--Image-->
								<td></td><!-- datasheet-->
								<th>Purchase<BR>Link </th><!-- Added touchnet links by Travis Hudson 10/5/2022-->
							</tr>
						</thead>
						<tbody>
							<?php echo $inventoryHTML;?>
						</tbody>
					</table>
				</div>
			</div>
			</div>
        </div>
    </div>


<script type='text/javascript'>

// Listen for storage events to handle cart updates across tabs
window.addEventListener('storage', (e) => {
    if (e.key === 'refreshCart') {
        console.log('Cart updated in another tab — reloading.');
        location.reload(); // Reload page to get latest session/cart data
    }
});

function addToCart(cartID, partID, quantity = 1) {
	

	let data = {
		cartID: cartID,
		partID: partID,
		qty: quantity, // Default quantity to 1
		action: 'addToCart'
	};

	api.post('/inventory.php', data).then(res => {
		//console.log(res.message);
		snackbar(res.message, 'Added to Cart');
	}).catch(err => {
		snackbar(err.message, 'error');
	});
}

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

$('#InventoryTable').DataTable({
		'dom': 'Bft',
		'buttons': [
			$.extend( true, {}, printButtonExtension, {
				extend: 'print'
			} )
		], 
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
			{ "orderable": false },
			{ "orderable": false },
			{ "orderable": false } //added row for puchase link
		  ]
		});
</script>

<?php 
include_once PUBLIC_FILES . '/modules/footer.php' ; 
?>
<?php
include_once '../bootstrap.php';
include_once PUBLIC_FILES . '/modules/header.php';

use Model\Cart;
use DataAccess\InventoryDao;

if (PHP_SESSION_ACTIVE != session_status()) {
    session_start();
}
include_once PUBLIC_FILES . '/modules/inventoryFunctions.php';

$inventoryDao = new InventoryDao($dbConn, $logger);

//Creates/acquires cart if not already in session
//Same as publicInventory.php
if (!isset($_SESSION['cart']) || ($_SESSION['cart'] === false)) {

	if (isset($_COOKIE['cartId'])) {
		$logger->info('COOKIE IS SET: '. $_COOKIE['cartId']);
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
//Copied from publicInventory.php ^^^

$cart = $_SESSION['cart'];
$contents = $_SESSION['cart']->getContents();


?>

<br/>
<br/>
<div id="page-top">

	<div id="wrapper">

	<div class="admin-content" id="content-wrapper">
        <div class="container-fluid">
			<div class='admin-paper'>
			<div class='row'>
				<div class="col-8">
					<div class="d-flex justify-content-between align-items-center">
						<h3 class="mb-1">
							Your Cart Code: <span style="color: red;"><?php echo $cart->getIdKey(); ?></span>
						</h3>
					</div>
				</div>
				<div class="col-4 justify-content-end d-flex">
					<button class="btn btn-outline-secondary" onClick = 'createNewCart()'>
						<i class="fas fa-cart-plus"></i> New Cart
					</button>
				</div>
				<div class="col-8">
					<p class = "mb-1">Provide your cart code to a TekBots employee for easy checkout</p>
					<p class = "mb-2">Below is a list of items currently in your cart. You can add more items to your cart from the <a href="./pages/publicInventory.php">inventory page</a>.</p>
				</div>
			</div>

            <?php 
                $totalPrice = 0;
				$totalCount = 0;
				$inventoryHTML = '';

				if(!isset($contents)) {
					$contents = [];
				} else {
					$contents = $cart->getContents(); // Get the contents of the cart
				}
				
                foreach ($contents as $c) {
                    $p = $c['part']; //Get the Part object
                    
					if ($c['quantity'] > 0 && $p->getArchive() == 0){
					$stocknumber = $p->getStocknumber();
					$type = $p->getType();
					$description = $p->getName();
					$lastPrice = getStudentPrice($p->getLastPrice());
					$totalPrice += getStudentPriceAsNumber($p->getLastPrice()) * $c['quantity'];
					$marketPrice = $p->getMarketPrice();
					$location = $p->getLocation();
					$quantity = $p->getQuantity();
                    $cartQuantity = $c['quantity'];
					$totalCount += $cartQuantity;
					$image = $p->getImage();
					$datasheet = $p->getDatasheet();
					$touchnetId = $p->getTouchnetId();

				
					$inventoryHTML .= "<tr>
						<td>$type: <BR>
						$description 
						<BR> <span id = 'StockCountWarning:$stocknumber' class= 'text-warning fw-bold'> ".
						(($quantity < $cartQuantity)? 
							("WARNING we ".(($quantity == 0) ? ("dont have any"):("only have ".$quantity)).
							" of this item in stock, check our exact inventory with a TekBots employee.")
							:("")
						)."
						</span></td>
						
						<td>$lastPrice</td>

                        <td>".(
							($cart -> getEditableStatus() == 0) ? $cartQuantity :
							"<input 
							type= 'number'
							min= '0'
							value='{$cartQuantity}'
							class='form-control'
							style='width: 80px;'
							onchange=\"setPartQuantityInCart(
								'{$cart->getIdKey()}',
								'{$stocknumber}',
								this.value,
								(Number('{$quantity}') < Number(this.value) ? Number('{$quantity}') : false)
        					)\"

      						/>").
						"</td>


						<td><a href='./pages/publicInventoryPart.php?stocknumber=$stocknumber'>More Info</a></td>
                        <td>
							<i class='fas fa-trash fa-lg' 
								onClick='deletePartInCart(\"{$cart -> getIdKey() }\",\"$stocknumber\")'>
							</i>
						</td>
					</tr>";
					}
				}
            ?>
			<div class='row'>
				<!-- Might not render properly on a phone as col-8 will be too small -->
				<div class='col-12 col-md-8 table-responsive'>
					<table class='table' id='InventoryTable' style='width: 100%; max-width: 100%;'>
						<thead>
							<tr>
								<th>Item</th>
								<th>Price</th>
								<th>Cart<BR>Quantity</th>
								<!--<th>Current<BR>Stock</th> -->
								<th>Item<BR>Link <!-- Added touchnet links by Travis Hudson 10/5/2022-->
								<th></th>
							</tr>
						</thead>
						<tbody>
							<?php echo $inventoryHTML;?>
						</tbody>
					</table>
				</div>

				<!-- Cart Summary (4/12 of row width) -->
				<div class="col-md-4 col-12">
					<div class="card p-3 shadow-sm">
					<?php echo ($cart -> getEditableStatus() == 0 ? "<h5 class = 'text-warning'>This cart is not editable</h5>":"") ?>
					<h5>Cart Summary</h5>
					<p>Cart Code: <span style="color: red;"><?php echo $cart->getIdKey(); ?></span></p>
					<p>Total Items: <span id="cart-total-items"><?= $totalCount?></span></p>
					<p>Total Price: $<span id="cart-total-price"><?= number_format($totalPrice, 2) ?></span></p>
					<div><h7 style="font-weight: bold;">To Order: </h7><p>Provide your cart code to a TekBots employee in-person at KEC 1110 during store hours, posted <a href = '../pages/index.php'>here</a></p></div>
					</div>
				</div>
			</div>
		</div>
    </div>
</div>

<script type='text/javascript'>

/*
Expects a cart object to be in session, doesnt pass in cart id to rebuild cart
Passes in the part that needs to be built and its quantity
*/
function generateStockCountWarningForPart(partID, quantity, stockQuantity) {
	let val = "";
	if(stockQuantity !== false ) {
		console.log('Generating stock count warning for quantity:', quantity, 'and stockQuantity:', stockQuantity, 
		'with partID:', partID);
		val = (quantity > stockQuantity)
			? `WARNING we ${stockQuantity === 0 
				? "dont have any" 
				: `only have ${stockQuantity}`
			} of this item in stock, check our exact inventory with a TekBots employee.`
		: "";
	} else {
		console.log('No stock quantity provided, skipping warning generation for partID:', partID);
	}
	console.log('Stock Count Warning:', val);
	document.getElementById('StockCountWarning:'+partID).innerText = val;
}
function setTotals(cartID){
	let data = {
		cartID: cartID,
		action: 'getCartTotals'
	};

	api.post('/inventory.php', data).then(res => {
		if(res.code === 200 && res.content) {
			// Update the page
			document.getElementById('cart-total-items').innerText = res.content.totalQuantity;
			document.getElementById('cart-total-price').innerText = res.content.totalPrice.toFixed(2);
		} else {
			snackbar('Failed to update cart totals', 'error');
		}
	}).catch(err => {
		snackbar(err.message, 'error');
	});
} 

function setPartQuantityInCart(cartID,partID, quantity, stockCount) {
	let data = {
		cartID: cartID,
		partID: partID,
		qty: quantity, 
		action: 'setPartQuantityInCart'
	};

	api.post('/inventory.php', data).then(res => {
		//reset totals after updating in backend to ensure it happens in order (no async await)
		setTotals(cartID);
		generateStockCountWarningForPart(partID, quantity, stockCount)
	}).catch(err => {
		snackbar(err.message, 'error');
	});
}

function deletePartInCart(cartID,partID) {
	console.log('Setting part quantity in cart:', partID, 0);
	let data = {
		cartID: cartID,
		partID: partID,
		qty: 0, 
		action: 'setPartQuantityInCart'
	};

	api.post('/inventory.php', data).then(res => {
		//console.log(res.message);
		snackbar(res.message, 'Item Deleted');
		location.reload();
	}).catch(err => {
		snackbar(err.message, 'error');
	});	
}

function createNewCart() {
	if(!confirm("Are you sure you want to create a new cart? This will delete your current cart. Store your cart ID to retrieve it later.")) {
		return;
	}
	console.log('Create new cart confirmed');
	let data = {
		action: 'createNewCart'
	};

	api.post('/inventory.php', data).then(res => {
		snackbar(res.message, 'New Cart Created');
		location.reload(); // Reload the page to reflect the new cart
		localStorage.setItem('refreshCart', Date.now());

	}).catch(err => {
		snackbar(err.message, 'error');
	});


}
</script>
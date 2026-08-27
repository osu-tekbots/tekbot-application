<?php
include_once '../bootstrap.php';

use DataAccess\InventoryDao;
use DataAccess\UsersDao;
use Util\Security;
use Model\Cart;


if (PHP_SESSION_ACTIVE != session_status()) {
    session_start();
}

include_once PUBLIC_FILES . '/lib/shared/authorize.php';
allowIf(verifyPermissions('employee', $logger), 'index.php');

$inventoryDao = new InventoryDao($dbConn, $logger);
$logger -> info('Accessing employee inventory carts page');

$title = 'Employee Inventory Carts';
$css = array(
	'assets/css/sb-admin.css',
	'assets/css/admin.css',
	'https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css',
	'https://cdn.datatables.net/buttons/1.6.2/css/buttons.dataTables.min.css',
	"https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
);

$js = array(
	'https://code.jquery.com/jquery-3.5.1.min.js',
	'https://cdn.datatables.net/1.10.21/js/jquery.dataTables.js',
	'https://cdn.datatables.net/buttons/1.6.2/js/dataTables.buttons.js',
	'https://cdn.datatables.net/buttons/1.6.2/js/buttons.print.js',
	"https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
);

include_once PUBLIC_FILES . '/modules/inventoryFunctions.php';
include_once PUBLIC_FILES . '/modules/receipt.php';

$cart = false;
$cartControlHTML = '';
$tableHTML = '';
$cartSummaryHTML = '';

//Add to cart handler
if(isset($_REQUEST['action'])){
	if ($_REQUEST['action'] == 'loadDescriptionSelect'){
		$names = $inventoryDao->getInventoryByTypeId($_REQUEST['typeId']);
		$namesSELECT = '<select id="newDescription" onchange="loadAddImage();">';
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
	if ($_REQUEST['action'] == 'print'){
	
		exit();
	}
}

if (isset($_GET['id'])) {
	if(!empty($_GET['id'])) {
		$cart = $inventoryDao -> getCartByID($_GET['id']);
		if(!$cart) {
			$logger->error('Invald cart ID provided');
			$tableHTML.= "<p class='error'>Invalid Cart ID provided.</p>";
		}
	}
} else if(isset($_SESSION['cart'])) {
	$cart = $_SESSION['cart'];

	echo "<script>
        window.location.href = './employeeInventoryCarts.php?id=" . urlencode($cart -> getIdKey()) . "';
    </script>";
}
//Cart status HTML, part of the cart input
$cartControlHTML .= "
        <div class='col-md-4 ms-auto mb-3'>
            <div class='row justify-content-end align-items-center'>";
if ($cart) {
    $cartControlHTML .= "
        <div class='col-auto'>
            <div class='d-flex flex-column gap-1'>
        		<div class='form-check form-switch'>
                	<input class='form-check-input' type='checkbox' id='cartEditableSwitch'
                        onchange='setCartEditableStatus(\"{$cart->getIdKey()}\", this.checked)'
                    	    " . ($cart->getEditableStatus() == 1 ? "checked" : "") . "
                    >
                    <label class='form-check-label' for='cartEditableSwitch'>Cart Editable</label>
                </div>
                <div class='form-check form-switch'>
                    <input class='form-check-input' type='checkbox' id='cartPermanenceSwitch'
                    	onchange='setCartPermanence(\"{$cart->getIdKey()}\", this.checked)'
                    	    " . ($cart->getPermanence() == 1 ? "checked" : "") . "
                    >
                    <label class='form-check-label' for='cartPermanenceSwitch'>Cart Permanence</label>
                </div>
            </div>
        </div>
    ";
}
$cartControlHTML .= "
			<div class='col-auto'>
				<a href='./publicInventory.php' target='_blank' class='btn btn-primary ms-3'>Go to Inventory</a>
            </div>
        </div>
    </div>
";

//Cart input html, always displayed
$cartId = '';
if($cart) {
	$cartId = $cart->getIdKey();
}
$cartInput = '
<div class="table-responsive col-md-8 col-7">
    <form method="GET" action="./employeeInventoryCarts.php" >
        <div class="d-flex mb-3 justify-content-end" style="gap: 20px;">
            <label for="id">Enter Cart ID:</label>
            <input type="text" id="id" name="id" value = "'.$cartId.'" required>
            <button type="submit" class = "btn btn-primary">Search</button>
        </div>
    </form>
</div>';

//If cart found display table
if ($cart) {
	$tableHTML = createCartReceiptTable($cart, true);

    $totalCount = 0;
    $totalPrice = 0;    
    foreach ($cart->getContents() as ['quantity' => $quantity, 'part' => $p]) {
		if ($quantity > 0 && $p->getArchive() == 0){
			$studentPrice = $p->getMarketPrice() ?: getStudentPrice($p->getLastPrice());
			$totalPrice += $studentPrice * $quantity;
			$totalCount += $quantity;
		}
	}
} 

//Add to cart logic:


$types = $inventoryDao->getTypes();

$typeSelect = "<select id='typeSelect' onchange='updateAddContents();'><option value=''>---</option>";
foreach ($types as $t) {
    $typeSelect .= "<option value='".$t['typeId']."'>".$t['type']."</option>";
}
$typeSelect .= "</select>";

if (isset($_REQUEST['stocknumber'])) {
	if ($_REQUEST['stocknumber'] != '') {
		$stocknumber = $_REQUEST['stocknumber'];
	}
}
	
$addToCartHTML = '';
if($cart) {
	$addToCartHTML .= "<form><div style='padding-left:4px;padding-right:4px;margin-top:4px;margin-bottom:4px;'><div class='form-row print-hide'>
							<div class='form-group col-sm-9'>
							<HR><h4>Add Item</h4><table>";
	$addToCartHTML .= "<tr>
		<td>$typeSelect</td>
			<td id='nameSelect'></td>
			<td><input type='text' id='addQuantity' placeholder='Add Quantity'></td>
			<td>
				<button type = 'button' class='btn btn-success' onclick='addToCart(\"{$cart->getIdKey()}\", document.getElementById(\"newDescription\").value, document.getElementById(\"addQuantity\").value);'>
					Add
				</button>
			</td>
	</tr>";
	$addToCartHTML .= "</table></div><div class='col-sm-3'><img src='' class='img-fluid rounded-lg' id='addImage'></div></div></div></form>";
}

if($cart) {
	$cartSummaryHTML .= 
	'<BR><BR>
	<div class="card p-3 shadow-sm" style="align-self:flex-start; max-height: 200px;">
		<div>
			<h4 class="text-center mb-3">Cart Summary</h4>
			<p class="fs-6">Cart Code: <span style="color: red;">' . $cart->getIdKey() . '</span></p>
			<p class="fs-6">Total Items: <span id="cart-total-items">' . $totalCount . '</span></p>
			<p class="fs-6">Total Price: <span id="cart-total-price">' . numberToDollarString($totalPrice) . '</span></p>
		</div>
	</div>';
}



include_once PUBLIC_FILES . '/modules/header.php';
include_once PUBLIC_FILES . '/modules/employee.php';
?>

<br/>
<div id="page-top">

	<div id="wrapper">

	
	<?php 
		// Located inside /modules/employee.php
		//renderEmployeeSidebar();
	?>

    <div class="admin-content" id="content-wrapper">
        <div class="container-fluid">
			<div class='admin-paper'>
				<div class="d-md-none mb-2">
					<button class="btn btn-primary w-100" type="button" data-bs-toggle="collapse" data-bs-target="#cartMenuRow" aria-expanded="false" aria-controls="menuRow">
					Cart Menu
					</button>
				</div>
				<div class='row collapse d-md-flex align-items-center mb-2' id='cartMenuRow'>
					<?php 
						echo $cartInput;
						echo $cartControlHTML;
					?>  
				</div>
				<div class='row'>
					<div class="col-md-8 col-12">
						<?php echo $tableHTML; ?> 
					</div>
					<div class="col-md-3 col-12" style="margin-left:auto;">
						<?php echo $cartSummaryHTML;?>
					</div> 
				</div>
				<?php echo $addToCartHTML; ?> 
			</div>
        </div>
    </div>
</div>

<script type='text/javascript'>

function setTotals(cartID){
	let data = {
		cartID: cartID,
		action: 'getCartTotals'
	};

	api.post('/inventory.php', data).then(res => {
		if(res.code === 200 && res.content) {
			// Update the page
			document.getElementById('cart-total-items').innerText = res.content.totalQuantity;
			document.getElementById('cart-total-price').innerText = res.content.totalPriceString;
		} else {
			snackbar('Failed to update cart totals', 'error');
		}
	}).catch(err => {
		snackbar(err.message, 'error');
	});
} 
/*
Expects a cart object to be in session, doesnt pass in cart id to rebuild cart
Passes in the part that needs to be built and its quantity
*/
function setPartQuantityInCart(cartID, partID, quantity, stockCount) {
	let data = {
		cartID: cartID,
		partID: partID,
		qty: quantity, 
		action: 'setPartQuantityInCart'
	};

	api.post('/inventory.php', data).then(res => {
		//reset totals after updating in backend to ensure it happens in order (no async await)
		setTotals(cartID);
		//generateStockCountWarningForPart(partID, quantity, stockCount)
	}).catch(err => {
		snackbar(err.message, 'error');
	});
}

function setCartEditableStatus(cartID, cartEditableStatus) {
	
	let data = {
		cartID: cartID,
		cartEditableStatus: (cartEditableStatus ? 1 : 0), // Convert boolean to 1 or 0
		action: 'setCartEditable'
	};

	api.post('/inventory.php', data).then(res => {
		//console.log(res.message);
		snackbar(res.message, 'Cart Editable Status Changed');
	}).catch(err => {
		snackbar(err.message, 'error');
	});
}

function setCartPermanence(cartID, cartPermanence) {
	
	let data = {
		cartID: cartID,
		cartPermanenceStatus: (cartPermanence ? 1 : 0), // Convert boolean to 1 or 0
		action: 'setCartPermanence'
	};

	api.post('/inventory.php', data).then(res => {
		//console.log(res.message);
		snackbar(res.message, 'Cart Permanence Changed');
	}).catch(err => {
		snackbar(err.message, 'error');
	});
}

function updateAddContents() {
    var typeid = $('#typeSelect').val();
    $.ajax({
        type: 'POST',
        url: './pages/employeeInventoryCarts.php',
        dataType: 'html',
        data: { typeId: typeid, action: 'loadDescriptionSelect' },
        success: function(result) {
            $('#nameSelect').fadeOut('fast', function() {
                $('#nameSelect').html(result);
            }).fadeIn('fast');
        },
        error: function(xhr, ajaxOptions, thrownError) {
            alert(xhr.status);
            alert(xhr.responseText);
            alert(thrownError);
        }
    });
}

function loadAddImage() {
    var stockNumber = $('#newDescription').val();
    $.ajax({
        type: 'POST',
        url: './pages/employeeInventoryCarts.php',
        dataType: 'html',
        data: { stockNumber: stockNumber, action: 'loadAddImage' },
        success: function(result) {
            $('#addImage').attr("src", result).show();
        },
        error: function(xhr, ajaxOptions, thrownError) {
            alert(xhr.status);
            alert(xhr.responseText);
            alert(thrownError);
        }
    });
}

function addToCart(cartID, partID, quantity = 1) {
	console.log('Adding to cart:', partID, quantity);
	let data = {
		cartID: cartID,
		partID: partID,
		qty: quantity, // Default quantity to 1
		action: 'addToCart'
	};

	api.post('/inventory.php', data).then(res => {
		//console.log(res.message);
		window.location.reload(true);

		
	}).catch(err => {
		snackbar(err.message, 'error');
	});
}

	<?= createReceiptDatatable(
		'Cart Code',
		'[
			null,
			null,
			null,
			{ orderable: false },
			null,
			{ orderable: false },
			{ className: "item-print-col" },
			{ className: "info-print-col" }
		]',
		'[6, 7]'
	) ?>
</script>

<?php 
include_once PUBLIC_FILES . '/modules/footer.php' ; 
?>


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
}

if (isset($_GET['cartID'])) {
	if(!empty($_GET['cartID'])) {
		$cart = $inventoryDao -> getCartByID($_GET['cartID']);
		if(!$cart) {
			$logger->error('Invald cart ID provided');
			$tableHTML.= "<p class='error'>Invalid Cart ID provided.</p>";
		}
	}
} 
//Cart status HTML, part of the cart input
if ($cart) {
	$cartControlHTML .= "
		<div class='form-check form-switch col-md-3 col-5 ps-5'>
			<input class='form-check-input' type='checkbox' id='cartEditableSwitch'
				onchange='setCartEditableStatus(\"{$cart->getIdKey()}\", this.checked)'
				" . ($cart->getEditableStatus() == 1 ? "checked" : "") . "
			>
			<label class='form-check-label' for='cartEditableSwitch'>Cart Editable</label>
			<BR>
			<input class='form-check-input' type='checkbox' id='cartPermanenceSwitch'
				onchange='setCartPermanence(\"{$cart->getIdKey()}\", this.checked)'
				" . ($cart->getPermanence() == 1 ? "checked" : "") . "
			>
			<label class='form-check-label' for='cartPermanenceSwitch'>Cart Permanence</label>
		</div>
	";
} 

//Cart input html, always displayed
$cartInput = '
<div class="table-responsive col-md-9 col-7">
    <form method="GET" action="/pages/employeeInventoryCarts.php" >
        <div class="d-flex mb-3 justify-content-end" style="gap: 15px;">
            <label for="cartID">Enter Cart ID:</label>
            <input type="text" id="cartID" name="cartID" required>
            <button type="submit">Search</button>
        </div>
    </form>
</div>';

//If cart found display table
if ($cart) {
	$tableHTML.="
			<table class='table ' id='InventoryTable' style='width: 100%; max-width: 100%; table-layout:fixed;'>
					<thead>
						<tr>
							<th style = 'width:35%'>Item</th>
							<th style = 'width:10%'>Loc</th>
							<th style = 'width:10%'class='d-none d-md-table-cell'>Price</th>
							<th style = 'width:15%'>QTY</th>
							<th style = 'width:10%'>Stock</th>
							<th style = 'width:20%'class='d-none d-md-table-cell'>Item<BR>Link</th>
						</tr>
					</thead>
					<tbody>";
							
    $contents = $cart->getContents();
    $totalCount = 0;
    $totalPrice = 0;    
    foreach ($contents as $c) {
    	$p = $c['part']; //Get the Part object
                    
       
		if ($c['quantity'] > 0 && $p->getArchive() == 0){

			$stocknumber = $p->getStocknumber();
			$type = $p->getType();
			$description = $p->getName();

			$lastPrice = getStudentPrice($p->getLastPrice());
			$totalPrice += getStudentPriceAsNumber($p->getLastPrice()) * $c['quantity'];

			$location = $p->getLocation();
			$quantity = $p->getQuantity();
            $cartQuantity = $c['quantity'];
			$totalCount += $cartQuantity;
			$touchnetId = $p->getTouchnetId();

				
			$tableHTML .= "<tr>
				<td>$type: <BR> $description</td>
				<td>$location</td>
				<td class='d-none d-md-table-cell'>$lastPrice</td>
                <td>
					<input 
						type= number
						min= 0
						value=$cartQuantity
						class='form-control'
						style='width: 80px;'
						onchange=\"setPartQuantityInCart(
								'{$cart->getIdKey()}',
								'{$stocknumber}',
								this.value,
								(Number('{$quantity}') < Number(this.value) ? Number('{$quantity}') : false)
						)\"
      				/>
				</td>
                <td>$quantity</td>
				<td class='d-none d-md-table-cell'>
					<a href='./pages/publicInventoryPart.php?stocknumber=$stocknumber'>More Info</a>
				</td>
                </tr>";
		}
	}
        
    $tableHTML.= "</tbody>
		</table>
    ";  
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
				<button class='btn btn-success' onclick='addToCart(\"{$cart->getIdKey()}\", document.getElementById(\"newDescription\").value, document.getElementById(\"addQuantity\").value);'>
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
			<p class="fs-6">Total Price: $<span id="cart-total-price">' . number_format($totalPrice, 2) . '</span></p>
		</div>
	</div>';
}

include_once PUBLIC_FILES . '/modules/header.php';
include_once PUBLIC_FILES . '/modules/employee.php';
include_once PUBLIC_FILES . '/modules/renderBrowse.php';
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
				<div class='row collapse d-md-flex' id='cartMenuRow'>
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
			document.getElementById('cart-total-price').innerText = res.content.totalPrice.toFixed(2);
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

var printButtonExtension = {
	text: 'Print Cart',
    exportOptions: {
		columns: [0, 1, 3, 4],
		modifier: {
            search: 'applied', // only export filtered rows
            order: 'applied'   // respect current sort order
        },
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
		'dom': ((window.innerWidth < 768) ? 't' : 'Bft'),
		buttons: [
			$.extend(true, {}, printButtonExtension, {
				extend: 'print',
				
				customize: function (win) {
					// Remove the automatically added <h1> title
        			$(win.document.body).find('h1').remove();
					// Set body font size
					$(win.document.body).css('font-size', '10pt');

					// Force table to 4in wide, centered
					$(win.document.body).find('table')
						.css('table-layout', 'fixed')
						.css('width', '4in')
						.css('margin', '0.25in 0.25in');

					// Optional: shrink text to fit
					$(win.document.body).find('table td, table th')
						.css('white-space', 'pre-wrap')
						.css('word-wrap', 'break-word');

					// Inject @page CSS for print width
					var style = `
						<style>
						@page {
							size: 4.5in auto !important;   /* 4.5in wide, unlimited height */
						}
						body {

						}
						table {
							
						}
						</style>
					`;

					$(win.document.head).append(style);
				}
			})
		],
		"autoWidth": true,
		'scrollX':false, 
		'paging':false, 
		'order':[[1, 'asc']],
		"columns": [
			null,
			null,
			null,
			{ "orderable": false },
			null,
			{ "orderable": false }
		  ]
		});

</script>

<?php 
include_once PUBLIC_FILES . '/modules/footer.php' ; 
?>


<?php
include_once '../bootstrap.php';

if (PHP_SESSION_ACTIVE != session_status()) {
  session_start();
}

$title = 'Check Out';
$css = array(
	'assets/css/sb-admin.css',
	'assets/css/admin.css',
);
$js = array();

include_once PUBLIC_FILES . '/lib/cookies.php';
include_once PUBLIC_FILES . '/modules/header.php';
include_once PUBLIC_FILES . '/modules/inventoryFunctions.php';

use DataAccess\InventoryDao;
use DataAccess\TransactionDao;
use DataAccess\UsersDao;
use Model\Transaction;
use Model\TransactionDelivery;

$inventoryDao = new InventoryDao($dbConn, $logger);
$transactionDao = new TransactionDao($dbConn, $logger);
$userDao = new UsersDao($dbConn, $logger);

// Handle redirect back from Touchnet
$alertMessage = '';
if (isset($_REQUEST['EXT_TRANS_ID'])) {
  $transaction = $transactionDao->getTransaction($_REQUEST['EXT_TRANS_ID']);
  if ($transaction) {
    if ($transaction->getStatus() == Transaction::SUCCESS) {
      generateNewCartSession($inventoryDao); // Don't let the user see their old cart now that it's paid for
      $alertMessage = '<div class="alert alert-success"><i class="fas fa-check mr-2"></i>Your payment was successfully processed.</div>';
    } else {
      $alertMessage = '<div class="alert alert-warning"><i class="fas fa-exclamation-circle mr-2"></i>Your payment was not processed. Please try again or contact a site administrator if this is unexpected.</div>';
    }
  }
}

refreshCartSession($inventoryDao);

$user = $userDao->getUserByID($_SESSION['userID']);
$cart = $_SESSION['cart'];
$contents = $_SESSION['cart']->getContents();

$contents = isset($contents) ? $cart->getContents() : [];

$cartPrice = 0;
$cartCount = 0;
$inventoryHTML = '';
foreach ($contents as $c) {
  $cartCount += $c['quantity'];
  $p = $c['part']; //Get the Part object

  if ($c['quantity'] == 0 || $p->getArchive()) continue;

  $stocknumber = $p->getStocknumber();

  $marketPrice = $p->getMarketPrice();
  $studentPrice = $marketPrice == 0 ? getStudentPrice($p->getLastPrice()) : $marketPrice;
  $cartPrice += $studentPrice * $c['quantity'];
  $studentPriceStr = numberToDollarString($studentPrice);


  $stockQuantity = $p->getQuantity();
  $cartQuantity = $c['quantity'];


  $inventoryHTML .= "<tr>
    <td>
      <a href='./publicInventoryPart.php?stocknumber=$stocknumber'>{$p->getType()}: {$p->getName()}</a>
      <BR>
      <span id='StockCountWarning$stocknumber' class='text-warning fw-bold'>
  ";
  if($stockQuantity < $cartQuantity) {
    $inventoryHTML .= 'WARNING: we '.(
        $stockQuantity == 0 ? "don't have any" : 'only have '.$stockQuantity
      ).' of this item in stock. Check our exact inventory with a TekBots employee.';
  }
  $inventoryHTML .= "
      </span>
    </td>
    <td>$studentPriceStr</td>
    <td>".($cart->getEditableStatus()
      ? "<input
        type='number' min='0' value='{$cartQuantity}' class='form-control' style='width: 80px;'
        onchange=\"setPartQuantityInCart('{$cart->getIdKey()}', '{$stocknumber}', Number(this.value), {$stockQuantity})\"
      >"
      : $cartQuantity
    ).'</td>';
    if ($cart -> getEditableStatus()) {
      $inventoryHTML .= "<td>
        <i class='fas fa-trash fa-lg' onClick=\"deletePartInCart('{$cart->getIdKey()}', '$stocknumber')\"></i>
      </td>";
    }
    $inventoryHTML .= '</tr>';
}


$shippingOptions = $transactionDao->getShippingOptions();

$shippingPrice = 0;
$shippingOptionsHTML = '';
foreach ($shippingOptions as $option) {
  $shippingOptionsHTML .= 
    "<option
      value='{$option->getID()}'
      data-base-cost='".$option->getBaseCost()."'
      data-item-cost='".$option->getItemCost()."'
      ".($option->getIsDefault() ? 'selected' : '')."
    >
      {$option->getName()}
    </option>";
  
  if ($option->getIsDefault()) {
    $shippingPrice = $option->getBaseCost() + $option->getItemCost() * $cartCount;
  }
}

?>

<br>

<div id="page-top">
  <div id="wrapper">
    <div class="admin-content" id="content-wrapper">
      <div class="container-fluid">
        <div class="admin-paper pb-4">
          <div class="row">
            <div class="col-8">
              <h2 class="h3 mb-1">
                Your Cart Code: <span class="text-danger"><?= $cart->getIdKey(); ?></span>
              </h2>
            </div>
            <div class="col-4 justify-content-end d-flex">
              <button class="btn btn-outline-secondary" onClick="createNewCart()">
                <i class="fas fa-cart-plus"></i> New Cart
              </button>
            </div>
            <div class="col-8">
              <p class="mb-1">Provide your cart code to a TekBots employee for easy checkout.</p>
              <p class="mb-2">Below is a list of items currently in your cart. You can add more items to your cart from the <a href="./publicInventory.php">inventory page</a>.</p>
            </div>
          </div>

          <?= $alertMessage ?>
      
          <div class="row d-flex">
            <!-- Cart items -->
            <div class="col-md-7 table-responsive">
              <table class="table" id="InventoryTable" style="width: 100%; max-width: 100%;">
                <thead>
                  <tr>
                    <th class="border-top-0">Item</th>
                    <th class="border-top-0">Price (ea.)</th>
                    <th class="border-top-0">Cart Quantity</th>
                    <?= $cart->getEditableStatus() ? '<th class="border-top-0"></th>' : '' ?>
                  </tr>
                </thead>
                <tbody>
                  <?php echo $inventoryHTML;?>
                </tbody>
              </table>
            </div>

            <!-- Cart summary -->
            <div class="col-md-5">
              <div class="card shadow" style="height: unset !important;">
                <div class="card-body">
                  <?= $cart->getEditableStatus() ? '' : '<h5 class="text-warning">This cart is not editable.</h5>' ?>
                  <h2 class="h4">Cart Summary</h2>
                  <p style="white-space: unset;">
                    Cart Code: <span class="text-danger"><?= $cart->getIdKey() ?></span><br>
                    Cart Items: <span class="cart-total-items"><?= $cartCount ?></span><br>
                    Cart Price: <span class="cart-total-price"><?= numberToDollarString($cartPrice) ?></span> USD
                  </p>
              
                  <h2 class="h4">Order</h2>
                  <ul class="nav nav-tabs" id="checkoutTab" role="tablist">
                    <li class="nav-item" role="presentation">
                      <button
                        class="btn nav-link active" id="pickupTab" type="button" role="tab"
                        data-toggle="tab" data-target="#pickup" aria-controls="pickup"
                        aria-selected="true" <?= $cartCount > 0 ? '' : 'disabled' ?>
                      >
                        Local Pickup
                      </button>
                    </li>
                    <li class="nav-item" role="presentation">
                      <button
                        class="btn nav-link" id="deliveryTab" type="button" role="tab"
                        data-toggle="tab" data-target="#delivery" aria-controls="delivery"
                        aria-selected="false" <?= $cartCount > 0 ? '' : 'disabled' ?>
                      >
                        Ship to Me
                      </button>
                    </li>
                  </ul>
                  <div class="tab-content mt-3">
                    <div class="tab-pane fade show active" id="pickup" role="tabpanel" aria-labelledby="pickupTab">
                      To pay when you pick up your items, provide your cart code to a TekBots employee in-person at
                      KEC 1110 during store hours, posted <a href="./pages/index.php">here</a>.
                      If you would like to pay ahead of time, press the button below and provide your receipt during
                      pickup instead.
                      
                      <form id="pickupForm">
                        <input type="hidden" name="method" value="0">
                        <div class="row d-flex">
                          <div class="col text-nowrap mt-auto">
                            Total cost:
                            <span class="cart-total-price"><?= numberToDollarString($cartPrice) ?></span> (cart)
                            + $0.00 (shipping)
                            = <span class="cart-total-price"><?= numberToDollarString($cartPrice) ?></span> USD
                          </div>
                          <div class="col text-right text-nowrap">
                            <button type="submit" class="btn btn-primary" <?= $cartCount > 0 ? '' : 'disabled' ?>>Pay Now</button>
                          </div>
                        </div>
                      </form>
                    </div>
                    <div class="tab-pane fade" id="delivery" role="tabpanel" aria-labelledby="deliveryTab">
                      <form id="deliveryForm">
                        <div class="form-row">
                          <div class="form-group col">
                            <label for="shipMethod">Shipping Method <span class="text-danger">*</span></label>
                            <select class="custom-select" id="shipMethod" name="method" onchange="setTotals('<?= $cart->getIdKey() ?>')" required>
                              <?= $shippingOptionsHTML ?>
                            </select>
                          </div>
                        </div>

                        <div class="form-row">
                          <div class="form-group col-md-4">
                            <label for="name">Name <span class="text-danger">*</span></label>
                            <input name="name" id="name" class="form-control" type="text" autocomplete="name" required value="<?= $user ? $user->getFirstName().' '.$user->getLastName() : '' ?>">
                          </div>
                          <div class="form-group col-md-5">
                            <label for="email">Email Address <span class="text-danger">*</span></label>
                            <input name="email" id="email" class="form-control" type="email" autocomplete="email" required value="<?= $user ? $user->getEmail() : '' ?>">
                          </div>
                          <div class="form-group col-md-3">
                            <label for="phone">Phone Number <span class="text-danger">*</span></label>
                            <input name="phone" id="phone" class="form-control" type="tel" autocomplete="tel" required value="<?= $user ? $user->getPhone() : '' ?>">
                          </div>
                        </div>
                        <div class="form-row">
                          <div class="form-group col-md-12">
                            <label for="address1">Street Address Line 1 <span class="text-danger">*</span></label>
                            <input name="address1" id="address1" class="form-control" type="text" autocomplete="address-line1" required>
                          </div>
                        </div>
                        <div class="form-row">
                          <div class="form-group col-md-12">
                            <label for="address2">Street Address Line 2</label>
                            <input name="address2" id="address2" class="form-control" type="text" autocomplete="address-line2">
                          </div>
                        </div>
                        <div class="form-row">
                          <div class="form-group col-md-4">
                            <label for="city">City <span class="text-danger">*</span></label>
                            <input name="city" id="city" class="form-control" type="text" autocomplete="address-level2" required>
                          </div>
                          <div class="form-group col-md-2">
                            <label for="state">State <span class="text-danger">*</span></label>
                            <input name="state" id="state" class="form-control" type="text" autocomplete="address-level1" required>
                          </div>
                          <div class="form-group col-md-2">
                            <label for="zip">Zip Code <span class="text-danger">*</span></label>
                            <input name="zip" id="zip" class="form-control" type="text" autocomplete="postal-code" required>
                          </div>
                          <div class="form-group col-md-4">
                            <label for="country">Country <span class="text-danger">*</span></label>
                            <input name="country" id="country" class="form-control" type="text" autocomplete="country-name" required>
                          </div>
                        </div>
                        <div class="form-row">
                          <div class="form-check">
                            <input type="checkbox" id="billAddress" class="form-check-input" checked>
                            <label for="billAddress">Use shipping address for billing</label>
                          </div>
                        </div>
                        <div class="row d-flex">
                          <div class="col text-nowrap mt-auto">
                            Total cost:
                            <span class="cart-total-price"><?= numberToDollarString($cartPrice) ?></span> (cart)
                            + <span class="shipping-total-price"><?= numberToDollarString($shippingPrice) ?></span> (shipping*)
                            = <span class="total-price"><?= numberToDollarString($cartPrice + $shippingPrice) ?></span> USD
                            <p class="text-muted small text-nowrap mb-0">*If your order's shipping significantly exceeds this cost, we will contact you.</p>
                          </div>
                          <div class="col text-right text-nowrap mt-auto">
                            <button type="submit" class="btn btn-primary" <?= $cartCount > 0 ? '' : 'disabled' ?>>Pay Now</button>
                          </div>
                        </div>
                      </form>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  $(function() {
    $('#deliveryForm').on('submit', handleCheckout);
    $('#pickupForm').on('submit', handleCheckout);
  });

  function handleUpaySubmit({amount, siteId, transactionId, url, validationKey}, params) {
    const data = {
      action: 'updateTransaction', // uPay will pass this back to posting URL, and our action handler needs it
      AMT: amount,
      EXT_TRANS_ID: transactionId,
      SHIP_METHOD: params.method,
      UPAY_SITE_ID: siteId,
      VALIDATION_KEY: validationKey,
    };

    if (params.method != <?= TransactionDelivery::PICKUP ?> && $('#billAddress').prop("checked")) {
      data.BILL_NAME = params.name;
      data.BILL_EMAIL_ADDRESS = params.email;
      data.BILL_STREET1 = params.address1;
      data.BILL_STREET2 = params.address2;
      data.BILL_CITY = params.city;
      data.BILL_STATE = params.state;
      data.BILL_POSTAL_CODE = params.zip;
      data.BILL_COUNTRY = params.country;
    }

    const form = $('<form>', { method: 'POST', action: url });
    for (const [name, value] of Object.entries(data)) {
      form.append($('<input>', { type: 'hidden', name, value }));
    }

    form.appendTo('body').submit().remove();
  }

  function handleCheckout(e) {
    e.preventDefault();

    const params = Object.fromEntries(new FormData(this));
    const body = {
      action: 'checkout',
      ...params
    };

    api.post('/transactions.php', body)
      .then(res => handleUpaySubmit(res.content, params))
      .catch(err => snackbar(err.message, 'error'));
  }

  function generateStockCountWarningForPart(partID, quantity, stockQuantity) {    
    if(quantity > stockQuantity) {
      $('#StockCountWarning'+partID).text(
        `WARNING: we ${stockQuantity === 0 
          ? "don't have any" 
          : `only have ${stockQuantity}`
        } of this item in stock. Check our exact inventory with a TekBots employee.`
      );
    } else {
      $('#StockCountWarning'+partID).text('');
    }
  }

  function setTotals(cartID) {
    let data = {
      cartID: cartID,
      action: 'getCartTotals'
    };

    api.post('/inventory.php', data).then(res => {
      const cartPrice = res.content.totalPrice,
        shippingCost = parseFloat($('#shipMethod option:selected').data('base-cost'))
          + parseFloat($('#shipMethod option:selected').data('item-cost')) * res.content.totalQuantity;
      
      $('.cart-total-items').text(res.content.totalQuantity);
      $('.cart-total-price').text(res.content.totalPriceString);
      $('.shipping-total-price').text(`$${shippingCost.toFixed(2)}`);
      $('.total-price').text(`$${(cartPrice + shippingCost).toFixed(2)}`);
    }).catch(err => {
      snackbar(err.message, 'error');
    });
  }

  function setPartQuantityInCart(cartID, partID, quantity, stockCount) {
    let data = {
      cartID: cartID,
      partID: partID,
      qty: quantity, 
      action: 'setPartQuantityInCart'
    };

    api.post('/inventory.php', data).then(res => {
      setTotals(cartID);
      generateStockCountWarningForPart(partID, quantity, stockCount);
    }).catch(err => {
      snackbar(err.message, 'error');
    });
  }

  function deletePartInCart(cartID,partID) {
    let data = {
      cartID: cartID,
      partID: partID,
      qty: 0, 
      action: 'setPartQuantityInCart'
    };

    api.post('/inventory.php', data).then(res => {
      snackbar(res.message, 'success');
      location.reload();
    }).catch(err => {
      snackbar(err.message, 'error');
    });	
  }

  function createNewCart() {
    if(!confirm("Are you sure you want to create a new cart? This will delete your current cart. Store your cart ID to retrieve it later.")) {
      return;
    }

    let data = {
      action: 'createNewCart'
    };

    api.post('/inventory.php', data).then(res => {
      snackbar(res.message, 'success');
      localStorage.setItem('refreshCart', Date.now()); // Used to update cart in other tabs
      location.reload();
    }).catch(err => {
      snackbar(err.message, 'error');
    });
  }
</script>

<?php include_once PUBLIC_FILES . '/modules/footer.php'; ?>

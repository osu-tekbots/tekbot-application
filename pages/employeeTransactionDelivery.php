<?php
include_once '../bootstrap.php';

use DataAccess\TransactionDao;
use Model\TransactionDelivery;

if (PHP_SESSION_ACTIVE != session_status())
  session_start();

// Make sure the user is logged in and allowed to be on this page
include_once PUBLIC_FILES . '/lib/shared/authorize.php';

allowIf(verifyPermissions('employee', $logger), 'index.php');

$title = 'Employee Transaction Delivery Methods';
$css = array(
  'assets/css/sb-admin.css',
  'assets/css/admin.css',
	'https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css',
);
$js = array(
  'assets/js/jquery.dataTables.min.js',
);
include_once PUBLIC_FILES . '/modules/header.php';
include_once PUBLIC_FILES . '/modules/employee.php';
include_once PUBLIC_FILES . '/modules/receipt.php';


$transactionDao = new TransactionDao($dbConn, $logger);

$deliveryMethods = $transactionDao->getAllDeliveryMethods();
// Class constants mean the method's value is hardcoded in PHP, so it's likely in use in
// the code. We should call these out so employees know to treat them with care.
$deliveryConstants = (new ReflectionClass(TransactionDelivery::class))->getConstants();


$tableHTML = '<table class="table">
  <thead>
    <tr>
      <th>Default</th>
      <th>Name</th>
      <th>Special Use</th>
      <th>Base Cost</th>
      <th>Cost per Item</th>
    </tr>
  </thead>
  <tbody>';

foreach($deliveryMethods as $method) {
  $tableHTML .= "<tr>
    <td>
      <input name='default' type='radio' onchange='setDefault({$method->getId()})' ".($method->getIsDefault() ? 'checked' : '').">
    </td>
    <td>
      <input value='{$method->getName()}' onchange='setName({$method->getId()}, this.value)' class='form-control'>
    </td>
    <td>".(
      in_array($method->getID(), $deliveryConstants, true)
        ? '<span class="badge badge-warning">Hardcoded</span>'
        : ''
    )."</td>
    <td>
      <div class='input-group'>
        <div class='input-group-prepend'><div class='input-group-text'>$</div></div>
        <input
          value='{$method->getBaseCost()}' onchange='setBaseCost({$method->getId()}, this.value)'
          type='number' class='form-control'
        >
      </div>
    </td>
    <td>
      <div class='input-group'>
        <div class='input-group-prepend'><div class='input-group-text'>$</div></div>
        <input
          value='{$method->getItemCost()}' onchange='setItemCost({$method->getId()}, this.value)'
          type='number' class='form-control'
        >
      </div>
    </td>
  </tr>";
}

$tableHTML .= '</tbody>
  </table>';

?>

<br/>
<div id="page-top">
  <div id="wrapper">
    <?php renderEmployeeSidebar() ?>
    <div class="admin-content" id="content-wrapper">
      <div class="container-fluid">
        <div class="admin-paper">
          <h2>Transaction Delivery Methods</h2>
          <?= $tableHTML ?>
        </div>
        <div class="admin-paper" onsubmit="event.preventDefault(); addMethod();">
          <form class="form-inline" style="gap: 1rem;">
            <div class="form-group flex-grow-1">
              <label for="name" class="mr-1">Name</label>
              <input id="name" class="form-control">
            </div>
            <div class="form-group flex-grow-1">
              <label for="baseCost" class="mr-1">Base Cost</label>
              <div class='input-group'>
                <div class='input-group-prepend'><div class='input-group-text'>$</div></div>
                <input id="baseCost" type="number" class="form-control" value="0.00">
              </div>
            </div>
            <div class="form-group flex-grow-1">
              <label for="itemCost" class="mr-1">Cost per Item</label>
              <div class='input-group'>
                <div class='input-group-prepend'><div class='input-group-text'>$</div></div>
                <input id="itemCost" type="number" class="form-control" value="0.00">
              </div>
            </div>
            <button type="submit" class="btn btn-primary">Add method</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  function addMethod() {
    const data = {
      action: 'addDeliveryMethod',
      name: $('#name').val(),
      baseCost: $('#baseCost').val(),
      itemCost: $('#itemCost').val(),
    };

    api.post('/transactions.php', data).then(res => {
      snackbar(res.message, 'success');
      setTimeout(() => window.location.reload(), 1000);
    }).catch(err => {
      snackbar(err.message, 'error');
    });
  }

  function setDefault(id) {
    const data = {
      action: 'setDefaultDeliveryMethod',
      id
    };

    api.post('/transactions.php', data).then(res => {
      snackbar(res.message, 'success');
    }).catch(err => {
      snackbar(err.message, 'error');
    });
  }

  function setName(id, name) {
    const data = {
      action: 'setDeliveryMethodName',
      id,
      name
    };

    api.post('/transactions.php', data).then(res => {
      snackbar(res.message, 'success');
    }).catch(err => {
      snackbar(err.message, 'error');
    });
  }

  function setBaseCost(id, cost) {
    const data = {
      action: 'setDeliveryMethodBaseCost',
      id,
      cost
    };

    api.post('/transactions.php', data).then(res => {
      snackbar(res.message, 'success');
    }).catch(err => {
      snackbar(err.message, 'error');
    });
  }

  function setItemCost(id, cost) {
    const data = {
      action: 'setDeliveryMethodItemCost',
      id,
      cost
    };

    api.post('/transactions.php', data).then(res => {
      snackbar(res.message, 'success');
    }).catch(err => {
      snackbar(err.message, 'error');
    });
  }
</script>

<?php include_once PUBLIC_FILES . '/modules/footer.php'; ?>

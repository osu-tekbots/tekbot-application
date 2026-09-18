<?php
include_once '../bootstrap.php';

use DataAccess\InventoryDao;
use DataAccess\TransactionDao;
use Model\Transaction;
use Model\TransactionDelivery;

if (PHP_SESSION_ACTIVE != session_status())
  session_start();

// Make sure the user is logged in and allowed to be on this page
include_once PUBLIC_FILES . '/lib/shared/authorize.php';

allowIf(verifyPermissions('employee', $logger), 'index.php');

$title = 'Employee Transaction';
$css = array(
  'assets/css/sb-admin.css',
  'assets/css/admin.css',
	'https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css',
	'https://cdn.datatables.net/buttons/1.6.2/css/buttons.dataTables.min.css',
);
$js = array(
  'assets/js/jquery.dataTables.min.js',
	'https://cdn.datatables.net/buttons/1.6.2/js/dataTables.buttons.js',
	'https://cdn.datatables.net/buttons/1.6.2/js/buttons.print.js',
);
include_once PUBLIC_FILES . '/modules/header.php';
include_once PUBLIC_FILES . '/modules/employee.php';
include_once PUBLIC_FILES . '/modules/receipt.php';


$inventoryDao = new InventoryDao($dbConn, $logger);
$transactionDao = new TransactionDao($dbConn, $logger);

$statusDescriptions = [
  'Fulfilled' => 'The order&apos;s items have already been distributed',
  'Success' => 'Payment was successful and the order&apos;s items should be distributed',
  'Pending' => 'The user was sent to TouchNet but has not paid',
  'Canceled' => 'The user clicked "cancel" instead of paying on on TouchNet',
];

$transactionID = $_GET['id'] ?? '';
if (!empty($transactionID)) {
  $transaction = $transactionDao->getTransaction($transactionID);
  
  if ($transaction) {
    $items = $transactionDao->getTransactionItems($transactionID);

    $tpgTransId = $transaction->getTpgTransId() ?? '';
    $sysTrackingId = $transaction->getSysTrackingId() ?? '';

    $totalItems = array_reduce($items, fn ($prev, $item) => $prev + $item->getQuantity());
    $totalCost = numberToDollarString($transaction->getAmount());
    
    $transactionItemsHTML = createTransactionReceiptTable($inventoryDao, $items);

    $transactionSummaryHTML = "
      <div class='card p-3 shadow-sm' style='height: fit-content !important'>
        <h4 class='text-center mb-3'>Transaction Summary</h4>
        <p style='white-space: normal'>
          External Transaction ID: <code>$transactionID</code><br>
          TPG transaction ID: <code>$tpgTransId</code><br>
          System tracking ID: <code>$sysTrackingId</code>
        </p>
        <p style='white-space: normal'>
          Payment Status:
          <b class='".( $transaction->getStatus() == Transaction::SUCCESS ? 'text-success' : 'text-danger')."'>
            {$transaction->getStatus()}
          </b>
          <span class='text-muted'>".(
            $transaction->getDatePaid()?->format('(\P\a\i\d \o\n m/d/Y)') ?? ''
          )."</span>
        </p>
        <p style='white-space: normal'>
          Total items: $totalItems<br>
          Total price: $totalCost
        </p>
        <div class='form-group'>
          <label for='employeeNotes' class='mb-0'>Employee Notes</label>
          <textarea id='employeeNotes' class='form-control' onchange='updateEmployeeNotes()' >{$transaction->getEmployeeNotes()}</textarea>
        </div>
        <p style='white-space: normal'>
          Delivery method: {$transaction->getDeliveryMethod()->getName()}<br>
          Fulfilled: ".(
            $transaction->getDateFulfilled()
              ? '<span class="text-danger">'.$transaction->getDateFulfilled()?->format('m/d/Y \a\t g:ia').'</span'
              : 'No'
          )."
        </p>";

    if ($transaction->getDeliveryMethod()->getID() != TransactionDelivery::PICKUP) {
      $transactionSummaryHTML .= "
        <p class='ml-4' style='white-space: normal'>
          {$transaction->getShippingName()}<br>
          {$transaction->getShippingAddress1()}<br>
          {$transaction->getShippingAddress2()}<br>
          {$transaction->getShippingCity()}, {$transaction->getShippingState()} {$transaction->getShippingCode()}<br>
          {$transaction->getShippingCountry()}
        </p>";
    }

    $transactionSummaryHTML .= '</div>';
  } else {
    $transactionItemsHTML = '<small class="text-muted">No transaction found</small>';
    $transactionSummaryHTML = '';
  }
  $transactionsTableHTML = '';
} else {
  $transactionItemsHTML = '';
  $transactionSummaryHTML = '';
  $transactionsTableHTML = '<table class="table" id="TransactionTable">
    <thead>
      <tr>
        <th>EXT_TRANS_ID</th>
        <th>Paid By</th>
        <th style="display: none;" class="d-md-table-cell">Items</th>
        <th style="display: none;" class="d-md-table-cell">Amount ($)</th>
        <th style="display: none;" class="d-md-table-cell">Status</th>
        <th>Date Paid</th>
        <th style="display: none;" class="d-md-table-cell">Shipping</th>
      </tr>
    </thead>
    <tbody>';
  
  $transactions = $transactionDao->getAllTransactions();

  foreach ($transactions as $transaction) {
    $items = count($transactionDao->getTransactionItems($transaction->getTransactionID()));
    $status = $transaction->getDateFulfilled()
      ? 'Fulfilled'
      : $transaction->getStatus();
    $statusClass = $status === 'Success'
      ? 'text-success'
      : ($status === 'Fulfilled'
        ? 'text-warning'
        : 'text-danger font-weight-bold'
      );

    $transactionsTableHTML .= "<tr>
      <td><a href='pages/employeeTransaction.php?id={$transaction->getTransactionID()}'>
        {$transaction->getTransactionID()}
      </a></td>
      <td>{$transaction->getCardName()}</td>
      <td style='display: none;' class='d-md-table-cell'>{$items}</td>
      <td style='display: none;' class='d-md-table-cell'>{$transaction->getAmount()}</td>
      <td style='display: none;' class='d-md-table-cell'>
        <span class='$statusClass'>{$status}</span>
        <i class='fas fa-question-circle float-right text-muted' data-toggle='tooltip' title='{$statusDescriptions[$status]}'></i>
      </td>
      <td>{$transaction->getDatePaid()?->format('Y-m-d')}</td>
      <td style='display: none;' class='d-md-table-cell'>{$transaction->getDeliveryMethod()->getName()}</td>
    </tr>";
  }
  
  $transactionsTableHTML .='
    </tbody>
  </table>
  <div class="row mt-3">
    <div class="col text-right">
      <button
        class="btn btn-outline-danger"
        type="button" onclick="purgeIncompleteTransactions()"
        data-toggle="tooltip" title="Deletes pending/canceled transactions from >30 days ago"
      >
        <i class="fas fa-trash-fill"></i>
        Purge Incomplete
      </button>
    </div>
  </div>';
}

$transactions = $transactionDao->getAllTransactions();

?>

<br/>
<div id="page-top">
  <div id="wrapper">
    <div class="admin-content" id="content-wrapper">
      <div class="container-fluid">
        <div class="admin-paper">
          <div class="row d-flex align-items-stretch">
            <form id="idForm" class="col-md-8" method="GET" action="./employeeTransaction.php">
              <div class="row align-items-end">  
                <div class="form-group col-sm-10 flex-shrink-1">
                  <label for="id">External Transaction ID:</label>
                  <input
                    id="id" name="id" class="form-control"
                    type="text" value="<?= $transactionID ?>" list="idList"
                    oninput="getTransactionCount(this.value)"
                  >
                  <datalist id="idList">
                    <?php
                      foreach ($transactions as $t) {
                        echo "<option value='{$t->getTransactionID()}'>";
                      }
                    ?>
                  </datalist>
                </div>
                <div class="col-sm-2 mb-3 d-flex">
                  <button type="submit" class="btn btn-primary ml-auto">
                    <i class="fas fa-search"></i> Search
                  </button>
                </div>
              </div>
            </form>
            <div class="col-md-4 mb-3 d-flex align-items-end justify-content-between">
              <div class="d-flex" style="gap: 1rem;">
                <?php
                  if (isset($transaction) && $transaction->getStatus() == Transaction::SUCCESS) {
                    if (is_null($transaction->getDateFulfilled())) {
                      echo '<button type="button" class="btn btn-outline-success" data-toggle="modal" data-target="#fulfillModal">
                        <i class="fas fa-check"></i>
                        Mark fulfilled
                      </button>';
                    }
                  } 
                ?>
              </div>
              <a href="./employeeInventoryCarts.php">Carts Page</a>
            </div>
          </div>

          <div class="row">
            <div class="col-md-8">
              <?= $transactionItemsHTML ?>
            </div>
            <div class="col-md-4">
              <?= $transactionSummaryHTML ?>
            </div>
          </div>

          <?= $transactionsTableHTML ?>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- "Mark fulfilled" modal -->
<div class="modal fade" id="fulfillModal">
  <div class="modal-dialog">
    <div class="modal-content">
        <!-- Modal Header -->
        <div class="modal-header">
          <h4 class="modal-title">Fulfill Transaction</h4>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>

        <!-- Modal body -->
        <div id="body" class="modal-body">
          Are you sure you wish to mark this transaction as fulfilled? This should be done
          <b>after</b> an order is handed out or shipped and <b>cannot be undone</b>.
        </div>

      <!-- Modal footer -->
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-success" data-dismiss="modal" onclick="fulfillTransaction()">
            Confirm
          </button>
        </div>
    </div>
  </div>
</div>

<script type='text/javascript'>
  function fulfillTransaction() {
    const urlParams = new URLSearchParams(window.location.search);
    const data = {
      action: 'fulfillTransaction',
      id: urlParams.get('id')
    };

    api.post('/transactions.php', data).then(res => {
      snackbar(res.message, 'success');
      setTimeout(() => window.location.reload(), 1000);
    }).catch(err => {
      snackbar(err.message, 'error');
    });
  }


  function updateEmployeeNotes() {
    const urlParams = new URLSearchParams(window.location.search);
    const data = {
      action: 'updateEmployeeNotes',
      id: urlParams.get('id'),
      notes: $('#employeeNotes').val()
    };

    api.post('/transactions.php', data).then(res => {
      snackbar(res.message, 'success');
    }).catch(err => {
      snackbar(err.message, 'error');
    });
  }


  function purgeIncompleteTransactions() {
    const data = {
      action: 'purgeIncompleteTransactions'
    };

    api.post('/transactions.php', data).then(res => {
      snackbar(res.message, 'success');
      setTimeout(() => window.location.reload(), 1000);
    }).catch(err => {
      snackbar(err.message, 'error');
    });
  }


  <?= createReceiptDatatable(
    'Transaction ID',
    '[
      null,
      null,
      null,
      null,
      null,
      { orderable: false },
      { className: "item-print-col" },
      { className: "info-print-col" }
    ]',
    '[6, 7]'
  ) ?>


  $('#TransactionTable').DataTable({
    lengthMenu: [[10, 20, -1], [10, 20, 'All']],
    aaSorting: [[5, 'desc']],
    initComplete: function () {
      // Make search input match Bootstrap style
      const searchInput = $(this.DataTable().table().container()).find('div.dataTables_filter');
      searchInput.addClass('form-inline mb-2');
      searchInput.find('input').addClass('form-control form-control-sm');
    },
  });

  $('[data-toggle="tooltip"]').tooltip();
</script>

<?php include_once PUBLIC_FILES . '/modules/footer.php'; ?>

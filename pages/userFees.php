<!-- 

    Keeping for future use to allow users to pay fees associated with improper equipment return

 -->


<?php
include_once '../bootstrap.php';

use DataAccess\UsersDao;
use DataAccess\EquipmentFeeDao;
use DataAccess\EquipmentCheckoutDao;

session_start();

include_once PUBLIC_FILES . '/lib/shared/authorize.php';

allowIf(verifyPermissions(['user', 'employee'], $logger), $configManager->getBaseUrl() . 'pages/login.php');

$title = 'My Fees';
$css = array(
	'assets/css/admin.css',
	'https://cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css'
);

$js = array(
    'https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js'
);
include_once PUBLIC_FILES . '/modules/header.php';
include_once PUBLIC_FILES . '/modules/newHandoutModal.php';
include_once PUBLIC_FILES . '/modules/renderBrowse.php';


$checkoutFeeDao = new EquipmentFeeDao($dbConn, $logger);
$equipmentCheckoutDao = new EquipmentCheckoutDao($dbConn, $logger);

$uID = $_SESSION['userID'];
$checkoutFees = $checkoutFeeDao->getFeesForUser($uID);
$checkoutFeeHTML = '';

foreach ($checkoutFees as $f){
    $checkoutID = $f->getCheckoutID();
    $checkout = $equipmentCheckoutDao->getCheckout($checkoutID);
    $feeNotes = $f->getNotes();
    $feeAmount = $f->getAmount();
    $feeCreated = $f->getDateCreated();
    $feeID = $f->getFeeID();

    $isPending = $f->getIsPending();
    $isPaid = $f->getIsPaid();
    renderEquipmentReturnModal($checkout);

    renderPayFeeModal($f);
    $payButton = $isPending ? "IS PENDING" : ($isPaid ? "PAID" : createPayButton($feeID));

    $checkoutFeeHTML .= "
    <tr>
        <td><a href='' data-toggle='modal' 
        data-target='#newReturnModal$checkoutID'>Checkout</a></td>
        <td>$feeNotes</td>
        <td>$feeAmount</td>
        <td>$payButton</td>
    </tr>
    ";


}

?>

<br /><br />
<div class="container-fluid">
    <h1>My Fees</h1>
     
        <?php
            echo "
            <br>
            <h2>Equipment Checkout Fees </h2>
            ";
        if (empty($checkoutFees)){
            echo '<h5>No Pending Fees!</h5>';

        } else {
            echo "
            
        <div class='admin-paper'>
            <table class='table' id='equipmentFees'>
                <thead>
                    <tr>
                        <th>Related Checkout</th>
                        <th>Notes</th>
                        <th>Amount</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    $checkoutFeeHTML
                </tbody>
            </table>
            <script>
                $('#equipmentFees').DataTable();
            </script>
        </div>
            
            ";
           
        }
            // File located inside modules/renderBrowse.php
        ?>
 
      
        
</div>
    

       




<?php include_once PUBLIC_FILES . '/modules/footer.php'; ?>
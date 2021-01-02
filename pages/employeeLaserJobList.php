
<?php
include_once '../bootstrap.php';

use DataAccess\LaserDao;
use DataAccess\UsersDao;
use Util\Security;

if (!session_id()) {
    session_start();
}

// Make sure the user is logged in and allowed to be on this page
include_once PUBLIC_FILES . '/lib/shared/authorize.php';

$isEmployee = isset($_SESSION['userID']) && !empty($_SESSION['userID']) 
	&& isset($_SESSION['userAccessLevel']) && $_SESSION['userAccessLevel'] == 'Employee';

allowIf($isEmployee, 'index.php');


$title = 'Employee Laser Job List';
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

// Handout Modal Functionality
include_once PUBLIC_FILES . '/modules/newHandoutModal.php';

$laserDao = new LaserDao($dbConn, $logger);
$userDao = new UsersDao($dbConn, $logger);
$printJobs = $laserDao->getLaserJobs();

?>


<br/>
<div id="page-top">

	<div id="wrapper">

	<?php 
		// Located inside /modules/employee.php
		renderEmployeeSidebar();
	?>

    <div class="admin-content" id="content-wrapper">
        <div class="container-fluid">
            <?php 
                renderEmployeeBreadcrumb('Employee', 'Laser Cuts Jobs List');

                echo "<div class='admin-paper'>";

                $printJobsHTML = "";

                $buttonScripts = "";

                foreach ($printJobs as $p) {
                    $printJobID = $p->getLaserJobId();
                    $userID = $p->getUserID();
                    $user = $userDao->getUserByID($p->getUserID());
                    $name = Security::HtmlEntitiesEncode($user->getFirstName()) . ' ' . Security::HtmlEntitiesEncode($user->getLastName());
                    $printType = Security::HtmlEntitiesEncode($laserDao->getLaserMaterialByID($p->getLaserCutMaterialId())->getLaserMaterialName());
                    $costPerSheet = Security::HtmlEntitiesEncode($laserDao->getCutMaterialByID($p->getLaserCutMaterialId())->getCostPerSheet());
                    $printer = Security::HtmlEntitiesEncode($laserDao->getLaserByID($p->getLaserCutterId())->getLaserName());
                    $dbFileName = $p->getDbFileName();
                    $dxfFileName = $p->getDxfFileName();
                    $dateCreated = $p->getDateCreated();
                    $validPrintDate = $p->getValidCutDate();
                    $userConfirm = $p->getUserConfirmDate();
                    $completePrintDate = $p->getCompleteCutDate();
                    $paymentMethod = $p->getPaymentMethod();
                    $customerNotes = $p->getCustomerNotes();
                    $employeeNotes = $p->getEmployeeNotes();
                    $pendingResponseDate = $p->getPendingCustomerResponse();
                    $paymentConfirmed = $p->getPaymentDate();
                    $voucherCode = $p->getVoucherCode();
                    $quantity = $p->getQuantity();
                    // $dateUpdated = $p->getDateUpdated();
                                        

                    // $currentStatus = "";

                    // // If the print is not yet validated
                    if(!$validPrintDate) {
                        $currentStatus = "<button id='sendConfirm$printJobID' class='btn btn-primary'>Send Confirmation</button>";
                    } else {
                        $currentStatus = "<a data-toggle='tool-tip' data-placement='top' title='$validPrintDate'>👀 Cut Validated</a><br/>";
                    }
                    
                    // If print is pending customer confirmation
                    if($userConfirm) {
                        $currentStatus .=  "<a  data-toggle='tool-tip' data-placement='top' title='$userConfirm'>👌 Confirmed By Customer</a><br/>";
                    } elseif ($validPrintDate) { //Only render if print was validated
                        $currentStatus .= "⌛Waiting for confirmation ";
                    }

                    // Render appropriate button for each payment method
                    $payment = "💲Paid: ";
                    $paymentValidation = null;
                    switch($paymentMethod) {
                        case "cc":
                            $paymentValidation = "<button id='ccpayment$printJobID' onClick='verifyCCPayment(\"$printJobID\", \"$name\")' class='btn btn-primary'>Verify CC Payment</button>";
                            $payment .= "CC";
                            break;
                        case "account":
                            $paymentValidation = "<button id='acountpayment$printJobID' onClick='verifyAccountCode(\"$printJobID\", \"$name\")' class='btn btn-primary'>Verify Account Code</button>";
                            $payment .= "Account Code";
                            break;
                        case "voucher":
                            $paymentConfirmed = 1;
                            $payment .= "Voucher ($voucherCode)";

                            break;
                    };

                    // If print is not pending confirmation, payment is not voucher, and has not been payed yet then render the payment button
                    if($userConfirm && $paymentMethod && !$paymentConfirmed) $currentStatus .= $paymentValidation;
                    elseif($paymentConfirmed && $userConfirm) $currentStatus .= "<a data-toggle='tool-tip' data-placement='top' title='$paymentConfirmed'>$payment</a><br/>";

                    if($paymentConfirmed && $completePrintDate && $userConfirm) {
                        $currentStatus .= "<a data-toggle='tool-tip' data-placement='top' title='$completePrintDate'>✔️Completed</a>";
                    } elseif($paymentConfirmed && $userConfirm) { //only render when payment is confirmed
                        $currentStatus .= "<button id='completePrint$printJobID' class='btn btn-primary'>Click when print is finished</button>";
                    }

                    
                    $status = '';
                    $status .= $dateCreated;
                    $status .= "<br/>";
                    $status .= $currentStatus;

                    
                    $printJobsHTML .= "
                    <tr>

                    <td>$name</td>
                    <td>$printer<br/>$printType</td>
                    <td>$quantity</td>
                    <td><a href='./uploads/lasercuts/$dbFileName'><button data-toggle='tool-tip' data-placement='top' title='$dxfFileName' class='btn btn-outline-primary capstone-nav-btn'>Download</button></td>
                    <td><textarea class='form-control' cols=50 rows=4 id='employeeNotes$printJobID'>$employeeNotes</textarea></td>
                    <td>$customerNotes</td>
                    <td>$status</td>
                    <td><button id='delete$printJobID'><i class='fas fa-fw fa-trash'></i></button><button id='process$printJobID'><i class='fas fa-fw fa-thumbs-up'></i></button></td>



                    </tr>
                
                    ";

                    $buttonScripts .= 
                "<script>
                

                $('#employeeNotes$printJobID').on('change', function() {
                    let inputVal = $('#employeeNotes$printJobID').val();
                    let printJobID = '$printJobID';
                    // alert('This is the input: ' + inputVal);
                    let data = {
                        action: 'updateEmployeeNotes',
                        laserJobID: printJobID,
                        employeeNotes: inputVal
                    }
                    api.post('/lasers.php', data).then(res => {
                        snackbar(res.message, 'success');
                    }).catch(err => {
                        snackbar(err.message, 'error');
                    });
                });

                $('#sendConfirm$printJobID').on('click', function() {
                    let totalCost = $costPerSheet * $quantity;
                    totalCost = totalCost.toFixed(2);
                    if(confirm('Confirm laser cut $dxfFileName with cost $' + totalCost + ' and send confirmation email to $name?')) {
                        $('#sendConfirm$printJobID').prop('disabled', true);
                        let printJobID = '$printJobID';
                        let userID = '$userID';
                        let data = {
                            action: 'sendCustomerConfirm',
                            laserJobID: printJobID,
                            userID: userID,
                            cutCost: totalCost
                        }
                        api.post('/lasers.php', data).then(res => {
                            snackbar(res.message, 'success');
                            setTimeout(function(){window.location.reload()}, 1000);
                        }).catch(err => {
                            snackbar(err.message, 'error');
                    });
                        $('#sendConfirm$printJobID').prop('disabled', true);
                    }
                });

                $('#completePrint$printJobID').on('click', function() {
                    if(confirm('Cut $dxfFileName has completed and send confirmation email to $name?')) {
                        $('#completePrint$printJobID').prop('disabled', true);
                        let printJobID = '$printJobID';
                        let userID = '$userID';
                        let data = {
                            action: 'completeCutJob',
                            laserJobID: printJobID,
                            userID: userID
                        }
                        api.post('/lasers.php', data).then(res => {
                            snackbar(res.message, 'success');
                            setTimeout(function(){window.location.reload()}, 1000);
                        }).catch(err => {
                            snackbar(err.message, 'error');
                    });
                        $('#completePrint$printJobID').prop('disabled', true);
                    }
                });

                $('#delete$printJobID').on('click', function() {
                    if(confirm('Delete $dxfFileName cut job created by $name?')) {
                        $('#delete$printJobID').prop('disabled', true);
                        let printJobID = '$printJobID';
                        let data = {
                            action: 'deleteCutJob',
                            laserJobID: printJobID,
                        }
                        api.post('/lasers.php', data).then(res => {
                            snackbar(res.message, 'success');
                            setTimeout(function(){window.location.reload()}, 1000);
                        }).catch(err => {
                            snackbar(err.message, 'error');
                    });
                        $('#completePrint$printJobID').prop('disabled', true);
                    }
                });

                $('#process$printJobID').on('click', function() {
                    if(confirm('Process and complete $dxfFileName cut job created by $name?')) {
                        $('#process$printJobID').prop('disabled', true);
                        let printJobID = '$printJobID';
                        let data = {
                            action: 'processCutJob',
                            laserJobID: printJobID,
                        }
                        api.post('/lasers.php', data).then(res => {
                            snackbar(res.message, 'success');
                            setTimeout(function(){window.location.reload()}, 1000);
                        }).catch(err => {
                            snackbar(err.message, 'error');
                    });
                        $('#completePrint$printJobID').prop('disabled', true);
                    }
                });
            </script>";

                }

                echo"
						
                <h3>Laser Jobs</h3>
                <p><strong>IMPORTANT</strong>: You must process the order in touchnet before approving fees!</p>

                <p>Time stamps in 'Is Cut Valid', 'Customer Confirmation' and 'Cut Completed' colums indicate the completion of that field</p>
                <p>Steps:</p>
                <ol>
                    <li>Verify that cut is valid for selected printer. If so, click 'Send Confirmation'</li>
                    <li>Wait until customer confirms the cut job, which will be indicated as a time stamp and 'Confirmed'</li>
                    <li>If the cut is confirmed, perform the cut job</li>
                    <li>Once finished, click 'Cut finished' button</li>
                </ol>

                <p>Make sure to process any fees that are awaiting approval.  Some of them are tied to prints or cuts and need to be processed before you are able to cut/print.</p>
                <table class='table' id='checkoutFees'>
                <caption>All Submitted Laser Cuts</caption>
                <thead>
                    <tr>
                        <th>Customer</th>
                        <th>Cutter and Material</th>
                        <th>Quantity</th>
                        <th>File</th>
                        <th>Employee Notes</th>
                        <th>Customer Notes</th>
                        <th>Creation Date and Status</th>
                        <th>Actions</th>

                    </tr>
                </thead>
                <tbody>
                    $printJobsHTML
                </tbody>
                </table>
                <script>
                    $('#checkoutFees').DataTable({ 'order':[[6, 'desc']]});
                </script>
                $buttonScripts
                "
                ;
            
                    echo "</div>";
                echo "</div>";
            ?>

        </div>
        <script>
        function verifyCCPayment(printJobID, name) {
            if(confirm("IMPORTANT: Only click ok if " + name + " has payed for the appropriate print in TouchNet and an employee has processed the payment"))
            {
                let data = {
                    action: 'verifyCutPayment',
                    laserJobID: printJobID,
                }
                api.post('/lasers.php', data).then(res => {
                    snackbar(res.message, 'success');
                    let button = "#accountpayment" + printJobID;
                    // disable is not working
                    $(button).prop('disabled', true);
                    setTimeout(function(){window.location.reload()}, 1000);
                }).catch(err => {
                    snackbar(err.message, 'error');
                });
            }    
        }        
        
        function verifyAccountCode(printJobID, name) {
            if(confirm("Verify that account code in 'employee notes' is a valid account code"))
            {
                let data = {
                    action: 'verifyCutPayment',
                    laserJobID: printJobID,
                }
                api.post('/lasers.php', data).then(res => {
                    snackbar(res.message, 'success');
                    let button = "#ccpayment" + printJobID;
                    // disable is not working
                    $(button).prop('disabled', true);
                    setTimeout(function(){window.location.reload()}, 1000);
                }).catch(err => {
                    snackbar(err.message, 'error');
                });
            }        
        }
        </script>
    </div>

<?php 
include_once PUBLIC_FILES . '/modules/footer.php' ; 
?>
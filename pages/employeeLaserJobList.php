
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

allowIf(verifyPermissions('employee'), 'index.php');


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

// View Laser Cuts modal
include_once PUBLIC_FILES . '/modules/viewLaserCutModal.php';

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

                echo "<div class='admin-paper'>";

                $jobsHTML = "";

                foreach ($printJobs as $p) {
                    $laserJobID = $p->getLaserJobId();
                    $userID = $p->getUserID();
                    $user = $userDao->getUserByID($p->getUserID());
                    $name = Security::HtmlEntitiesEncode($user->getFirstName()) . ' ' . Security::HtmlEntitiesEncode($user->getLastName());
                    $email = $user->getEmail();
                    $materialName = Security::HtmlEntitiesEncode($laserDao->getLaserMaterialByID($p->getLaserCutMaterialId())->getLaserMaterialName());
                    $costPerSheet = Security::HtmlEntitiesEncode($laserDao->getCutMaterialByID($p->getLaserCutMaterialId())->getCostPerSheet());
                    $laser = Security::HtmlEntitiesEncode($laserDao->getLaserByID($p->getLaserCutterId())->getLaserName());
                    $dbFileName = $p->getDbFileName();
                    $dxfFileName = $p->getDxfFileName();
                    $dateCreated = $p->getDateCreated();
                    $validCutDate = $p->getValidCutDate();
                    $userConfirm = $p->getUserConfirmDate();
                    $completeCutDate = $p->getCompleteCutDate();
                    $paymentMethod = $p->getPaymentMethod();
                    $customerNotes = $p->getCustomerNotes();
                    $employeeNotes = $p->getEmployeeNotes();
                    $pendingResponseDate = $p->getPendingCustomerResponse();
                    $paymentConfirmed = $p->getPaymentDate();
                    $voucherCode = $p->getVoucherCode();
                    $accountCode = Security::HtmlEntitiesEncode($p->getAccountCode());
                    $quantity = $p->getQuantity();
                    // $dateUpdated = $p->getDateUpdated();

                    $buttons = "<button id='delete$laserJobID' onclick='deleteCut(\"$laserJobID\", \"$name\", \"$dxfFileName\")'><i class='fas fa-fw fa-trash'></i></button>";
                    if(!$completeCutDate)
                        $buttons .= "<button id='process$laserJobID' onclick='processCut(\"$laserJobID\", \"$userID\", \"$name\", \"$dxfFileName\")'><i class='fas fa-fw fa-thumbs-up'></i></button>";
                                        
                    // Show that no notes were left if necessary
                    if($customerNotes == '') $customerNotes = '<p style="text-align:center;">--</p>';

                    // // If the cut is not yet validated
                    if(!$validCutDate) {
                        $currentStatus = "<button id='sendConfirm$laserJobID' class='btn btn-primary' onclick='sendCutConfirm(\"$laserJobID\", \"$dxfFileName\", \"$userID\", \"$name\", \"$costPerSheet\", \"$quantity\")'>Send Confirmation</button>";
                    } else {
                        // $currentStatus = "<a data-toggle='tool-tip' data-placement='top' title='$validCutDate'>👀 Cut Validated</a><br/>";
                        $currentStatus = ""; // Lines up with employeePrintJobList.php
                    }
                    
                    // If cut is pending customer confirmation
                    // if($userConfirm) {
                    //     $currentStatus .=  "<a  data-toggle='tool-tip' data-placement='top' title='$userConfirm'>👌 Confirmed By Customer</a><br/>";
                    // } elseif ($validCutDate) { //Only render if cut was validated
                    //     $currentStatus .= "⌛Waiting for confirmation ";
                    // }
                    // Removed to match employeePrintJobList.php

                    // Render appropriate button for each payment method
                    $payment = "";
                    $paymentValidation = null;
                    switch($paymentMethod) {
                        case "cc":
                            $paymentValidation = "<button id='ccpayment$laserJobID' onClick='verifyCCPayment(\"$laserJobID\", \"$name\")' class='btn btn-primary'>Card Payment Checked/Started Cut</button>";
                            $payment .= "Paid thru TouchNet";
                            break;
                        case "account":
                            $paymentValidation = "<button id='acountpayment$laserJobID' onClick='startCut(\"$laserJobID\", \"$name\")' class='btn btn-primary'>Started Cut</button>";
                            if($accountCode == "") {
                                // Get account code from notes (for old prints before accountCode field)
                                $offset = strpos($employeeNotes, "Account code: ") + strlen("Account code: ");
                                $accountCode = substr($employeeNotes, $offset, (strpos($employeeNotes, "\n", $offset) ?: strlen($employeeNotes)) - $offset);
                            }
                            $payment .= "Account: $accountCode";
                            break;
                        case "voucher":
                            $paymentValidation = "<button id='voucherpayment$laserJobID' onClick='startCut(\"$laserJobID\", \"$name\")' class='btn btn-primary'>Started Cut</button>";
                            $payment .= "Voucher: $voucherCode";

                            break;
                    };

                    $currentStatus .= "<a data-toggle='tool-tip' data-placement='top' title='$paymentConfirmed'>$payment</a><br/>";

                    // If cut is pending customer confirmation & cut was validated
                    if(!$userConfirm && $validCutDate) {
                        $currentStatus .= "<span style='color: #e6c300;'><i class='far fa-hourglass'>&nbsp;</i>Confirmation pending<BR></span>";
                    }

                    // If cut is not pending confirmation, payment is not voucher, and has not been payed yet then render the payment button
                    if($userConfirm && $paymentMethod && !$paymentConfirmed) {
                        $currentStatus .= $paymentValidation;
                    }

                    if($paymentConfirmed && $completeCutDate && $userConfirm) {
                        $currentStatus .= "<a data-toggle='tool-tip' data-placement='top' title='$completeCutDate' style='color: #00cc6a'><i class='fas fa-check'>&nbsp;</i>Completed</a>";
                    } elseif($paymentConfirmed && $userConfirm) { //only render when payment is confirmed
                        $currentStatus .= "<button id='completePrint$laserJobID' class='btn btn-primary' onclick='completeCut(\"$laserJobID\", \"$userID\", \"$name\", \"$dxfFileName\")'>Completed Cut</button>";
                    }

                    
                    $status = '';
                    $status .= $dateCreated;
                    $status .= "<br/>";
                    $status .= $currentStatus;

                    
                    $jobsHTML .= "
                    <tr>

                    <td>
                        <a href='#' onclick='toggleEmailField(\"$laserJobID\"); return false;'>$name</a><BR>
                        <div id='email$laserJobID' style='display:none; margin-bottom: 10px; align-items: center'>
                            <textarea id='emailContents$laserJobID' class='form-control' cols='10' rows='3' placeholder='NOTE: This area holds the entire email contents. There is no template structure for it.'></textarea>
                            <button id='emailBtn$laserJobID' class='btn btn-primary' onclick='sendUserEmail(\"$laserJobID\", \"$email\")'>Send Email</button>
                        </div>
                        <div style='position:relative'>
                            <a href='./uploads/lasercuts/$dbFileName' style='width:240px; display:inline-block; word-break: break-all;' download>$dxfFileName</a><BR>
                            <p style='color:darkgrey; margin-bottom:0px;padding-bottom:0;'>&emsp;$materialName</p>
                            ".//<button data-toggle='modal' data-target='#viewLaserCutModel' data-whatever='$dbFileName' class='btn btn-outline-primary capstone-nav-btn' style='position:absolute; right: 0; top: 0px;'>View</button>
                            // ^ For future use w/ a library that allows viewing DXF files
                        "</div>
                    </td>
                    <td>$quantity</td>
                    <td><textarea class='form-control' cols=50 rows=4 id='employeeNotes$laserJobID' onchange='updateEmployeeNotes(\"$laserJobID\");'>$employeeNotes</textarea></td>
                    <td>$customerNotes</td>
                    <td>$status</td>
                    <td>$buttons</td>



                    </tr>
                
                    ";

                }

                echo "
						
                    <h3>Laser Jobs</h3>
                    <p><strong>IMPORTANT:</strong> You must process the order in touchnet before approving fees!</p>

                    <p>Steps:</p>
                    <ol>
                        <li>Verify that cut is valid for selected laser cutter. 
                            <ul>
                                <li>If so, click 'Send Confirmation'.</li>
                                <li>Otherwise, click their name and compose an email explaining what they need to fix.</li>
                            </ul>
                        </li>
                        <li>Wait until customer confirms the cut job, which will be indicated by a '...Started Cut' button under 'Status'</li>
                        <li>If the customer paid with a credit card, verify the payment is correct in TouchNet</li>
                        <li>Begin cutting the part and click the 'Started Cut' button</li>
                        <li>Once the cut has successfully finished, click the 'Completed Cut' button</li>
                        <li>Profit!</li>
                    </ol>

                    <p>Make sure to process any fees that are awaiting approval.  Some of them are tied to cuts and need to be processed before you are able to cut.</p>
                    <button id='processAllFeesBtn' type='button' class='btn btn-outline-primary' style='margin-bottom: 20px; position: relative; left: 50%; transform:translate(-50%,0)' onclick='processAllFees();'>Proccess All Account Fees</button>
                    <table class='table' id='checkoutFees'>
                        <caption>All Submitted Laser Cuts</caption>
                        <thead>
                            <tr>
                                <th>Customer</th>
                                <th>Qty</th>
                                <th>Employee Notes</th>
                                <th>Customer Notes</th>
                                <th>Creation Date and Status</th>
                                <th>Actions</th>

                            </tr>
                        </thead>
                        <tbody>
                            $jobsHTML
                        </tbody>
                    </table>
                    <script>
                        $('#checkoutFees').DataTable({
                            'order':[[4, 'desc']],
                            'columns': [
                                { 'width': '30%' },
                                { 'width': '2em' },
                                null,						
                                { 'width': '20%' },
                                { 'width': '10em' },
                                null]
                        });
                    </script>
                "
                ;
            
                    echo "</div>";
                echo "</div>";
            ?>

        </div>
        <script>
            function processAllFees() {
                let data = {
                    action: 'processAllFees',
                    messageID: 'xJbyIRMbwEhbImib'
                }
                
                $('#processAllFeesBtn').prop('disabled', true);

                api.post('/lasers.php', data).then(res => {
                    $('#processAllFeesBtn').prop('disabled', false);
                    snackbar(res.message, 'success');
                }).catch(err => {
                    $('#processAllFeesBtn').prop('disabled', false);
                    snackbar(err.message, 'error');
                })
            }

            function toggleEmailField(laserJobID) {
                let field = document.getElementById('email'+laserJobID);
                
                if(field.style.display == 'none') {
                    field.style.display = 'flex';
                } else {
                    field.style.display = 'none';
                }
            }

            function sendUserEmail(laserJobID, email) {
                let message = $('#emailContents'+laserJobID).val();
                if(!message) return;

                let data = {
                    action: 'sendUserEmail',
                    laserJobID: laserJobID,
                    email: email,
                    message: message
                }

                $('#emailBtn'+laserJobID).prop('disabled', true);

                api.post('/lasers.php', data).then(res => {
                    $('#emailContents'+laserJobID).val('');
                    $('#emailBtn'+laserJobID).prop('disabled', false);
                    $('#email'+laserJobID).hide();
                    snackbar(res.message, 'success');
                }).catch(err => {
                    $('#emailBtn'+laserJobID).prop('disabled', false);
                    snackbar(err.message, 'error');
                })
            }

            function verifyCCPayment(laserJobID, name) {
                if(confirm("IMPORTANT: Only click ok if " + name + " has payed for the appropriate cut in TouchNet and an employee has processed the payment"))
                {
                    let data = {
                        action: 'verifyCutPayment',
                        laserJobID: laserJobID,
                    }
                    api.post('/lasers.php', data).then(res => {
                        snackbar(res.message, 'success');
                        let button = "#accountpayment" + laserJobID;
                        // disable is not working
                        $(button).prop('disabled', true);
                        setTimeout(function(){window.location.reload()}, 1000);
                    }).catch(err => {
                        snackbar(err.message, 'error');
                    });
                }
            }        
            
            // 8/3/23 -- No longer used since employees don't have access to check account codes;
            //     just making sure nothing breaks before deleting entirely
            /*function verifyAccountCode(laserJobID, name) {
                let data = {
                    action: 'verifyCutPayment',
                    laserJobID: laserJobID,
                }
                api.post('/lasers.php', data).then(res => {
                    snackbar(res.message, 'success');
                    setTimeout(function(){window.location.reload()}, 1000);
                }).catch(err => {
                    snackbar(err.message, 'error');
                });
            }*/
            
            // Will be used for account codes & voucher codes
            function startCut(laserJobID, name) {
                let data = {
                    action: 'verifyCutPayment',
                    laserJobID: laserJobID,
                }
                api.post('/lasers.php', data).then(res => {
                    snackbar(res.message, 'success');
                    setTimeout(function(){window.location.reload()}, 1000);
                }).catch(err => {
                    snackbar(err.message, 'error');
                });
            }

            function updateEmployeeNotes(laserJobID) {
                let inputVal = $('#employeeNotes'+laserJobID).val();
                let data = {
                    action: 'updateEmployeeNotes',
                    laserJobID: laserJobID,
                    employeeNotes: inputVal
                }
                api.post('/lasers.php', data).then(res => {
                    snackbar(res.message, 'success');
                }).catch(err => {
                    snackbar(err.message, 'error');
                });
            }

            function sendCutConfirm(laserJobID, dxfFileName, userID, name, costPerSheet, quantity) {
                let totalCost = costPerSheet * quantity;
                totalCost = totalCost.toFixed(2);

                if(confirm('Confirm that we can cut "'+dxfFileName+'" with a cost of $' + totalCost + ' and send a confirmation email to '+name+'?')) {
                    $('#sendConfirm'+laserJobID).prop('disabled', true);

                    let data = {
                        action: 'sendCustomerConfirm',
                        laserJobID: laserJobID,
                        userID: userID,
                        cutCost: totalCost,
                        messageID: 'jdkslkfajllkjfas'
                    }
                    api.post('/lasers.php', data).then(res => {
                        snackbar(res.message, 'success');
                        setTimeout(function(){window.location.reload()}, 1000);
                    }).catch(err => {
                        snackbar(err.message, 'error');
                    });
                }
            }

            function completeCut(laserJobID, userID, name, dxfFileName) {
                if(confirm('Cut '+dxfFileName+' has completed? Send confirmation email to '+name+'?')) {
                    $('#completePrint'+laserJobID).prop('disabled', true);
                    let data = {
                        action: 'completeCutJob',
                        laserJobID: laserJobID,
                        userID: userID,
                        messageID: 'ajlsekgjowefj'
                    }
                    api.post('/lasers.php', data).then(res => {
                        snackbar(res.message, 'success');
                        setTimeout(function(){window.location.reload()}, 1000);
                    }).catch(err => {
                        snackbar(err.message, 'error');
                    });
                }
            }
            
            function processCut(laserJobID, userID, name, dxfFileName) {
                if(confirm('Process and complete '+dxfFileName+' created by '+name+'?')) {
                    $('#process'+laserJobID).prop('disabled', true);
                    let data = {
                        action: 'processCutJob',
                        laserJobID: laserJobID,
                        userID: userID
                    }
                    api.post('/lasers.php', data).then(res => {
                        snackbar(res.message, 'success');
                        setTimeout(function(){window.location.reload()}, 1000);
                    }).catch(err => {
                        snackbar(err.message, 'error');
                    });
                }
            }

            function deleteCut(laserJobID, name, dxfFileName) {
                if(confirm('Delete '+dxfFileName+' created by '+name+'?')) {
                    $('#delete'+laserJobID).prop('disabled', true);
                    let data = {
                        action: 'deleteCutJob',
                        laserJobID: laserJobID,
                    }
                    api.post('/lasers.php', data).then(res => {
                        snackbar(res.message, 'success');
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
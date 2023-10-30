<?php
include_once '../bootstrap.php';

use DataAccess\PrinterDao;
use DataAccess\UsersDao;
use Model\EquipmentCheckoutStatus;
use Util\Security;

if (!session_id()) {
    session_start();
}

// Make sure the user is logged in and allowed to be on this page
include_once PUBLIC_FILES . '/lib/shared/authorize.php';

allowIf(verifyPermissions('employee'), 'index.php');


$title = 'Employee Print Job List';
$css = array(
	'assets/css/sb-admin.css',
	'assets/css/admin.css',
	'https://cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css'
);
$js = array(
    'https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js',
	'assets/Madeleine.js/src/lib/stats.js',
	'assets/Madeleine.js/src/lib/detector.js',
	'assets/Madeleine.js/src/lib/three.min.js',
	'assets/Madeleine.js/src/Madeleine.js'
);

include_once PUBLIC_FILES . '/modules/header.php';
include_once PUBLIC_FILES . '/modules/employee.php';
include_once PUBLIC_FILES . '/modules/renderBrowse.php';

// View 3D prints modal
include_once PUBLIC_FILES . '/modules/view3dPrintModal.php';

$printerDao = new PrinterDao($dbConn, $logger);
$userDao = new UsersDao($dbConn, $logger);
$printJobs = $printerDao->getPrintJobs();

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

                echo "<div class='admin-paper'  style='overflow: scroll'>";

                $printJobsHTML = "";

                $buttonScripts = "";

                foreach ($printJobs as $p) {
                    $printJobID = $p->getPrintJobID();
                    $userID = $p->getUserID();
                    $user = $userDao->getUserByID($p->getUserID());
                    $name = Security::HtmlEntitiesEncode($user->getFirstName()) . ' ' . Security::HtmlEntitiesEncode($user->getLastName());
                    $email = $user->getEmail();
                    $printType = Security::HtmlEntitiesEncode($printerDao->getPrintTypesByID($p->getPrintTypeID())->getPrintTypeName());
                    $printTypeCost = Security::HtmlEntitiesEncode($printerDao->getPrintTypesByID($p->getPrintTypeID())->getCostPerGram());
                    $printer = Security::HtmlEntitiesEncode($printerDao->getPrinterByID($p->getPrinterId())->getPrinterName());
                    $quantity = $p->getQuantity();
                    $material_amount = $p->getMaterialAmount();
                    $dbFileName = $p->getDbFileName();
                    $stlFileName = $p->getStlFileName();
                    $dateCreated = $p->getDateCreated();
                    $validPrintDate = $p->getValidPrintCheck();
                    $userConfirm = $p->getUserConfirmCheck();
                    $completePrintDate = $p->getCompletePrintDate();
                    $paymentMethod = $p->getPaymentMethod();
                    $customerNotes = $p->getCustomerNotes();
                    $employeeNotes = $p->getEmployeeNotes();
                    $pendingResponseDate = $p->getPendingCustomerResponse();
                    $paymentConfirmed = $p->getPaymentDate();
                    $voucherCode = $p->getVoucherCode();
                    $accountCode = Security::HtmlEntitiesEncode($p->getAccountCode());
                    // $dateUpdated = $p->getDateUpdated();
                                        

                    $buttons = "<button id='delete$printJobID' onclick='deletePrint(\"$printJobID\", \"$name\", \"$stlFileName\")' class='btn btn-outline-danger mr-1'><i class='fas fa-fw fa-trash'></i></button>";
					if (!$completePrintDate)
						$buttons .= "<button id='process$printJobID' onclick='processPrint(\"$printJobID\", \"$userID\", \"$name\", \"$stlFileName\")' class='btn btn-outline-success'><i class='fas fa-fw fa-thumbs-up'></i></button>";

                    // Show that no notes were left if necessary
                    if($customerNotes == '') $customerNotes = '<p style="text-align:center;">--</p>';

                    // If the print is not yet validated
                    if(!$validPrintDate) {
						$currentStatus = "<div class='input-group' style='margin-bottom:5px'><input class='form-control' type='number' id='grams$printJobID' name='grams$printJobID'><div class='input-group-append'><button class='btn'> Grams</button></div></div>";
                        $currentStatus .= "<button id='sendConfirm$printJobID' class='btn btn-primary' onclick='sendPrintConfirm(\"$printJobID\", \"$userID\", \"$printTypeCost\", \"$quantity\")'>Send Confirmation</button>";
                    } else {
                        $currentStatus = "";
                    }

                    // Render appropriate button for each payment method
                    $payment = "";
                    $paymentValidation = null;
                    switch($paymentMethod) {
                        case "cc":
                            $paymentValidation = "<button id='ccpayment$printJobID' onClick='verifyCCPayment(\"$printJobID\", \"$name\")' class='btn btn-primary'>Card Payment Checked/Started Print</button>";
                            $payment .= "Paid thru TouchNet";
                            break;
                        case "account":
                            $paymentValidation = "<button id='accountpayment$printJobID' onClick='startPrint(\"$printJobID\", \"$name\")' class='btn btn-primary'>Started Print</button>";
                            if($accountCode == "") {
                                // Get account code from notes (for old prints before accountCode field)
                                $offset = strpos($employeeNotes, "Account code: ") + strlen("Account code: ");
                                $accountCode = substr($employeeNotes, $offset, (strpos($employeeNotes, "\n", $offset) ?: strlen($employeeNotes)) - $offset);
                            }
                            $payment .= "Account: $accountCode";
                            break;
                        case "voucher":
                            $paymentValidation = "<button id='voucherpayment$printJobID' onClick='startPrint(\"$printJobID\", \"$name\")' class='btn btn-primary'>Started Print</button>";
                            $payment .= "Voucher: $voucherCode";

                            break;
                    };

                    $currentStatus .= "<a data-toggle='tool-tip' data-placement='top' title='$paymentConfirmed'>$payment</a><br/>";
                    
                    // If print is pending customer confirmation & print was validated
                    if(!$userConfirm && $validPrintDate) {
                        $currentStatus .= "<span style='color: #e6c300;'><i class='far fa-hourglass'>&nbsp;</i>Confirmation pending<BR></span>";
                    }

                    // If print is not pending confirmation, payment is not voucher, and has not been payed yet then render the payment button
                    if($userConfirm && $paymentMethod && !$paymentConfirmed) 
						$currentStatus .= $paymentValidation;

					if($paymentConfirmed && $completePrintDate && $userConfirm) {
                        $currentStatus .= "<a data-toggle='tool-tip' data-placement='top' title='$completePrintDate' style='color: #00cc6a'><i class='fas fa-check'>&nbsp;</i>Completed</a>";
                    } else if($paymentConfirmed && $userConfirm) { //only render when payment is confirmed
                        $currentStatus .= "<button id='completePrint$printJobID' class='btn btn-primary' onclick='completePrint(\"$printJobID\", \"$userID\", \"$name\", \"$stlFileName\")'>Completed Print</button>";
						
                    }

                    
                    $status = '';
                    $status .= $dateCreated;
                    $status .= "<br/>";
                    $status .= $currentStatus;				

                    
                    $printJobsHTML .= "
                    <tr>

                    <td>
                        <a href='#' onclick='toggleEmailField(\"$printJobID\"); return false;'>$name</a><BR>
                        <div id='email$printJobID' style='display:none; margin-bottom: 10px; align-items: center'>
                            <textarea id='emailContents$printJobID' class='form-control' cols='10' rows='3' placeholder='NOTE: This area holds the entire email contents. There is no template structure for it.'></textarea>
                            <button id='emailBtn$printJobID' class='btn btn-primary' onclick='sendUserEmail(\"$printJobID\", \"$email\")'>Send Email</button>
                        </div>
                        <div style='position:relative'>
                            <a href='./uploads/prints/$dbFileName' style='width:240px; display:inline-block; word-break: break-all;'>$stlFileName</a><BR>".
                            ($material_amount != 0 ? "<a data-toggle='tool-tip' data-placement='top' title='$validPrintDate' style='color:darkgrey; margin-bottom:-20px;padding-bottom:0;'>&emsp;$material_amount grams each</a>" : "")."<BR>
                            <button data-toggle='modal' data-target='#view3dModel' data-whatever='$dbFileName' class='btn btn-outline-primary capstone-nav-btn' style='position:absolute; right: 0; top: 0px;'>View</button>
                        <div>
                    </td>
                    <td style='text-align:center'>$quantity</td>
                    <td><textarea class='form-control' cols=50 rows=3 id='employeeNotes$printJobID' onchange='updateEmployeeNotes(\"$printJobID\");'>$employeeNotes</textarea></td>
                    <td>$customerNotes</td>
                    <td>$status</td>
                    <td>$buttons</td>

                    </tr>
                
                    ";
                }

                echo"
						
                <h3>3D Print Jobs</h3>
                <p><strong>IMPORTANT:</strong> You must process the order in TouchNet before approving fees!</p>

                <p>Steps:</p>
                <ol>
                    <li>Verify that print is valid for selected printer. 
                        <ul>
                            <li>If so, click 'Send Confirmation'.</li>
                            <li>Otherwise, click their name and compose an email explaining what they need to fix.</li>
                        </ul>
                    </li>
                    <li>Wait until customer confirms the print job, which will be indicated by a '...Started Print' button under 'Status'</li>
                    <li>If the customer paid with a credit card, verify the payment is correct in TouchNet</li>
                    <li>Begin printing the part and click the 'Started Print' button</li>
                    <li>Once the print has successfully finished, click the 'Completed Print' button</li>
                    <li>Profit!</li>
                </ol>

                <p>Make sure to process any fees that are awaiting approval.  Some of them are tied to prints and need to be processed before you are able to print.</p>
                <button id='processAllFeesBtn' type='button' class='btn btn-outline-primary' style='margin-bottom: 20px; position: relative; left: 50%; transform:translate(-50%,0)' onclick='processAllFees();'>Proccess All Account Fees</button>
                <table class='table' id='table3DPrints'>
                <caption>All Submitted 3D Prints</caption>
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
                    $printJobsHTML
                </tbody>
                </table>
                <script>
                    $('#table3DPrints').DataTable({ 
					'searching': true,
					'order':[[4, 'desc']],
					'paging': true,
					'columns': [
						{ 'width': '30%' },
						{ 'width': '2em' },
						null,						
						{ 'width': '20%' },
						{ 'width': '10em' },
						null]
					});
                </script>";
            
                echo "</div>";
                echo "</div>";
            ?>

        </div>
        <script>
            function processAllFees() {
                let data = {
                    action: 'processAllFees',
                    messageID: 'wsolshjlxkvfbpnn'
                }
                
                $('#processAllFeesBtn').prop('disabled', true);

                api.post('/printers.php', data).then(res => {
                    $('#processAllFeesBtn').prop('disabled', false);
                    snackbar(res.message, 'success');
                }).catch(err => {
                    $('#processAllFeesBtn').prop('disabled', false);
                    snackbar(err.message, 'error');
                })
            }

            function toggleEmailField(printJobID) {
                let field = document.getElementById('email'+printJobID);
                
                if(field.style.display == 'none') {
                    field.style.display = 'flex';
                } else {
                    field.style.display = 'none';
                }
            }

            function sendUserEmail(printJobID, email) {
                let message = $('#emailContents'+printJobID).val();
                if(!message) return;

                let data = {
                    action: 'sendUserEmail',
                    printJobID: printJobID,
                    email: email,
                    message: message
                }

                $('#emailBtn'+printJobID).prop('disabled', true);

                api.post('/printers.php', data).then(res => {
                    $('#emailContents'+printJobID).val('');
                    $('#emailBtn'+printJobID).prop('disabled', false);
                    $('#email'+printJobID).hide();
                    snackbar(res.message, 'success');
                }).catch(err => {
                    $('#emailBtn'+printJobID).prop('disabled', false);
                    snackbar(err.message, 'error');
                })
            }
            
            function verifyCCPayment(printJobID, name) {
                if(confirm("IMPORTANT: Only click ok if " + name + " has payed for the appropriate print in TouchNet and an employee has processed the payment"))
                {
                    let data = {
                        action: 'verifyPrintPayment',
                        printJobID: printJobID,
                    }
                    api.post('/printers.php', data).then(res => {
                        snackbar(res.message, 'success');
                        setTimeout(function(){window.location.reload()}, 1000);
                    }).catch(err => {
                        snackbar(err.message, 'error');
                    });
                }
            }       
        
            // 8/3/23 -- No longer used since employees don't have access to check account codes;
            //     just making sure nothing breaks before deleting entirely
            /*function verifyAccountCode(printJobID, name) {
                let data = {
                    action: 'verifyPrintPayment',
                    printJobID: printJobID,
                }
                api.post('/printers.php', data).then(res => {
                    snackbar(res.message, 'success');
                    setTimeout(function(){window.location.reload()}, 1000);
                }).catch(err => {
                    snackbar(err.message, 'error');
                });     
            }*/
            
            function startPrint(printJobID, name) {
                let data = {
                    action: 'verifyPrintPayment',
                    printJobID: printJobID,
                }
                api.post('/printers.php', data).then(res => {
                    snackbar(res.message, 'success');
                    setTimeout(function(){window.location.reload()}, 1000);
                }).catch(err => {
                    snackbar(err.message, 'error');
                });
            }

            function updateEmployeeNotes(printJobID) {
                let inputVal = $('#employeeNotes'+printJobID).val();
                let data = {
                    action: 'updateEmployeeNotes',
                    printJobID: printJobID,
                    employeeNotes: inputVal
                }
                api.post('/printers.php', data).then(res => {
                    snackbar(res.message, 'success');
                }).catch(err => {
                    snackbar(err.message, 'error');
                });
            }

            function sendPrintConfirm(printJobID, userID, printTypeCost, quantity) {
                if ($('#grams'+printJobID).val() != '' && $('#grams'+printJobID).val() != null){
                    let material_amount = $('#grams'+printJobID).val();
                    numGrams = parseInt(material_amount);
                    
                    let totalCost = (printTypeCost * numGrams) * quantity;
                    totalCost = totalCost.toFixed(2);
                    $('#sendConfirm'+printJobID).prop('disabled', true);

                    let data = {
                        action: 'sendCustomerConfirm',
                        printJobID: printJobID,
                        userID: userID,
                        printCost: totalCost,
                        material_amount: material_amount,
                        messageID: 'rsssdfsafifwkjfd'
                    }
                    
                    api.post('/printers.php', data).then(res => {
                        snackbar(res.message, 'success');
                        setTimeout(function(){window.location.reload()}, 1000);
                    }).catch(err => {
                        snackbar(err.message, 'error');
                    });
                } else {
                    alert('Please enter a number of grams');
                }
            }

            function completePrint(printJobID, userID, name, stlFileName) {
                if(confirm('Print '+stlFileName+' has completed? Send confirmation email to '+name+'?')) {
                    $('#completePrint'+printJobID).prop('disabled', true);
                    let data = {
                        action: 'completePrintJob',
                        printJobID: printJobID,
                        userID: userID,
                        messageID: 'iutrwoejrlkdfjla'
                    }
                    api.post('/printers.php', data).then(res => {
                        snackbar(res.message, 'success');
                        setTimeout(function(){window.location.reload()}, 1000);
                    }).catch(err => {
                        snackbar(err.message, 'error');
                    });
                }
            }

            function processPrint(printJobID, userID, name, stlFileName) {
                if(confirm('Process and complete '+stlFileName+' print job created by '+name+'?')) {
                    $('#process'+printJobID).prop('disabled', true);
                    let data = {
                        action: 'processPrintJob',
                        printJobID: printJobID,
                        userID: userID
                    }
                    api.post('/printers.php', data).then(res => {
                        snackbar(res.message, 'success');
                        setTimeout(function(){window.location.reload()}, 1000);
                    }).catch(err => {
                        snackbar(err.message, 'error');
                    });
                }
            }
            
            function deletePrint(printJobID, name, stlFileName) {
                if(confirm('Delete '+stlFileName+' print job created by '+name+'?')) {
                    $('#delete'+printJobID).prop('disabled', true);
                    let data = {
                        action: 'deletePrintJob',
                        printJobID: printJobID,
                    }
                    api.post('/printers.php', data).then(res => {
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
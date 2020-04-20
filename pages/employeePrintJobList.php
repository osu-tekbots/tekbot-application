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

$isEmployee = isset($_SESSION['userID']) && !empty($_SESSION['userID']) 
	&& isset($_SESSION['userAccessLevel']) && $_SESSION['userAccessLevel'] == 'Employee';

allowIf($isEmployee, 'index.php');


$title = 'Employee Add Printer';
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
                renderEmployeeBreadcrumb('Employee', 'Print Jobs List');

                echo "<div class='admin-paper'>";

                $printJobsHTML = "";

                $buttonScripts = "";

                foreach ($printJobs as $p) {
                    $printJobID = $p->getPrintJobID();
                    $userID = $p->getUserID();
                    $user = $userDao->getUserByID($p->getUserID());
                    $name = Security::HtmlEntitiesEncode($user->getFirstName()) . ' ' . Security::HtmlEntitiesEncode($user->getLastName());
                    $printType = Security::HtmlEntitiesEncode($printerDao->getPrintTypesByID($p->getPrintTypeID())->getPrintTypeName());
                    $printer = Security::HtmlEntitiesEncode($printerDao->getPrinterByID($p->getPrinterId())->getPrinterName());
                    $dbFileName = $p->getDbFileName();
                    $stlFileName = $p->getStlFileName();
                    $dateCreated = $p->getDateCreated();
                    $validPrintDate = $p->getValidPrintCheck();
                    $userConfirm = $p->getUserConfirmCheck();
                    $completePrintDate = $p->getCompletePrintDate();

                    $customerNotes = $p->getCustomerNotes();

                    $employeeNotes = $p->getEmployeeNotes();

                    $pendingResponse = $p->getPendingCustomerResponse();
                    // $dateUpdated = $p->getDateUpdated();
                                        
                    $printValidVal = $validPrintDate ? $validPrintDate : "<button id='sendConfirm$printJobID' class='btn btn-primary'>Send Confirmation</button>";

                    // $customerConfirmVal = $validPrintDate ? ($pendingResponse ? "Waiting for confirmation" : "Confirmed ✔️ <br/> $userConfirm") : "An employee has not validated print yet";
                    $customerConfirmVal = $validPrintDate ? ($pendingResponse ? "Waiting for confirmation" : "Confirmed ✔️ <br/> $userConfirm") : "";

                    // $completedVal = $validPrintDate ?
                    //     ($userConfirm ? 
                    //     ( $completePrintDate ? "✔️ Completed" : "<button id='completePrint$printJobID' class='btn btn-primary'>Click when print is finished</button>") 
                    //     : "❌ Customer has not confirmed print yet") 
                    //     : "❌ Print has not been validated by employee";


                    $completedVal = $validPrintDate ?
                        ($userConfirm ? 
                        ( $completePrintDate ? "✔️ Completed" : "<button id='completePrint$printJobID' class='btn btn-primary'>Click when print is finished</button>") 
                        : "") 
                        : "";
                    

                    
                    $printJobsHTML .= "
                    <tr>

                    <td>$name</td>
                    <td>$printType</td>
                    <td>$printer</td>
                    <td><a href='./prints/$dbFileName'>$stlFileName</td>
                    <td><textarea class='form-control' cols=50 rows=4 id='employeeNotes$printJobID'>$employeeNotes</textarea></td>
                    <td>$customerNotes</td>
                    <td>$dateCreated</td>
                    <td>$printValidVal</td>
                    <td>$customerConfirmVal</td>
                    <td>$completedVal</td>


                    </tr>
                
                    ";

                    $buttonScripts .= 
                "<script>
                
                $('#employeeNotes$printJobID').on('keypress', function(e) {
                    if(e.which == 13) {
                        let inputVal = $('#employeeNotes$printJobID').val();
                        let printJobID = '$printJobID';
                        // alert('This is the input: ' + inputVal);
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
                });

                $('#sendConfirm$printJobID').on('click', function() {
                    if(confirm('Confirm print $stlFileName and send confirmation email to $name?')) {
                        $('#sendConfirm$printJobID').prop('disabled', true);
                        let printJobID = '$printJobID';
                        let userID = '$userID';
                        let data = {
                            action: 'sendCustomerConfirm',
                            printJobID: printJobID,
                            userID: userID
                        }
                        api.post('/printers.php', data).then(res => {
                            snackbar(res.message, 'success');
                            setTimeout(function(){window.location.reload()}, 1000);
                        }).catch(err => {
                            snackbar(err.message, 'error');
                    });
                        $('#sendConfirm$printJobID').prop('disabled', true);
                    }
                });

                $('#completePrint$printJobID').on('click', function() {
                    if(confirm('Print $stlFileName has completed and send confirmation email to $name?')) {
                        $('#completePrint$printJobID').prop('disabled', true);
                        let printJobID = '$printJobID';
                        let userID = '$userID';
                        let data = {
                            action: 'completePrintJob',
                            printJobID: printJobID,
                            userID: userID
                        }
                        api.post('/printers.php', data).then(res => {
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
						
                <h3>3D Print Jobs</h3>
                <p><strong>IMPORTANT</strong>: You must process the order in touchnet before approving fees!</p>

                <p>Time stamps in 'Is Print Valid', 'Customer Confirmation' and 'Print Completed' colums indicate the completion of that field</p>
                <p>Steps:</p>
                <ol>
                    <li>Verify that print is valid for selected printer. If so, click 'Send Confirmation'</li>
                    <li>Wait until customer confirms the print job, which will be indicated as a time stamp and 'Confirmed'</li>
                    <li>If the print is confirmed, perform the print job</li>
                    <li>Once finished, click 'Print finished' button</li>
                </ol>

                <p>Make sure to process any fees that are awaiting approval.  Some of them are tied to prints or cuts and need to be processed before you are able to cut/print.</p>
                <table class='table' id='checkoutFees'>
                <caption>Fees Relating to Equipment Checkout</caption>
                <thead>
                    <tr>
                        <th>Customer</th>
                        <th>Print Type</th>
                        <th>Printer</th>
                        <th>File</th>
                        <th>Employee Notes</th>
                        <th>Customer Notes</th>
                        <th>Date Created</th>
                        <th>Is Print Valid</th>
                        <th>Customer Confirmation</th>
                        <th>Print Completed</th>
                    </tr>
                </thead>
                <tbody>
                    $printJobsHTML
                </tbody>
                </table>
                <script>
                    $('#checkoutFees').DataTable({'scrollX':true, 'order':[[6, 'desc']]});
                </script>
                $buttonScripts
                "
                ;
            
                    echo "</div>";
                echo "</div>";
            ?>

        </div>
    </div>

<?php 
include_once PUBLIC_FILES . '/modules/footer.php' ; 
?>
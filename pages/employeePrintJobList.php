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
                    $user = $userDao->getUserByID($p->getUserID());
                    $name = Security::HtmlEntitiesEncode($user->getFirstName()) . ' ' . Security::HtmlEntitiesEncode($user->getLastName());
                    $printType = Security::HtmlEntitiesEncode($printerDao->getPrintTypesByID($p->getPrintTypeID())->getPrintTypeName());
                    $printer = Security::HtmlEntitiesEncode($printerDao->getPrinterByID($p->getPrinterId())->getPrinterName());
                    $dbFileName = $p->getDbFileName();
                    $stlFileName = $p->getStlFileName();
                    $dateCreated = $p->getDateCreated();
                    $validPrintData = $p->getValidPrintCheck();
                    $userConfirm = $p->getUserConfirmCheck();
                    $completePrintData = $p->getCompletePrintDate();
                    $employeeNotes = $p->getEmployeeNotes();
                    $pendingResponse = $p->getPendingCustomerResponse();
                    $dateUpdated = $p->getDateUpdated();
                                        
                    $printValidVal = $validPrintData ? $validPrintData : "<button id='sendConfirm$printJobID' class='btn btn-primary'>Send Confirmation</button>";

                    // If print is validified and pending customer response is 0, then the print has been confirmed by the customer
                        // Insert completePrintData
                    $customerConfirmVal = $validPrintData ? ($pendingResponse ? "Waiting for confirmation" : "Confirmed (INSERT DATE HERE)") : "An employee has not validified print yet";

                    $completedVal = $completePrintData ? '<button class="btn btn-primary">Click when print is finished</button>' : "Customer has not confirmed print yet";
                    

                    
                    $printJobsHTML .= "
                    <tr>

                    <td>$name</td>
                    <td>$printType</td>
                    <td>$printer</td>
                    <td><a href='./prints/$dbFileName'>$stlFileName</td>
                    <td>$dateCreated</td>
                    <td>$printValidVal</td>
                    <td>$customerConfirmVal</td>
                    <td>$completedVal</td>
                    <td>$employeeNotes</td>
                    <td>$pendingResponse</td>
                    <td>$dateUpdated</td>


                    </tr>
                
                    ";

                    $buttonScripts .= 
                "<script>
                $('#sendConfirm$printJobID').on('click', function() {
                    if(confirm('Confirm print $stlFileName and send confirmation email to $name?')) {
                        $('#sendConfirm$printJobID').prop('disabled', true);
                        let printJobID = '$printJobID';
                        let data = {
                            action: 'sendCustomerConfirm',
                            printJobID: printJobID
                        }
                        api.post('/printers.php', data).then(res => {
                            snackbar(res.message, 'success');
                            // window.location.reload();
                        }).catch(err => {
                            snackbar(err.message, 'error');
                    });
                        $('#sendConfirm$printJobID').prop('disabled', true);
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
                        <th>Date Created</th>
                        <th>Is Print Valid</th>
                        <th>Customer Confirmation</th>
                        <th>Print Completed</th>
                        <th>Employee Notes</th>
                        <th>Pending Customer Response</th>
                        <th>Date Updated</th>
                    </tr>
                </thead>
                <tbody>
                    $printJobsHTML
                </tbody>
                </table>
                <script>
                    $('#checkoutFees').DataTable({'scrollX':true});
                </script>
                $buttonScripts
                "
                ;
            
                    echo "</div>";
                echo "</div>";
            ?>

        </div>
    </div>

<!-- <script>
    $('#sendConfirm$printJobID').on('click', function() {
        if(confirm('Confirm print $stlFileName and send confirmation email to $name?')) {
            let printJobID = '$printJobID';
            let data = {
                action: 'sendCustomerConfirm',
                printJobID: printJobID
            }
            api.post('/printers.php', data).then(res => {
                snackbar(res.message, 'success');
                window.location.reload();
		    }).catch(err => {
			    snackbar(err.message, 'error');
		});
        }
    });
</script> -->

<?php 
include_once PUBLIC_FILES . '/modules/footer.php' ; 
?>
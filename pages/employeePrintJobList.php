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

                foreach ($printJobs as $p) {
                    $printJobID = $p->getPrintJobID();
                    $user = $userDao->getUserByID($p->getUserID());
                    $name = Security::HtmlEntitiesEncode($user->getFirstName()) . ' ' . Security::HtmlEntitiesEncode($user->getLastName());
                    $printType = Security::HtmlEntitiesEncode($printerDao->getPrintTypesByID($p->getPrintTypeID())->getPrintTypeName());
                    $printer = Security::HtmlEntitiesEncode($printerDao->getPrinterByID($p->getPrinterId())->getPrinterName());
                    // $dbFileName = $p->getDbFileName();
                    $stlFileName = $p->getStlFileName();
                    $dateCreated = $p->getDateCreated();
                    $validPrintData = $p->getValidPrintCheck();
                    $userConfirm = $p->getUserConfirmCheck();
                    $completeData = $p->getCompletePrintDate();
                    $employeeNotes = $p->getEmployeeNotes();
                    $pendingResponse = $p->getPendingCustomerResponse();
                    $dateUpdated = $p->getDateUpdated();
                                        


                    $printJobsHTML .= "
                    <tr>

                    <td>$name</td>
                    <td>$printType</td>
                    <td>$printer</td>
                    <td>$stlFileName</td>
                    <td>$dateCreated</td>
                    <td>$validPrintData</td>
                    <td>$userConfirm</td>
                    <td>$completeData</td>
                    <td>$employeeNotes</td>
                    <td>$pendingResponse</td>
                    <td>$dateUpdated</td>


                    </tr>
                
                    ";
                }

                echo"
						
                <h3>Fees</h3>
                <p><strong>IMPORTANT</strong>: You must process the order in touchnet before approving fees!</p>
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
                </script>";

                

                    echo '<table id="printJobsTable">'; 

                        foreach ($printJobs as $p) {
                            $printJobID = $p->getPrintJobID();
                            $user = $userDao->getUserByID($p->getUserID());
                            $name = Security::HtmlEntitiesEncode($user->getFirstName()) . ' ' . Security::HtmlEntitiesEncode($user->getLastName());
                            $printType = Security::HtmlEntitiesEncode($printerDao->getPrintTypesByID($p->getPrintTypeID())->getPrintTypeName());

                            echo '<tr>';
                            echo '<td>' . '<input id="printerName' . $printJobID . '" value="' . $name . '"></input>'. '</td>';
                            echo '<td>' . '<input id="printerDescription' . $printJobID . '" value="' . $printType . '"></input>'. '</td>';
                            echo '<td>' . '<input id="printerLocation' . $printJobID . '" value="' . $p->getPrinterID() . '"></input>'. '</td>';
                            echo '</tr>'; 
                        }
                    echo "</div>";
                echo "</div>";
            ?>

        </div>
    </div>


<?php 
include_once PUBLIC_FILES . '/modules/footer.php' ; 
?>
<?php
include_once '../bootstrap.php';

use DataAccess\UsersDao;
use DataAccess\EquipmentFeeDao;
use DataAccess\EquipmentDao;
use DataAccess\EquipmentCheckoutDao;
use DataAccess\EquipmentReservationDao;
use DataAccess\KitEnrollmentDao;
use Model\Printer;
use Model\PrintFee;
use Model\PrintJob;
use Model\PrintType;
use DataAccess\PrinterDao;
use DataAccess\LaserDao;
use DataAccess\TicketDao;
use Util\Security;

session_start();

include_once PUBLIC_FILES . '/lib/shared/authorize.php';

$isEmployee = isset($_SESSION['userID']) && !empty($_SESSION['userID']) 
	&& isset($_SESSION['userAccessLevel']) && $_SESSION['userAccessLevel'] == 'Employee';

allowIf($isEmployee);

$checkoutFeeDao = new EquipmentFeeDao($dbConn, $logger);
$equipmentDao = new EquipmentDao($dbConn, $logger);
$checkoutDao = new EquipmentCheckoutDao($dbConn, $logger);
$reservationDao = new EquipmentReservationDao($dbConn, $logger);
$kitcheckoutDao = new KitEnrollmentDao($dbConn, $logger);
$printerJobsDao = new PrinterDao($dbConn, $logger);
$laserJobsDao = new LaserDao($dbConn, $logger);
$ticketDao = new TicketDao($dbConn, $logger);
//added new ticket DAO


$remainingKitCount = $kitcheckoutDao->getRemainingKitsCountForAdmin();
$equipmentReservationCount = $reservationDao->getReservationCountForAdmin();
$equipmentFeeCount =  $checkoutFeeDao->getPendingAdminFeesCount();
$printerJobs = $printerJobsDao->getPrintJobsRequiringAction();
$laserJobs = $laserJobsDao->getLaserJobsRequiringAction();
$tickets = $ticketDao->getTicketsByStatus(0);
//added getOpenTicket @param 0 = unresolved status

$dashboardText = "";

if ($equipmentReservationCount != 0)
	$dashboardText .= "There are $equipmentReservationCount active equipment reservations.  Students will be coming in soon to pick up the item. <a href='./pages/employeeEquipment.php'>Reservations</a><BR>";

if ($equipmentFeeCount != 0)
	$dashboardText .= "There are $equipmentFeeCount pending fees! Click <a href='./pages/adminFees.php'>here</a> to verify them.<BR>";

if (count($printerJobs) > 0)
	$dashboardText .= "There are 3D printing jobs that require employee actions. <a href='./pages/employeePrintJobList.php'>3D Print Jobs</a><BR>";

if (count($laserJobs) > 0)
	$dashboardText .= "There are Laser cutting jobs that require employee actions. <a href='./pages/employeeLaserJobList.php'>Laser Cutting Jobs</a><BR>";

if (count($tickets) > 0 )
	$dashboardText .= "There are Tickets that require employee actions. <a href='./pages/employeeTicketList.php'> Tickets</a><BR>";

if ($dashboardText == "")
	$dashboardText = "Nothing curently on the ToDo list";

$title = 'Employee Interface';
$css = array(
	'assets/css/sb-admin.css',
	'assets/css/jquery.dataTables.min.css'
);
$js = array(
    'assets/js/jquery.dataTables.min.js'
);

include_once PUBLIC_FILES . '/modules/header.php';
include_once PUBLIC_FILES . '/modules/employee.php';

?>
<br/>


<div id="page-top">

	<div id="wrapper">

		<?php
			renderEmployeeSidebar();
		?>

		<div id="content-wrapper">

			<div class="container-fluid">
			<div class='row' style='margin-left:2em;margin-right:2em;'><div class='col'>
				<h2>ToDo List</h2>
				<?php 
					echo $dashboardText;
				?>
			</div></div>
			<BR><BR>
			<!-- <div class='row' style='margin-left:2em;margin-right:2em;'><div class='col-6'><h2>Special Links</h2>
				<a href='https://docs.google.com/spreadsheets/d/1GnwYpOxxhOTz1xppm4-5vOdpuhsF5Rh4GB75oPCFSP4/edit#gid=436106946' target='_blank'>ECE272 Spring 2021 Kits to be shipped</a><BR>
				For any of the kits above, step 1 is to verify the student is enrolled by checking their ID number. If they are enrolled, be sure to mark them as handed out on the kit handout form and highlight the row in the spreadsheet when completed. Each package needs to be labeled with recipient address. Printed is preferred, but neatly hand written is fine.<BR>
				<BR><a href='https://docs.google.com/document/d/1iE-7fJOXA23DS68VmAgGwSxUjpfvsF8-KHcBa3PDg44/edit' target='_blank'>Shipping Contents Document</a><BR>This document needs to be updated with the correct contents and shipping information for each item to be shipped if it is going international. It needs to then be printed out and taped (blue tape) to the package to be sent. Print a second copy and file it in TekBots.<BR>
			</div></div> -->
			
			</div>
		</div>
	</div>
</div>


<script>



</script>

<?php 
include_once PUBLIC_FILES . '/modules/footer.php' ; 
?>


<?php
include_once '../bootstrap.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// use DataAccess\UsersDao;
use DataAccess\EquipmentFeeDao;
// use DataAccess\EquipmentDao;
// use DataAccess\EquipmentCheckoutDao; // Not in use? Noticed 8/15/23
use DataAccess\EquipmentReservationDao;
use DataAccess\KitEnrollmentDao;
use DataAccess\PrinterDao;
use DataAccess\LaserDao;
use DataAccess\TicketDao;
use DataAccess\ConfigurationDao;
use Util\Security;

if(!session_id()) session_start();

include_once PUBLIC_FILES . '/lib/shared/authorize.php';

allowIf(verifyPermissions('employee'));

$checkoutFeeDao = new EquipmentFeeDao($dbConn, $logger);
// $equipmentDao = new EquipmentDao($dbConn, $logger);
// $checkoutDao = new EquipmentCheckoutDao($dbConn, $logger);
$reservationDao = new EquipmentReservationDao($dbConn, $logger);
$kitcheckoutDao = new KitEnrollmentDao($dbConn, $logger);
$printerJobsDao = new PrinterDao($dbConn, $logger);
$laserJobsDao = new LaserDao($dbConn, $logger);
$ticketDao = new TicketDao($dbConn, $logger);
$configurationDao = new ConfigurationDao($dbConn, $logger);
//added new ticket DAO

/**
 * Uses the ConfigurationDao to check when the last cron emails were sent, & if it's time to send more
 * 
 * @param Model\Configuration $configuration  The config object from the DB
 * 
 * @return bool If emails still need to be sent today
 */
function checkDaysSinceCronEmails($configuration, $configManager) {
	$today = new DateTime("today"); // Creates DateTime with date set to midnight
	$lastSent = new DateTime($configuration->getLastCronEmailTime() ?? '0-0-0 0:0:0');
	$lastSent->setTime(0, 0, 0); // Set time part to midnight for accurate comparison

	$daysSinceLastSent = (int) $lastSent->diff($today)->format("%R%a");

	return $daysSinceLastSent < (int)$configManager->get('email.cron_frequency');
}

/**
 * Sends automatic reminder emails if they haven't been sent yet today
 * 
 * @return int|bool How many reminder emails were sent or false if emails were already sent today
 */
function sendCronEmailsIfNeeded($configurationDao, $configManager, $dbConn, $logger) {
	$configuration = $configurationDao->getConfiguration();

	// Don't do anything if emails were already sent today
	if(checkDaysSinceCronEmails($configuration, $configManager)) {
		return false;
	}

	// Can I just include equipmentCronjob here?
	include 'equipmentCronjob.php';

	// Update last email sent time
	$configuration->setLastCronEmailTime(new DateTime());
	$configurationDao->updateConfiguration($configuration);
	
	return $emailsSent;
}

$remainingKitCount = $kitcheckoutDao->getRemainingKitsCountForAdmin();
$equipmentReservationCount = $reservationDao->getReservationCountForAdmin();
$equipmentFeeCount =  $checkoutFeeDao->getPendingAdminFeesCount();
$printerJobs = $printerJobsDao->getPrintJobsRequiringAction();
$laserJobs = $laserJobsDao->getLaserJobsRequiringAction();
$tickets = $ticketDao->getTicketsByStatus(0);
$cronEmails = sendCronEmailsIfNeeded($configurationDao, $configManager, $dbConn, $logger);
//added getOpenTicket @param 0 = unresolved status

$dashboardText = "";

if ($equipmentReservationCount != 0)
	$dashboardText .= "<li>There are $equipmentReservationCount <a href='./pages/employeeEquipment.php'>active equipment reservations</a>.  Students will be coming in soon to pick up the item.</li>";

if ($equipmentFeeCount != 0)
	$dashboardText .= "<li>There are $equipmentFeeCount <a href='./pages/adminFees.php'>pending fees</a>!</li>";

if (count($printerJobs) > 1)
	$dashboardText .= "<li>There are ".count($printerJobs)." <a href='./pages/employeePrintJobList.php'>3D printing jobs</a> that require employee actions.</li>";
else if(count($printerJobs) > 0)
	$dashboardText .= "<li>There is 1 <a href='./pages/employeePrintJobList.php'>3D printing job</a> that requires employee actions.</li>";

if (count($laserJobs) > 1)
	$dashboardText .= "<li>There are ".count($laserJobs)." <a href='./pages/employeeLaserJobList.php'>laser cutting jobs</a> that require employee actions.</li>";
else if(count($laserJobs) > 0)
	$dashboardText .= "<li>There is 1 <a href='./pages/employeeLaserJobList.php'>laser cutting job</a> that requires employee actions.</li>";

if (count($tickets) > 1)
	$dashboardText .= "<li>There are ".count($tickets)." <a href='./pages/employeeTicketList.php'>tickets</a> that require employee actions.</li>";
else if (count($tickets) > 0)
	$dashboardText .= "<li>There is 1 <a href='./pages/employeeTicketList.php'>ticket</a> that requires employee actions.</li>";

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
			<div class='row' style='margin-left:2em;margin-right:2em; margin-top: 1em;'><div class='col'>
				<h2>To-Do List</h2>
				<?php 
					echo (($dashboardText != "") ? "<ul>".$dashboardText."</ul>" : "Nothing curently on the to-do list.");
				?>
			</div></div>
			<div class='row' style='margin-left:2em;margin-right:2em; margin-top: 1em;'><div class='col'>
				<h2>Automatic Updates Status</h2>
				<?php 
					echo $cronEmails > 0 ? 
						"Sent $cronEmails automatic email".($cronEmails > 1 ? "s" : "")." for today's reminders." 
						: ($cronEmails === false ? 
							"Automatic reminder emails were already sent recently." 
							: "No automatic reminder emails to send today.");
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

<?php 
include_once PUBLIC_FILES . '/modules/footer.php' ; 
?>


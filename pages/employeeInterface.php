<?php
include_once '../bootstrap.php';

use DataAccess\UsersDao;
use DataAccess\EquipmentFeeDao;
use DataAccess\EquipmentDao;
use DataAccess\EquipmentCheckoutDao;
use DataAccess\EquipmentReservationDao;
use DataAccess\KitEnrollmentDao;
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


$remainingKitCount = $kitcheckoutDao->getRemainingKitsCountForAdmin();
$equipmentReservationCount = $reservationDao->getReservationCountForAdmin();
$equipmentFeeCount =  $checkoutFeeDao->getPendingAdminFeesCount();

$dashboardText = "";

if ($equipmentReservationCount != 0){
	$dashboardText .= "<li>There are $equipmentReservationCount active equipment reservations.  Students will be coming in soon to pick up the item.</li>";
}
if ($equipmentFeeCount != 0){
	$dashboardText .= "<li style='color: red;'>There are $equipmentFeeCount pending fees! Click <a href='./pages/adminFees.php'>here</a> to verify them.</li>";
}

$title = 'Employee Interface';
$css = array(
	'assets/css/sb-admin.css'
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
			<section class="panel dashboard">
    		<h2>Dashboard </h2>
				<ul>
				<?php 
				echo $dashboardText;
				?>
				</ul>
			</section>
			
			</div>
		</div>
	</div>
</div>


<script>



</script>

<?php 
include_once PUBLIC_FILES . '/modules/footer.php' ; 
?>


<?php
include_once '../bootstrap.php';

use DataAccess\CoursePrintAllowanceDao;
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


$title = 'Employee Vouchers';
$css = array(
	'assets/css/sb-admin.css',
	'assets/css/admin.css',
	'https://cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css'
);
$js = array(
    'https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js',
    'assets/js/admin-groups.js'
);

include_once PUBLIC_FILES . '/modules/header.php';
include_once PUBLIC_FILES . '/modules/employee.php';

$coursePrintAllowanceDao = new CoursePrintAllowanceDao($dbConn, $logger);
$userDao = new UsersDao($dbConn, $logger);
$vouchers = $coursePrintAllowanceDao->getAdminVoucherCodes();
$services = $coursePrintAllowanceDao->getServices();
$voucherCodeHTML = '';
$printingCodesHTML = '';
$laserCodesHTML = '';

foreach ($vouchers as $voucher){
    $voucherID = $voucher->getVoucherID();
	$accountCode = $voucher->getLinkedAccount();
	$date_used = $voucher->getDateUsed();
	$user_id = $voucher->getUserID();
	$date_created = $voucher->getDateCreated();
	$date_expired= $voucher->getDateExpired();
	$service_id = $voucher->getServiceID();

	$onid = "";
	$user = $userDao->getUserByID($user_id);
	if($user) $onid = $user->getOnid();


	// TODO: Refactor to pull from tekbots_services table rather than hard comparison
	if($service_id == 5) {
		$laserCodesHTML .= "
		<tr id='$voucherID'>
		
			<td>$voucherID</td>
			<td>$accountCode</td>
			<td>$date_created</td>
			<td>$date_expired</td>
			<td>$date_used</td>
			<td>$onid</td>
	
		</tr>
		
		";
	} 
}


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
                    renderEmployeeBreadcrumb('Employee', 'Vouchers');

					echo "
						<div class='admin-paper'>
						
							<button id='generateAdditionalVouchers' class='btn btn-primary'>Generate additional voucher codes</button>
							<div id='confirmAdditionalVouchers' style='display:none'>
								<div>
									<h4 style='display: inline-block'>How Many?</h4>&nbsp;&nbsp;
									<select id='voucherAmount'>
									";



									for ($i = 1; $i < 26; $i++){
										echo "
											<option value='$i'>$i</option>
										";
									}
						echo "
									</select>
									&nbsp;&nbsp;
								</div>
								<div style='margin-top: 10px'>
									<h4 style='display: inline-block'>Linked account code:</h4>&nbsp;&nbsp;
									<div style='display: inline-block'>
										<input required type='text' class='form-control' max='' id='accountCode' placeholder='XXXXX-XXXX'/>
									</div>
								</div>
								<div>
									<h4 style='display: inline-block; float:left; margin-top:15px'>When should they expire?</h4>&nbsp;&nbsp;
									<div style='display: inline-block; margin-top: 10px'>
										<input required type='date' class='form-control' max='' id='dateExpired' placeholder='Date Vouchers Ends'/>
									</div>
								</div>
								<button id='confirmCreate' class='btn btn-primary'>Confirm</button>
								&nbsp;&nbsp;
								<button id='cancelCreate' class='btn btn-danger'>Cancel</button>
							</div>
							
							<div id='generatedVoucherCodes' style='display:none'><br><br><textarea id='generatedVoucherCodesText' readonly></textarea></div>
						</div>
       
					";
					
					

			

				echo "<div class='admin-paper' style='overflow: scroll'>
					<h3>Laser Cut Vouchers!</h3>
					<table class='table' id='laserVoucherTable' style='text-align: center'>
					<caption>Vouchers that can be used for a free laser cut</caption>
					<thead>
						<tr>
							<th>Voucher Code</th>
							<th>Linked Account</th>
							<th>Date Created</th>
							<th>Date Expire(d)</th>
							<th>Date Used</th>
							<th>User ONID</th>
						</tr>
					</thead>
					<tbody>
						$laserCodesHTML
					</tbody>
					</table>
					<button id='deleteAll' class='btn btn-danger'>Delete All Expired/Used Vouchers</button>

					<script>
						$('#laserVoucherTable').DataTable(
							{
								aaSorting: [[2, 'desc']]
							}
						);
					</script>
				</div>";

	
				?>


			</div>
		</div>
	</div>
</div>

<script>

// Hiding and showing functionality for prompting to generate new codes

$("#deleteAll").click(function() {
	if(confirm("Clicking OK will delete all exisiting Vouchers that have expired and/or have been used"))
	{
		let body = {
			action: 'clearCutVouchers'
		}
		api.post('/printcutgroups.php', body).then(res => {
			snackbar(res.message, 'success');
			setTimeout(function(){window.location.reload()}, 1000);
		}).catch(err => {
			snackbar(err.message, 'error');
		});
	}
});

$("#generateAdditionalVouchers").click(function() {
	$("#confirmAdditionalVouchers").css("display", "block");
	$(this).css("display", "none");
	$("#generatedVoucherCodes").css("display", "none");
});

$("#cancelCreate").click(function() {
	$("#generateAdditionalVouchers").css("display", "block");
	$("#confirmAdditionalVouchers").css("display", "none");
});


function addNewVouchers() {
	let num = $("#voucherAmount").val();
	let accountCode = $("#accountCode").val();
	let dateExpired = $("#dateExpired").val();
	// let serviceID = $("#services").val();
    
	// TODO: Change from hard set value to tekbot_services table value
    let serviceID = 5;
	
	if(accountCode == "" || accountCode == null){
		alert("Must connect with an account code");
		return;
	}
	
	if(dateExpired == "" || dateExpired == null){
		alert("Must choose an expiration date for voucher codes");
		return;
	}

	let body = {
        action: 'addVoucherCodes',
        num: num,
		accountCode: accountCode,
		date_expired: dateExpired,
		serviceID: serviceID
    };

	api.post('/printcutgroups.php', body)
	.then(res => {
		$("#generatedVoucherCodesText").html(res.message);
		$('#generatedVoucherCodesText').attr('rows', num);
		$("#generatedVoucherCodes").show();
		snackbar('Successfully generated vouchers', 'success');
		$("#generateAdditionalVouchers").css("display", "block");
		$("#confirmAdditionalVouchers").css("display", "none");
		
    }).catch(err => {
        snackbar(err.message, 'error');
	});
	
}

$("#confirmCreate").click(function() {
	addNewVouchers();
});


</script>

<?php 
include_once PUBLIC_FILES . '/modules/footer.php' ; 
?>

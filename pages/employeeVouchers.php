<?php
include_once '../bootstrap.php';

use DataAccess\CoursePrintAllowanceDao;
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
$vouchers = $coursePrintAllowanceDao->getAdminVoucherCodes();
$voucherCodeHTML = '';

foreach ($vouchers as $voucher){
    $voucherID = $voucher->getVoucherID();
    $date_used = $voucher->getDateUsed();
    $user_id = $voucher->getUserID();
    $date_created = $voucher->getDateCreated();


	$voucherCodeHTML .= "
	<tr id='$voucherID'>
	
		<td>$voucherID</td>
		<td>$date_used</td>
		<td>$user_id</td>
		<td>$date_created</td>

	</tr>
	
	";
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
								<br/>
								<h4 style='display: inline-block'>When should they expire?</h4>&nbsp;&nbsp;
								<div class='col-2' style='display: inline-block'>
									<input required type='date' class='form-control' max='' id='dateExpired' placeholder='Date Vouchers Ends'/>
								</div>&nbsp;&nbsp;<br/>
								<h4 style='display: inline-block'>For what service?</h4>&nbsp;&nbsp;
								<select id='services'>
								";
								for ($i = 1; $i < 26; $i++){
									echo "
										<option value='$i'>$i</option>
									";
											}
								echo "
								</select><br/>

								<button id='confirmCreate' class='btn btn-primary'>Confirm</button>
								&nbsp;&nbsp;
								<button id='cancelCreate' class='btn btn-danger'>Cancel</button>
							</div>
							
							<div id='generatedVoucherCodes' style='display:none'><br><br><textarea id='generatedVoucherCodesText' readonly></textarea></div>
						</div>
       
                        <div class='admin-paper'>
						<h3>Print/Cut Vouchers!</h3>
						<table class='table' id='voucherTable'>
						<caption>Vouchers that can be used for a free cut or print</caption>
						<thead>
							<tr>
								<th>Voucher Code</th>
								<th>Date Used</th>
								<th>User ID</th>
								<th>Date Created</th>
							</tr>
						</thead>
						<tbody>
							$voucherCodeHTML
						</tbody>
						</table>
						<script>
							$('#voucherTable').DataTable(
								{
									aaSorting: [[3, 'desc']]
								}

							);
						</script>
					</div>
						
						
						
						
						

						";
					

				

	
	
				?>


			</div>
		</div>
	</div>
</div>

<script>

// Hiding and showing functionality for prompting to generate new codes

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
	num = $("#voucherAmount").val();
	dateExpired = $("#dateExpired").val();
	
	if(dateExpired == "" || dateExpired == null){
		alert("Must choose an expiration date for voucher codes");
		return;
	}

	// let body = {
    //     action: 'addVoucherCodes',
    //     num: num,

    // };

	// api.post('/printcutgroups.php', body)
	// .then(res => {
	// 	$("#generatedVoucherCodesText").html(res.message);
	// 	$('#generatedVoucherCodesText').attr('rows', num);
	// 	$("#generatedVoucherCodes").css("display", "block");
	// 	snackbar('Successfully generated vouchers', 'success');
	// 	$("#generateAdditionalVouchers").css("display", "block");
	// 	$("#confirmAdditionalVouchers").css("display", "none");
		
    // }).catch(err => {
    //     snackbar(err.message, 'error');
	// });
	
}

$("#confirmCreate").click(function() {
	addNewVouchers();
});


</script>

<?php 
include_once PUBLIC_FILES . '/modules/footer.php' ; 
?>

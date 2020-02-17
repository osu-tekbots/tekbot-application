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


$title = 'Employee Classes';
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
$courses = $coursePrintAllowanceDao->getAdminCoursePrintAllowance();
$equipmentFeeHTML = '';
$courseClassesHTML = '';
foreach ($courses as $course){
    $allowanceID = $course->getAllowanceID();
    $courseName = $course->getCourseName();
    $allowed3dPrints = $course->getNumberAllowedPrints();
    $allowedLaserCuts = $course->getNumberAllowedCuts();


	$courseClassesHTML .= "
	<tr id='$allowanceID' class='clickableRow'>
	
		<td>$allowanceID</td>
		<td>$courseName</td>
		<td>$allowed3dPrints</td>
		<td>$allowedLaserCuts</td>

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
                    renderEmployeeBreadcrumb('Employee', 'Classes');
                    
                    echo "
                    <div class='admin-paper'>    
                    <form id='formNewUser'>
                        <input type='hidden' name='action' value='createProfile' />
                        <div class='form-row user-form'>
                            <div class='col-4'>
                                <input required type='text' class='form-control' max='' name='courseName' placeholder='Course Name'/>
                            </div>
                            <div class='col-2'>
                                <input required type='number' class='form-control' max='' name='numberallowedprints' placeholder='# Free 3D Prints'/>
                            </div>
                            <div class='col-2'>
                                <input required type='number' class='form-control' max='' name='numberallowedcuts' placeholder='# Free Cuts'/>
                            </div>
                            <div class='col-2'>
                                <button type='submit' class='btn btn-primary btn-sm'>
                                    <i class='fas fa-plus'></i>&nbsp;&nbsp;New Course
                                </button>
                            </div>
                        </div>
                    </form>
                    </div>
						
                        <div class='admin-paper'>
						<h3>Classes (Print/Cut Allowance)</h3>
						<table class='table' id='checkoutFees'>
						<caption>Classes relating to Print/Cut Allowance</caption>
						<thead>
							<tr>
								<th>ID</th>
								<th>Course Name</th>
								<th>3D Prints</th>
								<th>Laser Cuts</th>
							</tr>
						</thead>
						<tbody>
							$courseClassesHTML
						</tbody>
						</table>
						<script>
							$('#checkoutFees').DataTable();
						</script>
					</div>
						
						
						
						
						

						";
					

				

	
	
				?>


			</div>
		</div>
	</div>
</div>

<script>

// When clicking on a row, it will redirect to show the groups within that course

$('.clickableRow').click(function () {
   var id = $(this).attr("id");
   var url = "pages/employeeClassGroups.php?id=" + id;
   window.location.href = url;
});


/*
function show_hide_row(row)
{
 $("#"+row).toggle();
}
*/
</script>

<?php 
include_once PUBLIC_FILES . '/modules/footer.php' ; 
?>

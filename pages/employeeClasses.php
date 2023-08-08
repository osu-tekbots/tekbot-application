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
                    echo "
                    <div class='admin-paper'>    
                        <form id='addCourseForm' class='form-row user-form'>
                            <div class='col-4'>
                                <input required type='text' class='form-control' max='' id='courseName' placeholder='Course Name'/>
                            </div>
                            <div class='col-2'>
                                <input required type='number' class='form-control' max='' id='numberallowedprints' placeholder='# Free 3D Prints'/>
                            </div>
                            <div class='col-2'>
                                <input required type='number' class='form-control' max='' id='numberallowedcuts' placeholder='# Free Cuts'/>
                            </div>
                            <div class='col-2'>
                                <button id='submitNewCourse' class='btn btn-primary'>
                                    <i class='fas fa-plus'></i>&nbsp;&nbsp;New Course
                                </button>
                            </div>
                        </form>
                   
                    </div>
						
                        <div class='admin-paper'>
						<h3>Courses (Print/Cut Allowance)</h3>
						<p>*Click on a course to see groups within the course</p>
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

/**
 * Handles the form submission for creating a new user by making a request to the API server to create a new profile.
 */
function onNewCourseSubmit() {
    let courseName = $("#courseName").val();
    let numberallowedprints = $("#numberallowedprints").val();
	let numberallowedcuts = $("#numberallowedcuts").val();
	
	if (courseName === '') {
		snackbar('Course name cannot be empty!', 'error');
		return false;
	} 
	if (numberallowedprints === '') {
		snackbar('Number of allowed prints cannot be empty!', 'error');
		return false;
	} 
	if (numberallowedcuts === ''){
		snackbar('Number of allowed cuts cannot be empty!', 'error');
		return false;
	}

    let data = {
        action: 'addCourse',
        courseName: courseName,
        numberallowedprints: numberallowedprints,
        numberallowedcuts: numberallowedcuts
    }
	
    api.post('/printcutgroups.php', data)
        .then(res => {
            snackbar(res.message, 'success');
            document.getElementById("addCourseForm").reset();
        })
        .catch(err => {
            snackbar(err.message, 'error');
        });
	

    return false;
}

$( "#submitNewCourse" ).click(function() {
    onNewCourseSubmit();
});


</script>

<?php 
include_once PUBLIC_FILES . '/modules/footer.php' ; 
?>

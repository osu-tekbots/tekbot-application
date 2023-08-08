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

$cID = $_GET['id'];
$title = 'Employee Class Groups';
$css = array(
	'assets/css/sb-admin.css',
	'assets/css/admin.css',
	'https://cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css',
	'https://cdn.datatables.net/rowgroup/1.1.1/css/rowGroup.dataTables.min.css'
);
$js = array(
	'https://code.jquery.com/jquery-3.3.1.js',
	'https://cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js',
	'https://cdn.datatables.net/responsive/2.2.3/js/dataTables.responsive.min.js',
	'https://cdn.datatables.net/rowgroup/1.1.1/js/dataTables.rowGroup.min.js'
);

include_once PUBLIC_FILES . '/modules/header.php';
include_once PUBLIC_FILES . '/modules/employee.php';


$coursePrintAllowanceDao = new CoursePrintAllowanceDao($dbConn, $logger);
$students = $coursePrintAllowanceDao->getStudentsFromAllowanceIDView($cID);

$equipmentFeeHTML = '';
$courseGroupsHTML = '';
$studentsHTML = '';
$courseName = '';
foreach ($students as $student){

	$courseGroup = $student->getCourseGroup();
	$groupID = $courseGroup->getCourseGroupID();
	$groupName = $courseGroup->getGroupName();
	$termCode = $courseGroup->getAcademicYear();
	$groupExpiration = $courseGroup->getDateExpiration();
	$groupCreated = $courseGroup->getDateCreated();

	$studentID = $student->getCourseStudentID();
	$onid = $student->getOnid();
	$userID = $student->getUserID();

	$course = $student->getCourse();
	$courseID = $course->getAllowanceID();
	$courseName = $course->getCourseName();



	$studentsHTML .= "
	<tr id='$studentID'>
	
		<td>$termCode</td>
		<td>$groupName</td>
		<td>$onid</td>
		<td>$userID</td>
		<td>$groupExpiration</td>
		<td>$groupCreated</td>
		

	</tr>
	
	";
}









?>
<br/>
<div id="page-top">
<style>
tr.odd td:first-child,
tr.even td:first-child {
    padding-left: 4em;
}
</style>

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
					<button type='button' onclick='history.back()'>
					<i class='fas fa-arrow-circle-left'></i> Back to Courses
					</button>
					</div>
					";
					
                    echo "
					<div class='admin-paper'>    
					
                    <form id='formNewUser' class='form-row user-form'>
						<div class='col-3'>
							<input required type='text' class='form-control' max='' id='groupName' placeholder='Group Name'/>
						</div>
						<div class='col-3'>
							<input required type='number' class='form-control' max='' id='academicYear' placeholder='Academic Year (e.g. 2021)'/>
						</div>
						<div class='col-2'>
							<input required type='date' class='form-control' max='' id='dateStart' placeholder='Date Group Starts'/>
						</div>
						<div class='col-2'>
							<input required type='date' class='form-control' max='' id='dateExpired' placeholder='Date Group Ends'/>
						</div>
						<div class='col-2'>
							<button id='submitNewGroup' class='btn btn-primary '>
								<i class='fas fa-plus'></i>&nbsp;&nbsp;New Group
							</button>
						</div>
                    </form>
                    </div>
						
                        <div class='admin-paper'>
						<h3>Groups For $courseName</h3>
						<table class='table display nowrap' id='courseGroups' style='width:100%'>
						<caption>Course Groups</caption> 
						<thead>
							<tr>
								
								<th>Term Code</th>
								<th>Group Name</th>
								<th>ONID</th>
								<th>User ID</th>
								<th>Date Expired</th>
								<th>Date Created</th>
							</tr>
						</thead>
						<tbody>
							$studentsHTML
						</tbody>
						</table>
				
					</div>
						
						";




				


						echo "
						<div class='admin-paper'>    
						
						<form id='formNewUser' class='form-row user-form'>
							<div class='col-3'>
								<input required type='text' class='form-control' max='' id='groupName' placeholder='Group Name'/>
							</div>
							<div class='col-3'>
								<input required type='number' class='form-control' max='' id='academicYear' placeholder='Academic Year (e.g. 2021)'/>
							</div>
							<div class='col-2'>
								<input required type='date' class='form-control' max='' id='dateStart' placeholder='Date Group Starts'/>
							</div>
							<div class='col-2'>
								<input required type='date' class='form-control' max='' id='dateExpired' placeholder='Date Group Ends'/>
							</div>
							<div class='col-2'>
								<button id='submitNewGroup' class='btn btn-primary '>
									<i class='fas fa-plus'></i>&nbsp;&nbsp;New Group
								</button>
							</div>
						</form>
						</div>
							
							<div class='admin-paper'>
							<h3>Groups For $courseName</h3>
							<table class='table display nowrap' id='courseGroups' style='width:100%'>
							<caption>Course Groups</caption> 
							<thead>
								<tr>
									
									<th>Term Code</th>
									<th>Group Name</th>
									<th>ONID</th>
									<th>User ID</th>
									<th>Date Expired</th>
									<th>Date Created</th>
								</tr>
							</thead>
							<tbody>
								$studentsHTML
							</tbody>
							</table>
					
						</div>
							
							";
	
				?>





			</div>
		</div>
	</div>
</div>

<script>

$(document).ready(function() {
    $('#courseGroups').DataTable( {
        order: [[2, 'asc'], [1, 'asc']],
        rowGroup: {
            dataSrc: [ 0, 1 ]
			
        },
        columnDefs: [ {
            targets: [ 1, 0 ],
            visible: false
        } ]
    } );
} );


/**
 * Handles the form submission for creating a new user by making a request to the API server to create a new profile.
 */
function onNewGroupSubmit() {
	let groupName = $("#groupName").val();
	let allowanceID = <?php echo $cID ?>; 
    let academicYear = $("#academicYear").val();
	let dateExpired = $("#dateExpired").val();
	let dateCreated = $("#dateStart").val();
	
	if (groupName === '') {
		snackbar('Group name cannot be empty!', 'error');
		return false;
	} 
	if (academicYear === '') {
		snackbar('Academic Year cannot be empty!', 'error');
		return false;
	} 
	if (dateCreated === ''){
		snackbar('Date created cannot be empty!', 'error');
		return false;
	}
	if (dateExpired === ''){
		snackbar('Date for group to expire cannot be empty!', 'error');
		return false;
	}

    let data = {
        action: 'addGroup',
		groupName: groupName,
		allowanceID: allowanceID,
		academicYear: academicYear,
		dateExpired: dateExpired,
		dateCreated: dateCreated
    }
	
    api.post('/printcutgroups.php', data)
        .then(res => {
            snackbar(res.message, 'success');
            document.getElementById("formNewUser").reset();
        })
        .catch(err => {
            snackbar(err.message, 'error');
        });
	

    return false;
}

$("#submitNewGroup").click(function(e) {
	e.preventDefault();
    onNewGroupSubmit();
});


</script>

<?php 
include_once PUBLIC_FILES . '/modules/footer.php' ; 
?>

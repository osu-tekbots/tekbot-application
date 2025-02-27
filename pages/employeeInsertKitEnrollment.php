<?php
include_once '../bootstrap.php';

use DataAccess\KitEnrollmentDao;

if (PHP_SESSION_ACTIVE != session_status())
	session_start();

include_once PUBLIC_FILES . '/lib/shared/authorize.php';

allowIf(verifyPermissions('employee', $logger));


$title = 'Add Kit Enrollments';
$css = array(
	'assets/css/sb-admin.css',
	'assets/css/admin.css',
	'assets/css/kitenrollments.css',
	'https://cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css'
);
$js = array(
    'https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js'
);
include_once PUBLIC_FILES . '/modules/header.php';
include_once PUBLIC_FILES . '/modules/employee.php';
include_once PUBLIC_FILES . '/modules/renderTermData.php';
?>
<br/>
<div id="page-top">

	<div id="wrapper">

		<?php
			renderEmployeeSidebar();
		?>

		<div id="content-wrapper">

			<div class="container-fluid">

			<p>To insert a batch of kit enrollments for students, create an excel/google sheet in the following order: 
			StudentID&nbsp;&nbsp;&nbsp;&nbsp;ONID&nbsp;&nbsp;&nbsp;&nbsp;FullName (Last, First M.)&nbsp;&nbsp;&nbsp;&nbsp;CourseCode</p>
			<p>Example <a href="https://docs.google.com/spreadsheets/d/1n9qQE-jRnjK9Q-9YuTkdsOLD9n_ZvC48_Ur-bqzmNTM/edit#gid=0">here</a> - in the shared Tekbot folder under Tekbots -> Web Development -> Kit Enrollment Example</p>
			<p>Copy all the values from the spreadsheet and paste it into the textbox below.  After hitting submit, you will be prompted to select a term and make sure all the entries look correct.</p>
			Tab seperated values, Newline seperated entries
			<br>
			<div class="row">
				<textarea class="jsonEntry" id="jsonData"></textarea>
				<button class="draw meet jsonButton" id="submitData">Submit</button>
			</div>
            <br><br><br><br>
            <div id="tableContainer" class='admin-paper' style="display:none">
				<h3>Are these correct? Select Term:
				<?php 
				// This renders a select dropdown with term information located in /modules/renderTermData.php
				renderTermSelect(20);
				?>
				<button class="validJsonButton" id="validEntry">Yes, Upload to DB</button><button class="invalidJsonButton" onClick="window.location.reload();">No</button></h3>
					<table class='table' id='kitEnrollments'>
					<caption>List Check</caption>
						<thead>
							<tr>
								<th>ID Number</th>
								<th>Onid</th>
								<th>Last, First Middle</th>
								<th>Course Code</th>
							</tr>
						</thead>
						<tbody id="tableBody">
							
						</tbody>
					</table>
					<script>
					$('#kitEnrollments').DataTable(
						{
							"paging":   false,
							"ordering": false,
							"info":     false,
							"searching":false
						}
					);

					</script>
			</div>
                





            <div>

        </div>
    </div>
</div>




<script>
    $('#submitData').on('click', function() {
        let jsonData = document.getElementById("jsonData").value;
		if (jsonData.trim() === ""){
			// Input is empty
			document.getElementById("jsonData").style.borderColor = "red";
			return;
		} else {
			document.getElementById("jsonData").style.borderColor = "";
		}
		jsonData = jsonData.trim();
         let data = {
            action: 'showParseInput',
            jsonData: jsonData
         };
         api.post('/kitenrollment.php', data).then(res => {
             //snackbar(res.message, 'success');
            document.getElementById("tableBody").innerHTML = res.message;
			$('#tableContainer').attr('style', '');
         }).catch(err => {
             snackbar(err.message, 'error');
         });
    });


	$('#validEntry').on('click', function() {
        let jsonData = document.getElementById("tableBody").innerHTML;
		let term = document.getElementById("termSelect").value;
		if (term === ""){
			document.getElementById("termSelect").style.borderColor = "red";
			return;
		} else {
			document.getElementById("termSelect").style.borderColor = "";
		}
		let data = {
            action: 'uploadKitEnrollments',
            htmlData: jsonData,
			termData: term
        };
         api.post('/kitenrollment.php', data).then(res => {
             snackbar(res.message, 'success');
         }).catch(err => {
             snackbar(err.message, 'error');
         });
    });


</script>

<?php 
include_once PUBLIC_FILES . '/modules/footer.php' ; 
?>


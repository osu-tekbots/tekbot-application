<?php

include_once '../bootstrap.php';

include_once PUBLIC_FILES . '/modules/header.php';
include_once PUBLIC_FILES . '/modules/employee.php';


use Util\Security;
//Add the Daos and Models you need here
use DataAccess\EquipmentDao;
use DataAccess\PrinterDao;
use DataAccess\UsersDao;

use Model\User;


$title = '3D Print Submission';
$printerDao = new PrinterDao($dbConn, $logger);
$usersDao = new UsersDao($dbConn, $logger);

$isLoggedIn = isset($_SESSION['userID']) && $_SESSION['userID'] . ''  != '';
if ($isLoggedIn){
   $isEmployee = isset($_SESSION['userID']) && !empty($_SESSION['userID'])
   && isset($_SESSION['userAccessLevel']) && $_SESSION['userAccessLevel'] == 'Admin'|| $_SESSION['userAccessLevel'] == 'Employee';
} else {
   $isEmployee = FALSE;
}

$printers = $printerDao->getPrinters();
$printTypes = $printerDao->getPrintTypes();
$user = $usersDao->getUserByID($_SESSION['userID']);


$css = array(
	'https://cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css',
	'assets/Madeleine.js/src/css/Madeleine.css'
);
$js = array(
    'https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js',
	'assets/Madeleine.js/src/lib/stats.js',
	'assets/Madeleine.js/src/lib/detector.js',
	'assets/Madeleine.js/src/lib/three.min.js',
	'assets/Madeleine.js/src/Madeleine.js',
);



$isEmployee = isset($_SESSION['userID']) && !empty($_SESSION['userID']) 
	&& isset($_SESSION['userAccessLevel']) && $_SESSION['userAccessLevel'] == 'Employee';

?>
 
	
<script type="text/javascript">
	function Upload(action,id) {
		var html= '<B>LOADING</B>';
		$('#uploadTextDiv').html(html).css('visibility','visible');
		var file_data = $('#uploadFileInput').prop('files')[0]
		var form_data = new FormData();
		form_data.append('file', file_data);
		form_data.append('action',action);
		form_data.append('id',id);
		
		$.ajax({ 
			url: './ajax/Handler.php', 
			type: 'POST',
			dataType: 'json',	/*what we are expecting back*/
			contentType: false,
			processData: false,
			data: form_data, 
			success: function(result)
			{
				if(result["successful"] == 1)
				{
					var setPath = document.getElementById("uploadpath");
					setPath.value = result["path"];
					var html= '<B><font color="green">✓</font></B>' + '<a href="'+result["path"]+'">' + result["string"] + '</a>';
					
				}
				else if(result["successful"] == 0)
					var html= '<font color="red">❌ </font> Error: '+result["string"];
					else
						var html= result["string"];
				$('#txt'+id).html(html).css('visibility','visible');
			},
			error: function(result)
			{
				var html= '<font color="red">❌ </font> Failed: '+result["string"];
				$('#txt'+id).html(html).css('visibility','visible');
			}
		});
	}

</script>




<div class="container-fluid">



	<br/><br/><br/>
	<h1>3D Print Submission Form</h1>
	
				Using this form you can upload a 3D model to be created. Print using a Stratasys BST1220 machine. It produces plastic final models. Once a file is uploaded, we will review the model and email you with the cost to print. Once you approve the charge, we will print the model. 
			<br/><br/>
			<ul>
				<li><b>OSU Account Code:</b> $5.50/cubic inch + $5.00 set-up fee</li>
				<li><b>Credit Card:</b> $16.00/cubic inch + $10.00 set-up fee</li>
			</ul>
			<button class="btn btn-primary" data-toggle="collapse" data-target="#collapseExample">
            Printing FAQs
            </button>
            <br/><br/>
            <div class="collapse" id="collapseExample">
                <div class="card card-body">
                        <i>Q: How big can your printer print?</i> <br/>
                        A:10" deep x 10" wide x 12" tall <br/>
                        <br/>
                        <i>Q: How thick should my part's walls be?</i><br/>
                        A: This is not a simple answer, as it depends on the size of the wall and the strength needed. We recommend at least .1" thick walls when feasible.<br/>
                        <br/>
                        <i>Q: How can I keep my printing costs down?</i>
                        <br/>
                        A: One hidden cost often overlooked is the support material. To print an object with an 'overhang' the printer will insert a softer support material as it builds. This support will be from the bottom up until the overhang is reached often increasing the amount of material used by 10x. The best design methodology is to try to think of everything as a bowl where it be be built of the open side up.<br/>
                        
                </div>
				
            </div>
			<p>If you would like to pay via credit card, please submit your file with this form and enter 'Credit Card' in the account code field. We will reply with instructions on how to submit payment.</p>
			<p class="text-danger">NOTE We only accept files of the 'Stereo Lithography Type' (.stl) and the attachment file size must be smaller than 10Mb</p>
		

	



	<div class="row">  
		<div class="col-sm-6">
			Email:<br/>
			<input name="emailInput" class="form-control" type="email" value="<?php echo $user->getEmail();?>" id="emailInput"/>
			First Name: <br/>
			<input name="firstNameInput" class="form-control" value="<?php echo $user->getFirstName();?>" placeholder="Enter your first name here..." id="firstNameInput"/>
			Last Name: <br/>
			<input name="lastNameInput" class="form-control" value="<?php echo $user->getLastName();?>" placeholder="Enter your last name here..." id="lastNameInput"/>
			<br/>
			<input name="userIDInput" id="userIDInput" value="<?php echo $user->getUserID(); ?>" hidden />
			<!-- <select name="materialSelect" name="materialSelect" id="materialSelect">	
				<option value="Stencil">Stencil ($2.00)</option>
				<option value="Clear_Acrylic_3mm">Clear Acrylic - 3mm (1/8") ($5.00)</option>
				<option value="Clear_Acrylic_5mm">Clear Acrylic - 5mm (1/4") ($8.00)</option>
				<option value="Plywood_5mm">Plywood - 5mm (7/32") ($5.00)</option>
				<option value="Plywood_3mm">Plywood - 3mm (1/8") ($5.00)</option>
			</select> -->
			Which Printer would you like to print on?: <br/>
			<select name="printerSelect" id="printerSelect">
				<?php
					foreach($printers as $p) {
						$printerName = $p->getPrinterName();
						$printerId = $p->getPrinterId();
						echo "<option value=$printerId>$printerName</option>";
					}
				?>
			</select><br/>

			What Print type would you like?: <br/>
			<select name="printTypeSelect" id="printTypeSelect">
				<?php
					foreach($printTypes as $p) {
						$printTypeName = $p->getPrintTypeName();
						$printTypeId = $p->getPrintTypeId();
						echo "<option value=$printTypeId>$printTypeName</option>";
					}
				?>
			</select><br/>
			
			<b>Payment Method:</b>
			<br/>
                <input type="radio" name="accounttype" value="cc">
				Credit Card? 
				<input type="radio" name="accounttype" value="account">
				OSU Account Code:
				<input class=fi type=text size=30 name=account value="">
                <BR>*Note:<b> We can not directly bill your student account.</b> Students must use the credit card option. Do not enter your credit card info here.*
                <br/>

			<b>Notes</b>
			<BR>Any special instructions or deadlines that you have should be entered here
			<textarea name="notes" rows="4" cols="50"></textarea><br/>

		</div>
		<div class="col-sm-6">
			<div id="targetDiv"></div>
			<input type="file" id="uploadFileInput" class="form-control" name="uploadFileInput" onchange="Upload();" multiple>
			<div id="uploadTextDiv"></div>
			<input name="uploadpath" value="" id="uploadpath" type='hidden'>
			<button id="submit3DPrintBtn" class="btn btn-primary">Submit</button>
		</div>
	



	</div>

</div>






<br /><br /><br/>
<br /><br /><br/>
<script>
window.onload = function(){
    Lily.ready({
        target: 'targetDiv',  // target div id
        file: 'uploadFileInput',  // file input id
        path: 'assets/Madeleine.js/src/' // path to source directory from current html file
    });
}; 


$('#submit3DPrintBtn').on('click', function () {
	// Capture the data we need
	let data = {
		action: 'createprintjob',
		email: $('#emailInput').val(),
		firstName: $('#firstNameInput').val(),
		lastName: $('#lastNameInput').val(),
		userId: $('#userIDInput').val(),
		// material: $('#materialSelect').val(),
		fileName: $('#uploadFileInput').val(),
		printerId: $('#printerSelect').val(),
		printTypeId: $('#printTypeSelect').val(),
		// payment method
		// Course group id
		// voucher code (is a foreign key?)
		// date created? Check if dao already has
		// valid print (maybe put in api)
		// 
	}; 
	
	// Send our request to the API endpoint
	api.post('/printers.php', data).then(res => {
		snackbar(res.message, 'success');
		//window.location.replace('pages/submit3DPrint.php');
	}).catch(err => {
		snackbar(err.message, 'error');
	});
	
});


</script>



<?php
include_once PUBLIC_FILES . '/modules/footer.php';
?>


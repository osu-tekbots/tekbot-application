<?php

include_once '../bootstrap.php';


use Util\Security;
//Add the Daos and Models you need here
use DataAccess\EquipmentDao;
use DataAccess\PrinterDao;


$title = '3D Print Submission';
$printerDao = new PrinterDao($dbConn, $logger);

$isLoggedIn = isset($_SESSION['userID']) && $_SESSION['userID'] . ''  != '';
if ($isLoggedIn){
   $isEmployee = isset($_SESSION['userID']) && !empty($_SESSION['userID'])
   && isset($_SESSION['userAccessLevel']) && $_SESSION['userAccessLevel'] == 'Admin'|| $_SESSION['userAccessLevel'] == 'Employee';
} else {
   $isEmployee = FALSE;
}

$printers = $printerDao->getPrinters();

/**** FIX Me ****/
require_once('../../../includes/config.php');
$mysqli = new mysqli($server, $user, $password, $databaseName, 3307);

$printerid = 1;
$max_size = 11000000;
/****************/

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

include_once PUBLIC_FILES . '/modules/header.php';
include_once PUBLIC_FILES . '/modules/employee.php';


$isEmployee = isset($_SESSION['userID']) && !empty($_SESSION['userID']) 
	&& isset($_SESSION['userAccessLevel']) && $_SESSION['userAccessLevel'] == 'Employee';

?>
 


	
	<script type="text/javascript">
		function Upload(action,id) {
			var html= '<B>LOADING</B>';
			$('#txt'+id).html(html).css('visibility','visible');
			var file_data = $('#file'+id).prop('files')[0]
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
	
<br /><br /><br/>

<!--
TO DO: Use bootstrap div dividing in order to format page. 
-->

<div class="container-fluid">
<h1>3D Print Submission Form</h1>


<?php
		echo '<table id="printerTable">'; 
		echo '<tr>';
		echo '<td></td>';
		echo '<td><b> Printer Name </b></td>';
		echo '<td><b> Description </b></td>';
		echo '<td><b> Location </b></td>';
		echo '</tr>';
		foreach ($printers as $p) {
			echo '<tr>';
			echo '<td>' . '<button id="edit' . $p->getPrinterId() . '">Edit</button> <button id="remove' . $p->getPrinterId() . '">Remove</button>'. '</td>';
			echo '<td>' . '<input id="printerName' . $p->getPrinterId() . '" value="' . $p->getPrinterName() . '"></input>'. '</td>';
			echo '<td>' . '<input id="printerDescription' . $p->getPrinterId() . '" value="' . $p->getDescription() . '"></input>'. '</td>';
			echo '<td>' . '<input id="printerLocation' . $p->getPrinterId() . '" value="' . $p->getLocation() . '"></input>'. '</td>';
			echo '</tr>';
		
	}
	echo '<tr>';
	echo '<td><button id="addButt">Add</button></td>'; 
	echo '<td><input id="addPrinterName"></input></td>';
	echo '<td><input id="addPrinterDescription"></input></td>';
	echo '<td><input id="addPrinterLocation"></input></td>';
	echo '</tr>';

		echo '</table>';	
?>

<script type="text/javascript">
	$("#addButt").click(function(){
		if($("#addPrinterName").val() == "")
		{
			alert("Printer must have a name!");
		}
		else
		{
			//IS NOT WORKING!!
			let printName = $("#addPrinterName").val();
			let printDescription = $("#addPrinterDescription").val();
			let data = {
				action: 'createprinter',
				title: printName,
				description: printDescription
			}
			api.post('/printer.php', data).then(res => {
             snackbar(res.message, 'success');
         }).catch(err => {
             snackbar(err.message, 'error');
         });
		}

	});
</script>

<script>
	$(document).ready(function () {
		$('#printerTable').DataTable();
	});
</script>



<br/>
        <p>Welcome to the EECS 3D printing submission form. Using this form you can upload a 3D model to be created. Print using a Stratasys BST1220 machine. It produces plastic final models. Once a file is uploaded, we will review the model and email you with the cost to print. Once you approve the charge, we will print the model. Presently the models are $5.50 per cubic inch with a $5.00 set-up fee for internal use (using an OSU account code). External costs (paying by credit card) are $16.00 per cubic inch with a $10.00 setup fee. This includes 'support' material.</p>
    <button class="btn btn-primary" data-toggle="collapse" data-target="#collapseExample">
            Printing FAQs
            </button>
            <br/><br/>
        
            <div class="collapse" id="collapseExample">
                <div class="card card-body">
                        Q: How big can your printer print? <br/>
                        A:10" deep x 10" wide x 12" tall  <br/>
                        <br/>
                        Q: How thick should my part's walls be?<br/>
                        A: This is not a simple answer, as it depends on the size of the wall and the strength needed. We recommend at least .1" thick walls when feasible.<br/>
                        <br/>
                        Q: How can I keep my printing costs down?
                        <br/>
                        A: One hidden cost often overlooked is the support material. To print an object with an 'overhang' the printer will insert a softer support material as it builds. This support will be from the bottom up until the overhang is reached often increasing the amount of material used by 10x. The best design methodology is to try to think of everything as a bowl where it be be built of the open side up.<br/>
                        
                </div>
				
            </div>
			    <p>If you would like to pay via credit card, please submit your file with this form and enter 'Credit Card' in the account code field. We will reply with instructions on how to submit payment.</p>
				<p class="text-danger">NOTE We only accept files of the 'Stereo Lithography Type' (.stl) and the attachment file size must be smaller than 10Mb</p>

<form name="submit" id="submit" action="./submit3DPrint.php" method="post" ENCTYPE="multipart/form-data">

	<input name="uploadpath" value="" id="uploadpath" type='hidden'>
	
<input type="hidden" id="printerid" value="<?php echo $printerid; ?>" name="printerid">
<div class='row form-group' id='payment_div'><div class='col-sm-4 col-sm-offset-2'><b>Payment Method:</b>
<?php echo (isset($_SESSION['phpCAS']['user']) ? '' : "<strong><u>Login above to use student or grant accounts.</u></strong>"); ?></div>
<div class='col-sm-4'><select class='fi form-control' id="payment_method" name="payment_method" onchange="paymenttype()">
<option>- Select Payment Method</option>
<?php
$query = "SELECT * FROM `paymenttypes` WHERE printerid = $printerid ORDER BY name ASC";
$result = $mysqli->query($query);
while ($row = $result->fetch_assoc()){
	/*
	if ($row['loginneeded'] == 0 OR ($row['loginneeded'] == 1 AND isset($_SESSION['userID']))){
		echo "<option value = '" . $row['id'] . "'" . ">" . $row['name'] . "</option>";
	}
	*/
	}

?>
</select></div></div>


<script>
		function selectmaterial(){
			printerid = $('#printerid').val();
			materialid = $('#materialid').val();
			$.ajax({
			type: 'POST',
			url: '../ajax/materialoptions.php',
			dataType: 'html',
			data: {	printerid: printerid,
					materialid: materialid},
			success: function(result)
				{
					$('#jobproperties').fadeOut('fast', function() {
						$('#jobproperties').html(result);
					});
					$('#jobproperties').fadeIn('fast');
				}
			});
		}
		</script>

<script>
	function paymenttype(){
		payment_method = $('#payment_method').val();
		$.ajax({
		type: 'GET',
		url: '../ajax/userinfo.php',
		dataType: 'html',
		data: {	payment_method: payment_method},
		success: function(result)
			{
				$('#userinfo_div').fadeOut('fast', function() {
				$('#userinfo_div').html(result);
			});
			$('#userinfo_div').fadeIn('fast');
			selectmaterial();
			}
		});
	}
</script>

<div class='form-group' id='userinfo_div'></div>

<div class='form-group' id='jobproperties'></div>
</form>

</div>
<script>
$('#addjobbutton').click(function(){
    event.preventDefault();
	payment_method = $('#payment_method').val();
	firstname = $('#firstname').val();
	lastname = $('#lastname').val();
	printerid = $('#printerid').val();
	email = $('#email').val();
	notes = $('#notes').val();
	materialid = $('#materialid').val();
	$.ajax({
	type: 'GET',
	url: '../ajax/addjob.php',
	dataType: 'html',
	data: {payment_method: payment_method,
			firstname: firstname,
			lastname: lastname,
			printerid: printerid,
			email: email,
			notes: notes,
			materialid: materialid},
	success: function(result)
		{
			$('#main_div').fadeOut('fast', function() {
			$('#main_div').html(result);
		});
		$('#main_div').fadeIn('fast');
		}
	});
});
</script>

<br /><br /><br/>
<br /><br /><br/>
<script>
window.onload = function(){
    Lily.ready({
        target: 'target',  // target div id
        file: 'file1',  // file input id
        path: 'assets/Madeleine.js/src/' // path to source directory from current html file
    });
}; 
</script>

<?php
include_once PUBLIC_FILES . '/modules/footer.php';
?>


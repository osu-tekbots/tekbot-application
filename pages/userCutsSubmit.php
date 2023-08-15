<?php
include_once '../bootstrap.php';

use DataAccess\UsersDao;
use DataAccess\LaserDao;


//Add the Daos and Models you need here
use DataAccess\EquipmentDao;


session_start();

$usersDao = new UsersDao($dbConn, $logger);
$user = $usersDao->getUserByID($_SESSION['userID']);

/*
 * TO DO: Reference the submit3DPrint and use npm in order to install three-dxf. 
 * Reference the sample folder within the three-dxf library to see how to incorporate
 * the file upload viewer functionality. Make sure to set permissions correctly on the 
 * terminal for all the files. 
 * File can be found here: https://github.com/gdsestimating/three-dxf
 */

$title = 'Laser Cut Submission';
$laserDao = new LaserDao($dbConn, $logger);

$laserCutters = $laserDao->getLaserCutters();
$laserMaterials = $laserDao->getLaserCutMaterials();

$laserMaterialDescriptionGetter = function ($material) {
	return $material->getDescription() . " ($" . $material->getCostPerSheet() . ")";
};

$laserMaterialIdGetter = function ($material) {
	return $material->getLaserMaterialId();
};

$laserNameGetter = function ($laser) {
	return $laser->getLaserName();
};
$laserIdGetter = function ($laser) {
	return $laser->getLaserId();
};

$css = array(
	'https://cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css'
);
$js = array(
	'https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js',

);

include_once PUBLIC_FILES . '/modules/header.php';
include_once PUBLIC_FILES . '/modules/employee.php';
include_once PUBLIC_FILES . '/modules/userHtml.php';
include_once PUBLIC_FILES . '/modules/submissionPage.php';
?>

<script type="text/javascript">
	var isValidFile = false;
	var dbFileName = "";

	function Upload(action, id) {

		var html = '<B>LOADING...</B>';
		// $('#uploadTextDiv').html(html).css('visibility','visible');
		$('#fileFeedback').html(html);
		var file_data = $('#uploadFileInput').prop('files')[0]
		var form_data = new FormData();
		form_data.append('file', file_data);
		form_data.append('action', 'laserUpload');

		$.ajax({
			url: './ajax/Handler.php',
			type: 'POST',
			dataType: 'json',
			/*what we are expecting back*/
			contentType: false,
			processData: false,
			data: form_data,
			success: function(result) {
				if (result["successful"] == 1) {
					var html = '✔️ File is valid: ' + result["string"];
					isValidFile = true;
					dbFileName = result["path"];
					$('#fileFeedback').text(html);
				} else if (result["successful"] == 0) {
					isValidFile = false;
					var html = '❌' + result["string"];
					$('#fileFeedback').text(html);
				}

			},
			error: function(result) {
				isValidFile = false;
				var html = '<font color="red">❌ </font> Failed: ' + result["string"];
				$('#fileFeedback').text(html);
			}
		});
	}
</script>

<br /><br /><br />

<div class="container-fluid">
	<div class="row">
		<div class="col-sm-6">
			<h1>Laser Cutter Submission Form</h1>
			To check your currently queued or finished cuts, visit <a href='https://eecs.engineering.oregonstate.edu/education/tekbotSuite/tekbot/pages/userDashboard.php'>My Tekbots</a><br /><br />

			<p>Using this form you can upload a .dxf or .svg file to be cut using the laser cutter. It produces final models made out of the material which you can chose from the material list below. Once a file is uploaded, we will review the model and email you with the cost to cut. Once you approve the charge, we will start cutting the model.
			<br><strong>Note:</strong> DXF format is strongly recommended for all cuts. However, if your cut requires a raster engrave or you are struggling to make a DXF file, SVG format may be necessary. We will alert you if this change must be made.
			</p>
			<p>If you would like to pay via credit card, please submit your file with this form and enter 'Credit Card' in the account code field. We will reply with instructions on how to submit payment.
			</p>
			<button class="btn btn-primary" data-toggle="collapse" data-target="#collapseExample">
				Laser Cutting FAQs
			</button>
			<br /><br />
			<div class="collapse" id="collapseExample">
				<div class="card card-body">
					<i>Q: How big can your Laser cutter cut?</i> <br> A: Our sheet size is 290 mm x 220mm. However, in order to ensure that your part fits, you should design your part to be at least 5mm shorter than the sheet size on both sides. The x-axis is the longer side on our laser cutter, please try to design your parts accordingly to reduce the processing time for your part.<br><br>
					<i>Q: What units should my .dxf file be in?</i><br>A: Your .dxf MUST be in millimeters. <br><br>
					<i>Q: How can I keep my cutting costs down?</i><br>A: The price depends on the material which is being used. *Remember, if a cutting needs to be repeated make sure they are in one sheet if it fits, since every cutting job submitted WILL be charged as a separate sheet regardless if it uses the entire sheet or not. <br><br>
					<i>Q: How much does it cost?</i><br>A: Our minimum purchase is for a single sheet of material, regardless of the part size. Currently every file you submit is processed and charged as a single sheet, so it is cheaper to combine your parts into a single .dxf file before submitting. You can see our current prices at the TekBots Online Store. Student pricing is 50% off the listed price (discount code generated after order submission). <br><br>
					<i>Q: How do I combine my parts into a single dxf file?</i><br>A: You may either do this in your CAD software before exporting the dxf or by using software that can edit dxf files, like Inkscape. DraftSight is another option that is a 2D CAD tool. <br><br>
					<i>Q: How can I be sure my files are cutable?</i><br>A: Once you have created your dxf file, open it with the free InkScape software (www.inkscape.org). When the file is imported, be sure that you DO NOT select the 'scale to page' option or your dimensions will be incorrect. If this opens and your dxf appears to be correct, then you should be good to submit for cutting.<br><br>
				</div>

			</div>


		</div>
		<div class='col-sm-6'>
			<form id='submit' action="redirect.php">

				<?php renderUserFixedInput($user) ?>

				<b>Payment Method:</b>
				<BR />
				<?php renderPaymentForm() ?>
				<br /><b>Quantity</b><br />
				<select id="quantitySelect" name="quantity" form="mainform">
					<option value="1">1</option>
					<option value="2">2</option>
					<option value="3">3</option>
					<option value="4">4</option>
					<option value="5">5</option>
					<option value="6">6</option>
					<option value="7">7</option>
					<option value="8">8</option>
					<option value="9">9</option>
					<option value="10">10</option>
				</select>
				<br /><b>Material</b>
				<BR>Costs shown are reflective of the price after student discount. Code generated after order submission
				<?php
				renderSelector($laserMaterials, $laserMaterialIdGetter, $laserMaterialDescriptionGetter, "laserMaterialSelect");
				?>
				<br />
				<b>Select Laser Cutter</b>
				<?php
				renderSelector($laserCutters, $laserIdGetter, $laserNameGetter, "laserSelect");
				?>
				<br />
				<b>Notes</b>
				<BR>Any special instructions or deadlines that you have should be entered here
				<textarea name=notes id="specialNotes" rows="4" cols="50"></textarea><br />
				<label id="fileFeedback"></label>
				<input type="file" id="uploadFileInput" class="form-control" name="uploadFileInput" onchange="Upload();" accept=".dxf, .svg"> <!-- Not multiple bc the server's only processing 1 file-->
			</form>
			<div id="target"></div>

			<br />
			<button id="submitLaserCutBtn">Submit</button>
			<br /><br />
		</div>
	</div>
</div>


<script>
	$('#submitLaserCutBtn').on('click', function() {
		// let selectedPayment = $("input[type=radio][name=accounttype]:checked").val();
		if (!isValidFile) alert("Please enter a valid DXF file of a size less than 10 MB");

		let selectedPayment = getPaymentMethod();

		if (selectedPayment == null) alert("Please select a payment method");

		if (isValidFile && (selectedPayment != null)) {

			let voucherVal = null;
			let accountVal = null;

			if (selectedPayment == 'voucher') {
				voucherVal = $("#voucherInput").val();
			}
			let employeeNotes = '';
			if (selectedPayment == 'account') {
				accountVal = $("#accountInput").val();
			}

			let filePath = $('#uploadFileInput').val();
			var filename = filePath.replace(/^.*\\/, "");

			let data = {
				action: 'createCutJob',
				userId: '<?php echo $user->getUserID(); ?>',
				cutterId: $('#laserSelect').val(),
				cutMaterialId: $('#laserMaterialSelect').val(),
				dbFileName: dbFileName,
				quantity: $('#quantitySelect').val(),
				dxfFileName: filename,
				payment: selectedPayment,
				courseGroup: 0,
				voucherCode: voucherVal,
				accountCode: accountVal,
				customerNotes: $('#specialNotes').val(),
				employeeNotes: employeeNotes,
				messageID: 'wersspdogggkjfd'
			};
			// Send our request to the API endpoint
			api.post('/lasers.php', data).then(res => {
				snackbar(res.message, 'success');
				$('#submitLaserCutBtn').prop('disabled', true);
				setTimeout(function() {
					window.location.replace('pages/userCutsSubmit.php')
				}, 2000);
			}).catch(err => {
				snackbar(err.message, 'error');
			});
		}
	});
</script>
<?php
include_once PUBLIC_FILES . '/modules/footer.php';
?>
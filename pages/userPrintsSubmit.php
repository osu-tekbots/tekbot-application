<?php

include_once '../bootstrap.php';

use DataAccess\PrinterDao;
use DataAccess\UsersDao;

if (PHP_SESSION_ACTIVE != session_status())
	session_start();



include_once PUBLIC_FILES . '/lib/shared/authorize.php';

allowIf(verifyPermissions(['user', 'employee'], $logger), $configManager->getBaseUrl() . 'pages/login.php');

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

$title = '3D Print Submission';

include_once PUBLIC_FILES . '/modules/header.php';
include_once PUBLIC_FILES . '/modules/employee.php';
include_once PUBLIC_FILES . '/modules/userHtml.php';
include_once PUBLIC_FILES . '/modules/submissionPage.php';

$printerDao = new PrinterDao($dbConn, $logger);
$usersDao = new UsersDao($dbConn, $logger);
$validFile = false;


$printers = $printerDao->getPrinters();
$printTypes = $printerDao->getPrintTypes();
$user = $usersDao->getUserByID($_SESSION['userID']);



$printerNameGetter = function ($printer) {
	return $printer->getPrinterName();
};
$printerIdGetter = function ($printer) {
	return $printer->getPrinterId();
};

$printTypeNameGetter = function ($printType) {
	return $printType->getPrintTypeName();
};
$printTypeIdGetter = function ($printType) {
	return $printType->getPrintTypeId();
};

?>



<script type="text/javascript">
	var isValidFile = false;
	var dbFileName = "";

	function Upload(action, id) {

		var html = 'LOADING...';
		// $('#uploadTextDiv').html(html).css('visibility','visible');
		$('#fileFeedback').text(html);
		var file_data = $('#uploadFileInput').prop('files')[0]
		var form_data = new FormData();
		form_data.append('file', file_data);
		form_data.append('action', 'upload');

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
			<h1>3D Print Submission Form</h1>
			To check your currently queued or finished prints, visit <a href='https://eecs.engineering.oregonstate.edu/education/tekbotSuite/tekbot/pages/userDashboard.php'>MyTekbots</a><br /><br />

			<p>Using this form, you can upload a 3D model to be printed. Once you upload a file, we will review the model and email you with the cost to print. Once you approve the charge, we will print the model and it can be picked up during store hours or in the TekBoxes after store hours (if the print fits). We only process prints during store hours; please be aware that there might also be other prints in queue.
				<br>Be aware that our 3d printer build plate dimensions are 280 mm x 280 mm x 250mm (about the size of an average textbook and about the height of a piece of paper) and our maximum print weight is 500 grams, so we can only accomodate prints within those bounds.
				<br>If you would like to pay via credit card, we will reply with instructions on how to submit payment when we confirm your model.
			</p>
			<!-- <p class="text-danger">NOTE: We only accept files of the 'Stereo Lithography Type' (.stl) and the attachment file size must be smaller than 10Mb</p> -->
			<button class="btn btn-primary" data-toggle="collapse" data-target="#collapseExample">
				Printing FAQs
			</button>
			<br /><br />
			<div class="collapse" id="collapseExample">
				<div class="card card-body">
					<i>Q: How big can your printer print?</i> <br /> A: Within 280 mm x 280 mm x 250mm <br /><br />
					<i>Q: How much can your printer print weigh?</i> <br /> A: At most 500 grams <br /><br />
					<i>Q: How thick should my part's walls be?</i><br /> A: This is not a simple answer, as it depends on the size of the wall and the strength needed. We recommend at least .1" thick walls when feasible.<br /><br />
					<i>Q: How can I keep my printing costs down?</i><br /> A: One hidden cost often overlooked is the support material. To print an object with an 'overhang' the printer will insert a softer support material as it builds. This support will be from the bottom up until the overhang is reached often increasing the amount of material used by 10x. The best design methodology is to try to think of everything as a bowl where it be be built of the open side up.<br />
				</div>
			</div>
		</div>
		<div class="col-sm-6">
			<?php renderUserFixedInput($user) ?>

			<b>Payment Method</b>
			<br />
			<?php renderPaymentForm() ?>
			<br />

			<b>Select Material</b>
			<br />
			<select id="printTypeSelect" name="printTypeSelect" class="custom-select">
			</select>
			<br />
			<br />

			<b>Select Printer</b>
			<br />
			<?php
			renderSelector($printers, $printerIdGetter, $printerNameGetter, "printerSelect");
			?>
			<br />

			<script>
				/*
				Structure for associating print types with printer. Allows filtering of print types dropdown
				let lookup = {
					printerName1: [
						[
							printTypeName,
							printTypeId,
						],
						[
							printTypeName,
							printTypeId,
						], ....
					],
					printerName2: [
						....
					]
				}
				*/

				let lookup = {
					<?php
					foreach ($printers as $printer) {
						$printerId = $printer->getPrinterId();
						echo $printerId . ":[";
						foreach ($printTypes as $printType) {
							if ($printType->getPrinterId() == $printerId) {

								$printTypeName = $printType->getPrintTypeName();

								echo "[" . "'$printTypeName'" . "," . $printType->getPrintTypeId() . "],";
							}
						}
						echo "],";
					}
					?>
				};


				// Initially set the Print Type Dropdown
				$('#printTypeSelect').empty();
				let initialPrinterValue = $('#printerSelect').val();
				for (i = 0; i < lookup[initialPrinterValue].length; i++) {
					$('#printTypeSelect').append("<option value='" + lookup[initialPrinterValue][i][1] + "'>" + lookup[initialPrinterValue][i][0] + "</option>");
				}

				// Dynamically change Print Type Dropdown on change of Printer
				$('#printerSelect').on('change', function() {
					// Set selected option as variable
					var selectValue = $(this).val();

					// Empty the target field
					$('#printTypeSelect').empty();

					// For each choice in the selected option
					for (i = 0; i < lookup[selectValue].length; i++) {
						// Output choice in the target field
						$('#printTypeSelect').append("<option value='" + lookup[selectValue][i][1] + "'>" + lookup[selectValue][i][0] + "</option>");
					}
				});
			</script>

			<b>Quantity</b><br />
			<select id="quantitySelect" class="custom-select" name="quantity" form="mainform">
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
			<br />
			<br />

			<b>Notes</b><br />
			Any special instructions or deadlines that you have should be entered here<br />
			<textarea class="form-control" id="specialNotes" name="notes" rows="4" cols="50"></textarea><br />
			
			<div id="targetDiv"></div>
			<label id="fileFeedback"></label>
			<input type="file" id="uploadFileInput" class="form-control" name="uploadFileInput" onchange="Upload();" accept=".stl"><!-- NOT multiple; Upload() fn only handles 1-->
			<div id="uploadTextDiv"></div>
			
			<br />
			<button id="submit3DPrintBtn" class="btn btn-primary">Submit</button>
		</div>

	</div>

</div>




<br /><br /><br />
<br /><br /><br />
<script>
	window.onload = function() {
		Lily.ready({
			target: 'targetDiv', // target div id
			file: 'uploadFileInput', // file input id
			path: 'assets/Madeleine.js/src/' // path to source directory from current html file
		});
	};


	$('#submit3DPrintBtn').on('click', function() {
		// let selectedPayment = $("input[type=radio][name=accounttype]:checked").val();
		if (!isValidFile) alert("Please enter a valid STL file of a size less than 10 MB");

		let selectedPayment = getPaymentMethod();

		if (selectedPayment == null) alert("Please select a payment method");

		if (isValidFile && (selectedPayment != null)) {

			let voucherVal = null;
			let accountVal = null;

			if (selectedPayment == 'voucher') {
				voucherVal = $("#voucherInput").val();
			}
			
			// let employeeNotes = '';
			if (selectedPayment == 'account') {
				accountVal = $("#accountInput").val();
			// 	employeeNotes = 'Account code: ' + $("#accountInput").val(); // Won't need after this update
			}

			let filePath = $('#uploadFileInput').val();
			var filename = filePath.replace(/^.*\\/, "");

			let data = {
				action: 'createprintjob',
				userId: '<?php echo $user->getUserID(); ?>',
				printerId: $('#printerSelect').val(),
				printTypeId: $('#printTypeSelect').val(),
				dbFileName: dbFileName,
				stlFileName: filename,
				quantity: $('#quantitySelect').val(),
				payment: selectedPayment,
				courseGroup: 0,
				voucherCode: voucherVal,
				accountCode: accountVal,
				customerNotes: $('#specialNotes').val(),
				employeeNotes: '',
				messageID: 'wersspdoifwkjfd'
			};
			// Send our request to the API endpoint
			api.post('/printers.php', data).then(res => {
				snackbar(res.message, 'success');
				$('#submit3DPrintBtn').prop('disabled', true);
				setTimeout(function() {
					window.location.replace('pages/userPrintsSubmit.php')
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
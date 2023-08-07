<?php
include_once '../bootstrap.php';

use DataAccess\TicketDao;
use DataAccess\LabDao;

if (!session_id()) {
    session_start();
}

// Make sure the user is logged in and allowed to be on this page
// include_once PUBLIC_FILES . '/lib/shared/authorize.php';

$title = "Ticket Submission Form";

$css = array(
	'assets/css/sb-admin.css',
	'assets/css/admin.css',
	'https://cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css'
);
$js = array(
    'https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js'
);


include_once PUBLIC_FILES . '/modules/header.php';
include_once PUBLIC_FILES . '/modules/renderBrowse.php';
include_once PUBLIC_FILES . '/modules/userHtml.php';
include_once PUBLIC_FILES . '/modules/submissionPage.php';

$ticketDao = new TicketDao($dbConn, $logger);
$labDao = new LabDao($dbConn, $logger);

$roomOptions = "<option value=''></option>";
$rooms = $labDao->getRooms();

foreach ($rooms as $room){
	$roomOptions .= "<option value='".$room->getId()."'>".$room->getName()."</option>";
}
$roomIdGetter = function ($room) {
	return $room->getId();
};
$roomNameGetter = function ($room) {
	return $room->getName();
};
$stationIdGetter = function ($station) {
	return $station->getId();
};
$stationNameGetter = function ($station) {
	return $station->getName();
};
$roomInput = $_GET['room'] ?? 1;
$benchInput = NULL;
$stationInput = $_GET['station'] ?? NULL;
// if ($_POST['email']) {
// 	echo 'Ticket Submitted!';
// 	exit();
// }

if ($stationInput != NULL) {
	$station = $labDao->getStationById($stationInput);
	$roomInput = $station->getRoomId();
	$benchInput = $station->getName();
}

if ($roomInput != NULL && isset($_GET['bench'])) {
	$roomInput = $_GET['room'];
	$benchInput = $_GET['bench'];
	$stationInput = $labDao->getStationIdFromRoomAndBench($roomInput, $benchInput);
}
?>
<script type='text/javascript'>
	var isValidFile = false;
	var dbFileName = "";

	function Upload(action, id) {

		var html = '<B>LOADING</B>';
		// $('#uploadTextDiv').html(html).css('visibility','visible');
		$('#fileFeedback').text(html);
		var file_data = $('#uploadFileInput').prop('files')[0]
		var form_data = new FormData();
		form_data.append('file', file_data);
		form_data.append('action', 'ticketImageUpload');

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

	function Select(action){
		var html = '<B>LOADING</B>';
		$('#fileFeedback').text(html);
		var file_data = $('#uploadFileInput').prop('files')[0]
		var form_data = new FormData();
		form_data.append('file', file_data);
		form_data.append('action', action);

		$.ajax({
			url: './ajax/selectHandler.php',
			type: 'POST',
			dataType: 'json',
			/*what we are expecting back*/
			contentType: false,
			processData: false,
			data: form_data,
			success: function(result) {
				if (result["successful"] == 1) {
					var html = result["string"];
					$('#fileFeedback').text(html);
				} else if (result["successful"] == 0) {
					var html = result["string"];
					$('#fileFeedback').text(html);
				}

			},
			error: function(result) {
				var html = '<font color="red">❌ </font> Failed: ' + result["string"];
				$('#fileFeedback').text(html);
			}
		});
	}

	var path = "https://eecs.engineering.oregonstate.edu/education/tekbotSuite/tekbot/pages/publicTicketSubmit.php";
	
	function roomChange() {
		var room = document.getElementById("RoomId").value;
		window.location = path+"?room="+room;
	}
	function benchChange() {
		var room = document.getElementById("RoomId").value;
		var bench = document.getElementById("BenchId").value;
		window.location = path+"?room="+room+"&bench="+bench;
	}
</script>
<br/>
<div id="page-top">

	<div id="wrapper">
        <div class="container-fluid">
			<div class='col-9'>
				<br />
				<h3>EECS Lab Reporting Tool</h3><br />
				<p>Thank you for using this tool. You can report problems with equipment and stations here. If you don't report it no one will know!</p>
			<!-- </div><div class='col-3'></div> -->
			</div>
			<div class = 'row col-9'>
				<form method="post">
					<div class="form-group col-sm-9">
						<b>Room:</b>
						<?php
							
							$roomOptions = "";
							$rooms = $labDao->getRooms();
							foreach ($rooms as $r){
								if ($r->getId() == $roomInput) {
									$roomOptions .= "<option selected value='".$r->getId()."'>".$r->getName()."</option>";
								} else {
									$roomOptions .= "<option value='".$r->getId()."'>".$r->getName()."</option>";	
								}
							}
							//renderSelectorOnChange($stations, $stationIdGetter, $stationNameGetter, "BenchId", "benchChange()")
						?>
						<!-- <p id="roomid"></p> -->
						<select id="RoomId" name="RoomId" form="mainform" class="form-control" onchange="roomChange()">
							<?php echo $roomOptions;?>
						</select>
						<br />
						<b>Station:</b>
						<?php
							$stationOptions = "";
							$stations = $labDao->getStationsFromRoom($roomInput);
							foreach ($stations as $s){
								if ($s->getName() == $benchInput) {
									if($s->getName() == 100) {
										$stationOptions .= "<option selected value='100'>Entire Room</option>";
									} else if($s->getName() == 99) {
										$stationOptions .= "<option selected value='99'>Special Request</option>";
									} else if($s->getName() == 50) {
										$stationOptions .= "<option selected value='50'>Resistor Reel</option>";
									} else {
										$stationOptions .= "<option selected value='".$s->getName()."'>".$s->getName()."</option>";	
									}
								} else {
									if($s->getName() == 100) {
										$stationOptions .= "<option value='100'>Entire Room</option>";
									} else if($s->getName() == 99) {
										$stationOptions .= "<option value='99'>Special Request</option>";
									} else if($s->getName() == 50) {
										$stationOptions .= "<option value='50'>Resistor Reel</option>";
									} else {
										$stationOptions .= "<option value='".$s->getName()."'>".$s->getName()."</option>";	
									}
								}
							}
							//renderSelectorOnChange($stations, $stationIdGetter, $stationNameGetter, "BenchId", "benchChange()")
						?>
						<select id="BenchId" name="BenchId" form="mainform" class="form-control" onchange="benchChange()">
							<?php echo $stationOptions;?>
						</select>
						<br />
						<label for="email"><b>Enter your email:</b></label>
						<input type="email" id="email" name="email"> 
						<br />
						<label for="notes"><b>Issue:</b></label>
						<textarea name="notes" id="notes" rows="4" cols="20"></textarea><br />
						<label id="fileFeedback"></label>
						<label for="uploadFileInput"><b>Add pictures:</b></label>
						<input type="file" id="uploadFileInput" class="form-control" name="uploadFileInput" onchange="Upload();" accept=".jpeg,.jpg,.png,.bmp,.JPG,.JPEG,.PNG,.BMP,.heic,.HEIC" multiple>
						<div class="form-group col-sm-9">
							<button id="submitTicketBtn" class="btn btn-primary" onclick="return false">Submit Ticket</button>
						</div>
						<div class="form-group col-sm-9">
							<p>If you have trouble using this page contact us at: <a href="mailto: 	tekbot-worker@engr.oregonstate.edu">Tekbot Worker</a></p>
						</div>
					</div>
				</form>
				</div>
			</div>
        </div>
    </div>
	<script>
	$('#submitTicketBtn').on('click', function() {
		email = $("#email").val();
		issue = $("#notes").val();

		if ((email != null) && (issue != null)) {

			let filePath = dbFileName;
			var filename = filePath.replace(/^.*\\/, "");

			let data = {
				action: 'createTicket',
				roomId: $('#RoomId').val(),
				benchId: $('#BenchId').val(),
				email: email,
				issue: issue,
				image: filename,
				messageID: '4ruw95452yebfy9x'
			};
			console.log(data);
			// Send our request to the API endpoint
			api.post('/tickets.php', data).then(res => {
				snackbar(res.message, 'success');
				$('#submitTicketBtn').prop('disabled', true);
				setTimeout(function() {
					window.location.replace('pages/publicTicketSubmit.php')
				}, 2000);
			}).catch(err => {
				snackbar(err.message, 'error');
			});
		}
		else{
			alert("Email and reason for ticket are required.");
		}
	});
</script>
<?php 
include_once PUBLIC_FILES . '/modules/footer.php' ;
?>
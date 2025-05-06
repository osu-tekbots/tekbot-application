<?php
include_once '../bootstrap.php';

use DataAccess\TicketDao;
use DataAccess\LabDao;
use DataAccess\UsersDao;
use Model\Room;	
use Util\Security;

/*
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL); 
*/

if (PHP_SESSION_ACTIVE != session_status())
    session_start();

// Make sure the user is logged in and allowed to be on this page
include_once PUBLIC_FILES . '/lib/shared/authorize.php';

allowIf(verifyPermissions('employee', $logger), 'index.php');

$_SESSION['currentRoom'] = $roomID; //TODO: Set this to update when new room is chosen

$title = 'Employee Lab Room Details';
$css = array(
	'assets/css/sb-admin.css',
	'assets/css/admin.css',
	'https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css',
	'https://cdn.datatables.net/buttons/1.6.2/css/buttons.dataTables.min.css'
	
    
);
$js = array(
    'https://code.jquery.com/jquery-3.5.1.min.js',
	'https://cdn.datatables.net/1.10.21/js/jquery.dataTables.js',
	'https://cdn.datatables.net/buttons/1.6.2/js/dataTables.buttons.js',
	'https://cdn.datatables.net/buttons/1.6.2/js/buttons.print.js'
);

include_once PUBLIC_FILES . '/modules/header.php';
include_once PUBLIC_FILES . '/modules/employee.php';
include_once PUBLIC_FILES . '/modules/renderBrowse.php';

$userDao = new UsersDao($dbConn, $logger);
$ticketDao = new TicketDao($dbConn, $logger);
$labDao = new LabDao($dbConn, $logger);


if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'loadAddImage' && isset($_REQUEST['roomID'])) {
    $roomID = $_REQUEST['roomID'];

    $rooms = $labDao->getRooms();

    foreach ($rooms as $room) {
        if ($room->getID() === $roomID) {
            $image = $room->getImage();
            echo "/images/maps/" . ($image != '' ? $image : 'noimage.jpg');
            exit();
        }
    }
    echo "/../../noimage.jpg";
    exit();
}

?>

<script>

function loadAddImage(){
	var roomID = $('#newdescription').val();
	$.ajax({
		type: 'POST',
		url: './pages/employeeTicketLabRooms.php',
		dataType: 'html',
		data: {roomID: roomID,
				action: 'loadAddImage'},
		success: function(result)
				{
				$('#addImage').attr("src",result);
				$('#addImage').show();
				},
		error: function (xhr, ajaxOptions, thrownError) {
					alert(xhr.status);
					alert(xhr.responseText);
					alert(thrownError);
				}
			});
}

</script> 

<br>
<div id="page-top">

	<div id="wrapper">

	<?php 
		// Located inside /modules/employee.php
		renderEmployeeSidebar();
	?>

    <div class="admin-content" id="content-wrapper">
        <div class="container-fluid">
			<?php 

			echo "<div class='admin-paper' id='fullList'>";    

            echo "<h1 id=\"roomsHead\">Lab Rooms:</h1>";

            $labSELECT = "<form action=''><div class='form-row print-hide'><div class='form-group col-sm-9'><select name='labrooms' id='newdescription' class='custom-select' onchange='loadAddImage();'>"; 
			
			$currentRoom = new Room();

			//TODO: exclude storage rooms from the dropdown of labrooms

			$labrooms = $labDao->getRooms();
            foreach ($labrooms as $room) {
                $roomID = $room->getID();
                $roomName = $room->getName();
				$roomID = $roomName;
				$roomName = $room;

                // if (in_array($roomID, $excludedRooms)) {
                //     continue;
                // }

				if ($roomID == $_SESSION['currentRoom']){
					$labSELECT .= "<option selected value='$roomID'".($labrooms == $roomID ? 'selected' : '' ).">$roomID</option>";
					$roomID = $currentRoom;
					
				} else {
                	$labSELECT .= "<option value='$roomID'>$roomID</option>";
				}

            }

			//TODO: Get image to display correctly
			$labSELECT .= "</select></div></div></form>";
			$labIMAGE = "<br><img id='labImage' src='./images/maps/". $currentRoom->getMap() ."' style='width: 500px; height: auto;' />";
			
            echo $labSELECT;
			echo $labIMAGE;
            echo "</div>";

            ?>
        </div>
    </div>
</div>

<?php 
include_once PUBLIC_FILES . '/modules/footer.php' ; 
?>
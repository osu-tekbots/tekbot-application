<?php
include_once '../bootstrap.php';
//include './employeeTicketList.php';

use DataAccess\TicketDao;
use DataAccess\LabDao;
use DataAccess\UsersDao;

if (!session_id()) {
    session_start();
}

// Make sure the user is logged in and allowed to be on this page
include_once PUBLIC_FILES . '/lib/shared/authorize.php';

$isEmployee = isset($_SESSION['userID']) && !empty($_SESSION['userID']) 
	&& isset($_SESSION['userAccessLevel']) && $_SESSION['userAccessLevel'] == 'Employee';

allowIf($isEmployee, 'index.php');


$key = $_GET['key']; //use to pull correct 

$title = 'Ticket Detail';
$css = array(
	'assets/css/sb-admin.css',
	'assets/css/admin.css',
	'slideshow.css',
	'https://cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css'
);
$js = array(
    'https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js'
);

include_once PUBLIC_FILES . '/modules/header.php';
include_once PUBLIC_FILES . '/modules/employee.php';
include_once PUBLIC_FILES . '/modules/renderBrowse.php';

$ticketDao = new TicketDao($dbConn, $logger);
$stationDao = new LabDao($dbConn, $logger);
$users = new UsersDao($dbConn, $logger);
//pull history

$ticket = $ticketDao->getTicketById($key); //replace with $key
$outputHTML =  "<div class='admin-paper d-flex flex-column'>";
$userImg = $ticket->getImage();

$station = $stationDao->getStationById($ticket->getStationId());


$history = $ticketDao->getStationHistory($station->getId());

$historyContents = array();
foreach($history as $h) {
	$historyContents[] = array(
		"contents" => substr(strip_tags($h->getIssue()), 0, 60) . 
			(strlen(strip_tags($h->getIssue())) > 60 ? '...' : ''), 
		"id" => $h->getId()
	);
};

$outputHTML .= '<h3>Current Issue: #'.$key.'</h3>';
$outputHTML .= 'Room: '.$station->getRoom()->getName().'<BR>';
$outputHTML .= 'Bench Number: '.$station->getName().'<BR><BR>';

$outputHTML .= '<div class="row">';
$outputHTML .= '<div id="currentIssue" class= "flex-fill col-6">';
$outputHTML .= '<p><b>Submitter\'s note: </b>'.htmlspecialchars($ticket->getIssue()).'</p>';

if ($userImg != null AND $userImg!= ''){
	$outputHTML .= '
	<div id="userImg" class="flex-fill" style="margin-bottom: 10px;">
		<p style="margin-bottom:0; color: #666;">User Provided Image</p>
		<img src=../tekbot/uploads/tickets/'.$userImg.' class="img-fluid" style="max-width: 400px; max-height: 500px; float: left;">
		<br style="clear:both;">
	</div>
	';
} else {
	$outputHTML .=  '
	<div id="userImg" class="flex-fill">
		
	</div>
	';
}
$outputHTML .=
	'<form>
		<label for="comments">Internal Ticket Comments:</label>
		<textarea id="admin_contents" class="form-control" rows="5" name="comments" placeholder="Internal Comments" ' . (($ticket->getStatus() == 1) ? 'disabled' : 'onchange="updateComments('.$key.')"') . '>'.$ticket->getComment().'</textarea>
		<br>
		<label for="repsonse">Resolution Message to Submitter:</label>
		<textarea id="resolve_text" class="form-control" rows="5" name="response" placeholder="Resolution Message" ' . (($ticket->getStatus() == 1) ? 'disabled' : '') . '>'.$ticket->getResponse().'</textarea>
		<br>
	</form>';
if($ticket->getStatus() != 1) {
	$outputHTML .=  '<div id="submit-buttons" style="display:flex; flex-direction: row">';
	$outputHTML .=  '<button class="btn col-sm-1" id="button" style="background-color: #d1ffcb; border: 2px solid black; margin:10px; padding:20px 60px; font-size: 20px; display: flex; align-items: center; justify-content: center;" onclick="resolveTicket('.$key.')">Resolve</button>';
	$outputHTML .=  '<button class="btn col-sm-1" id="button" style="background-color: #ffcccb; border: 2px solid black; margin: 10px; padding:20px 60px; font-size: 20px; display: flex; align-items: center; justify-content: center;" onclick="escalateTicket('.$key.')">Escalate</button>';
	$outputHTML .=  '</div>';
}
$outputHTML .=  '<h4>Station History</h4><ul>';
	foreach($historyContents as $hc) {
		$outputHTML .=  '<li><a href="https://'.$_SERVER['HTTP_HOST'].substr($_SERVER['REQUEST_URI'], 0, strpos($_SERVER['REQUEST_URI'], '?')).'?key='.$hc['id'].'">'.$hc['contents'].'</a></li>';
	};
	$outputHTML .=  '</ul>';

$outputHTML .=  '</div>';

$outputHTML .= ' <div id="stationInfo" class = "flex-fill col-6">';
$outputHTML .= '<h4>Room Layout</h4>';
$outputHTML .= '<img src="../../labs/image/map/' . $station->getRoom()->getMap() . '" class="img-responsive" width="400" >';
$outputHTML .= '<h4>Room Equipment</h4>';

$outputHTML .=  '</div></div>';

$outputHTML .=  '</div>'; //close admin paper div
?>

<script>
	function updateComments(id) {
		let content = {
			action: 'updateComment',
			id: id,
			message: document.getElementById('admin_contents').value
		}

		api.post('/tickets.php', content).then(res=> {
			snackbar(res.message, 'success');
		}).catch(err => {
			snackbar(err.message, 'error');
		});
	}

	function updateResponse(id) {
		let content = {
			action: 'updateResponse',
			id: id,
			message: document.getElementById('resolve_text').value
		}
		
		api.post('/tickets.php', content).then(res=> {
			snackbar(res.message, 'success');
		}).catch(err => {
			snackbar(err.message, 'error');
		});
	}

    function resolveTicket(id) {
        //var ticketDesc = $('#contents'+id).val();

		let content = {
			action: 'resolveTicket',
			id: id,
			messageId: 'x4z7mxqzw3ppucgc',
			message: document.getElementById('resolve_text').value
		}

		api.post('/tickets.php', content).then(res=> {
			snackbar(res.message, 'success');
			$('#row'+id).css({opacity: 0});
			setTimeout(() => {location.reload()}, 3000);
		}).catch(err => {
			snackbar(err.message, 'error');
		})
        }
    

	function escalateTicket(id) {
		
		var contents = $('#contents'+id).val();

			let content = {
                action: 'escalateTicket',
                id: id,
                contents: contents,
				messageId: '4pbd37ranu88fqkm',
				empEmail: '<?php echo $users->getUserByID($_SESSION['userID'])->getEmail();?>'
            }

			api.post('/tickets.php', content).then(res=> {
				snackbar(res.message, 'Ticket Escalated');
				$('#row'+id).css({opacity:0});
			}).catch(err => {
				snackbar(err.message, 'error');
			})

	}
</script>
<BR>
<div id="page-top">

	<div id="wrapper">

		<?php 
			// Located inside /modules/employee.php
			renderEmployeeSidebar();
		?>

		<div class="admin-content" id="content-wrapper">
			<div class="container-fluid">
				
				<?php 
				echo $outputHTML; 
				?>
			</div>
		</div>
	</div>
</div>
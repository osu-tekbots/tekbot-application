<?php
include_once '../bootstrap.php';

use DataAccess\TicketDao;
use DataAccess\LabDao;
use DataAccess\UsersDao;
use Util\Security;
/*
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL); 
*/

if (!session_id()) {
    session_start();
}

// Make sure the user is logged in and allowed to be on this page
include_once PUBLIC_FILES . '/lib/shared/authorize.php';

$isEmployee = isset($_SESSION['userID']) && !empty($_SESSION['userID']) 
	&& isset($_SESSION['userAccessLevel']) && $_SESSION['userAccessLevel'] == 'Employee';

allowIf($isEmployee, 'index.php');


$title = 'Tickets';
$css = array(
	'assets/css/sb-admin.css',
	'assets/css/admin.css',
	'https://cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css'
);
$js = array(
    'https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js'
);

include_once PUBLIC_FILES . '/modules/header.php';
include_once PUBLIC_FILES . '/modules/employee.php';
include_once PUBLIC_FILES . '/modules/renderBrowse.php';

$userDao = new UsersDao($dbConn, $logger);
$ticketDao = new TicketDao($dbConn, $logger);
$labDao = new LabDao($dbConn, $logger);
$activeTickets = $ticketDao->getTicketsByStatus(0);
$resolvedTickets = $ticketDao->getTicketsByStatus(1);
$escalatedTickets = $ticketDao->getTicketsByStatus(2);

function specialDeskNumber($deskNum) {
    if ($deskNum == 99) {
        $deskNum = 'Special Request';
        return $deskNum;
    }
    if ($deskNum == 100) {
        $deskNum = 'Entire Room';
        return $deskNum;
    }
    else {
        return $deskNum;
    }
}

?>
<script>
    function resolveTicket(id) {
        //var ticketDesc = $('#contents'+id).val();

        let content = {
            action: 'resolveTicket',
            id: id,
            messageId: 'x4z7mxqzw3ppucgc',
            //changed ticketId to id
            
        }

        api.post('/tickets.php', content).then(res=> {
            snackbar(res.message, 'Ticket Resolved');

            // Rest of function is updating the page to display same as reload
            document.querySelector('#row'+id+' > .btn-warning').remove();
            document.querySelector('#row'+id+' > .btn-success').remove();
            // TODO: Change 'created' time to 'resolved' time
            
            document.getElementById('resolvedHead').after(document.querySelector('#row'+id+'+ hr'));
            document.getElementById('resolvedHead').after(document.querySelector('#row'+id));

            if(document.querySelector('#escalatedHead + h1') !== null)
                document.getElementById('escalatedHead').remove();
            
            if(document.querySelector('#activeHead + h1') !== null)
                document.getElementById('activeHead').remove();
            
        }).catch(err => {
            console.error(err.message);
            snackbar(err.message, 'error');

            // Refresh the page to help clear up user confusion from stale cashed page
            if(err.message == 'Ticket Already Resolved.') {
                setTimeout(() => location.reload(), 3000);
            }
        })
    }

    function escalateTicket(id) {
		var contents = $('#issue'+id).val();

        let content = {
            action: 'escalateTicket',
            id: id,
            messageId: '4pbd37ranu88fqkm',
            contents: contents,
            empEmail: '<?php echo $userDao->getUserByID($_SESSION['userID'])->getEmail();?>'
            //changed ticketId to id
        }

        api.post('/tickets.php', content).then(res=> {
            snackbar(res.message, 'Ticket Escalated');
            // Rest of function is updating the page to display same as reload
            
            if(document.getElementById('escalatedHead')) {
                document.getElementById('escalatedHead').after(document.querySelector('#row'+id+'+ hr'));
                document.getElementById('escalatedHead').after(document.querySelector('#row'+id));
            } else {
                let newHeader = document.createElement('h1')
                newHeader.id = 'escalatedHead';
                newHeader.appendChild(document.createTextNode('Escalated:'));
                document.getElementById('fullList').prepend(newHeader);
                
                document.getElementById('escalatedHead').after(document.querySelector('#row'+id+'+ hr'));
                document.getElementById('escalatedHead').after(document.querySelector('#row'+id));
            }
            
            if(document.querySelector('#activeHead + h1') !== null)
                document.getElementById('activeHead').remove();
        }).catch(err => {
            snackbar(err.message, 'error');
        })
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
			renderEmployeeBreadcrumb('Employee', 'Ticket List');

			echo "<div class='admin-paper' id='fullList'>";

            if(count($escalatedTickets)) {
                echo "<h1 id=\"escalatedHead\">Escalated:</h1>";
                foreach ($escalatedTickets as $t) {
                    $ticketId = $t->getId();
                    $stationId = $t->getStationId();
                    $userImg = $t->getImage();
                    $issue = htmlspecialchars($t->getIssue());
                    $email = $t->getEmail();
                    $comment = $t->getComment();
                    $response = $t->getResponse();
                    $status = $t->getStatus();
                    $created = $t->getCreated();
                    $resolved = $t->getResolved();
                    $isEscalated = $t->getIsEscalated();
                    $escalatedComments = $t->getEscalatedComments();
                    // $room = $t->getRoom();
                    // $deskNumber = $t->getDeskNumber();
                    $station = $labDao->getStationById($stationId);
                    $room = $station->getRoom()->getName();
                    $bench = $station->getName();
    
                    $deskNumber = specialDeskNumber($bench);
    
                        
                    echo '<div class="row" id="row'.$ticketId.'" style="padding-left:4px;padding-right:4px;margin-top:4px;margin-bottom:4px;">';
                    echo '<div class="col-sm-1" style="text-align:right;">
                        <b>Room:</b> '.$room.'
                        </div>';
                    echo '<div class="col-sm-1">
                        <b>Desk:</b> '.$deskNumber.'
                        </div>';
                    echo '<div class= "col-sm-1" style="margin-left: 10px; margin-right: 10px;"><b>Created:</b> ' .$created . '</div>';
                    echo '<div class="col"> '.$issue.'</div>';
                    //echo '<button class="btn col-sm-1" id="button'.$ticketId.'" style="border: 2px solid black;margin:5px; height: 60px; width: 80px;"><a href="./employeeTicketDetail.php?key='.$ticketId.'"> Details</a></button>';
                    echo '<a href="./employeeTicketDetail.php?key='.$ticketId.'"><button class="btn btn-dark" id="button" style= "border: 1px solid black !important; padding-top: 12px; padding-bottom: 12px;">Details</button></a>';
                    echo '<button class="btn btn-warning" id="button" style="background-color: #d1ffcb; border: 1px solid black !important;" onclick="resolveTicket(\''.$ticketId.'\')">Resolve</button>';
                    echo '<button class="btn btn-success" id="button" style="background-color: #ffcccb; color: black !important; border: 1px solid black !important;" onclick="escalateTicket(\''.$ticketId.'\')">Escalate</button>';
                    
                    echo '</div>';
                    echo '<hr>';
                }
            }

            if(count($activeTickets)) {
                echo "<h1 id=\"activeHead\">Active:</h1>";
                foreach ($activeTickets as $t) {
                    $ticketId = $t->getId();
                    $stationId = $t->getStationId();
                    $userImg = $t->getImage();
                    $issue = htmlspecialchars($t->getIssue());
                    $email = $t->getEmail();
                    $comment = $t->getComment();
                    $response = $t->getResponse();
                    $status = $t->getStatus();
                    $created = $t->getCreated();
                    $resolved = $t->getResolved();
                    $isEscalated = $t->getIsEscalated();
                    $escalatedComments = $t->getEscalatedComments();
                    // $room = $t->getRoom();
                    // $deskNumber = $t->getDeskNumber();
                    $station = $labDao->getStationById($stationId);
                    $room = $station->getRoom()->getName();
                    $bench = $station->getName();
    
                    $deskNumber = specialDeskNumber($bench);
    
                        
                    echo '<div class="row" id="row'.$ticketId.'" style="padding-left:4px;padding-right:4px;margin-top:4px;margin-bottom:4px;">';
                    echo '<div class="col-sm-1" style="text-align:right;">
                        <b>Room:</b> '.$room.'
                        </div>';
                    echo '<div class="col-sm-1">
                        <b>Desk:</b> '.$deskNumber.'
                        </div>';
                    echo '<div class= "col-sm-1" style="margin-left: 10px; margin-right: 10px;"><b>Created:</b> ' .$created . '</div>';
                    echo '<div class="col"> '.$issue.'</div>';
                    //echo '<button class="btn col-sm-1" id="button'.$ticketId.'" style="border: 2px solid black;margin:5px; height: 60px; width: 80px;"><a href="./employeeTicketDetail.php?key='.$ticketId.'"> Details</a></button>';
                    echo '<a href="./employeeTicketDetail.php?key='.$ticketId.'"><button class="btn btn-dark" id="button" style= "border: 1px solid black !important; padding-top: 12px; padding-bottom: 12px;">Details</button></a>';
                    echo '<button class="btn btn-warning" id="button" style="background-color: #d1ffcb; border: 1px solid black !important;" onclick="resolveTicket(\''.$ticketId.'\')">Resolve</button>';
                    echo '<button class="btn btn-success" id="button" style="background-color: #ffcccb; color: black !important; border: 1px solid black !important;" onclick="escalateTicket(\''.$ticketId.'\')">Escalate</button>';
                    
                    echo '</div>';
                    echo '<hr>';
                }
            }

            echo "<h1 id=\"resolvedHead\">Resolved:</h1>";
            for ($i = (sizeof($resolvedTickets) - 1); $i > (sizeof($resolvedTickets) - 20); $i--) {

                $ticketId = $resolvedTickets[$i]->getId();
                $stationId = $resolvedTickets[$i]->getStationId();
                $userImg = $resolvedTickets[$i]->getImage();
                $issue = htmlspecialchars($resolvedTickets[$i]->getIssue());
                $email = $resolvedTickets[$i]->getEmail();
                $comment = $resolvedTickets[$i]->getComment();
                $response = $resolvedTickets[$i]->getResponse();
                $status = $resolvedTickets[$i]->getStatus();
                $created = $resolvedTickets[$i]->getCreated();
                $resolved = $resolvedTickets[$i]->getResolved();
                $isEscalated = $resolvedTickets[$i]->getIsEscalated();
                $escalatedComments = $resolvedTickets[$i]->getEscalatedComments();
                // $room = $resolvedTickets[$i]->getRoom();
                // $deskNumber = $resolvedTickets[$i]->getDeskNumber();
                $station = $labDao->getStationById($stationId);
	            $room = $station->getRoom()->getName();
	            $bench = $station->getName();

            //}
                $deskNumber = specialDeskNumber($bench);
                echo '<div class="row" id="row'.$ticketId.'" style="padding-left:4px;padding-right:4px;margin-top:4px;margin-bottom:4px;">';
                echo '<div class="col-sm-1" style="text-align:right;">
                    <b>Room:</b> '.$room.'
                    </div>';
                echo '<div class="col-sm-1">
                    <b>Desk:</b> '.$deskNumber.'
                    </div>';
                echo '<div class= "col-sm-1" style="margin-left: 10px; margin-right: 10px;"><b>Resolved:</b> ' .$resolved . '</div>';
                echo '<div class="col">'.$issue.'</div>';
                //echo '<button class="btn col-sm-1" id="button' .$ticketId. '" style="border: 2px solid black;margin:5px; height: 60px; width: 80px;""><a href="./employeeTicketDetail.php?key='.$ticketId.'"> Details</a></button>';
                echo '<a href="./employeeTicketDetail.php?key='.$ticketId.'"><button class="btn btn-dark" id="button" style= "border: 1px solid black !important; padding-top: 12px; padding-bottom: 12px;">Details</button></a>';
                echo '</div>';
                echo '<hr>';
            }

            echo "</div>"; //close admin paper div

            echo "</div>"

            ?>
        </div>
    </div>
</div>

<?php 
include_once PUBLIC_FILES . '/modules/footer.php' ; 
?>
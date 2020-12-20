<?php
include_once '../bootstrap.php';

use DataAccess\UsersDao;
use DataAccess\BoxDao;
use Util\Security;

session_start();

include_once PUBLIC_FILES . '/lib/shared/authorize.php';

$isLoggedIn = isset($_SESSION['userID']) && !empty($_SESSION['userID']);
allowIf($isLoggedIn, $configManager->getBaseUrl() . 'pages/login.php');

$title = 'My Profile';
$css = array(
	'assets/css/admin.css',
	'https://cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css',
	'assets/Madeleine.js/src/css/Madeleine.css'
);

$js = array(
	'https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js',
	'assets/Madeleine.js/src/lib/stats.js',
	'assets/Madeleine.js/src/lib/detector.js',
	'assets/Madeleine.js/src/lib/three.min.js',
	'assets/Madeleine.js/src/Madeleine.js'
);
include_once PUBLIC_FILES . '/modules/header.php';
include_once PUBLIC_FILES . '/modules/newHandoutModal.php';
include_once PUBLIC_FILES . '/modules/employee.php';
include_once PUBLIC_FILES . '/modules/renderBrowse.php';

$usersDao = new UsersDao($dbConn, $logger);

$user = $usersDao->getUserByID($_SESSION['userID']);

if ($user){
	$uId = $user->getUserID();
	$uFirstName = $user->getFirstName();
	$uLastName = $user->getLastName();
	$uPhone = $user->getPhone();
	$uEmail = $user->getEmail();
	$uOnid = $user->getOnid();
	$uAccessLevel = $user->getAccessLevelID()->getName();
} else {
	echo "<h1>You are not in db. You shoudl never have seen this.</h1>";
	exit();
}

$userDao = new UsersDao($dbConn, $logger);
$boxDao = new BoxDao($dbConn, $logger);

$uID = $_SESSION['userID'];

$tekBoxHTML = '';
$boxes = $boxDao->getBoxByUser($uId);
$tekBoxHTML .= "<div class='card col-3' style='padding-top:1em;padding-bottom:1em;margin:1em;'>
					<h5 class'card-title'>TekBoxs</h5>
					<div class='card-body'>";
if (count($boxes) > 0){
	foreach ($boxes AS $b){
		$tekBoxHTML .= "<div class='row'><div class='col-9'>TekBox #: " . $b->getNumber() . "<BR>";
		$tekBoxHTML .= "Filled: " .date("l, M/d",strtotime($b->getFillDate())). "<BR>";
		
		if ($b->getLocked() == 0){
			$tekBoxHTML .= "Status: <span id='status".$b->getBoxKey()."'>Unlocked</span></div>";
			$tekBoxHTML .= "<div class='col-3'><button id='tekboxButton".$b->getBoxKey()."' class='btn btn-danger' onclick='lock(\"$uId\", \"".$b->getBoxKey()."\")'>Lock?</button></div></div>";
		} else {
			$tekBoxHTML .= "Status: <span id='status".$b->getBoxKey()."'>Locked</span></div>";
			$tekBoxHTML .= "<div class='col-3'><button id='tekboxButton".$b->getBoxKey()."' class='btn btn-success' onclick='unlock(\"$uId\", \"".$b->getBoxKey()."\")'>Unlock?</button></div></div>";	
		}
	}
	$tekBoxHTML .= "</div>
					</div>";
} else {
	$tekBoxHTML .= "You do not have any items in a TekBox.</div>
					</div>";
}


?>
<br><br><br><br>
<div class="alert">
<h3>TekBox Pick-Up System</h3>
<div class="col-7">
<p class="lead mb-0">The TekBox system allows for students, faculty, and staff with OSU ONID login abilities to pick up items via a no contact modality. After an order has been placed or a laser cut or 3D print completes, it can be placed into a TekBox locker. These lockers are outside of the KEC1110 room in the Kelley Engineering Center. Users may then digitally unlock the locker using this inerface. On the physical TekBox, pressing the front button will cause the locker to query the server for the state of the locker (locked or unlocked) and respond accordingly.
</p></div>
</div>

<?php echo $tekBoxHTML; ?>

<script defer type="text/javascript">
function lock(uid, id){
	
	let content = {
		action: 'lock',
		boxId: id,
		uId: uid
	}
	
	api.post('/boxes.php', content).then(res => {
		snackbar(res.message, 'Box Locked');
		$('#tekboxButton'+id).hide();
		$('#status'+id).html('Locked');
	}).catch(err => {
		snackbar(err.message, 'error');
	});
}

function unlock(uid, id){
	
	let content = {
		action: 'unlock',
		boxId: id,
		uId: uid
	}
	
	api.post('/boxes.php', content).then(res => {
		snackbar(res.message, 'Box Unlocked');
		$('#tekboxButton'+id).hide();
		$('#status'+id).html('Unlocked');
	}).catch(err => {
		snackbar(err.message, 'error');
	});
}
</script>


<?php include_once PUBLIC_FILES . '/modules/footer.php'; ?>
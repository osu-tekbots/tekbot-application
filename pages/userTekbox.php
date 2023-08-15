<?php
include_once '../bootstrap.php';

use DataAccess\UsersDao;
use DataAccess\BoxDao;
use Util\Security;

session_start();

include_once PUBLIC_FILES . '/lib/shared/authorize.php';

allowIf(verifyPermissions(['user', 'employee']), $configManager->getBaseUrl() . 'pages/login.php');

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
$tekBoxHTML .= "<div style='padding-top:1em;padding-bottom:1em;margin:1em;'>";
if (count($boxes) > 0){
	
	foreach ($boxes AS $b){
		$tekBoxHTML .= "<div class='alert border rounded'>TekBox #: " . $b->getNumber() . "<BR>";
		$tekBoxHTML .= "Filled: " .date("l, M/d",strtotime($b->getFillDate())). "<BR>";
		$tekBoxHTML .= "Contents: " .$b->getContents(). "<BR>";
		
		if ($b->getLocked() == 0){
			$tekBoxHTML .= "Status: <span id='status".$b->getBoxKey()."'>Unlocked</span>";
			$tekBoxHTML .= "<button style='position:absolute; top:1em; right:1em;' id='tekboxButton".$b->getBoxKey()."' class='btn btn-danger btn-lg' onclick='lock(\"$uId\", \"".$b->getBoxKey()."\")'>Lock?</button></div>";
		} else {
			$tekBoxHTML .= "Status: <span id='status".$b->getBoxKey()."'>Locked</span>";
			$tekBoxHTML .= "<button style='position:absolute; top:1em; right:1em;' id='tekboxButton".$b->getBoxKey()."' class='btn btn-success btn-lg' onclick='unlock(\"$uId\", \"".$b->getBoxKey()."\")'>Unlock?</button></div>";	
		}
	}
	$tekBoxHTML .= "</div>";
} else {
	$tekBoxHTML .= "You do not have any items in a TekBox.</div>";
}


?>
<br><br>
<div class="alert">
	<h1>TekBox Pick-Up System</h1>
	<div class="row">
		<div class="col-7">
		<p class="lead mb-0">The TekBox system allows for students, faculty, and staff with OSU ONID login abilities to pick up items via a no contact modality. After an order has been placed or a laser cut or 3D print completes, it can be placed into a TekBox locker. These lockers are outside of the KEC1110 room in the Kelley Engineering Center. Users may then digitally unlock the locker using this inerface. On the physical TekBox, pressing the front button will cause the locker to query the server for the state of the locker (locked or unlocked) and respond accordingly.</p>
		
		<?php echo $tekBoxHTML;?>
		</div>
		<div class="col-5"><img class="img-fluid rounded" src="./assets/img/rect2.png">
		</div>
	</div>
</div>

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
	
	if (confirm("This will unlock your TekBox and anyone walking by the box can open it. You should wait to do this until you are in front of the box. Is it OK to unlock?")){
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
}
</script>


<?php include_once PUBLIC_FILES . '/modules/footer.php'; ?>
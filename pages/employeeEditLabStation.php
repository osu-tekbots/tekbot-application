<?php
include_once '../bootstrap.php';
// ini_set('display_errors', 1);
// error_reporting(E_ALL);

use DataAccess\TicketDao;
use DataAccess\LabDao;
use DataAccess\UsersDao;

if (!session_id()) {
    session_start();
}

// Make sure the user is logged in and allowed to be on this page
include_once PUBLIC_FILES . '/lib/shared/authorize.php';

allowIf(verifyPermissions('employee', $logger), 'index.php');


$stationid = $_GET['stationid']; //use to pull correct station

$title = 'Edit Lab Station Equipment';
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

$stationDao = new LabDao($dbConn, $logger);
$users = new UsersDao($dbConn, $logger);

$activeEquipment = $stationDao->getStationEquipment($stationid, 1);
$inactiveEquipment = $stationDao->getStationEquipment($stationid, 0);
$station = $stationDao->getStationById($stationid);

$outputHTML =  "<BR><div class='admin-paper d-flex flex-column'>";

$outputHTML .= '<h3>Edit Lab Equipment for '.$station->getRoom()->getName().' Station '.$station->getName().'</h3><BR>';
//$outputHTML .= '<img src="../../labs/image/map/' . $station->getRoom()->getMap() . '" class="img-responsive" width="400" >';
$outputHTML .= '<h5>Active Equipment</h5><BR>';
$outputHTML .= '<div class="row">
                <div class="col-sm-2"><strong>Type</strong></div>
                <div class="col-sm-2"><strong>Model</strong></div>
                <div class="col-sm-3"><strong>Manual</strong></div>
                <div class="col-sm-2"><strong>Image</strong></div>
                </div><hr>';
foreach($activeEquipment as $ae) {
    $outputHTML .= '<div class="form-row">';
    //<textarea class="form-control" id="activeType'.$ae->getId().'" onchange="updateType('.$ae->getId().');">'.$ae->getType().'</textarea>
	//$outputHTML .= "<div class='col'><input type='text' id='adddescription' class='form-control' placeholder='Part/Kit Description'></div>";
    $outputHTML .= '<div class="col-sm-2"><input id="type'.$ae->getId().'" class="form-control" type="text" value="'.$ae->getType().'" onchange="updateType('.$ae->getId().');"></div>';
    $outputHTML .= '<div class="col-sm-2"><input id="model'.$ae->getId().'" class="form-control" type="text" value="'.$ae->getModel().'" onchange="updateModel('.$ae->getId().');"></div>'; // model
    $outputHTML .= '<div class="col-md-3"><input id="manual'.$ae->getId().'" class="form-control" type="text" value="'.$ae->getManual().'" onchange="updateManual('.$ae->getId().');"></div>'; // manual
    $outputHTML .= '<div class="col-md-3"><img src="images/lab_equipment/'.$ae->getImage().'" class="img-fluid"></div>'; // image
    $outputHTML .= '<div class="d-flex col-sm-2 justify-content-center align-items-center"><button class="btn btn-outline-warning" onclick="updateStatus('.$ae->getId().', 0)">Deactivate</button></div>'; // deactivate
    $outputHTML .= '</div><BR>';
}
if ($inactiveEquipment) {
    $outputHTML .= '<h5>Inactive Equipment</h5><BR>';
    $outputHTML .= '<div class="row">
                <div class="col-sm-2"><strong>Type</strong></div>
                <div class="col-sm-2"><strong>Model</strong></div>
                <div class="col-sm-3"><strong>Manual</strong></div>
                <div class="col-sm-2"><strong>Image</strong></div>
                </div><hr>';
    foreach($inactiveEquipment as $ie) {
        $outputHTML .= '<div class="row">';
        $outputHTML .= '<div class="col-sm-2" style="word-break: break-all">'.$ie->getType().'</div>'; // type
        $outputHTML .= '<div class="col-sm-2" style="word-break: break-all">'.$ie->getModel().'</div>'; // model
        $outputHTML .= '<div class="col-sm-3" style="word-break: break-all"><a href="'.$ie->getManual().'" target="_blank">'.$ie->getManual().'</a></div>'; // manual
        $outputHTML .= '<div class="col-lg-3"><img src="images/lab_equipment/'.$ie->getImage().'" class="img-fluid"></div>'; // image
        $outputHTML .= '<div class="col-sm-1"><button class="btn btn-outline-warning" onclick="updateStatus('.$ie->getId().', 1)">Activate</button></div>'; // activate
        $outputHTML .= '</div><BR>';
    }
}

$outputHTML .= "</div>" // close admin paper div
?>
<script>
    function updateStatus(id, status) {
		let content = {
			action: 'updateEquipmentStatus',
			id: id,
			status: status
		};

		api.post('/labs.php', content).then(res=> {
			snackbar(res.message, 'success');
			setTimeout(() => location.reload(), 2000);
		}).catch(err => {
			snackbar(err.message, 'error');
		});
	}

    function updateType(id) {
		type = document.getElementById('type'+id).value;
		let content = {
			action: 'updateEquipmentType',
			id: id,
			type: type
		};

		api.post('/labs.php', content).then(res=> {
			snackbar(res.message, 'success');
		}).catch(err => {
			snackbar(err.message, 'error');
		});
	}

    function updateModel(id) {
		model = document.getElementById('model'+id).value;
		let content = {
			action: 'updateEquipmentModel',
			id: id,
			model: model
		};

		api.post('/labs.php', content).then(res=> {
			snackbar(res.message, 'success');
		}).catch(err => {
			snackbar(err.message, 'error');
		});
	}

    function updateManual(id) {
		manual = document.getElementById('manual'+id).value;
		let content = {
			action: 'updateEquipmentManual',
			id: id,
			manual: manual
		};

		api.post('/labs.php', content).then(res=> {
			snackbar(res.message, 'success');
		}).catch(err => {
			snackbar(err.message, 'error');
		});
	}


</script>
<div id="page-top"><BR>

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
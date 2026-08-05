<?php
include_once '../bootstrap.php';

use DataAccess\EquipmentTypeDao;
use DataAccess\UsersDao;
use Util\Security;

if (PHP_SESSION_ACTIVE != session_status())
    session_start();

// Make sure the user is logged in and allowed to be on this page
include_once PUBLIC_FILES . '/lib/shared/authorize.php';

allowIf(verifyPermissions('employee', $logger), 'index.php');


$title = 'Employee Inventory List';
$css = array(
	'assets/css/sb-admin.css',
	'assets/css/admin.css',
	'https://cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css'
);
$js = array(
    'https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js'
);

$equipmentDao = new EquipmentTypeDao($dbConn, $logger);
$userDao = new UsersDao($dbConn, $logger);


$items = array_keys($_POST);
if (count($items) > 0) { //Need to render labels
	$labelsHTML = "
		<style>
		body
		{
			font-family: 'Arial' , monospace;
			font-size:6pt;
		}

		header
		{
			display: none;
		}

		footer
		{
			display: none;
		}
		div.printpagelarge{
			page-break-inside: avoid;
			width:8.35in; 
			height:10.04in; 
			padding-left: .35in; 
			padding-right: 0 in; 
			padding-top: .45in; 
			padding-bottom: .48in; 
			float: none;
			/*padding: .5in .3in .5in .2in;*/
			box-sizing: border-box;
		}

		div.printlabellarge{
			width:4 in;
			min-width: 4in;
			height:2in;
			min-height: 2in;
			float:left;
			padding-left:.2 in;
			padding-right:.2 in;
			padding-top:.1 in;
			padding-bottom:.1 in;
			border-style:solid;
			border-color: white;
			box-sizing: border-box;
			font-size:6pt;
			background-color: #ffffff;
		}
		</style>
	";
	
	/*
	 * -DOUBLECHECK- This section creates a page of large labels to print.
	 * If there are 10 items in the page, page is completed, otherwise continues to fill
	 */
	$j = 0;
	$labelsHTML .= "<div class='printpagelarge'>";
	foreach ($items AS $i){
		if ($i != 'labeltype'){
			if ($j == 10){
				$labelsHTML .= '</div><div class="printpagelarge">';
				$j = 0;
			}

			$e = $equipmentDao->getEquipmentByItemID($i);
			
			if (!empty($e->getImages())){
				$imageName = $e->getImages()[0]->getImageID();
				$imagePath = "images/equipment/$imageName";
			} else {
				$imageName = "no-image.png";
				$imagePath = "assets/img/$imageName";
			}
			$equipmentName = $e->getName();
			$parts = nl2br($e->getParts());
			$itemID = $e->getInstances()[0]->getItemID();

			$labelsHTML .= "
				<div class='printlabellarge'>
					<div style='width:1.6in;height:1.8in;float:left;' ><img style='width:100%;height:100%;object-fit:contain;' src='../$imagePath'></div>
					<div style='float:left;width:2.1in;height:1.8in;padding-left:.05in;padding-top:.1in;'>
						<div style='height:1.5in;'><span style='font-size:2em;'>$equipmentName<BR></span>Contents:<BR>$parts</div>
						<span style='height:.2in; font-size: 2em;'>Unit $itemID</span>
					</div>
				</div>
			";
			$j++;
		}
	}
	$labelsHTML .= "</div>";
	
	
	echo $labelsHTML;
	echo "<script>alert('When printing, you must select \'No Margin\' for correct scaling.');</script>";
	exit();
}


include_once PUBLIC_FILES . '/modules/header.php';
include_once PUBLIC_FILES . '/modules/employee.php';


$equipment = $equipmentDao->getEmployeeEquipment();
				
/* 
 * Creates data table that populates from the equipment DAO
 */
$tableBodyHTML = "";
foreach ($equipment as $e) {
	$name = Security::HtmlEntitiesEncode($e->getName());
	$description = Security::HtmlEntitiesEncode($e->getDescription());
	
	foreach ($e->getInstances() as $i) {
		$id = $i->getItemID();
		$location = $i->getLocation();

		$tableBodyHTML .= "<tr>
			<td><input type='checkbox' id='checkbox$id' name='$id' class='select-button'></td>
			<td>$name</td>
			<td>$id</td>
			<td>$description</td>
			<td>$location</td>
		</tr>";
	}
}

?>

<br/>
<div id="page-top">
	<div id="wrapper">

	<?php renderEmployeeSidebar(); ?>

    <div class="admin-content" id="content-wrapper">
        <div class="container-fluid">
			<div class="admin-paper">
				<form method="post" target="_blank" id="mainform">
					<div class="form-row">
						<div class="form-group col-sm-3">
							<button class="btn btn-info" type="submit" form="mainform">Get Selected Labels</button>
						</div>
					</div>
					<table class='table' id='EquipmentTable'>
						<thead>
							<tr>
								<th>
									<input type="checkbox" id="selectAll" onclick="handleSelectAllClick(this)">
									<label for="selectAll">Select&nbsp;All</label>
								</th>
								<th>Name</th>
								<th>Unit ID</th>
								<th>Description</th>
								<th>Location</th>
							</tr>
						</thead>
						<tbody>
							<?= $tableBodyHTML ?>
						</tbody>
					</table>
				</form>
			</div>
        </div>
    </div>


<script>
	$('#EquipmentTable').DataTable({
		autoWidth: false, // If not disabled, DataTable sizing breaks if `description` doesn't wrap
		scrollX: true,
		paging: false,
		order: [[1, 'asc'], [2, 'asc']],
		columns: [
			{ orderable: false },
			null,
			null,
			null,
			null
		]
	});

	function handleSelectAllClick(selectAll) {
		const elements = document.getElementsByClassName('select-button');
		Array.from(elements).forEach(element => {
			element.checked = selectAll.checked;
		});
	}
</script>

<?php include_once PUBLIC_FILES . '/modules/footer.php'; ?>

<?php
include_once '../bootstrap.php';

use DataAccess\InventoryDao;
use DataAccess\UsersDao;
use Util\Security;

if (PHP_SESSION_ACTIVE != session_status())
    session_start();

// Make sure the user is logged in and allowed to be on this page
include_once PUBLIC_FILES . '/lib/shared/authorize.php';


function studentPrice($price){
	$markup = .15;
	if ($price == 0)
		return ('$0.00');
		
	$price = (($price * $markup) > .1 ? (1+$markup) * $price : $price + .1) ;
	
	if ( (1 / $price ) < 1 )
		return ('$' . ceil($price) . '.00');
	else if (intval((1 / $price )) == 1 )
		return ('$1.00');
	else if ((1 / $price ) < 2 )
		return ('2 for $1');
	else if ((1 / $price ) < 3 )
		return ('3 for $1');
	else if ((1 / $price ) < 4 )
		return ('4 for $1');
	else if ((1 / $price ) < 5 )
		return ('5 for $1');
	else
		return ('Free for one / ' . number_format($price,2) . ' ea.');
return $price;
}

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


if (isset($_REQUEST['location'])){
	if ($_REQUEST['location'] == 'all')
		unset($_SESSION['location']);
	else
		$_SESSION['location'] = $_REQUEST['location'];
	}

//TODO: We need to add a selector to this page to set the location. This should be handled similar to how we handle
// the type selection on the main inventory page.
//$_SESSION['location'] = 'A1';

$items = array_keys($_POST);
$labelsHTML = "";
if (count($items) > 0) { //Need to render labels
	$labelsHTML .= "
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
		padding-left: .15in; 
		padding-right: 0 in; 
		padding-top: .35in; 
		padding-bottom: .48in; 
		float: none;
		/*padding: .5in .3in .5in .2in;*/
		box-sizing: border-box;
	}

	div.printpagesmall{
		page-break-inside: avoid;
		width:8.35in; 
		height:10.04in; 
		padding-left: .3in; 
		padding-right: 0in; 
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
		padding-left:.1 in;
		padding-right:.1 in;
		padding-top:.1 in;
		padding-bottom:.1 in;
		border-style:solid;
		border-color: white;
		box-sizing: border-box;
		font-size:6pt;
		background-color: #ffffff;
	}

	div.printlabelsmall{
		width:1.95in;
		min-width: 1.95in;
		height:1.25in;
		min-height: 1.25in;
		float:left;
		padding-left:.1 in;
		padding-right:.3 in;
		padding-top:.1 in;
		padding-bottom:.1 in;
		margin-left:.1in
		margin-right:.1in
		border-style:solid;
		border-color: white;
		box-sizing: border-box;
		font-size:6pt;
		background-color: #ffffff;
	}

	</style>";
	
	
	$inventoryDao = new InventoryDao($dbConn, $logger);
	
	if ($_POST['labeltype'] == 1) { // Larger Labels
		$j = 0;
		$labelsHTML .= "<div class='printpagelarge'>";
		foreach ($items AS $i){
			if ($i != 'labeltype'){
				$p = $inventoryDao->getPartByStocknumber($i);
				if ($j == 10){
					$labelsHTML .= '</div><div class="printpagelarge">';
					$j=0;
				}
				
				
				
				$labelsHTML .= '
				<div class="printlabellarge">
					<div style="float:left;width:45%;min-height:130px;margin-top:.5em;"><BR>
						<img style="height:1.25in;display:block;margin-left: auto;margin-right: auto;" src="createqr.php?data=https://eecs.engineering.oregonstate.edu/education/store/Inventory/mobile.php?stocknumber=' . $i . '">
					</div>
					<div style="float:right;width:55%;min-height:130px;margin-top:.5em;">
						<BR>
						<img src="../../../inventory_images/'.$p->getImage().'" style="max-width:1.5in;height:10em;">
						<div style="float:right;bottom: 0;">
							<span style="font-size:5em;margin-right:1em;margin-top:1em;">' . $p->getLocation() . '</span>
						</div>
						<BR>
						<BR>
						<BR>
						<div style="position: relative;">
							<div style="float:left;">
								<span style="font-size:1.5em">' . $i . '</span>
							</div>
						</div>
					</div>
					<div style="padding-top:1em;margin-top:130px;">
						<span style="font-size:2em;margin-left:2em;">' . substr($p->getType() . ': ' . $p->getName(),0,37) . '</span>
					</div>
				</div>';
				$j++;
			}
		}
		$labelsHTML .= "</div>";
	} else if ($_POST['labeltype'] == 2) { //Small Labels
		$j = 0;
		$labelsHTML .= '<div class="printpagesmall">';
		foreach ($items AS $i){
			if ($i != 'labeltype'){
				$p = $inventoryDao->getPartByStocknumber($i);
				if ($j == 32){
					$labelsHTML .= '</div><div class="printpagesmall">';
					$j=0;
				}		

				$j++;
				$labelsHTML .= "<div class='printlabelsmall'>
						<div style='float:left;width:55%;'>
							<img style='height:1in;display:block;margin-left: auto;margin-right: auto;' src='createqr.php?data=https://eecs.engineering.oregonstate.edu/education/store/Inventory/mobile.php?stocknumber=" . $i . "'>
						</div>
						<div style='float:left;width:40%;'>
							<BR>
							<BR>" . $p->getType() . 
							"<BR>" . substr($p->getName(),0,30) . 
							"<BR>" . $i . 
							"<BR><span style='font-size:2em;'>" . $p->getLocation() . "</span>
						</div>
					</div>";
				
			}
		}
		$labelsHTML .= "</div>";
	} else if ($_POST['labeltype'] == 3) { //Ordering Labels
		$j = 0;
		$labelsHTML .= "<div class='printpagelarge'>";
		foreach ($items AS $i){
			if ($i != 'labeltype'){
				$p = $inventoryDao->getPartByStocknumber($i);
				if ($j == 10){
					$labelsHTML .= '</div><div class="printpagelarge">';
					$j=0;
				}
				$labelsHTML .= '
					<div class="printlabellarge">
						<div>
							<div style="float:left;width:50%;min-height:130px;margin-top:1em;">
								<BR>
								'.($p->getTouchnetId() != '' ? '<img style="height:1.4in;display:block;margin-left: auto;margin-right: auto;margin-top:-.2in" src="createqr.php?data=https://secure.touchnet.net/C20159_ustores/web/product_detail.jsp?PRODUCTID=' . $p->getTouchnetId() . '">' : '').'
							</div>
							<div style="float:right;width:50%;min-height:130px;margin-top:1em;">
								<img src="../../../inventory_images/'.$p->getImage().'" style="max-width:1.5in;height:13em;">
							</div>
						</div>
						<div style="padding-top:1em;margin-top:110px;">
							<span style="font-size:1.5em;margin-left:1em;">' . substr($p->getName(),0,50) . '</span>
						</div>
					</div>';
				$j++;
			}
		}
		$labelsHTML .= "</div>";
	} else if ($_POST['labeltype'] == 4) { //Kit Labels
		
		
		foreach ($items AS $i){
			if ($i != 'labeltype'){
				$labelsHTML .= '<div class="printpagesmall">';
				$p = $inventoryDao->getPartByStocknumber($i);
				for($j=0;$j<32;$j++){	
					$labelsHTML .= "<div class='printlabelsmall'>
										<div style='margin-top:.5em;margin-right:auto;margin-left:auto;width:70%;text-align: center;'>
											<span style='font-size:2em;'><b>" . $p->getName() . 
											"</b><BR>" . date('m-d-Y',time()) ."</span>
										</div>
									</div>";
				}
				$labelsHTML .= "</div>";
			}
		}
		
	} 		
	echo $labelsHTML;
	echo "<script>alert('When printing, you must select \'No Margin\' for correct scaling.');</script>";
	exit();
}

include_once PUBLIC_FILES . '/modules/header.php';
include_once PUBLIC_FILES . '/modules/employee.php';
//Removed 12-13-2021 by Don
//include_once PUBLIC_FILES . '/modules/renderBrowse.php';

$inventoryDao = new InventoryDao($dbConn, $logger);
$userDao = new UsersDao($dbConn, $logger);

if (isset($_SESSION['location']))
	$parts = $inventoryDao->getInventoryByLocation($_SESSION['location']);
else
	$parts = $inventoryDao->getInventory();


$options = "";
$options .= "<div class='form-row'>
				<div class='form-group col-sm-3'>
					<select name='labeltype' id='labeltype' class='form-control'>
						<option value='1'>Large Inventory Label</option>
						<option value='2'>Small Inventory Label</option>
						<option value='3'>Touchnet Ordering Label</option>
						<option value='4'>Kit Labels</option>
					</select>
				</div>
				<div class='form-group col-sm-1'>
					<button class='btn btn-info' type='submit' form='mainform'>Get Selected Labels</button>
				</div>
			</div>";

$formHTML = "<form method='post' target='_blank' id='mainform'>
				$options
				<table class='table' id='InventoryTable'>
                <caption>Current Inventory</caption>
                <thead>
                    <tr>
						<th></th>
                        <th>Type</th>
                        <th>Description</th>
						<th>Touchnet ID</th>
						<th>Last Updated</th>
                        <th>Location</th>
                    </tr>
                </thead>
                <tbody>";
				
foreach ($parts as $p) {
	$stocknumber = $p->getStocknumber();
	$type = $p->getType();
	$description = $p->getName();
	$lastUpdated = date('Y-m-d', strtotime($p->getLastCounted()));
	$location = $p->getLocation();
	$touchnetId = $p->getTouchnetId();
	
	if ($p->getArchive() == 0)
		$formHTML .= "<tr>
		<td><input type='checkbox' id='checkbox$stocknumber' name='$stocknumber'></td>
		<td>$type</td>
		<td>Stock: $stocknumber<BR>$description</td>
		<td>$touchnetId</td>
		<td>$lastUpdated</td>
		<td>$location</td>
		</tr>";
}

$formHTML .= "</tbody>
			</table>
			</form>";

?>
<script type='text/javascript'>
//We need to add a select all function to this page to check all displayed boxes.
</script>

<br/>
<div id="page-top">

	<div id="wrapper">

	<?php 
		// Located inside /modules/employee.php
		renderEmployeeSidebar();
	?>

    <div class="admin-content" id="content-wrapper">
        <div class="container-fluid">
			<div class='admin-paper'>
            <?php 
				echo $formHTML;   
				echo $labelsHTML;
            ?>                
			</div>
        </div>
    </div>

<script>

$('#InventoryTable').DataTable({
		"autoWidth": true,
		'scrollX':false, 
		'paging':false, 
		'order':[[1, 'asc'], [2, 'asc']],
		"columns": [
			{ "orderable": false },
			null,
			null,
			null,
			null,
			null
		  ]
		});


</script>

<?php 
include_once PUBLIC_FILES . '/modules/footer.php' ; 
?>
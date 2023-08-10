<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

//require_once ('../../../virtualmakerspace/includes/phpfunctions.php');
//include_once ('../../../virtualmakerspace/phpCAS-master/CAS.php');
require_once('../../../includes/config.php');

$mysqli = new mysqli($server, $user, $password, $databaseName,'3307');
if ($mysqli->connect_errno) {
	printf("Connection Failed, <B>Error: ".mysql_error()."</B><p>Contact <a HREF=\"mailto::support@engr.orst.edu\">COE Support</A></p>Connect failed: %s\n</BODY></HTML>", $mysqli->connect_error);
	exit();
}
	
if (isset($_REQUEST['printerid']) && $_REQUEST['printerid'] != 0){
	$printerid = mysqli_real_escape_string($mysqli, check_input($_REQUEST['printerid']));
} else {
	echo '<h2>You need to select a printer before seeing material options for that printer.</h2>';
	exit();
}
if (isset($_REQUEST['materialid']) && $_REQUEST['materialid'] != 0){
	$materialid = mysqli_real_escape_string($mysqli, check_input($_REQUEST['materialid']));
} else {
	$materialid = 0; 
}

$query = "SELECT * FROM printers WHERE id = $printerid";
//echo $query . '<BR>';
$result = $mysqli->query($query);
$row = $result->fetch_assoc();
if ($row['jobtypeid'] == 0 ){ //3D Printing
$materialinfo = '';
$materialoptions = '';
$query = "SELECT * FROM printermaterials WHERE printerid = $printerid AND active = 1 AND deleted = 0 ORDER BY name ASC";
//echo $query . '<BR>';
$result = $mysqli->query($query);
while ($row = $result->fetch_assoc()){
	$materialoptions .= '<option value="' . $row['id'] . '" ' . ($row['id'] == $materialid ? 'selected' : '') . '>'. $row['name'] . '</option>';
	if ($row['id'] == $materialid){
		if ($row['internalcostpergram'] == 0)
			$materialinfo = 'Price per cubic inch: Internal: $' . number_format($row['internalcostpercuin'],2) . ' External: $' . number_format($row['externalcostpercuin'],2)  ;
		else
			$materialinfo = 'Price per gram: $' .  number_format($row['internalcostpergram'],2) . ' External: $' . number_format($row['externalcostpergram'],2)  ;
		$materialinfo .= '<BR>Setup Fee per job: Internal: $' .  number_format($row['internalsetupperprint'],2) . ' External: $' .  number_format($row['externalsetupperprint'],2);
		$materialinfo .= '<BR><p>' . html_entity_decode($row['information']) . '</p>';
		}
	}

echo "<div class='row'><div class='col-sm-4 col-sm-offset-2'><b>Select Material:</b></div><div class='col-sm-4'><select required class='fi form-control' id='materialid' name='materialid' onchange='selectmaterial()'><option value=''>Select a Material</option>'. $materialoptions  .'</select>
	<BR>$materialinfo</div></div>";
echo "<div class='row'><div class='col-sm-4 col-sm-offset-2'><b>Select Quantity:</b></div>
	<div class='col-sm-4'><select required class='fi form-control' id='quantity' name='quantity'><option value=''>---</option><option value='1'>1</option><option value='2'>2</option><option value='3'>3</option><option value='4'>4</option><option value='5'>5</option></select></div></div>";
echo "<div class='row'><div class='col-sm-4 col-sm-offset-2'><b>Select Units for STL File:</b></div>
	<div class='col-sm-4'><select required class='fi form-control' id='units' name='units'><option value=''>---</option><option value='mm'>Millimeters</option><option value='in'>Inches</option></select></div></div>";
	
echo "
<div class='row form-group'><div class='col-sm-4 col-sm-offset-2'><b>Notes</b><BR>Any special instructions or deadlines that you have should be entered here</div>
<div class='col-sm-4'><textarea class='fi form-control' id='notes' name='notes' rows='4' cols='50'></textarea>
</div></div>
<div class='row form-group'><div class='col-sm-4 col-sm-offset-2'><b>ATTACHMENT FILE</b> <input class='fi form-control' type='file' size='40' name='upfile' accept='.stl,.STL,.gz,.GZ,.cmb,.CMB' required><br>
<u>NOTE</u> We only accept files of the 'Stereo Lithography Type' (.stl) and the attachment file size must smaller than <b>10Mb</b>
</div><div class='col-sm-4'><button id='addjobbutton'>Submit Job</button>  <input class=fb type=reset>
</div></div>
";
} else { //Laser Cutting
$materialinfo = '';
$materialoptions = '';
$query = "SELECT * FROM lasermaterials WHERE printerid = $printerid AND active = 1 AND deleted = 0 ORDER BY name ASC";
//echo $query . '<BR>';
$result = $mysqli->query($query);
while ($row = $result->fetch_assoc()){
	$materialoptions .= '<option value="' . $row['id'] . '" ' . ($row['id'] == $materialid ? 'selected' : '') . '>'. $row['name'] . '</option>';
	if ($row['id'] == $materialid){
		$materialinfo = 'Price per sheet: Internal: $' . number_format($row['internalcost'],2) . ' External: $' . number_format($row['externalcost'],2)  ;
		$materialinfo .= '<BR>Setup Fee per job: Internal: $' .  number_format($row['internalsetup'],2) . ' External: $' .  number_format($row['externalsetup'],2);
		$materialinfo .= '<BR><p>' . html_entity_decode($row['information']) . '</p>';
		}
	}

echo "<div class='row'><div class='col-sm-4 col-sm-offset-2'><b>Select Material:</b></div><div class='col-sm-4'><select required class='fi form-control' id='materialid' name='materialid' onchange='selectmaterial()'><option value=''>Select a Material</option>'. $materialoptions  .'</select>
	<BR>$materialinfo</div></div>";
echo "<div class='row'><div class='col-sm-4 col-sm-offset-2'><b>Select Quantity:</b></div>
	<div class='col-sm-4'><select required class='fi form-control' id='quantity' name='quantity'><option value=''>---</option><option value='1'>1</option><option value='2'>2</option><option value='3'>3</option><option value='4'>4</option><option value='5'>5</option></select></div></div>";
echo "<input type='hidden' id='units' name='units' value='mm'>";
	
echo "
<div class='row form-group'><div class='col-sm-4 col-sm-offset-2'><b>Notes</b><BR>Any special instructions or deadlines that you have should be entered here</div>
<div class='col-sm-4'><textarea class='fi form-control' id='notes' name='notes' rows='4' cols='50'></textarea>
</div></div>
<div class='row form-group'><div class='col-sm-4 col-sm-offset-2'><b>ATTACHMENT FILE</b> <input class='fi form-control' type='file' size='40' name='upfile' accept='.dxf,.DXF,.gz,.GZ,.zip,.ZIP' required><br>
<u>NOTE</u> We only accept files of the 'Drawing Exchange Format' (.dxf) and the attachment file size must smaller than <b>10Mb</b>
</div><div class='col-sm-4'><button>Submit Job</button>  <input class=fb type=reset>
</div></div>
";
}
?>





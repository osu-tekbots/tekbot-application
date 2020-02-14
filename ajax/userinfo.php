<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

//require_once ('../../../virtualmakerspace/includes/phpfunctions.php');
//include_once ('../../../virtualmakerspace/phpCAS-master/CAS.php');
require_once('../../../includes/config.php');

session_start();
	
$mysqli = new mysqli($server, $user, $password, $databaseName, 3307);
if ($mysqli->connect_errno) {
	printf("Connection Failed, <B>Error: ".mysql_error()."</B><p>Contact <a HREF=\"mailto::support@engr.orst.edu\">COE Support</A></p>Connect failed: %s\n</BODY></HTML>", $mysqli->connect_error);
	exit();
}
	
	if (isset($_REQUEST['payment_method']) && $_REQUEST['payment_method'] != 0){
		$payment_method = mysqli_real_escape_string($mysqli, check_input($_REQUEST['payment_method']));
	} else {
		echo "<div class='row'><div class='col-sm-8 col-sm-offset-2'><h2>You need to select a payment method</h2></div></div>";
		exit();
	}

$query = "SELECT * FROM paymenttypes WHERE id = $payment_method";
//echo $query;
$result = $mysqli->query($query);
$paymentrow = $result->fetch_assoc();
echo "<div class='row'><div class='col-sm-8 col-sm-offset-2'><strong>" . $paymentrow['info'] . "</strong></div></div>";
echo "<div class='row' style='padding-top:1%;'><div class='col-sm-4 col-sm-offset-2'><b>Your Email:</b> (Must be valid to confirm order)</div>";
if (isset($_SESSION['phpCAS']['user']))
	echo "<div class='col-sm-4'><input class='fi form-control' type='email' id='email' name='email' value='".$_SESSION['phpCAS']['user']."@oregonstate.edu' readonly='readonly'>
	</div></div>
	<div class='row' style='padding-top:1%;'><div class='col-sm-4 col-sm-offset-2'><b>First Name:</b></div>
	<div class='col-sm-4'><input class='fi form-control' type=text size=55 id='firstname' name='firstname' value='".$_SESSION['phpCAS']['attributes']['firstname']."' readonly='readonly'></div></div>
	<div class='row' style='padding-top:1%;'><div class='col-sm-4 col-sm-offset-2'><b>Last Name:</b></div>
	<div class='col-sm-4'><input class='fi form-control' type=text size=55 id='lastname' name='lastname' value='".$_SESSION['phpCAS']['attributes']['lastname']."' readonly='readonly'></div></div>";
else
	echo "<div class='col-sm-4'><input class='fi form-control' type='email' id='email' pattern='[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}$' name='email'></div></div><div class='row' style='padding-top:1%;'><div class='col-sm-4 col-sm-offset-2'><b>First Name:</b></div>
	<div class='col-sm-4'><input class='fi form-control' required type=text size=55 id='firstname' name='firstname'></div></div>
	<div class='row' style='padding-top:1%;'><div class='col-sm-4 col-sm-offset-2'><b>Last Name:</b></div>
	<div class='col-sm-4'><input class='fi form-control' required type=text size=55 id='lastname' name='lastname'></div></div>";

if ($paymentrow['name'] == 'University Account')
	echo "<div class='row' style='padding-top:1%;'><div class='col-sm-4 col-sm-offset-2'><b>Account Number:</b></div>
	<div class='col-sm-4'><input class='fi form-control' type=text size=55 id='account' required name='account'></div></div>";
else 
	echo "<input type=hidden id='account' required name='account' value='".$paymentrow['name']."'>";

?>

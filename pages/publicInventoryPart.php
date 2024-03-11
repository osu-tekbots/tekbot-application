<?php
include_once '../bootstrap.php';

use DataAccess\InventoryDao;
use DataAccess\UsersDao;
use Util\Security;

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL); 

if (!session_id()) {
    session_start();
}

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
	else 
		return ('Free for one / 10 for $1');
return $price;
}



$title = 'Part Details';
$css = array(
	'assets/css/sb-admin.css',
	'assets/css/admin.css',
	'https://cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css'
);
$js = array(
    'https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js'
);

include_once PUBLIC_FILES . '/modules/header.php';


if (isset($_REQUEST['stocknumber']) && $_REQUEST['stocknumber'] != ''){
	$stocknumber = $_REQUEST['stocknumber'];
}

$inventoryDao = new InventoryDao($dbConn, $logger);
$part = $inventoryDao->getPartByStocknumber($stocknumber);

$stocknumber = $part->getStocknumber();//
$description = $part->getName();//
$lastPrice = $part->getLastPrice();//
$location = $part->getLocation();//
$quantity = $part->getQuantity();//
$image = $part->getImage();
$datasheet = $part->getDatasheet();
$touchnetId = $part->getTouchnetId();//
$originalImage = $part->getOriginalImage();
$lastSupplier = $part->getLastSupplier();//
$type = $part->getType();//
$manufacturer = $part->getManufacturer();//
$manufacturerNumber = $part->getManufacturerNumber();//
$partMargin = $part->getPartMargin();//
$stocked = $part->getStocked();//
$archive = $part->getArchive();//
$marketPrice = $part->getMarketPrice();//
$publicdesc = $part->getPublicDescription();
$lastUpdated = $part->getLastUpdated();
$lastCounted = $part->getLastCounted();



$contents = $inventoryDao->getKitContentsByStocknumber($stocknumber);// Get the list of stocknumbers/quantity of each in the kit
$contentsHTML = '';
if (count($contents) > 0){
	$contentsHTML = "<h4>Contents as of ". date("m-d-y",time()) . "</h4><table>
                <thead>
                    <tr>
						<th>Quantity<BR>per Kit</th>
						<th>Type</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>";
/*	foreach ($contents AS $row){
		$p = $inventoryDao->getPartByStocknumber($row['ChildID']);
		
		$contentsHTML .= "<tr><td>".$row['Quantity']."</td><td>".$p->getType()."</td>
		<td><a href='./pages/publicInventoryPart.php?stocknumber=".$row['ChildID']."'>".$p->getName()."</a></td></tr>";	
	}*/
	foreach ($contents AS $key => $value){
		$p = $inventoryDao->getPartByStocknumber($key);
		
		$contentsHTML .= "<tr><td>$value</td><td>".$p->getType()."</td>
		<td><a href='./pages/publicInventoryPart.php?stocknumber=$key'>".$p->getName()."</a></td></tr>";	
	}
	$contentsHTML .= "</tbody></table>";
}

$partHTML = '';

$partHTML .= "<h3>".Security::HtmlEntitiesEncode($description)."</h3>
			<form>
			<div style='padding-left:4px;padding-right:4px;margin-top:4px;margin-bottom:4px;'>
				<div class='form-row'>
					<div class='form-group col-sm-3'>
					<label style='font-weight:bold;'>Type</label>$type</div>
					<div class='form-group col-sm-3'>
					<label style='font-weight:bold;'>Stock Number:</label>$stocknumber</div>
					".($touchnetId != '' ? "<div class='form-group col-sm-3'>
					<a href='https://secure.touchnet.net/C20159_ustores/web/product_detail.jsp?PRODUCTID=$touchnetId'><button type='button' class='btn btn-info'>Buy on Marketplace</button></a></div>" : '' )."
				</div>
				<div class='form-row'>
					<div class='form-group col-sm-3'><label style='font-weight:bold;'>Last Supplier</label>".(Security::HtmlEntitiesEncode($lastSupplier) != '' ? Security::HtmlEntitiesEncode($lastSupplier) : '<i>Not Present</i>' )."</div>
					<div class='form-group col-sm-3'><label style='font-weight:bold;'>Manufacturer</label>".(Security::HtmlEntitiesEncode($manufacturer) != '' ? Security::HtmlEntitiesEncode($manufacturer) : '<i>Not Present</i>' )."</div>
					<div class='form-group col-sm-3'><label style='font-weight:bold;'>Manufacturer Number</label>".(Security::HtmlEntitiesEncode($manufacturerNumber) != '' ? Security::HtmlEntitiesEncode($manufacturerNumber) : '<i>Not Present</i>' )."</div>
				</div>

				<div class='form-row'>
					<div class='col-sm-6'>
						<div class='form-group col-sm-12'><label style='font-weight:bold;'>Student Price</label>".($marketPrice == 0 ? studentPrice($lastPrice) : "$".number_format($marketPrice,2))."</div>
						<div class='form-group col-sm-12'><label style='font-weight:bold;'>In Stock Quantity</label>$quantity</div>
						<div class='form-group col-sm-12'><label style='font-weight:bold;'>Datasheet</label>".($datasheet != '' ? "<a href='../../inventory_datasheets/$datasheet' target='_blank'>$datasheet</a>" : '<i>Not Present</i>' )."</div>
						<div class='form-group col-sm-12'><label style='font-weight:bold;'>Public Description</label>".nl2br($publicdesc ?? '')."</div>
					</div>		
					<div class='col-sm-6'>
						<div class='form-group'><label style='font-weight:bold;'>Image <a href='../../inventory_images/".($image != '' ? $image : 'noimage.jpg')."' target='_blank'>".($image != '' ? $image : '')."</a></label><img src='../../inventory_images/".($image != '' ? $image : 'noimage.jpg')."' class='img-fluid rounded-lg' id='partImage'></div>
					</div>
				</div>
				$contentsHTML
			</div>
			</form>";

?>
<script type='text/javascript'>


</script>

<br/>
<div id="page-top">

	<div id="wrapper">
    <div class="admin-content" id="content-wrapper">
        <div class="container-fluid">
			<div class='admin-paper'>
			<a href='./pages/publicInventory.php'>Back to Inventory List</a>
			<?php echo $partHTML;?>

			</div>
        </div>
    </div>

<?php 
include_once PUBLIC_FILES . '/modules/footer.php' ; 
?>
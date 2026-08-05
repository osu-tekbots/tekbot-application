<?php
include_once '../bootstrap.php';

use DataAccess\EquipmentCheckoutDao;
use DataAccess\EquipmentTypeDao;

include PUBLIC_FILES . '/lib/shared/authorize.php';

$eID = null;
if(isset($_GET['id']))
	$eID = $_GET['id'];

allowIf(isset($_GET['id']));

$title = 'Single Equipment';
$css = array(
    'assets/css/slideshow.css'
);
$js = array(
    array(
        'defer' => 'true',
        'src' => 'assets/js/slideshow.js'
    )
);
    
include_once PUBLIC_FILES . '/modules/employee.php';
include_once PUBLIC_FILES . '/modules/header.php';


$isEmployee = verifyPermissions('employee', $logger);
$isLoggedIn = verifyPermissions(['user', 'employee'], $logger);

$equipmentDao = new EquipmentTypeDao($dbConn, $logger);
$checkoutDao = new EquipmentCheckoutDao($dbConn, $logger);
$equipment = $equipmentDao->getEquipment($eID);

// Allow if equipment is a valid object
allowIf(!empty($equipment) && !$equipment->getIsDeleted(), $configManager->getBaseUrl() . 'pages/index.php');

// If item isn't public, only allow if user is employee status or higher.  Else, redirect to home page
if (! array_filter($equipment->getUnits(), fn($unit) => $unit->getIsPublic())) {
	allowIf($isEmployee, $configManager->getBaseUrl() . 'pages/index.php');
}

$name = $equipment->getName();
$description = $equipment->getDescription();
$notes = $equipment->getNotes();
$parts = $equipment->getParts();
$instructions = $equipment->getUsageInstructions();
$replacementCost = $equipment->getReplacementCost();

$unitAvailable = !!array_filter($equipment->getUnits(), fn($unit) => (
    $unit->getIsPublic() && $unit->getCheckoutStatus() == 'Available'
));

// Gather the images and generate the HTML to render them in a slideshow
$pImagesHtml = '';
$pImagesDotsHtml = '';
$pImagesHeaderHtml = '';
$i = 1;
$numImages = count($equipment->getImages());
foreach ($equipment->getImages() as $image) {
    $pImagesHtml .= "
        <div class='slide fade'>
            <img src='images/equipment/{$image->getImageID()}' />
        </div>
    ";

    if($numImages > 1) {
        if ($i == 1) {
            $pImagesDotsHtml = "
                <div class='dot-container'>
            ";
        }

        $pImagesDotsHtml .= "
            <span class='dot' onclick='currentSlide($i)'></span>
        ";
    }

    $i++;
}

if ($numImages > 1) {
    $pImagesDotsHtml .= '
        </div>
    ';
    $pImagesHtml .= "
        <a class='prev' onclick='plusSlides(-1)'>&#10094;</a>
        <a class='next' onclick='plusSlides(1)'>&#10095;</a>
    ";
}

$unitsHtml = '';
foreach ($equipment->getUnits() as $unit) {
    if (! $unit->getIsPublic() && ! $isEmployee)
        continue;

    $status = $unit->getCheckoutStatus();
    if (! $unit->getIsPublic()) $status = 'Hidden';

    // Show the expected return date
    if ($status == 'Checked out') {
        $checkout = $checkoutDao->getActiveCheckout($unit->getUnitID());
        $status .= ' (expected return ' . $checkout->getDateDue()->format('m/d/Y') . ')';
    }
    // Show the user if they reserved the unit
    else if ($status == 'Reserved') {
        $reservation = $checkoutDao->getActiveReservation($unit->getUnitID());
        if ($reservation->getUserID() == $_SESSION['userID']) {
            $status .= ' by you';
        }
    }

    $unitsHtml .= "<li>{$status}</li>";
}

?>

<div class="viewSingleProject">
    <input type="hidden" id="equipmentID" value="<?php echo $equipment->getEquipmentID(); ?>" />
    <input type="hidden" id="userID" value="<?php echo $_SESSION['userID']; ?>" />


    <!-- Header -->
    <div class="bg-primary py-5 mb-5">
        <div class="container h-100">
            <div class="row h-100 align-items-center">
                <div class="col-lg-12">
                    <h1 class="display-4 text-white mt-5 mb-2"><?php echo($name);?></h1>
                    <p class="lead mb-5"><?php echo($description);?></p>
                </div>
            </div>
        </div>
    </div>

<!-- Page Content -->
<div class='container'>

    <div class='row'>
        <div class='col-md-8 mb-5'>
<?php
	if ($numImages > 0){
        echo"<h2>Images</h2>
            <hr>

            <div class='showcase-project-images row justify-content-md-center'>
                <div class='col-md-8'>
                    $pImagesHeaderHtml
                    <div class='slideshow-container'>
                        $pImagesHtml
                        $pImagesDotsHtml
                    </div>
                </div>
            </div>";
    }

    if ($isEmployee && !empty($notes)){  
        echo"<h2>Employee Notes</h2>
            <hr>
            <p>$notes</p>";
    }
    if (!empty($instructions)){
        echo"<h2>Usage Instructions</h2>
            <hr>
            <p>$instructions</p>";
    }
	
    if (! $unitAvailable) {
        echo '
            <button class="btn btn-lg btn-outline-primary capstone-nav-btn" type="button" data-toggle="modal" 
                data-target="#newReservationModal" id="openNewReservationBtn" disabled>
                No units available to reserve
            </button>
        ';
    } else if ($isLoggedIn) {
		include_once PUBLIC_FILES . '/modules/reserveEquipmentModal.php';
			
		echo '
			<button class="btn btn-lg btn-outline-primary capstone-nav-btn" type="button" data-toggle="modal" 
				data-target="#newReservationModal" id="openNewReservationBtn">
				Reserve this Equipment &raquo
			</button>
			';
    } else {
        echo '
			<a href="./pages/login.php"><button class="btn btn-lg btn-outline-primary capstone-nav-btn" type="button" >
				Login to Reserve
			</button></a>
			';
    }
?>
		</div>

		<div class="col-md-4 mb-5">
            <h2>Details</h2>
            <hr>
            <div>
                <h3 class='h5'>Parts</h3>
                <p><?php echo($parts);?></p>
            </div>
            <div>
                <h3 class='h5'>Replacement Cost</h3>
                <p>$<?php printf('%0.2f', $replacementCost);?></p>
            </div>
            <div>
                <h3 class='h5'>Units</h3>
                <ul><?php echo $unitsHtml;?></ul>
            </div>
		</div>
	</div>

		

<script type="text/javascript">
/**
 * Event handler for creating a new reservation based on user input into the modal
 */
function onReserveClick() {
    let body = {
        action: 'reserveEquipment',
        equipmentID: $('#equipmentID').val(),
        userID: $('#userID').val()
    };


    api.post('/equipment-checkout.php', body).then(res => {
        snackbar('Successfully Reserved!', 'success');
        setTimeout(() => window.location.reload(), 1000);
    }).catch( err=> {
        snackbar(err.message, 'error');
    });
}
$('#createReservationBtn').on('click', onReserveClick);

</script>
<?php
include_once '../bootstrap.php';

use DataAccess\EquipmentDao;
use Model\EquipmentStatus;
use Util\Security;

include PUBLIC_FILES . '/lib/shared/authorize.php';
$title = 'Single Equipment';


$eID = $_GET['id'];
allowIf($eID . '' != '');

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

$dao = new EquipmentDao($dbConn, $logger);
$equipment = $dao->getEquipment($eID);

if ($equipment) {
    $name = $equipment->getEquipmentName();
    //$category = $equipment->getCategoryID()->getName();
    $health = $equipment->getHealthID()->getName();
    $description = $equipment->getDescription();
    $notes = $equipment->getNotes();
    $numberparts = $equipment->getNumberParts();
    $location = $equipment->getLocation();
    $partslist = $equipment->getPartList();
	$equipmentcheck = $equipment->getReturnCheck();
	$instructions = $equipment->getUsageInstructions();
	$isPublic = $equipment->getIsPublic();
	$replacement_cost = $equipment->getReplacementCost();
	$instances = $equipment->getInstances();

	// Gather the images and generate the HTML to render them in a slideshow
    $pImagesHtml = '';
    $pImagesDotsHtml = '';
    $pImagesHeaderHtml = '';
    $i = 1;
    $numImages = count($equipment->getEquipmentImages());
    foreach ($equipment->getEquipmentImages() as $image) {
        $count = $i . ' / ' . $numImages;
        $imageId = $image->getImageID();

        $pImagesHtml .= "
            <div class='slide fade'>
                <img src='images/equipment/$imageId' />
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
}



// Allow if equipment is a valid object
allowIf(!empty($equipment), $configManager->getBaseUrl() . 'pages/index.php');

// If item isn't public, only allow if user is employee status or higher.  Else, redirect to home page
if (!$isPublic) {
	allowIf($isEmployee, $configManager->getBaseUrl() . 'pages/index.php');
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
<?php 
	echo "
	  <!-- Page Content -->
	  <div class='container'>

	    <div class='row'>
	      <div class='col-md-8 mb-5'>
	";			
	if ($numImages > 0){
	echo"  <h2>Images</h2>
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

		if (!empty($notes)){  
			echo "
			<h2>Employee Notes</h2>
			<hr>
			<p>$notes</p>
			";
		}
		if (!empty($instructions)){
			echo "
			<h2>Usage Instructions</h2>
			<hr>
			<p>$instructions</p>
			";
		}
			
		if ($isLoggedIn) {
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
		<address>
			<strong>Equipment Health:</strong>
			<p><?php echo($health);?></p>
		</address>
		<address>
			<strong>Number of Parts:</strong>
			<p><?php echo($numberparts);?></p>
		</address>
		<address>
			<strong>Parts List:</strong>
			<p><?php echo($partslist);?></p>
		</address>
		<address>
			<strong>Replacement Cost:</strong>
			<p>$<?php echo(number_format($replacement_cost,2));?></p>
		</address>
		<address>
			<strong>Number of units:</strong>
			<p><?php echo($instances);?></p>
		</address>
	
		</div>
	</div>

		

<script type="text/javascript">
/**
 * Event handler for creating a new reservation based on user input into the modal
 */
function onCreateReservationClick() {
    let body = {
        action: 'createReservation',
        equipmentID: $('#equipmentID').val(),
        userID: $('#userID').val(),
		messageID: 'wersdhwrujhssfuj'
    };


    api.post('/./equipmentrental.php', body).then(res => {
        snackbar('Successfully Reserved!', 'success');
    }).catch( err=> {
        snackbar(err.message, 'error');
    });
}
$('#createReservationBtn').on('click', onCreateReservationClick);

</script>
<?php
/**
 * This page handles client requests to modify or fetch user-related data. All requests made to this page should be a 
 * POST request with a corresponding `action` field in the request body.
 */
include_once '../bootstrap.php';

use DataAccess\InventoryDao;
use DataAccess\UsersDao;
use DataAccess\MessageDao;
use Api\InventoryActionHandler;
use Api\Response;

if(!session_id()) {
    session_start();
}

// Setup our data access and handler classes
$inventoryDao = new InventoryDao($dbConn, $logger);
$userDao = new UsersDao($dbConn, $logger);
$messageDao = new MessageDao($dbConn, $logger);
$handler = new InventoryActionHandler($inventoryDao, $userDao, $messageDao, $logger);

// Authorize the request
if (isset($_SESSION['userID']) && !empty($_SESSION['userID'])) {
    // Handle the request
	$stockNumber = $_REQUEST['stockNumber'];
	if ($_REQUEST['action'] == 'updatePartImage'){
		handleUpdateInventoryImage($stockNumber, $inventoryDao, $handler, $configManager, $logger);	
	}else if ($_REQUEST['action'] == 'updatePartDatasheet') {
		handleUpdateInventoryDatasheet($stockNumber, $inventoryDao, $handler, $configManager, $logger);
	}else
		$handler->respond(new Response(Response::UNAUTHORIZED, 'Invalid request for this resource'));
} else {
    $handler->respond(new Response(Response::UNAUTHORIZED, 'You do not have permission to access this resource'));
}


function correctImageOrientation($filename) {
  if (function_exists('exif_read_data')) {
    $exif = exif_read_data($filename);
    if($exif && isset($exif['Orientation'])) {
      $orientation = $exif['Orientation'];
      if($orientation != 1){
        $img = imagecreatefromjpeg($filename);
        $deg = 0;
        switch ($orientation) {
          case 3:
            $deg = 180;
            break;
          case 6:
            $deg = 270;
            break;
          case 8:
            $deg = 90;
            break;
        }
        return $deg;
      } // if there is some rotation necessary
    } // if have the exif orientation info
  } // if function exists   
	return 0;  
}

function imageresize($image_file, $max_width, $max_height) {
	// Get current dimensions
	$old_width  = imagesx($image_file);
	$old_height = imagesy($image_file);

	// Calculate the scaling we need to do to fit the image inside our frame
	$scale      = min($max_width/$old_width, $max_height/$old_height);

	// Get the new dimensions
	$new_width  = ceil($scale*$old_width);
	$new_height = ceil($scale*$old_height);

	// Create new empty image
	$new = imagecreatetruecolor($new_width, $new_height);

	// Resize old image into new
	imagecopyresampled($new, $image_file, 0, 0, 0, 0, $new_width, $new_height, $old_width, $old_height);
	
	return $new;
}

/**
 * Handles a request to add an artifact to a Equipment.
 *
 * @param string $equipmentID the ID of the Equipment to add the artifact to
 * @param \DataAccess\ShowcaseProjectsDao $equipmentDao data access object for projects
 * @param \Util\ConfigManager $configManager configuration manager to getting file location information
 * @param \Util\Logger $logger logger for capturing information
 * @return void
 */
function handleUpdateInventoryImage($stocknumber, $inventoryDao, $handler, $configManager, $logger) {
	//TODO: Put this inot a config file that matches the design of these pages.
	$max_width = 400;
	$max_height = 400;


	if (!isset($_FILES['imageFile'])) 
       $handler->respond(Response::OK, 'Must include file in request to update Part image');
    $file_name = $_FILES['imageFile']['name'];
	$file_size = $_FILES['imageFile']['size'];
	$file_tmp  = $_FILES['imageFile']['tmp_name'];

	$supported_image = array(
		'gif',
		'jpg',
		'jpeg',
		'png'
	);
	$path_parts = pathinfo($file_name);
	$extension = strtolower($path_parts['extension']);
	
	if(!in_array($extension, $supported_image)) 
		$handler->respond(Response::OK, "Unsupported file type.");

	if ($file_size > (5 * 2097152)) 
        $handler->respond(new Response(Response::OK, "File size must be less than 10MB"));
        	
	switch(strtolower($_FILES['imageFile']['type'])){
		case 'image/jpeg':
			$image_file = imagecreatefromjpeg($_FILES['imageFile']['tmp_name']);
			break;
		case 'image/png':
			$image_file = imagecreatefrompng($_FILES['imageFile']['tmp_name']);
			break;
		case 'image/gif':
			$image_file = imagecreatefromgif($_FILES['imageFile']['tmp_name']);
			break;
		default:
			$handler->respond(Response::OK, "Unsupported file type.");
		}
    $deg = correctImageOrientation($file_tmp);
	$image_file = imagerotate($image_file, $deg, 0); //Fixes rotated images
	$image_file = imageresize($image_file, $max_width, $max_height);
	
	
	$filepath = 
        $configManager->get('server.upload_part_image_files_path') .
        "/".$stocknumber.".jpg";

	
	// Save the imagedata
	ob_start();
	$ok = imagejpeg($image_file, $filepath);
	if (!$ok) {
        $handler->respond(new Response(Response::OK, 'Failed to upload image file'));
    }
	$data = ob_get_clean();
	
	$part = $inventoryDao->getPartByStocknumber($stocknumber);
	$part->setImage($stocknumber.".jpg");
	$part->setOriginalImage($file_name);
	$inventoryDao->updatePart($part);
	
	// Destroy resources
	imagedestroy($image_file);

    $handler->respond(new Response(Response::OK, 'Image Updated'));
}

function handleUpdateInventoryDatasheet($stocknumber, $inventoryDao, $handler, $configManager, $logger) {

	if (!isset($_FILES['datasheetFile'])) 
       $handler->respond(new Response(Response::OK, "Must include file in request to update datasheet"));
    $file_name = $_FILES['datasheetFile']['name'];
	$file_size = $_FILES['datasheetFile']['size'];
	$file_tmp  = $_FILES['datasheetFile']['tmp_name'];

	$path_parts = pathinfo($file_name);
	$extension = strtolower($path_parts['extension']);
	
	if ($file_size > (5 * 2097152)) 
        $handler->respond(new Response(Response::OK, "File size must be less than 10MB"));
	
	$filepath = 
        $configManager->get('server.upload_part_datasheet_files_path') .
        "/".$stocknumber.'.'.$extension;

	// Remove old datasheet and move new one
	if(file_exists($filepath)) {
		chmod($filepath,0755); //Change the file permissions if allowed
		unlink($filepath); //remove the file
	}
	$ok = move_uploaded_file($file_tmp, $filepath);
	if (!$ok) {
        $handler->respond(new Response(Response::OK, 'Failed to upload datasheet.'));
    }
	
	$part = $inventoryDao->getPartByStocknumber($stocknumber);
	$part->setDatasheet($stocknumber.'.'.$extension);
	$inventoryDao->updatePart($part);

    $handler->respond(new Response(Response::OK, 'Datasheet uploaded: ' . $filepath));
}

?>

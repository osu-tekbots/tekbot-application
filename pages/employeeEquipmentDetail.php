<?php
include_once '../bootstrap.php';

use DataAccess\EquipmentDao;
use Model\EquipmentStatus;
use Util\Security;

if (!session_id()) {
    session_start();
}

/*
If we ever decide to implement the category for equipment
<label for="equipmentCategorySelect">Equipment Category 
	</label>
	<select class="form-control input" id="equipmentCategorySelect" name="equipmentCategoryID" data-toggle="tooltip" 
		data-placement="bottom" title="tooltiptext">
		<?php
		foreach ($categories as $c) {
			$id = $c->getId();
			$name = $c->getName();
			$selected = $id == $category ? 'selected' : '';
			echo "<option $selected value='$id'>$name</option>";
		}
		?>
	</select>
*/
// Make sure the user is logged in and allowed to be on this page
include_once PUBLIC_FILES . '/lib/shared/authorize.php';

allowIf(verifyPermissions('employee'), 'index.php');


$title = 'Edit Equipment';
$css = array(
    'assets/css/sb-admin.css'
);
$js = array(
    array(
        'defer' => 'true',
        'src' => 'assets/js/edit-equipment.js'
    ),
    array(
        'defer' => 'true',
        'src' => 'assets/js/admin-review.js'
    ),
    array(
        'defer' => 'true',
        'src' => 'assets/js/upload-image.js'
    )
);
include_once PUBLIC_FILES . '/modules/header.php';
include_once PUBLIC_FILES . '/modules/employee.php';


$dao = new EquipmentDao($dbConn, $logger);

$healths = $dao->getEquipmentHealth();
$categories = $dao->getEquipmentCategory();

$eID = $_GET['id'];
$equipment = $dao->getEquipment($eID);
if ($equipment) {
    $equipmentname = $equipment->getEquipmentName();
    //$category = $equipment->getCategoryID()->getId();
    $health = $equipment->getHealthID()->getId();
    $description = $equipment->getDescription();
    $notes = $equipment->getNotes();
    $numberparts = $equipment->getNumberParts();
    $location = $equipment->getLocation();
    $partslist = $equipment->getPartList();
	$equipmentcheck = $equipment->getReturnCheck();
	$eImages = $equipment->getEquipmentImages();
	$isPublic = $equipment->getIsPublic();
	$instructions = $equipment->getUsageInstructions();
	$instances = $equipment->getInstances();
	$replacementCost = $equipment->getReplacementCost();
}
/* Image variables */
// Fetch any images for the project
$pImagePreviewSrc = '';
$pButtonImageDeleteStyle = 'style="display: none;"';
$pButtonImagePreviewStyle = $pButtonImageDeleteStyle;
$pProjectImagesSelectHtml = "
    <select class='image-picker' id='selectProjectImages'>
";
$eImages = $equipment->getEquipmentImages();
$first = true;
foreach ($eImages as $i) {
    $id = $i->getImageID();
    $name = $i->getImageName();
    $selected = $first ? 'selected' : '';
    $pProjectImagesSelectHtml .= "
        <option 
            $selected
            id='$id'
            data-img-src='images/equipment/$id'
            data-img-class='project-image-thumbnail'
            data-img-alt='$name'
            value='$id'>
            $name
        </option>
    ";
    if ($first) {
        $pButtonImageDeleteStyle = '';
        $pButtonImagePreviewStyle = '';
        $pImagePreviewSrc = "images/equipment/$id";
        $first = false;
    }
}
$pProjectImagesSelectHtml .= '
    </select>
';
/* Image variables END */


/**
 * Renders the HTML for an option that will render an image to select as the default image.
 */
function renderDefaultImageOption($imageId, $imageName, $selected) {
    $selectedAttr = $selected ? 'selected' : '';
    echo "
	<option 
		$selectedAttr
		class='image-option'
        data-img-src='images/$imageId' 
		data-img-class='data-img'
		id='$imageId'
        value='$imageId'>
    $imageName
    </option>";
}


?>
<br/>
<div id="page-top">

	<div id="wrapper">

        <?php 
		renderEmployeeSidebar();
		?>


		<div id="content-wrapper">

			<div class="container-fluid">

				<!-- Breadcrumbs-->
				<ol class="breadcrumb">
					<li class="breadcrumb-item">
						<a>Equipment Checkout</a>
					</li>
					<li class="breadcrumb-item active">Add</li>
				</ol>
			<?php 
			if ($isPublic)
			{
				echo "
				<div class='alert alert-warning' role='alert'>
					This equipment is currently PUBLIC! Updates made here will reflect the live item on browse equipment.
				</div>
				";
			}
			else {
				echo "
				<div class='alert alert-info' role='alert'>
					This equipment is currently HIDDEN! If you would like to make it public hit the 'make public' button!
				</div>
				";
			}

			?>

            <form id="formEquipment">
			<input type="hidden" id="equipmentID" name="equipmentID" value="<?php echo $eID; ?>" />
                <div class="row">
                    <div class="col-sm-3">
                        <div class="form-group">
							<label for="equipmentNameText">
								Equipment Name  <font size="2" style="color:red;">*</font>
							</label>
							<textarea class="form-control input" id="equipmentNameText" name="equipmentName"
								rows="1" data-toggle="tooltip" 
								data-placement="top" 
								title="Name of the equipment"><?php 
									echo "$equipmentname";
								?></textarea>
						</div>
                    </div>
                    <div class="col-sm-3">
                        <div class="form-group">
							<label for="equipmentLocationText">
								Equipment Location <font size="2" style="color:red;">*</font>
							</label>
							<textarea class="form-control input" id="equipmentLocationText" name="equipmentLocation"
								rows="1" data-toggle="tooltip" 
								data-placement="top" 
								title="Where the employee can find this equipment"><?php 
									echo "$location"; 
								?></textarea>
	
						</div>
                    </div>
					<div class="col-sm">
                        <div class="form-group">
							<label for="equipmentUnitsText">
								(#) Units <font size="2" style="color:red;">*</font>
							</label>
							<input class="form-control input" type="number" step="1" id="equipmentUnitsText" value="<?php echo $instances ?>" name="instances"
							 data-toggle="tooltip" data-placement="top" title="The number of available units of that item we have">
	
						</div>
                    </div>
					<div class="col-sm">
                        <div class="form-group">
							<label for="equipmentReplacementCost">
								($) Replace <font size="2" style="color:red;">*</font>
							</label>
							<input class="form-control input" type="number" step="0.10" id="equipmentReplacementCost" value="<?php echo $replacementCost ?>" name="replacementCost"
							 data-toggle="tooltip" data-placement="top" title="Cost for replacement of item.  Make sure you put the original price of an item not a sale price.">
						</div>
                    </div>
                    <div class="col-sm-2">
                        <div class="form-group">
							<label for="equipmentHealthSelect"> Health <font size="2" style="color:red;">*</font>
                            </label>
							<select class="form-control input" id="equipmentHealthSelect" name="equipmentHealthID" data-toggle="tooltip" 
								data-placement="bottom" title="Health based on functionality of the equipment.  Fully functional has no issues, broken does not work, and partial functionality is functional but has some quirks">
								<?php
								foreach ($healths as $h) {
								    $id = $h->getId();
								    $name = $h->getName();
									$selected = '';
									if ($name == $health){
										$selected = 'selected';
									}
								    echo "<option $selected value='$id'>$name</option>";
								}
								?>
							</select>
						</div>
                    </div>
					<div class="col-sm">
                        <div class="form-group">
							<label for="equipmentNumberpartsSelect">(#) Parts <font size="2" style="color:red;">*</font>
                            </label>
							<select class="form-control input" id="equipmentNumberpartsSelect" name="equipmentNumberparts" data-toggle="tooltip" 
								data-placement="bottom" title="Number of parts that come with the equipment.  Include manuals, cords, accesories in this count.">
								<?php
								for ($n = 1; $n <= 25; $n++) {
								    $selected = $n == $numberparts ? 'selected' : '';
								    echo "<option $selected value='$n'>$n</option>";
								}
								?>
							</select>
						</div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-sm-8">
                        <div class="form-group">
							<label for="equipmentDescriptionText">
								Equipment Description <font size="2" style="color:red;">*required</font>
							</label>
							<textarea class="form-control input" id="equipmentDescriptionText" name="equipmentDescription"
								rows="4" data-toggle="tooltip" 
								data-placement="top" 
								title="Give some description of the item.  A copy of the item description will work here"><?php 
									echo "$description"; 
								?></textarea>
						</div>
                    </div>
                    <div class="col-sm-4">
                    <div class="form-group">
							<label for="equipmentNotesText">
								Equipment Notes
							</label>
							<textarea class="form-control input" id="equipmentNotesText" name="equipmentNotes"
								rows="4" data-toggle="tooltip" 
								data-placement="top" 
								title="Notes that are relevant towards the functionality or apperance of the item.  For example, item will only work when tilted upright or item has large scratch near the bottom of the pan."><?php 
									echo "$notes"; 
								?></textarea>
						</div>
                    </div>
                </div>

                <div class="row">
					<div class="col-sm-4">
                        <div class="form-group">
							<label for="equipmentPartlistText">
								Parts List <font size="2" style="color:red;">*required</font>
							</label>
							<textarea class="form-control input" id="equipmentPartlistText" name="equipmentPartlist"
								rows="6" data-toggle="tooltip" 
								data-placement="top" 
								title="List of parts that correlate to the number of parts above"><?php 
									echo $partslist; 
								?></textarea>
						</div>
                    </div>

                    <div class="col-sm-4">
                        <div class="form-group">
							<label for="equipmentCheckText">
								Equipment Return Check <font size="2" style="color:red;">*required</font>
							</label>
							<textarea class="form-control input" id="equipmentCheckText" name="equipmentCheck"
								rows="6" data-toggle="tooltip" 
								data-placement="top" 
								title="Steps that the employee will need to take when taking back the item.  Things for them to check to make sure that the student handed it back in the same condition it was given."><?php 
									echo $equipmentcheck; 
								?></textarea>
						</div>
                    </div>
					<div class="col-sm-4">
						<div class="form-group">
							<label for="equipmentUsageText">
								Equipment Usage Instructions
							</label>
							<textarea class="form-control input" id="equipmentUsageText" name="equipmentUsage"
								rows="6" data-toggle="tooltip" 
								data-placement="top" 
								title="Instructions for usage, this could be a link to a pdf"><?php 
									echo $instructions; 
								?></textarea>
						</div>
                    </div>
                </div>

			</div>

        


	
			<div class="row">
				<div class="col-sm">
			<?php

					echo("
					<button id='saveEquipmentBtn' class='btn btn-success capstone-nav-btn' type='button' 
					data-toggle='tooltip' data-placement='bottom' 
					title='Updates the current information on the page'>
					Update Information</button>
					");
					
					if ($isPublic){
						createEquipmentHideButton($eID);
					}
					else if (!$isPublic){
						createShowEquipmentButton($eID);
					}

					createArchiveEquipmentButton($eID);

			?>
				</div>
			</div>

            </form>
	<br><br><br>
					<h3 id="images">Images</h3>
							<p><i class="fas fa-info-circle"></i><i>&nbsp;&nbsp;You can upload images to help showcase the equipment.
								Images must be no larger than 5MB. The selected image will be the default image.</i>
							</p>
							<div class="edit-project-images-container">
								<button type="button" class="btn btn-sm btn-danger" id="btnDeleteSelectedImage" 
									<?php echo $pButtonImageDeleteStyle; ?>>
									<i class="fas fa-trash"></i>&nbsp;&nbsp;Delete Selected Image
								</button>
								<div class="project-images-select-container">
									<?php echo $pProjectImagesSelectHtml; ?>
								</div>
								<form id="formAddNewImage">
									<input type="hidden" name="equipmentID" value="<?php echo $eID; ?>" />
									<div class="form-group row custom-file-row" id="divNewArtifactFile">
										<div class="custom-file col-md-4">
											<input required name="imageFile" type="file" class="custom-file-input" id="imageFile">
											<label class="custom-file-label" for="imageFile" id="labelImageFile">
												Choose a new image to upload
											</label>
										</div>
									</div>
									<div class="form-group row">
										<div class="col-md-4 row-project-image-submit">
											<button type="submit" id="btnUploadImage" class="btn btn-primary btn-sm">
												<i class="fas fa-upload"></i>&nbsp;&nbsp;Upload
											</button>
											<div class="loader" id="formAddNewImageLoader"></div>
										</div>
									</div>
								</form>
								<h6>Image Preview</h6>
								<img id="projectImagePreview" src="<?php echo $pImagePreviewSrc; ?>" <?php echo $pButtonImagePreviewStyle; ?>>
							</div>







				


			</div>
		</div>
	</div>
</div>

<script>



</script>

<?php 
include_once PUBLIC_FILES . '/modules/footer.php' ; 
?>


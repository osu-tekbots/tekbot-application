<?php
include_once '../bootstrap.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

use DataAccess\EquipmentCheckoutDao;
use DataAccess\EquipmentHealthDao;
use DataAccess\EquipmentTypeDao;
use DataAccess\UsersDao;
use Model\EquipmentStatus;
use Util\Security;

if (PHP_SESSION_ACTIVE != session_status())
    session_start();

// Make sure the user is logged in and allowed to be on this page
include_once PUBLIC_FILES . '/lib/shared/authorize.php';

allowIf(verifyPermissions('employee', $logger), 'index.php');


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

$showDeleted = isset($_GET['show_deleted']) && strtolower($_GET['show_deleted']) != 'false';


$equipmentCheckoutDao = new EquipmentCheckoutDao($dbConn, $logger);
$equipmentHealthDao = new EquipmentHealthDao($dbConn, $logger);
$equipmentTypeDao = new EquipmentTypeDao($dbConn, $logger);
$userDao = new UsersDao($dbConn, $logger);


$eID = $_GET['id'];
$equipment = $equipmentTypeDao->getEquipment($eID, $showDeleted);
$healthOptions = $equipmentHealthDao->getAllHealthOptions();

allowIf($equipment, 'employeeEquipmentListNew.php');

$isPublic = array_filter($equipment->getInstances(), fn($unit) => $unit->getIsPublic() && !$unit->getIsDeleted());
$instances = $equipment->getInstances();

/* Image variables */
$pImagePreviewSrc = '';
$pButtonImageDeleteStyle = 'style="display: none;"';
$pButtonImagePreviewStyle = $pButtonImageDeleteStyle;
$pProjectImagesSelectHtml = "
    <select class='image-picker' id='selectProjectImages'>
";
$first = true;
$eImages = $equipment->getImages();
foreach ($eImages as $i) {
    $id = $i->getImageID();
    $name = $i->getFilename();
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
        <?php renderEmployeeSidebar(); ?>

		<div id="content-wrapper">
			<div class="container-fluid">
			<?php
				if ($equipment->getIsDeleted()) {
					echo "
					<div class='alert alert-danger' role='alert'>
						This equipment is currently DELETED! If you would like to restore it,
						use the &quot;Restore Equipment&quot; button below.
					</div>
					";
				} else if ($isPublic)
				{
					echo "
					<div class='alert alert-warning' role='alert'>
						This equipment is currently PUBLIC! Updates made here will be immediately shown on the public equipment listing.
						If you would like to make it private (visible only to employees), make all units hidden.
					</div>
					";
				} else {
					echo "
					<div class='alert alert-info' role='alert'>
						This equipment is currently HIDDEN! If you would like to make it public, make at least one unit public.
					</div>
					";
				}
			?>

			<h3>General Info</h3>
            <form id="formEquipment">
				<input type="hidden" id="equipmentID" name="equipmentID" value="<?php echo $eID; ?>" />

                <div class="row">
                    <div class="col-sm-4">
                        <div class="form-group">
							<label for="equipmentNameText">
								Equipment Name  <font size="2" style="color:red;">*</font>
							</label>
							<input 
								class="form-control input" id="equipmentNameText" name="name"
								value="<?= $equipment->getName() ?>"
							>
						</div>
                    </div>
					<div class="col-sm-3">
                        <div class="form-group">
							<label for="equipmentReplacementCost">
								Replacement Cost <font size="2" style="color:red;">*</font>
							</label>
							<div class="input-group">
								<div class="input-group-prepend"><div class="input-group-text">$</div></div>
								<input
									class="form-control input" type="number" step="0.10" id="equipmentReplacementCost" name="replacementCost" aria-describedby="cost-help"
									value="<?= $equipment->getReplacementCost() ?>"
								>
							</div>
							<small id="cost-help" class="form-text text-muted">Item's original price, not a sale price.</small>
						</div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-sm-6">
                        <div class="form-group">
							<label for="equipmentDescriptionText">
								Equipment Description <font size="2" style="color:red;">*required</font>
							</label>
							<textarea class="form-control input" id="equipmentDescriptionText" name="description" rows="3" aria-describedby="description-help"><?=
								$equipment->getDescription()
							?></textarea>
							<small id="description-help" class="form-text text-muted">
								Give some description of the item. A copy of the item description will work here.
							</small>
						</div>
                    </div>
                    <div class="col-sm-6">
						<div class="form-group">
							<label for="equipmentNotesText">Equipment Notes</label>
							<textarea class="form-control input" id="equipmentNotesText" name="notes" rows="3" aria-describedby="notes-help"><?=
								$equipment->getNotes()
							?></textarea>
							<small id="notes-help" class="form-text text-muted">
								Notes that are relevant towards the functionality or apperance of the item.  For example, "item will only work when tilted upright" or "item has large scratch near the bottom of the pan".
							</small>
						</div>
                    </div>
                </div>

                <div class="row">
					<div class="col-sm-4">
                        <div class="form-group">
							<label for="equipmentPartlistText">
								Parts List <font size="2" style="color:red;">*required</font>
							</label>
							<textarea class="form-control input" id="equipmentPartlistText" name="parts" rows="4" aria-describedby="parts-help"><?=
								$equipment->getParts()
							?></textarea>
							<small id="parts-help" class="form-text text-muted">
								List of parts that come with the equipment. Include manuals, cord, and accessories.
							</small>
						</div>
                    </div>

                    <div class="col-sm-4">
                        <div class="form-group">
							<label for="equipmentCheckText">
								Equipment Return Check <font size="2" style="color:red;">*required</font>
							</label>
							<textarea
								class="form-control input" id="equipmentCheckText" name="returnCheck" rows="4"
							><?=
								$equipment->getReturnCheck()
							?></textarea>
							<small id="return-help" class="form-text text-muted">
								Steps for employees to follow when recieving the item to ensure that it was returned in the same condition as handout.
							</small>
						</div>
                    </div>
					<div class="col-sm-4">
						<div class="form-group">
							<label for="equipmentUsageText">Equipment Usage Instructions</label>
							<textarea class="form-control input" id="equipmentUsageText" name="usageInstructions" rows="4" aria-describedby="usage-help"><?=
								$equipment->getUsageInstructions()
							?></textarea>
							<small id="usage-help" class="form-text text-muted">
								Instructions for usage. This could be a link to a pdf.
							</small>
						</div>
                    </div>
                </div>
	
				<div class="row">
					<div class="col-sm">
						<button
							id='saveEquipmentBtn' class='btn btn-success capstone-nav-btn' type='button' 
							data-toggle='tooltip' data-placement='bottom' title='Saves the currently-entered information above'
						>
							Update Information
						</button>
						<?php
							if ($equipment->getIsDeleted()) {
								createUnarchiveEquipmentButton($eID);
							} else {
								createArchiveEquipmentButton($eID);
							}
						?>
					</div>
				</div>
            </form>

			<br><br><br>

			<div class='d-flex justify-content-between'>
				<h3>Units</h3>
				<form class="form-group form-inline">
					<input type="hidden" name="id" value="<?php echo $eID ?>">
					<input
						name="show_deleted" id="deletedCheckbox" class='form-control mr-1' type="checkbox"
						onchange='this.form.submit()' <?php echo $showDeleted ? 'checked' : '' ?>
					>
					<label for="deletedCheckbox">Show deleted units</label>
				</form>
			</div>

			<?php
				foreach ($instances as $unit) {
					$unitID = $unit->getItemID();
					$healthLogs = $equipmentHealthDao->getHealthLogsForItem($unitID);

					// Sort with most recent first for easy reading
					usort(
						$healthLogs,
						fn($a, $b) => ($a->getDateCreated() > $b->getDateCreated() 
							? -1
							: ($a->getDateCreated() == $b->getDateCreated()
								? 0
								: 1
							)
						)
					);
					
					$background = $unit->getIsDeleted() // Will only be included if specifically requested in DAO call
									? 'background: rgb(255, 230, 230) !important;'
									: '';

					echo "<div class='bg-light rounded my-4 p-3' style='$background'>
						<div class='row'>
							<div class='col-xl-7'>
								<div class='row'>
									<div class='col-md-3'>
										<div class='form-group'>
											<label for='unitID$unitID'>Unit ID</label>
											<div class='input-group'>
												<div class='input-group-prepend'><div class='input-group-text'>#</div></div>
												<input id='unitID$unitID' class='form-control input' disabled value='$unitID'>
											</div>
										</div>
									</div>
									<div class='col-md-4'>
										<div class='form-group'>
											<label for='unitCheckout$unitID'>Checkout Status</label>
											<div class='input-group'>
												<div class='input-group-prepend'><div class='input-group-text'><i class='fa-solid fa-shopping-basket'></i></div></div>
												<input id='unitCheckout$unitID' class='form-control input' disabled value='{$unit->getCheckoutStatus()}'>
											</div>
										</div>
									</div>
									<div class='col-md-5'>
										<div class='form-group'>
											<label for='unitHealth$unitID'>Health</label>
											<div class='input-group'>
												<div class='input-group-prepend'><div class='input-group-text'><i class='fa-solid fa-heartbeat'></i></div></div>
												<select id='unitHealth$unitID' onchange='onUpdateItemHealth($unitID)' class='custom-select'>";
					foreach ($healthOptions as $option) {
						echo "<option value='{$option->getOptionID()}'" . ($option->getName() == $unit->getHealthStatus() ? ' selected' : '') . ">
							{$option->getName()}
						</option>";
					}
					echo "						</select>
											</div>
										</div>
									</div>
								</div>
								<div class='row'>
									<div class='col-sm'>
										<div class='form-group'>
											<label for='unitLocation$unitID'>Location</label>
											<div class='input-group'>
												<div class='input-group-prepend'><div class='input-group-text'><i class='fa-solid fa-map-marker-alt'></i></div></div>
												<input id='unitLocation$unitID' onchange='onUpdateItemLocation($unitID);' class='form-control input' value='{$unit->getLocation()}'>
											</div>
										</div>
									</div>
									<div class='col-sm pt-4'>";
					
					if ($unit->getIsPublic()) { createEquipmentHideButton($unit->getItemID()); }
					else 					  { createShowEquipmentButton($unit->getItemID()); }

					if ($unit->getIsDeleted()) { createUnarchiveUnitButton($unit->getItemID()); }
					else                       { createArchiveUnitButton($unit->getItemID()); }
					
					echo "			</div>
								</div>
							</div>
							<div class='col-xl-5'>
								<div class='form-group'>
									<label for='unitNotes$unitID'>Notes</label>
									<textarea id='unitNotes$unitID' onchange='onUpdateItemNotes($unitID);' class='form-control input' rows='4' >{$unit->getNotes()}</textarea>
								</div>
							</div>
						</div>
						<div class='row'>
							<div class='col'>
								<ul>";
					foreach ($healthLogs as $log) {
						echo "<li><b>{$log->getDateCreated()->format('Y-m-d H:i:s')}</b> (";

						if ($log->getCheckoutID()) {
							$checkout = $equipmentCheckoutDao->getCheckout($log->getCheckoutID());
							$user = $userDao->getUserByID($checkout->getUserID());
							
							echo 'used by '
								. Security::HtmlEntitiesEncode($user->getFirstName()) 
								. ' ' 
								. Security::HtmlEntitiesEncode($user->getLastName());
						} else {
							echo '<i>employee update</i>';
						}

						echo ") &ndash; <b>{$log->getHealthOption()->getName()}</b>";
						
						if ($log->getNotes()) {
							echo " &ndash; {$log->getNotes()}";
						}
						
						echo "</li>";
					}
					echo "		</ul>
							</div>
						</div>
					</div>";
				}
			?>

			<button class="btn btn-outline-primary capstone-nav-btn" type="button" onclick="onCreateItem('<?= $equipment->getEquipmentID() ?>')">
				Create New Unit
			</button>

			<br><br><br>

			<h3 id="images">Images</h3>
			<p style="white-space: normal">
				<i class="fas fa-info-circle"></i>
				<i style="white-space: normal">
					&nbsp;&nbsp;You can upload images to help showcase the equipment. Images must be no larger than 5MB. The 
					selected image will be the default image.</i>
			</p>

			<div class="edit-project-images-container mx-2">
				<button type="button" class="btn btn-sm btn-danger" id="btnDeleteSelectedImage" <?= $pButtonImageDeleteStyle ?>>
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

<?php include_once PUBLIC_FILES . '/modules/footer.php'; ?>

/*
 * Javascript functionality for the editProject page. This script is 'deferred', so the following code
 * won't execute until the page has finished parsing.
 */

/**
 * Fetches the equipment ID from the HTML
 * @returns {string}
 */
function getEquipmentID() {
    return $('#equipmentID').val();
}

/**
 * Serializes the form and returns a JSON object with the keys being the values of the `name` attribute.
 * @returns {object}
 */
function getEquipmentFormDataAsJson() {
    let form = document.getElementById('formEquipment');
    let data = new FormData(form);

    let json = {
        name: $('#equipmentNameText').val()
    };
    for (const [key, value] of data.entries()) {
        json[key] = value;
    }

    return json;
}

// Instantiates all tool tips.
$('[data-toggle="tooltip"]').tooltip();

/**
 * Sets the selected image as the default image for the equipment. On ever select, the default value will be
 * updated on the server.
 * @param {string} imageId the ID of the selected image
 */
function onProjectImageSelected(imageId) {
    let body = {
        action: 'setDefaultImage',
        imageID: imageId
    };

    api.post('/equipment.php', body)
        .then(res => {
            $('#nameOfImageInput').val(res.content.name);
            $('#img-upload').attr('src', 'images/' + imageId);
        })
        .catch(err => {
            snackbar(err.message, 'error');
        });
}
$('.image-picker').on('change', function() {
    onProjectImageSelected($(this).val());
});

//Generates the save icon animation.
function createSaveIcon() {
    loaderDivText = `
    <div class="loaderdiv">
        <span class="save-icon">
            <span class="loader"></span>
            <span class="loader"></span>
            <span class="loader"></span>
        </span>
    </div>`;
    $('#cssloader').html(loaderDivText);
}

/**
 * Handler for a user click on the 'Update Information' button. It will use AJAX to save the equipment in the
 * database. The equipment title must not be empty.
 */
function onSaveEquipmentClick() {
    let equipment = getEquipmentFormDataAsJson();

    // Validate the form
    if (equipment.name == '') {
        return snackbar('Please provide an equipment name', 'error');
    } else if (equipment.replacementCost == 0) {
        return snackbar('Please provide input for the equipment\'s replacement cost', 'error');
    } else if (equipment.description == '') {
        return snackbar('Please provide input for a equipment description', 'error');
    } else if (equipment.parts == '') {
        return snackbar('Please provide input for the equipment parts list', 'error');
    } else if (equipment.equipmentCheck == '') {
        return snackbar('Please provide input for the equipment return check', 'error');
    }
	
     // Validation completed. Make the request.
     let body = {
        ...equipment,
        action: 'saveEquipment'
    };

    api.post('/equipment.php', body)
        .then(res => {
            snackbar(res.message, 'success');
        })
        .catch(err => {
            snackbar(err.message, 'error');
        });
}
$('#saveEquipmentBtn').on('click', onSaveEquipmentClick);

function onCreateItem(typeId) {
    let body = {
        action: 'createEquipmentItem',
        equipmentID: typeId
    };

    api.post('/equipment.php', body)
        .then(res => {
            snackbar(res.message, 'success');
            setTimeout(() => window.location.reload(), 1000);
        })
        .catch(err => {
            snackbar(err.message, 'error');
        });
}

function onUpdateItemHealth(id) {
    let body = {
        action: 'setEquipmentItemHealth',
        healthStatus: $(`#unitHealth${id}`).val(),
        notes: '', //$(`#unitNotes${id}`).val(),
        itemID: id
    };

    api.post('/equipment.php', body)
        .then(res => {
            snackbar(res.message, 'success');
        })
        .catch(err => {
            snackbar(err.message, 'error');
        });
}

/** Handler for user updating an equipment item's notes */
function onUpdateItemNotes(id) {
    let body = {
        action: 'saveEquipmentItem',
        notes: $(`#unitNotes${id}`).val(),
        itemID: id
    };

    api.post('/equipment.php', body)
        .then(res => {
            snackbar(res.message, 'success');
        })
        .catch(err => {
            snackbar(err.message, 'error');
        });
}

/** Handler for user updating an equipment item's location */
function onUpdateItemLocation(id) {
    let body = {
        action: 'saveEquipmentItem',
        location: $(`#unitLocation${id}`).val(),
        itemID: id
    };

    api.post('/equipment.php', body)
        .then(res => {
            snackbar(res.message, 'success');
        })
        .catch(err => {
            snackbar(err.message, 'error');
        });
}


/**
 * Detects when there is a change to the image input and changes the label text to match the name of the file to
 * upload. This will also change the image preview src.
 */
function onImageFileChange() {
    // Show the name of the file
    if (this.files.length > 0) {
        // Get a preview of the selected files
        let reader = new FileReader();
        reader.onload = e => {
            let $preview = $('#projectImagePreview');
            $preview.attr('src', e.target.result);
            $preview.show();
        };
        reader.readAsDataURL(this.files[0]);
        $('#labelImageFile').text(this.files[0].name);
        $('#selectProjectImages').val('');
        $('#selectProjectImages')
            .data('picker')
            .sync_picker_with_select();
    }
}
$('#imageFile').change(onImageFileChange);

/**
 * Initiate the image picker
 */
function initializeImagePicker() {
    $('#selectProjectImages').imagepicker({
        selected: onImagePickerOptionChange
    });
    $('#selectProjectImages')
        .data('picker')
        .sync_picker_with_select();
}
initializeImagePicker();

/**
 * Handles rendering the new image preview when the image picker option has changed.
 */
function onImagePickerOptionChange(pickerOption) {
    $('#projectImagePreview').attr('src', $(pickerOption.option[0]).data('img-src'));
}

/**
 * Handles a form submission for uploading a new image to associate with the project.
 */
function onAddNewImageFormSubmit() {
    let form = new FormData(this);
    form.append('action', 'addEquipmentImage');

    api.post('/equipment-images.php', form, true)
        .then(res => {
            snackbar(res.message, 'success');
            onUploadImageSuccess(res.content.id);
        })
        .catch(err => {
            snackbar(err.message, 'error');
            $('#btnUploadImage').attr('disabled', false);
            $('#formAddNewImageLoader').hide();
        });

    $('#btnUploadImage').attr('disabled', true);
    $('#formAddNewImageLoader').show();

    return false;
}
$('#formAddNewImage').submit(onAddNewImageFormSubmit);

/**
 * Handles HTML rendering DOM manipulation after a successful upload of a new project image
 * @param {string} id the ID of the newly uploaded image
 */
function onUploadImageSuccess(id) {
    $('#btnUploadImage').attr('disabled', false);
    $('#formAddNewImageLoader').hide();
    $('#btnDeleteSelectedImage').show();
    let name = $('#labelImageFile').text();
    $('#selectProjectImages').append(
        $(`
        <option
            id='${id}'
            data-img-src='images/equipment/${id}'
            data-img-class='project-image-thumbnail'
            data-img-alt='${name}'
            value='${id}'>
            ${name}
        </option>
    `)
    );
    $('#selectProjectImages').val(id);
    initializeImagePicker();
}

/**
 * Handles deleting an image from the project by sending a request to the server for the project image to be deleted.
 */
function onDeleteSelectedImageButtonClick() {
    let res = confirm('You are about to delete the currently selected image. This action is not reversible');
    if (!res) return;

    let form = new FormData();
    let id = $('#selectProjectImages').val();
    form.append('action', 'deleteEquipmentImage');
    form.append('equipmentID', $('#equipmentID').val());
    form.append('equipmentImageID', id);

    api.post('/equipment-images.php', form, true)
        .then(res => {
            $(`option[id=${id}]`).remove();
            initializeImagePicker();
            snackbar(res.message, 'success');
            $('#labelImageFile').text('Choose a new file to upload');
            $('#projectImagePreview').attr('src', '');
            if ($('#selectProjectImages option').length == 0) {
                $('#btnDeleteSelectedImage').hide();
            }
        })
        .catch(err => {
            snackbar(err.message, 'error');
        });
}
$('#btnDeleteSelectedImage').click(onDeleteSelectedImageButtonClick);
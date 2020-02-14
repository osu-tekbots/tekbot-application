/**
 * Serializes the form and returns a JSON object with the keys being the values of the `name` attribute.
 * @returns {object}
 */
function getFaqFormDataAsJson() {
    let form = document.getElementById('faqForm');
    let data = new FormData(form);

    let json = {};
    for (const [key, value] of data.entries()) {
        json[key] = value;
    }

    return json;
}

/**
 * Handler for a user click on the 'Save Project Draft' button. It will use AJAX to save the faq in the
 * database. The faq title must not be empty.
 */
function onUpdateFaqClick() {
    let faq = getFaqFormDataAsJson();

    // Validate the form
    if (faq.category == '') {
        return snackbar('Please select a category', 'error');
    } else if (faq.question == '') {
        return snackbar('Please provide input for the faq question', 'error');
    } else if (faq.answer == '') {
        return snackbar('Please provide input for the faq answer', 'error');
    } else if (faq.id == '') {
        return snackbar('Error with the ID', 'error');
    }
	
     // Validation completed. Make the request.
     let body = {
        ...faq,
        action: 'updateGeneralFaq'
    };

    api.post('/generalfaq.php', body)
        .then(res => {
            snackbar(res.message, 'success');
        })
        .catch(err => {
            snackbar(err.message, 'error');
        });
}

function onCreateFaqClick() {
    let faq = getFaqFormDataAsJson();

    // Validate the form
    if (faq.category == '') {
        return snackbar('Please select a category', 'error');
    } else if (faq.question == '') {
        return snackbar('Please provide input for the faq question', 'error');
    } else if (faq.answer == '') {
        return snackbar('Please provide input for the faq answer', 'error');
    } 
	
     // Validation completed. Make the request.
     let body = {
        ...faq,
        action: 'createGeneralFaq'
    };

    api.post('/generalfaq.php', body)
        .then(res => {
            snackbar(res.message, 'success');
        })
        .catch(err => {
            snackbar(err.message, 'error');
        });
}
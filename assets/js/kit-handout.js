// Javascript functionality for employeeKitHandout page

// When toggling Ready to hand out
$(function() {
    $(".switch").click(function(){
      // Action for handing out a kit
      var kid = $(this).attr('id');
      if ($(this).hasClass("on")){
          // Returning kit
          var status = 1;
      } else {
          // Handing out kit
          var status = 2;
      }
      let data = {
          action: 'updateHandoutKitEnrollments',
          kid: kid,
          status: status
      };
          api.post('/kitenrollment.php', data).then(res => {
              snackbar(res.message, 'success');
              $(this).toggleClass("on");
          }).catch(err => {
              snackbar(err.message, 'error');
          });
  
      
      
    })
  });

  // After dom elements loads
  $(document).ready(function () {
    
    // Automatically clicks the submit button after the length is 9
    $("#studentidinput").bind("paste keyup", function() {
        if ($(this).val().trim().length == 9){
            $("#studentidsubmit").click();
        }
     });
  });


/**
 * Serializes the form and returns a JSON object with the keys being the values of the `name` attribute.
 * @returns {object}
 */
function getHandoutFormDataAsJson() {
    let form = document.getElementById('formAddCourse');
    let data = new FormData(form);

    let json = {};
    for (const [key, value] of data.entries()) {
        json[key] = value;
    }

    return json;
}

function onAddCourseClick() {
    let kitenrollment = getHandoutFormDataAsJson();
    if (kitenrollment.idnumber == ''){
        return snackbar('Please provide an ID number', 'error');
    } else if (kitenrollment.lfm == ''){
        return snackbar('Please provide a name', 'error');
    } else if (kitenrollment.onid == ''){
        return snackbar('Please provide an ONID', 'error');
    } else if (kitenrollment.course == ''){
        return snackbar('Please select a course', 'error');
    } else if (kitenrollment.term == ''){
        return snackbar('Issue with current term', 'error');
    }

    // Form validation
      // Validation completed. Make the request.
      let body = {
        ...kitenrollment,
        action: 'createSingleKitEnrollment'
    };
    api.post('/kitenrollment.php', body)
        .then(res => {
            window.location.replace('pages/employeeKitHandout.php?studentid=' + res.content.id);
        })
        .catch(err => {
            snackbar(err.message, 'error');
        });

}

function onShowKitsRemaining() {
    var termID = "Term";
    if (kitenrollment.term == ''){
        return snackbar('Select a term!', 'error');
    }

    // Form validation
      // Validation completed. Make the request.
      let body = {
        termID: termID,
        action: 'createSingleKitEnrollment'
    };
    api.post('/kitenrollment.php', body)
        .then(res => {
            window.location.replace('pages/employeeKitHandout.php?studentid=' + res.content.id);
        })
        .catch(err => {
            snackbar(err.message, 'error');
        });

}
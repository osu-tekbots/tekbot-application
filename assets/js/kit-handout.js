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

function onAddCourseClick(){
    let kitenrollment = getHandoutFormDataAsJson();

    // Form validation

}
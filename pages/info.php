<?php
include_once '../bootstrap.php';

use DataAccess\FaqDao;
use Util\Security;

$title = 'TekBot FAQ';
include_once PUBLIC_FILES . '/modules/header.php';
$isEmployee = verifyPermissions('employee', $logger);

$FaqDao = new FaqDao($dbConn, $logger);
$FAQs = $FaqDao->getAllFaqs();
?>
<br><br>


 <!-- Filter Form -->
 <div class="form-group container" id="filter-form">
 <?php 
	if ($isEmployee){
		echo '
		<a class="btn btn-info btn-lg" href="pages/employeeFaqDetail.php">Add new FAQ</a>
		<br><br>
		';
	}
?>
    <label for="filter">
    Search for a Question
  </label>
    <input id="filter" type="text" class="form-control noEnterSubmit" placeholder="Enter a keyword or phrase" />
    <small>
    <span id="filter-help-block" class="help-block">
      No filter applied.
    </span>
  </small>
  </div>

<?php 
if (!empty($FAQs)){
	$count = 1;
	echo '<div class="panel-group searchable container" id="accordion" role="tablist" aria-multiselectable="true">';
	foreach ($FAQs as $f){
		echo '
		<div class="panel panel-primary">
			<div class="panel-heading" role="tab" id="heading'.$count.'">
				<h4 class="panel-title d-flex justify-content-between align-items-center">
					<a class="text-primary" role="button" data-toggle="collapse" data-parent="#accordion" href="#collapse'.$count.'" aria-expanded="false" aria-controls="collapse'.$count.'">
		';

		echo '['.$f->getCategory().'] '. $f->getQuestion().'
					</a>
		';
		if ($isEmployee){
			echo '
				<a class="btn btn-outline-primary" href="./pages/employeeFaqDetail.php?id='.$f->getFaqID().'">
					<i class="fas fa-pencil-alt mr-1"></i>
					Edit
				</a>
			';
		}
		echo '
		</h4>
		</div>
		<div id="collapse'.$count.'" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="heading'.$count.'">
		  <div class="panel-body">
			'.$f->getAnswer().'
		  </div>
		</div>
	  </div>
		
		';
		$count++;
	}
	echo '
	</div>
</div>
	';

}

?>
  



<script>
$(document).ready(function() {

(function($) {
  
  var $form = $('#filter-form');
  var $helpBlock = $("#filter-help-block");
  
  //Watch for user typing to refresh the filter
  $('#filter').keyup(function() {
	var filter = $(this).val();
	$form.removeClass("has-success has-error");
	
	if (filter == "") {
	  $helpBlock.text("No filter applied.")
	  $('.searchable .panel').show();
	} else {
	  //Close any open panels
	  $('.collapse.in').removeClass('in');
	  
	  //Hide questions, will show result later
	  $('.searchable .panel').hide();

	  var regex = new RegExp(filter, 'i');

	  var filterResult = $('.searchable .panel').filter(function() {
		return regex.test($(this).text());
	  })

	  if (filterResult) {
		if (filterResult.length != 0) {
		  $form.addClass("has-success");
		  $helpBlock.text(filterResult.length + " question(s) found.");
		  filterResult.show();
		} else {
		  $form.addClass("has-error").removeClass("has-success");
		  $helpBlock.text("No questions found.");
		}

	  } else {
		$form.addClass("has-error").removeClass("has-success");
		$helpBlock.text("No questions found.");
	  }
	}
  })

}($));
});

//
//  This function disables the enter button
//  because we're using a form element to filter, if a user
//  pressed enter, it would 'submit' a form and reload the page
// 
$('.noEnterSubmit').keypress(function(e) {
if (e.which == 13) e.preventDefault();
});
</script>

<?php 

include_once PUBLIC_FILES . '/modules/footer.php'; 
?>

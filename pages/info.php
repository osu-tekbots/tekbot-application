<?php
include_once '../bootstrap.php';

$title = 'TekBot FAQ';
$css = array(
	'assets/css/homepage.css'
);
include_once PUBLIC_FILES . '/modules/header.php';
?>
<br>
<div class="stickytabs">
	<button class="tablink" onclick="openPage('Store', this, 'red')" id="defaultOpen">General Store</button>
	<button class="tablink" onclick="openPage('Services', this, 'green')">Laser Cuts & 3D Prints</button>
	<button class="tablink" onclick="openPage('Equipment', this, 'blue')">Equipment Checkout</button>
</div>
	<br>
    
	<!-- ***** FAQ Start ***** -->
	<div class="content-wrapper" id="Store">
          <section class="faq-section">
          <div class="container">
               <div class="row">
          
          <div class="col-md-10 offset-md-1">
               <div class="faq-title text-center pb-3">
                    <h2>Store FAQ</h2>
               </div>
          </div>

		<div class="col-md-12 offset-md">
			<div class="faq" id="accordion">


			   	<div class="card">
					<div class="card-header" id="faqHeading-1">
						<div class="mb-0">
						<h5 class="faq-title" data-toggle="collapse" data-target="#faqCollapse-'.$faqnumber.'" data-aria-expanded="true" data-aria-controls="faqCollapse-1">
							<span class="badge">1</span>My Question?
						</h5>
						</div>
					</div>
					<div id="faqCollapse-'.$faqnumber.'" class="collapse" aria-labelledby="faqHeading-'.$faqnumber.'" data-parent="#accordion">
						<div class="card-body">
						<p>Body information</p>
						</div>
					</div>
				</div>



				<div class="card">
					<div class="card-header" id="faqHeading-'.$faqnumber.'">
						<div class="mb-0">
						<h5 class="faq-title" data-toggle="collapse" data-target="#faqCollapse-'.$faqnumber.'" data-aria-expanded="true" data-aria-controls="faqCollapse-'.$faqnumber.'">
							<span class="badge">'.$faqnumber.'</span>'.$faqquestion.'
						</h5>
						</div>
					</div>
					<div id="faqCollapse-'.$faqnumber.'" class="collapse" aria-labelledby="faqHeading-'.$faqnumber.'" data-parent="#accordion">
						<div class="card-body">
						<p>'.$faqcurrent.'</p>
						</div>
					</div>
				</div>



				<div class="card">
					<div class="card-header" id="faqHeading-'.$faqnumber.'">
						<div class="mb-0">
						<h5 class="faq-title" data-toggle="collapse" data-target="#faqCollapse-'.$faqnumber.'" data-aria-expanded="true" data-aria-controls="faqCollapse-'.$faqnumber.'">
							<span class="badge">'.$faqnumber.'</span>'.$faqquestion.'
						</h5>
						</div>
					</div>
					<div id="faqCollapse-'.$faqnumber.'" class="collapse" aria-labelledby="faqHeading-'.$faqnumber.'" data-parent="#accordion">
						<div class="card-body">
						<p>'.$faqcurrent.'</p>
						</div>
					</div>
				</div>


			</div>
		</div>
	</div>	
	</div>
	</section>
	</div>


	<div class="content-wrapper" id="Services">
          <section class="faq-section">
          <div class="container">
               <div class="row">
          
          <div class="col-md-10 offset-md-1">

               <div class="faq-title text-center pb-3">
                    <h2>Laser Cut & 3D Print FAQ</h2>
               </div>
          </div>
          <div class="col-md-12 offset-md">
               <div class="faq" id="accordion">


			   	<div class="card">
					<div class="card-header" id="faqHeading-1">
						<div class="mb-0">
						<h5 class="faq-title" data-toggle="collapse" data-target="#faqCollapse-'.$faqnumber.'" data-aria-expanded="true" data-aria-controls="faqCollapse-1">
							<span class="badge">1</span>My Question?
						</h5>
						</div>
					</div>
					<div id="faqCollapse-'.$faqnumber.'" class="collapse" aria-labelledby="faqHeading-'.$faqnumber.'" data-parent="#accordion">
						<div class="card-body">
						<p>Body information</p>
						</div>
					</div>
				</div>



				<div class="card">
					<div class="card-header" id="faqHeading-'.$faqnumber.'">
						<div class="mb-0">
						<h5 class="faq-title" data-toggle="collapse" data-target="#faqCollapse-'.$faqnumber.'" data-aria-expanded="true" data-aria-controls="faqCollapse-'.$faqnumber.'">
							<span class="badge">'.$faqnumber.'</span>'.$faqquestion.'
						</h5>
						</div>
					</div>
					<div id="faqCollapse-'.$faqnumber.'" class="collapse" aria-labelledby="faqHeading-'.$faqnumber.'" data-parent="#accordion">
						<div class="card-body">
						<p>'.$faqcurrent.'</p>
						</div>
					</div>
				</div>



				<div class="card">
					<div class="card-header" id="faqHeading-'.$faqnumber.'">
						<div class="mb-0">
						<h5 class="faq-title" data-toggle="collapse" data-target="#faqCollapse-'.$faqnumber.'" data-aria-expanded="true" data-aria-controls="faqCollapse-'.$faqnumber.'">
							<span class="badge">'.$faqnumber.'</span>'.$faqquestion.'
						</h5>
						</div>
					</div>
					<div id="faqCollapse-'.$faqnumber.'" class="collapse" aria-labelledby="faqHeading-'.$faqnumber.'" data-parent="#accordion">
						<div class="card-body">
						<p>'.$faqcurrent.'</p>
						</div>
					</div>
				</div>


			</div>
		</div>
	</div>
	</div>
	</section>
	</div>

	<div class="content-wrapper" id="Equipment">
          <section class="faq-section">
          <div class="container">
               <div class="row">
          
          <div class="col-md-10 offset-md-1">

               <div class="faq-title text-center pb-3">
                    <h2>Laser Cut & 3D Print FAQ</h2>
               </div>
          </div>
          <div class="col-md-12 offset-md">
               <div class="faq" id="accordion">


			   	<div class="card">
					<div class="card-header" id="faqHeading-1">
						<div class="mb-0">
						<h5 class="faq-title" data-toggle="collapse" data-target="#faqCollapse-'.$faqnumber.'" data-aria-expanded="true" data-aria-controls="faqCollapse-1">
							<span class="badge">1</span>My Question?
						</h5>
						</div>
					</div>
					<div id="faqCollapse-'.$faqnumber.'" class="collapse" aria-labelledby="faqHeading-'.$faqnumber.'" data-parent="#accordion">
						<div class="card-body">
						<p>Body information</p>
						</div>
					</div>
				</div>



				<div class="card">
					<div class="card-header" id="faqHeading-'.$faqnumber.'">
						<div class="mb-0">
						<h5 class="faq-title" data-toggle="collapse" data-target="#faqCollapse-'.$faqnumber.'" data-aria-expanded="true" data-aria-controls="faqCollapse-'.$faqnumber.'">
							<span class="badge">'.$faqnumber.'</span>'.$faqquestion.'
						</h5>
						</div>
					</div>
					<div id="faqCollapse-'.$faqnumber.'" class="collapse" aria-labelledby="faqHeading-'.$faqnumber.'" data-parent="#accordion">
						<div class="card-body">
						<p>'.$faqcurrent.'</p>
						</div>
					</div>
				</div>



				<div class="card">
					<div class="card-header" id="faqHeading-'.$faqnumber.'">
						<div class="mb-0">
						<h5 class="faq-title" data-toggle="collapse" data-target="#faqCollapse-'.$faqnumber.'" data-aria-expanded="true" data-aria-controls="faqCollapse-'.$faqnumber.'">
							<span class="badge">'.$faqnumber.'</span>'.$faqquestion.'
						</h5>
						</div>
					</div>
					<div id="faqCollapse-'.$faqnumber.'" class="collapse" aria-labelledby="faqHeading-'.$faqnumber.'" data-parent="#accordion">
						<div class="card-body">
						<p>'.$faqcurrent.'</p>
						</div>
					</div>
				</div>


			</div>
		</div>
		</div>
	</div>
	</section>
	</div>



<script defer type="text/javascript">

function openPage(pageName, elmnt, color) {
	// Hide all elements with class="tabcontent" by default */
	var i, tabcontent, tablinks;
	tabcontent = document.getElementsByClassName("tabcontent");
	for (i = 0; i < tabcontent.length; i++) {
		tabcontent[i].style.display = "none";
	}

	// Remove the background color of all tablinks/buttons
	tablinks = document.getElementsByClassName("tablink");
	for (i = 0; i < tablinks.length; i++) {
		tablinks[i].style.backgroundColor = "";
	}

	// Show the specific tab content
	document.getElementById(pageName).style.display = "block";

	// Add the specific color to the button used to open the tab content
	elmnt.style.backgroundColor = color;
}

// Get the element with id="defaultOpen" and click on it
document.getElementById("defaultOpen").click();


</script>

<?php 
include PUBLIC_FILES . '/modules/newProjectModal.php';
include_once PUBLIC_FILES . '/modules/footer.php'; 
?>

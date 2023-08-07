<?php
include_once '../bootstrap.php';


$title = 'Homepage';
$css = array(
	'assets/css/homepage.css'
);
include_once PUBLIC_FILES . '/modules/header.php';


?>
          <!-- Main header -->
          <header class="maincenter">
               <!-- First replaceble image -->
               <img class="img-absolute" src="assets/img/kelly1.jpg">     
               <div class="wrapper astonish animated fadeInDown">
                    <!--<h1><strong>Tek</strong>bots</h1>-->
                    <img class="tekbotslogo" src="assets/img/resize_tekbots.png">
                    <h2 class="logotext">Creating Platforms For Learning. <br>
                    <h2><strong><a href="./pages/userPrints.php" target="_blank">3D Printing</a> <span style="font-size:35px;">·</span> <a href="./pages/userCuts.php" target="_blank">Laser Cutting</a> <span style="font-size:35px;">·</span> <a href="./pages/publicInventory.php" target="_blank">Inventory</a></strong></h2>
                    <h2><strong><a href="https://secure.touchnet.net/C20159_ustores/web/classic/store_main.jsp?STOREID=8" target="_blank">Marketplace</a> <span style="font-size:35px;">·</span> <a href="./pages/publicEquipmentList.php" target="_blank">Reserve Equipment</a> <span style="font-size:35px;">·</span> <a href="./pages/info.php">Technical Help</strong></h2> </a>
                    <h3>KEC 1110 Hours: <BR>
					Summer Term (6/26-8/17): M-Th 9am-11:50am<BR>
                    <td><a href='mailto:tekbot-worker@engr.orst.edu'> Email Us Here</a></td><BR></h3></h2>
                    
                    <a class="arrowLink" href="#about"><div class="arrow bounce"></div></a>

               </div>
          </header>

          <!-- Main content -->

               <div class="content-wrapper" id="about" style="padding:0;">

               

               <!-- Image Showcases -->
                    <section class="showcase">
                    <div class="container-fluid p-0">
                         <div class="row no-gutters">

                         <div class="col-lg-6 order-lg-2 text-white showcase-img" style="background-image: url('assets/img/storecabinet.jpg');"></div>
                         <div class="col-lg-6 order-lg-1 my-auto showcase-text">
                              <h2>TekBots Store</h2>
                              <p class="lead mb-0">The TekBots store contains all sorts of electronics and modules that can be purchased for projects or classes!  For more information about the items we have in stock, please check our entire inventory list <a href="./pages/publicInventory.php" target="_blank">here</a>.  This is also where ECE students pick up their lab kits.</p>
                         </div>
                         </div>
                    <section class="makerspace">
                         <div class="row no-gutters">
                         <div class="col-lg-6 text-white showcase-img" style="background-image: url('assets/img/workstation.jpg');"></div>
                         <div class="col-lg-6 my-auto showcase-text">
                              <h2>Maker Space</h2>
                              <p class="lead mb-0">The TekBots room features a small makerspace where students can come in to work on projects or assignments!  The equipment consists of a soldering iron, power supply, hardware tools, and much more!  Stop by to find out more information.</p>
                         </div>
                         </div>
                    </section>
                         <div class="row no-gutters">
                         <div class="col-lg-6 order-lg-2 text-white showcase-img" style="background-image: url('assets/img/3dprinter_unblurred.jpg');"></div>
                         <div class="col-lg-6 order-lg-1 my-auto showcase-text">
                              <h2>3D Printing &amp; Laser Cutting</h2>
                              <p class="lead mb-0">We provide services for 3D printing and Laser Cutting.  We can print using PLA and dual-extruder material and we can cut wood, acrylic, and some other materials.  For more information about these services, check out our <a href="./pages/info.php">FAQ</a>. To submit a print or cut request, head to <a href="./pages/userPrints.php" target="_blank">3D Printer Submission</a> or <a href="./pages/userCuts.php" target="_blank">Laser Cutter Submission</a>. </p>
                         </div>
                         </div>
                    </div>
                    </section>
               </div>
          </div>
          

<!--
               <div class="content-wrapper" id="calendar">
                    
                    <iframe src="https://calendar.google.com/calendar/embed?title=TekBots%20&amp;showTitle=0&amp;showNav=0&amp;showDate=0&amp;showPrint=0&amp;showTabs=0&amp;showCalendars=0&amp;showTz=0&amp;mode=WEEK&amp;bgcolor=%23000000&amp;src=oregonstate.edu_0053u0kv5bf6l7lube27096lls%40group.calendar.google.com&amp;color=%23865A5A&amp;ctz=America%2FLos_Angeles/#disable-mouse-scroll" style="border-width:0" frameborder="0" scrolling="no"></iframe>     
               </div>


               <div class="content-wrapper" id="contactUs">
                    <br><br>
                    <div class="col-md-10 offset-md-1">
                         <div class="faq-title text-center pb-3">
                              <h2>Contact Us</h2>
                         </div>
                    </div>

                    <form id="send_email" method="post" action="send_form_email.php" class="contact-form">
                  
                         <div class="row">
                              <div class="col-sm-6">
                                   <div class="input-block ">
                                        <label for="">First Name</label>
                                        <input id="first_name" type="text" class="form-control" name="first_name">
                                   </div>
                              </div>
                              <div class="col-sm-6">
                                   <div class="input-block">
                                   <label for="">Last Name</label>
                                   <input id="last_name" type="text" class="form-control" name="last_name">
                                   </div>
                              </div>
                         </div>
                         <div class="row">
                              <div class="col-sm-12">
                                   <div class="input-block">
                                   <label for="">Email</label>
                                   <input id="email" type="text" class="form-control" name="email">
                                   </div>
                              </div>
                         </div>
                         <div class="row">
                              <div class="col-sm-12">
                                   <div class="input-block">
                                   <label for="">Message Subject</label>
                                   <input id="subject" type="text" class="form-control" name="subject">
                                   </div>
                              </div>
                         </div>
                         <div class="row">
                              <div class="col-sm-12">
                                   <div class="input-block textarea">
                                   <label for="">Drop your message here</label>
                                   <textarea id="message" rows="3" type="text" name="comments" class="form-control"></textarea>
                                   </div>
                              </div>
                         </div>
                         
                         <div class="row">
                              <div class="col-sm-12">
                                   <button type="submit" class="btn-4 square-button"><span>Send</span></button>
                              </div>
                         </div>
                         <input type="hidden" name="recaptcha_response" id="recaptchaResponse">
                         <input type="hidden" name="ip" value="<?php echo $ip; ?>">
                    </form>
               </div>
		-->
		<script>
          
			//material contact form animation
		$('.contact-form').find('.form-control').each(function() {
		var targetItem = $(this).parent();
		if ($(this).val()) {
			$(targetItem).find('label').css({
			'top': '10px',
			'fontSize': '14px'
			});
		}
		});
		$('.contact-form').find('.form-control').focus(function() {
		$(this).parent('.input-block').addClass('focus');
		$(this).parent().find('label').animate({
			'top': '10px',
			'fontSize': '14px'
		}, 300);
		});
		$('.contact-form').find('.form-control').blur(function() {
		if ($(this).val().length == 0) {
			$(this).parent('.input-block').removeClass('focus');
			$(this).parent().find('label').animate({
			'top': '25px',
			'fontSize': '18px'
			}, 300);
		}
		</script>
<?php 
include_once PUBLIC_FILES . '/modules/footer.php'; 
?>
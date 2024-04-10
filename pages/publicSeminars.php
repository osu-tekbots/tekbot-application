<?php
//This page is linked to https://beav.es/ScG

include_once '../bootstrap.php';

use DataAccess\TicketDao;
use DataAccess\LabDao;

if (!session_id()) {
    session_start();
}

$title = "Seminars";

$css = array(
    'assets/css/sb-admin.css',
    'assets/css/admin.css',
    'https://cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css'
);
$js = array(
    'https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js'
);

$upcomingSeminars = [
  [
    'title'  => 'KiCad Overview: Linear Regulators',
    'time' => 'Wednesday, April 17 at 6:00pm',
    'location' => 'Location: WNGR 149',
    'image' => 'https://placehold.co/600x400',
    'details' => 'Join TekBots for an introduction to KiCad and PCB development for Linear Voltage Regulators. During this 1 hour session, we will discuss the basics of schematic entry and layout. Attendees will be given the opportunity for free components to build their own supply once they have completed their design! Design schematic can be found here (<a href="https://drive.google.com/file/d/1Kk9PPzLCA2-kYXbhFXEEAvyV3OcdFMm7/view">schematic.pdf</a>) and the bill of materials is found here (<a href="https://drive.google.com/file/d/1MGYdwOwrlyGHpibb6aiXqtRquPJCdn-B/view">BOM.csv</a>). Attendees will get the most out of the seminar if they bring a laptop with <a href="https://www.kicad.org/download/">KiCad v8.0</a> installed and have a mouse.'
  ]
];


include_once PUBLIC_FILES . '/modules/header.php';

?>

<br/>
<div id="page-top">
	<div id="wrapper">
        <div class="container-fluid">
			<div class="col-md-5">
				<br />
				<h3>TekBots Seminars</h3>
			</div>
			<div class="col-md-5 mt-4">
				<h4 class="h5">Upcoming</h4>
			</div>
            <div><!-- Closed by first iteration of loop below -->
            <?php
            foreach($upcomingSeminars as $index => $seminar) {
                $title = $seminar['title'];
                $time = $seminar['time'];
                $location = $seminar['location'];
                $image = $seminar['image'];
                $details = $seminar['details'];

                if($index % 3 == 0)
                    echo '</div><div class="card-deck mx-4 mb-4">';

                echo <<< HTML
                    <div class="col-lg-4">
                        <div class="card" style="height: fit-content !important;">
                            <div class="card-body">
                                <h5 class="card-title">$title</h5>
                                <h6 class="card-subtitle mb-2">$time</h6>
                                <h6 class="card-subtitle mb-2 text-secondary">$location</h6>
                                <p class="card-text">$details</p>
                            </div>
                        </div>
                    </div>
                HTML;
            }
            ?>
            <!-- Closed by last iteration of loop above --></div>
			<div class="col-md-5 mt-4">
				<h4 class="h5">Past</h4>
			</div>
        </div>
    </div>
</div>

<!-- 
  -- This modal can be triggered by a button with the attributes 
  --   type="button" data-toggle="modal" data-target="#detailsModal"
  -- Its content can be specified with the attributes (added via JS below)
  --   data-title="$title" data-details="$details"  
  -->
<div id="detailsModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p></p>
            </div>
        </div>
    </div>
</div>

<script>
/* 
 * Loads the details modal content when a modal trigger button is pressed
 * using the button's data-* attributes
 */
$('#detailsModal').on('show.bs.modal', function (event) {
    const button = $(event.relatedTarget);
    const title = button.data('title');
    const details = button.data('details');

    const modal = $(this);
    modal.find('.modal-title').text(title);
    modal.find('.modal-body p').text(details);
});

</script>

<?php 
include_once PUBLIC_FILES . '/modules/footer.php' ;
?>
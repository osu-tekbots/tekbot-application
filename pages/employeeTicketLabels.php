<?php
include_once '../bootstrap.php';

use DataAccess\TicketDao;
use DataAccess\LabDao;
use DataAccess\UsersDao;
use Model\Ticket;
use Model\Room;
use Model\Station;
use Model\StationContents;
use Util\Security;

if (!session_id()) {
    session_start();
}

// Make sure the user is logged in and allowed to be on this page
include_once PUBLIC_FILES . '/lib/shared/authorize.php';

allowIf(verifyPermissions('employee'), 'index.php');


$title = 'Employee Generate Lab Labels';
$css = array(
	'assets/css/sb-admin.css',
	'assets/css/admin.css',
	'https://cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css'
);
$js = array(
    'https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js'
);

$labelsHTML = "
	<style>
	body
	{
		font-family: 'Arial' , monospace;
		font-size:6pt;
	}

	header
	{
		display: none;
	}

	footer
	{
		display: none;
	}
	div.printpagelarge{
		page-break-inside: avoid;
		width:8.35in; 
		height:10.04in; 
		padding-left: .35in; 
		padding-right: 0 in; 
		padding-top: .45in; 
		padding-bottom: .48in; 
		float: none;
		/*padding: .5in .3in .5in .2in;*/
		box-sizing: border-box;
	}

	div.printlabellarge{
		width:4 in;
		min-width: 4in;
		height:2in;
		min-height: 2in;
		float:left;
		padding-left:.2 in;
		padding-right:.2 in;
		padding-top:.1 in;
		padding-bottom:.1 in;
		border-style:solid;
		border-color: white;
		box-sizing: border-box;
		font-size:6pt;
		background-color: #ffffff;
	}

	</style>";
	
	
$ticketDao = new TicketDao($dbConn, $logger);
$labDao = new LabDao($dbConn, $logger);
$userDao = new UsersDao($dbConn, $logger);
$stations = $labDao->getStations();
	
/* 
* -DOUBLECHECK- This section creates a page of large labels to print.
* If there are 10 items in the page, page is completed, otherwise continues to fill
*/
		$j = 0;
		$labelsHTML .= "<div class='printpagelarge'>";
		foreach ($stations AS $s){
				$qrCode = "<img src='https://chart.googleapis.com/chart?cht=qr&chs=200x200&chl=eecs.engineering.oregonstate.edu/education/tekbotSuite/tekbot/pages/publicTicketSubmit.php?station=".$s->getId()."'>";

				if ($j == 10){
					$labelsHTML .= '</div><div class="printpagelarge">';
					$j=0;
				}
				$labelsHTML .= '
				<div class="printlabellarge">
					<div style="width:1.8in;height:1.8in;float:left;" >'.$qrCode.'</div>
					<div style="float:left;width:2in;height:1.8in;padding-left:.05in;padding-top:.3in;">
						<font style="font-size: 14pt"><b>Report Issues:</b><br> 
						Room: '.$s->getRoom()->getName().'<BR> Bench: '.$s->getName().'<BR> https://beav.es/ScG <BR>
						</font>
					</div>
				</div>';
				$j++;
		}
/* 
* If the page is not full, continues to fill with the last label
*/
		if ($j != 10){
			while ($j != 10){
				$labelsHTML .= '
				<div class="printlabellarge">
					
				</div>';
				$j++;
			}
		}
		$labelsHTML .= "</div>";
	
	
	echo $labelsHTML;
	echo "<script>alert('When printing, you must select \'No Margin\' for correct scaling.');</script>";
	exit;
	?>
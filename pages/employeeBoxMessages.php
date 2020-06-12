<?php
include_once '../bootstrap.php';

use DataAccess\MessageDao;
use DataAccess\UsersDao;
use Model\EquipmentCheckoutStatus;
use Util\Security;

$tool_id = 4;

if (!session_id()) {
    session_start();
}

// Make sure the user is logged in and allowed to be on this page
include_once PUBLIC_FILES . '/lib/shared/authorize.php';

$isEmployee = isset($_SESSION['userID']) && !empty($_SESSION['userID']) 
	&& isset($_SESSION['userAccessLevel']) && $_SESSION['userAccessLevel'] == 'Employee';

allowIf($isEmployee, 'index.php');


$title = 'Employee Lockers';
$css = array(
	'assets/css/sb-admin.css',
	'assets/css/admin.css',
	'https://cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css'
);
$js = array(
    'https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js'
);

include_once PUBLIC_FILES . '/modules/header.php';
include_once PUBLIC_FILES . '/modules/employee.php';
include_once PUBLIC_FILES . '/modules/renderBrowse.php';

// Handout Modal Functionality
include_once PUBLIC_FILES . '/modules/newHandoutModal.php';

$messageDao = new MessageDao($dbConn, $logger);
$userDao = new UsersDao($dbConn, $logger);

//$messages = $messageDao->getMessages();
$messages = $messageDao->getMessagesByTool($tool_id);

?>
<script type='text/javascript'>
/*********************************************************************************
* Function Name: updateMessage(id)
* Description: Updates the content of a message.
*********************************************************************************/
function updateMessage(id) {
	var subject = document.getElementById('subject'+id).value;
	var body = document.getElementById('body'+id).value;
	var format = 1;
	
//		alert(subject);
//		alert(body);
//		alert(format);
	
	let content = {
		action: 'updateMessage',
		subject: subject,
		body: body,
		format: format,
		message_id: id
	}
	
	api.post('/message.php', content).then(res => {
		snackbar(res.message, 'Updated');
	}).catch(err => {
		snackbar(err.message, 'error');
	});
}

/*********************************************************************************
* Function Name: updateMessage(id)
* Description: Updates the content of a message.
*********************************************************************************/
function sendTestMessage(id) {
	let content = {
		action: 'sendMessage',
		replacements: '',
		email: 'heer@oregonstate.edu',
		message_id: id
	}
	
	api.post('/message.php', content).then(res => {
		snackbar(res.message, 'Updated');
	}).catch(err => {
		snackbar(err.message, 'error');
	});
}
</script>

<br/>
<div id="page-top">

	<div id="wrapper">

	<?php 
		// Located inside /modules/employee.php
		renderEmployeeSidebar();
	?>

    <div class="admin-content" id="content-wrapper">
        <div class="container-fluid">
            <?php 
                renderEmployeeBreadcrumb('Employee', 'Edit Box Messages');

               
                foreach ($messages as $m) {
                    $message_id = $m->getMessageId();
					$subject = $m->getSubject();
					$body = $m->getBody();
					$format = $m->getFormat();
					
					echo "<div class='admin-paper'>";

					echo '<h6>Message ID: '.$message_id.'</h6>';
					echo '<form>';
					echo '<div id="row'.$message_id.'" style="padding-left:4px;padding-right:4px;margin-top:4px;margin-bottom:4px;">
						  <div class="form-group row">
							<label for="subject'.$message_id.'" class="col-sm-2 col-form-label">Subject</label>
							<div class="col-sm-10"><input type="text" class="form-control" id="subject'.$message_id.'" value="'.Security::HtmlEntitiesEncode($subject).'"></div>
						  </div>';
					echo '<div class="form-group row">
							<label for="body'.$message_id.'" class="col-sm-2 col-form-label">Body</label>
							<div class="col-sm-8"><textarea type="text" class="form-control" id="body'.$message_id.'">'.$body.'</textarea></div>
							<div class="col-sm-2"><strong>Inserts</strong><BR>
							{{name}}: Full Name<BR>
							{{email}}: User Email<BR>
							{{number}}: TekBox Number<BR></div>
						  </div>';
					echo '<div class="form-group row">
							<label for="format'.$message_id.'" class="col-sm-2 col-form-label">Format</label>
							<div class="col-sm-10"><input type="text" class="form-control" id="format'.$message_id.'" value="Email" disabled></div>
						  </div>';
					echo '<div class="form-group row">
							<div class="col-sm-10"><button type="submit" class="btn btn-primary" onclick="updateMessage(\''.$message_id.'\');">Update</button>   <button type="submit" class="btn btn-primary" onclick="sendTestMessage(\''.$message_id.'\');">Test Stored Email</button></div>
						  </div>';  
					echo '</form>';

					echo "</div>";
					echo "</div>";
                }

                   
                
            ?>

        </div>
    </div>

<?php 
include_once PUBLIC_FILES . '/modules/footer.php' ; 
?>
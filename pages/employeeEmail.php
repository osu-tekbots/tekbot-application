<?php
/**
 * Temporary solution to allow Gareth to email people from tekbot-worker
 */
include_once '../bootstrap.php';

if (!session_id()) {
    session_start();
}

// Make sure the user is logged in and allowed to be on this page
include_once PUBLIC_FILES . '/lib/shared/authorize.php';

allowIf(verifyPermissions('employee'), 'index.php');

$title = 'Email From Tekbot-Worker';
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


?>
<br/>
<div id="page-top">

	<div id="wrapper">

	<?php 
		// Located inside /modules/employee.php
		renderEmployeeSidebar();
	?>

    <div class="admin-content" id="content-wrapper">
        <div class="container-fluid">
            <h5 class="modal-title w-100 text-center">Send Email</h5>

            <div class="alert alert-info mt-2">
                <i class="fa fa-info-circle"></i>
                Allows you to send an email from <code>tekbot-worker@engr.oregonstate.edu</code>. To send an email to
                several people simultaneously, seperate their email addresses with commas.
            </div>

            <div class="form-group row my-4">
                <label for="emailAddresses" class="col-xl-1">Recipients</label>
                <input id="emailAddresses" class="form-control form-control-lg col-xl-8">
            </div>
            <div class="form-group row my-4">
                <label for="emailSubject" class="col-xl-1">Subject</label>
                <input id="emailSubject" class="form-control form-control-lg col-xl-8">
            </div>
            <div class="form-group row my-4">
                <label for="emailBody" class="col-xl-1">Body</label>
                <textarea id="emailBody" class="form-control form-control-lg col-xl-8" rows="6"></textarea>
            </div>
            <div class="row pt-2">
                <div class="col">
                    <button type="button" class="btn btn-primary" onclick="sendEmail(this)">Send</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function sendEmail(thisVal) {
        let data = {
            action: 'sendEmail',
            addresses: document.getElementById('emailAddresses').value,
            subject: document.getElementById('emailSubject').value,
            body: document.getElementById('emailBody').value
        };

        thisVal.disabled = true;

        api.post('/email.php', data).then(res => {
            document.getElementById('closeEmailModal').click();
            document.getElementById('emailAddresses').value = '';
            document.getElementById('emailSubject').value = '';
            document.getElementById('emailBody').value = '';
            snackbar(res.message, 'success');
        }).catch(err => {
            snackbar(err.message, 'error');
        }).finally(() => thisVal.disabled = false);
    }
</script>
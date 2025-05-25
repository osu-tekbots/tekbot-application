<?php
include_once '../bootstrap.php';


if (PHP_SESSION_ACTIVE != session_status())
    session_start();

// Make sure the user is logged in and allowed to be on this page
include_once PUBLIC_FILES . '/lib/shared/authorize.php';

allowIf(verifyPermissions('employee', $logger), 'index.php');

$title = 'Admin Control';
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
                <div class="admin-paper" ">
                    <h1>Admin Control</h1>
                    <br>
                    <h3>Clear up the database here: </h3>
                    <p>Deletes respective both old files and entries in the database</p>
                    <br>

                    <div>
                        <label> Delete all files older than 2 years: </label>
                        <button style = 'float: right' type = 'button' onClick = {purgeAllOldFiles();}
                        class="btn btn-primary"> Purge all files </button>

                    </div>
                    <br>
                    <div>
                        <label> Delete all prints older than 2 years: </label>
                        <button style = 'float: right' type = 'button' onClick = {purgeOldPrints();}
                        class="btn btn-primary"> Purge Old Print files </button>
                    </div>
                    <br>
                    <div>
                        <label> Delete all laser cuts older than 2 years: </label>
                        <button style = 'float: right' type = 'button' onClick = {purgeOldLaserCuts();}
                        class="btn btn-primary"> Purge Old Laser Cuts </button>
                    </div>
                    <br>
                    <div>
                        <label> Delete all tickets older than 2 years: </label>
                        <button style = 'float: right' type = 'button' onClick = {purgeOldTickets();}
                        class="btn btn-primary"> Purge Old Tickets</button>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<script type='text/javascript'>
    function purgeAllOldFiles() {
        purgeOldLaserCuts();
        purgeOldPrints();
        purgeOldTickets();
    }
    function purgeOldTickets() {
        let data = {
            action: 'purgeOldTickets'
        };

        api.post('/tickets.php', data).then(res => {
            snackbar(res.message, 'success');
        }).catch(err => {
            snackbar(err.message, 'error');
        });
    }
    function purgeOldLaserCuts() {
        let data = {
            action: 'purgeOldLaserCuts'
        };

        api.post('/lasers.php', data).then(res => {
            snackbar(res.message, 'success');
        }).catch(err => {
            snackbar(err.message, 'error');
        });
    }
    function purgeOldPrints() {
        let data = {
            action: 'purgeOldPrintJobs'
        };

        api.post('/printers.php', data).then(res => {
            snackbar(res.message, 'success');
        }).catch(err => {
            snackbar(err.message, 'error');
        });
    }
</script>

<?php 
include_once PUBLIC_FILES . '/modules/footer.php' ; 
?>
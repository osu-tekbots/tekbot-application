<?php
include_once '../bootstrap.php';

use DataAccess\InventoryDao;

if(!session_id()) session_start();

include_once PUBLIC_FILES . '/lib/shared/authorize.php';

allowIf(verifyPermissions('employee'));

$inventoryDao = new InventoryDao($dbConn, $logger);


$title = 'Employee Interface';
$css = array(
    'assets/css/admin.css',
	'assets/css/sb-admin.css',
	'assets/css/jquery.dataTables.min.css'
);
$js = array(
    'assets/js/jquery.dataTables.min.js'
);

include_once PUBLIC_FILES . '/modules/header.php';
include_once PUBLIC_FILES . '/modules/employee.php';


$types = $inventoryDao->getTypes(true);
$typeTable = '';
foreach($types as $type) {
    $dateUpdated = "<i>Not Updated</i>";
    if($type->getDateUpdated())
        $dateUpdated = $type->getDateUpdated()?->format('m/d/Y');

    $typeTable .= "
        <tr>
            <td><input onchange='updateDescription(\"".$type->getId()."\", this.value)' class='form-control' value='".$type->getDescription()."'</td>
            <td>$dateUpdated</td>
            <td class='d-flex justify-content-end'>
                <button onclick='archiveType(\"".$type->getId()."\")' type='button' class='btn btn-outline-danger'>";
    if($type->getArchived())
        $typeTable .= "Unarchive";
    else
        $typeTable .= "Archive";
    $typeTable .= "
                </button>
            </td>
        </tr>
    ";
}

?>
<br/>


<div id="page-top">

	<div id="wrapper">

		<?php
			renderEmployeeSidebar();
		?>

		<div id="content-wrapper">

			<div class="container-fluid">
                <div class="admin-paper">
                    <div class="alert alert-warning">Please let Don know if you add a new type.</div>
                    <form class="form-inline d-flex justify-content-center" style="gap: 16px">
                        <span class="form-inline d-flex" style="gap: 8px">
                            <label for="new-name">Name</label>
                            <input id="new-name" class="form-control">
                        </span>
                        <button class="btn btn-success" onclick="addType()" type="button">Add Type</button>
                    </form>
                </div>

                <div class="admin-paper">
                    <div class="row">
                        <div class="col">
                            <table id="TypeTable">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Date Updated</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?= $typeTable ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
		</div>
	</div>
</div>


<script type='text/javascript'>
$('#TypeTable').DataTable({
    "autoWidth": true,
    'scrollX':false, 
    'paging':false, 
    'order':[[0, 'asc']],
    'columns': [
        null,
        null
    ]
});

function addType() {
    let content = {
		action: 'addType',
		description: document.getElementById('new-name').value,
	}
	
	api.post('/inventory.php', content).then(res => {
		snackbar(res.message, 'info');
        setTimeout(() => window.location.reload(), 1000);
	}).catch(err => {
		snackbar(err.message, 'error');
	});
}

function archiveType(id) {
	let content = {
		action: 'toggleArchiveType',
		id: id,
	}
	
	api.post('/inventory.php', content).then(res => {
		snackbar(res.message, 'info');
        setTimeout(() => window.location.reload(), 1000);
	}).catch(err => {
		snackbar(err.message, 'error');
	});
}

function updateDescription(id, description) {
	let content = {
		action: 'updateTypeDescription',
		id: id,
        description: description
	}
	
	api.post('/inventory.php', content).then(res => {
		snackbar(res.message, 'info');
        setTimeout(() => window.location.reload(), 1000);
	}).catch(err => {
		snackbar(err.message, 'error');
	});
}

</script>

<?php 
include_once PUBLIC_FILES . '/modules/footer.php' ; 
?>


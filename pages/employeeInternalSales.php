<?php
include_once '../bootstrap.php';


use DataAccess\InternalSalesDao;
use DataAccess\UsersDao;
use Util\Security;


// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL); 


if (PHP_SESSION_ACTIVE != session_status())
    session_start();

// Make sure the user is logged in and allowed to be on this page
include_once PUBLIC_FILES . '/lib/shared/authorize.php';

allowIf(verifyPermissions('employee', $logger), 'index.php');


$title = 'Internal Sales';
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

$internalSalesDao = new InternalSalesDao($dbConn, $logger);
$sales = $internalSalesDao->getSales();


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
			<!-- Form for data entry -->
			<div class="admin-paper col-lg-5">
				<h3>New Transaction:</h3>
				<input type="hidden" value="add" name="action">
				<div class="form-group">
					<label>Buyer</label>
					<input class="form-control" type="text" id="addbuyer" name="buyer" required placeholder="Enter Buyer Name" size="40">
				</div>
				<div class="form-group">
					<label>Buyer Email</label>
					<input class="form-control" type="email" id="addemail" name="email" size="40" required placeholder="Enter a valid email address">
				</div>
				<div class="row">
					<div class="col-6">
						<div class="form-group mb-0">
							<label>Workday ID</label>
							<input class="form-control" type="text" id="addaccount" name="account" size="12" required placeholder="XXXXX" oninput="this.value = this.value.toUpperCase();">
						</div>
					</div>
					<div class="col-6">
						<div class="form-group mb-0">
							<label>Activity ID</label>
							<input class="form-control" type="text" id="addactivity" name="account" size="12" required placeholder="XXXXX" oninput="this.value = this.value.toUpperCase();">
						</div>
					</div>
				</div>
				<small class="text-muted">If purchasing for ENGR201 or ENGR202, use the account code <code>ESE025</code>.</small>
				
				<div class="form-group">
					<label>Amount</label>
					<div class="input-group">
						<div class="input-group-prepend"><div class="input-group-text">$</div></div>
						<input class="form-control" size="7" type="text"  id="addamount" name="amount" required pattern="\d+(\.\d{2})?" placeholder="X.XX">
					</div>
				</div>
				<div class="form-group">
					<label>Description of Purchased Items</label>
					<textarea class="form-control" id="adddescription" name="description" ROWS="6" COLS="40" required placeholder="Please be detailed in your description."></textarea>
				</div>
				<div class="form-group">
					<label>Seller</label>
					<input class="form-control" type="text" id="addseller" name="seller" required placeholder="Enter Your (Seller) Name" size="40">
				</div>
				<button id="addSale" class="btn btn-primary btn-lg"onclick="addSale();">Add</button>
			</div>
			<div class="admin-paper" style="overflow-x: scroll">
				<h3>Transactions:</h3>
				<button id="billAllInternalSales" class="btn btn-primary btn-lg float-right mb-2" onclick="billAllInternalSales();">Bill All</button>
				<table class="table" id="internalSales">
					<thead>
						<tr>
							<th>Id</th>
							<th>Date</th>
							<th>Email</th>
							<th>Account</th>
							<th>Amount</th>
							<th>Buyer</th>
							<th>Seller</th>
							<th>Description</th>
							<th>Bill Date</th>
							<th>Delete</th>
						</tr>
					</thead>
					<tbody>
			
			<?php
			/********************************
			This creates the transaction table 
			for each piece of transaction information 
			*********************************/
			foreach ($sales as $s) {
                $saleId = $s->getSaleId();
                $timestamp = $s->getTimestamp();
                $email = $s->getEmail();
                $account = $s->getAccount();
                $amount = $s->getAmount();
                $buyer = $s->getBuyer();
                $seller = $s->getSeller();
                $description = $s->getDescription();
                $processed = $s->getProcessed() == '0000-00-00 00:00:00' ? '<i>Not billed</i>' : $s->getProcessed();
                    
				echo "
				<tr>
					<td>$saleId</td>
					<td>$timestamp</td>
					<td><a href='mailto:$email'>$email</a></td>
					<td>$account</td>
					<td>$$amount</td>
					<td>$buyer</td>
					<td>$seller</td>
					<td>$description</td>
					<td>$processed</td>
					<td><button id='deleteSale' onclick='deleteSale($saleId);' class='btn btn-outline-danger'><i class='fas fa-fw fa-trash'></i></button></td>
				</tr>
				";
                }

			echo " </tbody> </table>";

			?>
			<script type='text/javascript'>
				
				/********************************
				This function adds a sale only if 
				all the form items are filled out 
				and also reloads the page
				*********************************/
				function addSale(){
		
					let buyer =  $('#addbuyer').val().trim();
					let email =  $('#addemail').val().trim();
					let account =  $('#addaccount').val().trim();
					let amount =  $('#addamount').val().trim();
					let seller =  $('#addseller').val().trim();
					let description =  $('#adddescription').val().trim();
					if ($('#addactivity').val().trim())
						account += '-' + $('#addactivity').val().trim();

					let data = {
						buyer: buyer,
						email: email,
						account: account,
						amount: amount,
						seller: seller,
						description: description,
						action: 'addSale'
					};

					//This makes sure that the form has all the needed information
					if (buyer != '' && email != '' && account != '' && amount != '' && seller != '' && description != ''){
						api.post('/internalSales.php', data).then(res => {
							//console.log(res.message);
							snackbar(res.message, 'info');
							location.reload();
						}).catch(err => {
							snackbar(err.message, 'error');
						});
					} else {
						alert('Form left empty, no changes made');
					}
				}

				/********************************
				This function deletes a sale by id 
				and asks the user for confirmation
				before deletion. The page is also
				reloaded.
				*********************************/
				function deleteSale(saleId){
					if(confirm('Confirm deletion of this sale?')){
							let data = {
								saleId: saleId,
								action: 'deleteSale',
							}
							api.post('/internalSales.php', data).then(res => {
								//console.log(res.message);
								snackbar(res.message, 'info');
								location.reload();
							}).catch(err => {
								snackbar(err.message, 'error');
							});
						}
					
				}

				/********************************
				This function bills all sales that are
				unprocessed, it processes them and
				sends Don an email
				*********************************/
				function billAllInternalSales(){
					if(confirm('Bill all sales?')){
							let data = {
								messageID: 'fiuywuo837945ywk',
								action: 'billAllInternalSales'
							}
							api.post('/internalSales.php', data).then(res => {
								//console.log(res.message);
								snackbar(res.message, 'info');
								setTimeout(() => location.reload(), 3000);
							}).catch(err => {
								snackbar(err.message, 'error');
							});
						}
					
				}
			

				$('#internalSales').DataTable(
					{
						lengthMenu: [[10, 20, -1], [10, 20, 'All']],
						aaSorting: [[0, 'desc']]
					}
				);

			</script>
			</div>
		</div>
	</div>
</div>

<?php 
include_once PUBLIC_FILES . '/modules/footer.php' ; 

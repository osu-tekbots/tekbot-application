<?php
include_once '../bootstrap.php';


use DataAccess\InternalSalesDao;
use DataAccess\UsersDao;
use Util\Security;


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL); 


if (!session_id()) {
    session_start();
}

// Make sure the user is logged in and allowed to be on this page
include_once PUBLIC_FILES . '/lib/shared/authorize.php';

$isEmployee = isset($_SESSION['userID']) && !empty($_SESSION['userID']) 
	&& isset($_SESSION['userAccessLevel']) && $_SESSION['userAccessLevel'] == 'Employee';

allowIf($isEmployee, 'index.php');


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
include_once PUBLIC_FILES . '/modules/renderBrowse.php';

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
			<?php 
			echo "
				<div class='admin-paper w-35 p-10 h-25 d-inline-block'>
				<h3>New Transaction: </h3> 
							<input type='hidden' value='add' name='action'>
							Buyer:<input type='text' class='row mb-3 ml-5 mr-5' id='addbuyer' name='buyer' required placeholder='Enter Buyer Name' size='40'>
							Buyer Email: <input type='email' class='row mb-3 ml-5' id='addemail' name='email' size='40' required placeholder='Enter a valid email address'>
							Account Number: <input type='text' class='row mb-3 ml-5' id='addaccount' name='account' size='12' required placeholder='XXXXX-XXXX' oninput='this.value = this.value.toUpperCase();'><p>If purchasing for ENGR201 or ENGR202 use the account code: ESE025</p>
							Amount: $<input size='7' type='text'  class='row mb-3 ml-5' id='addamount' name='amount' required pattern='\d+(\.\d{2})?' placeholder='X.XX'>
							Description of Purchased Items:<BR><textarea id='adddescription' class='row mb-3 ml-5' name='description' ROWS='6' COLS='40' required placeholder='Please be detailed in your description.'></textarea>
							Seller: <input type='text' id='addseller' class='row ml-5' name='seller' required placeholder='Enter Your (Seller) Name' size='40'>
							<button id='addSale' class='btn btn-primary btn-lg row mt-3 ml-5'onclick='addSale();'>Add</button>
				</div>
			";
			echo "
			<div class='admin-paper'>
				<h3>Transactions:</h3><button id='billAllInternalSales' class= 'btn btn-primary btn-lg float-right mb-2' onclick='billAllInternalSales();'>Bill All</button>
					<table class='table' id='internalSales'>
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
								<th>Billed?</th>
								<th>Delete</th>
							</tr>
						</thead>
						<tbody>
			</div>	
			";
			
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
                $processed = $s->getProcessed();
                    
				echo "
				<tr>
					<td>$saleId</td>
					<td>$timestamp</td>
					<td><a href='mailto:$email'>$email</a></td>
					<td>$account</td>
					<td>$amount</td>
					<td>$buyer</td>
					<td>$seller</td>
					<td>$description</td>
					<td>$processed</td>
					<td><button id='deleteSale' onclick='deleteSale($saleId);'><i class='fas fa-fw fa-trash'></i></button></td>
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

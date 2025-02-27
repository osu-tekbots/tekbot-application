<?php
include_once '../bootstrap.php';

use DataAccess\FaqDao;
use Util\Security;

if (PHP_SESSION_ACTIVE != session_status())
    session_start();

// Make sure the user is logged in and allowed to be on this page
include_once PUBLIC_FILES . '/lib/shared/authorize.php';

allowIf(verifyPermissions('employee', $logger), 'index.php');


$title = 'Edit Faq';
$css = array(
    'assets/css/sb-admin.css'
);
$js = array(
    array(
        'defer' => 'true',
        'src' => 'assets/js/edit-faq.js'
    )
);
include_once PUBLIC_FILES . '/modules/header.php';
include_once PUBLIC_FILES . '/modules/employee.php';


$dao = new FaqDao($dbConn, $logger);

if (isset($_GET['id'])){
    $id = $_GET['id'];
} else {
    $id = "";
}


$faq = $dao->getFaq($id);

if ($faq) {
	$category = $faq->getCategory();
	$question = $faq->getQuestion();
	$answer = $faq->getAnswer();
} else {
    $category = "";
    $question = "";
    $answer = "";
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
                <div class="container"><br>
                <form id="faqForm">
                <h5>Category:</h5>
                <?php 
                echo '
                <select name="category" class="custom-select">
                    <option value=""></option>
                    <option '. ($category == '3D Printing' ? 'selected' : '') .' value="3D Printing">3D Printing</option>
                    <option '. ($category == 'Laser Cutting' ? 'selected' : '') .' value="Laser Cutting">Laser Cutting</option>
                    <option '. ($category == 'Equipment Checkout' ? 'selected' : '') .' value="Equipment Checkout">Equipment Checkout</option>
                    <option '. ($category == 'Payment' ? 'selected' : '') .' value="Payment">Payment</option>
                    <option '. ($category == 'General' ? 'selected' : '') .' value="General">General</option>
                    <option '. ($category == 'Store' ? 'selected' : '') .' value="Store">Store</option>
                </select>
                ';
                ?>
                <br>
                <br>
                <h5>Question:</h5>
                <textarea class="form-control" rows="3" type="text" name="question"><?php echo ($question); ?></textarea>
                <br>
                <h5>Answer:</h5>
                <textarea class="form-control" rows="10" type="text" name="answer"><?php echo ($answer); ?></textarea>
                <br>
                <input style="display:none" name="id" value="<?php echo ($id); ?>">
                <?php 
                if (!empty($faq)){
                    // Update
                    echo '
                    <input type="button" value="Update FAQ" class="btn btn-lg btn-primary" onclick="onUpdateFaqClick();"/>
                    ';
                } else {
                    echo '
                    <input type="button" value="Add New FAQ" class="btn btn-lg btn-primary" onclick="onCreateFaqClick();"/>
                    ';
                }
                ?>
                </form>
                </div>







            </div>
        </div>
	
	</div>
</div>

<script>



</script>

<?php 
include_once PUBLIC_FILES . '/modules/footer.php' ; 
?>


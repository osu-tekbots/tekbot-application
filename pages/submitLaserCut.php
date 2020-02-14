<?php
include_once '../bootstrap.php';

use Util\Security;

//Add the Daos and Models you need here
use DataAccess\EquipmentDao;


session_start();

/*
 * TO DO: Reference the submit3DPrint and use npm in order to install three-dxf. 
 * Reference the sample folder within the three-dxf library to see how to incorporate
 * the file upload viewer functionality. Make sure to set permissions correctly on the 
 * terminal for all the files. 
 * File can be found here: https://github.com/gdsestimating/three-dxf
 */

$title = 'Laser Cut Submission';
 
$css = array(
	'https://cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css'
);
$js = array(
    'https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js',
	
);

include_once PUBLIC_FILES . '/modules/header.php';
include_once PUBLIC_FILES . '/modules/employee.php';
?>

	<script type="text/javascript">
		function Upload(action,id) {
			var html= '<B>LOADING</B>';
			$('#txt'+id).html(html).css('visibility','visible');
			var file_data = $('#file'+id).prop('files')[0]
			var form_data = new FormData();
			form_data.append('file', file_data);
			form_data.append('action',action);
			form_data.append('id',id);
			
			$.ajax({ 
				url: './ajax/Handler.php', 
				type: 'POST',
				dataType: 'json',	/*what we are expecting back*/
				contentType: false,
				processData: false,
				data: form_data, 
				success: function(result)
				{
					if(result["successful"] == 1)
					{
						var setPath = document.getElementById("uploadpath");
						setPath.value = result["path"];
						var html= '<B><font color="green">✓</font></B>' + '<a href="'+result["path"]+'">' + result["string"] + '</a>';
						
					}
					else if(result["successful"] == 0)
						var html= '<font color="red">❌ </font> Error: '+result["string"];
						else
							var html= result["string"];
					$('#txt'+id).html(html).css('visibility','visible');
				},
				error: function(result)
				{
					var html= '<font color="red">❌ </font> Failed: '+result["string"];
					$('#txt'+id).html(html).css('visibility','visible');
				}
			});
		}

	</script>

<br/><br/><br/>

<div class="container-fluid">
    <div class="row">
        <div class="col-sm-6">
            <h1>Laser Cutter Submission Form</h1>
            <p>Using this form you can upload a .dxf file to be created and cut using the laser cutter. It produces final models made out of the material which you can chose from the material list below. Once a file is uploaded, we will review the model and email you with the cost to cut. Once you approve the charge, we will start cutting the model.
            </p>
            <p>If you would like to pay via credit card, please submit your file with this form and enter 'Credit Card' in the account code field. We will reply with instructions on how to submit payment.
            </p>
            <p>Q: How big can your Laser cutter cut? <br> A: Our sheet size is 290 mm x 220mm. However, in order to ensure that your part fits, you should design your part to be at least 5mm shorter than the sheet size on both sides. The x-axis is the longer side on our laser cutter, please try to design your parts accordingly to reduce the processing time for your part.<br><br>Q: What units should my .dxf file be in?<br>A: Your .dxf MUST be in millimeters. Q: How can I keep my cutting costs down?<br>A: The price depends on the material which is being used. *Remember, if a cutting needs to be repeated make sure they are in one sheet if it fits, since every cutting job submitted WILL be charged as a separate sheet regardless if it uses the entire sheet or not. Q: How much does it cost?<br>A: Our minimum purchase is for a single sheet of material, regardless of the part size. Currently every file you submit is processed and charged as a single sheet, so it is cheaper to combine your parts into a single .dxf file before submitting. You can see our current prices at the TekBots Online Store. Student pricing is 50% off the listed price (discount code generated after order submission). Q: How do I combine my parts into a single dxf file?<br>A: You may either do this in your CAD software before exporting the dxf or by using software that can edit dxf files, like Inkscape. DraftSight is another option that is a 2D CAD tool. Q: How can I be sure my files are cutable?<br>A: Once you have created your dxf file, open it with the free InkScape software (www.inkscape.org). When the file is imported, be sure that you DO NOT select the 'scale to page' option or your dimensions will be incorrect. If this opens and your dxf appears to be correct, then you should be good to submit for cutting.
            </p>
        </div>
        <div class='col-sm-6'>
            <form id='submit' action="redirect.php">

                <b>Email: </b> (Must be valid to confirm order)
				<input name="emailInput" class="form-control" type="email" placeholder="Enter your email here..." id="emailInput" form="submit">
				<br/>First Name:
				<input type="text" name="firstNameInput" class="form-control" placeholder="Enter your first name here..." id="firstNameInput" form="submit" required></input>
				<br/>Last Name:
				<input type="text" name="lastNameInput" class="form-control" placeholder="Enter your last name here..." id="lastNameInput" form="submit" required></input>
                <b>Payment Method:</b>
                <BR/>
                <input type="radio" name="accounttype" value="cc">
				Credit Card? 
				<input type="radio" name="accounttype" value="account">
				OSU Account Code:
				<input class=fi type=text size=30 name=account value="">
                <BR>*Note:<b> We can not directly bill your student account.</b> Students must use the credit card option. Do not enter your credit card info here.*
                <br/>
				<br/><b>Quantity</b><br/>
                <select name="quantity" form="mainform"><option value="1">1</option><option value="2">2</option><option value="3">3</option><option value="4">4</option><option value="5">5</option><option value="6">6</option><option value="7">7</option><option value="8">8</option><option value="9">9</option><option value="10">10</option></select>
                <br/><b>Material</b>
                <BR>Costs shown are reflective of the price after student discount. Code generated after order submission

                <select name="print_quality" form="mainform">
	
	
	<option value="Stencil">Stencil ($2.00)</option>
	<option value="Clear_Acrylic_3mm">Clear Acrylic - 3mm (1/8") ($5.00)</option>
	<option value="Clear_Acrylic_5mm">Clear Acrylic - 5mm (1/4") ($8.00)</option>
	<option value="Plywood_5mm">Plywood - 5mm (7/32") ($5.00)</option>
	<option value="Plywood_3mm">Plywood - 3mm (1/8") ($5.00)</option>
	</select>
                <br/>
                <b>Select Laser Cutter</b>
                <select name="laserCutterId" form="mainform"><option value="1">KEC1111 Laser Cutter</option></select>
                <br/>
                <b>Notes</b>
                <BR>Any special instructions or deadlines that you have should be entered here
                <textarea name=notes rows="4" cols="50"></textarea><br/>

                <input type="file" id="fileInput" name="fileInput" multiple>
            </form>
            <div id="target"></div>
  
            <br/>
            <button>Submit</button>
			<br/><br/>
        </div>
    </div>
</div>


<script>

</script>
<?php
include_once PUBLIC_FILES . '/modules/footer.php';
?>


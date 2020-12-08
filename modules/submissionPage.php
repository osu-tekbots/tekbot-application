<?php


// Maybe make printer and laser cut inherit from a class so that this can just pull a general "id"
// Can declare function parameter types here: https://www.php.net/manual/en/functions.arguments.php
function renderSelector($listOfObjects, $valueGetter, $contentGetter, $id)
{
	$optionsHtml = "";
	foreach ($listOfObjects as $o) {
		$value = $valueGetter($o);
		$content = $contentGetter($o);
		$optionsHtml .= <<< HTML
<option value={$value}>{$content}</option>
HTML;
	}
	echo <<< HTML
			<select class="custom-select" name="{$id}" id="{$id}">
			$optionsHtml
			</select><br />
HTML;
}

function renderPaymentForm()
{
	echo <<< HTML
<div class="form-check">
				<input class="form-check-input" id="voucherRadio" type="radio" name="accounttype" value="voucher">
				Voucher Code:
				<input class=fi id="voucherInput" type=text size=30 name=account value="">
			</div>
			<div class="form-check">
				<input class="form-check-input" id="accountRadio" type="radio" name="accounttype" value="account">
				OSU Account Code:
				<input class=fi id="accountInput" type=text size=30 name=account value="">
			</div>
			<div class="form-check">
				<input id="paymentradio1" class="form-check-input" type="radio" name="accounttype" value="cc">
				<label class="form-check-label" for="paymentradio1">
					Credit Card?
				</label>
			</div>
			<BR>*Note:<b> We can not directly bill your student account.</b> Students must use the credit card option. Do not enter your credit card info here.*
			<br />
HTML;

	echo <<< 'JS'
<script>
	$('#voucherInput').focus(function() {
		$('#voucherRadio').prop("checked", true);
	});

	$('#accountInput').focus(function() {
		$('#accountRadio').prop("checked", true);
	});

	function getPaymentMethod() {
		return $("input[type=radio][name=accounttype]:checked").val();
	}

</script>
JS;
}




// Add JS script here too to return value of inputs

<?php

function renderUserFixedInput($user)
{
    echo <<< HTML
        			<div>
				Email:<br />
				<input disabled name="emailInput" class="form-control" type="email" value="{$user->getEmail()}" id="emailInput" />
				First Name: <br />
				<input disabled name="firstNameInput" class="form-control" value="{$user->getFirstName()}" placeholder="Enter your first name here..." id="firstNameInput" />
				Last Name: <br />
				<input disabled name="lastNameInput" class="form-control" value="{$user->getLastName()}" placeholder="Enter your last name here..." id="lastNameInput" />
				<br />
				<input disabled name="userIDInput" id="userIDInput" value="{$user->getUserID()}" hidden />
			</div>
HTML;
}

?>
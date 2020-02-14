<div class="modal fade" id="newReservationModal">
    <br><br><br><br>
	<div class="modal-dialog modal-lg">
		<div class="modal-content">

			  <!-- Modal Header -->
			  <div class="modal-header">
				<h4 class="modal-title">Reserve Equipment For 1 Hour</h4>
				<button type="button" class="close" data-dismiss="modal">&times;</button>
			  </div>

			  <!-- Modal body -->
			  <div class="modal-body">
		
				<h5>This is just a reservation indicating that you will pick up the item at TekBots (KEC 1110) within the next hour.</h5>
				<h5>If you fail to pick up the item within the next hour your reservation will expire and other users will be able to reserve the item.</h5>
				<h5>By hitting the 'Reserve' button below, you are agreeing to the following terms and conditions when renting out the equipment.</h5>
				<br>
				<h5>Terms & Conditions:</h5>
				<b>Responsibility and Use & Disclaimer Warrenties:</b> You are responsible for the use of the rented
				items. You assume all risks inherent to the operation and use of rented items, and agree to
				assume the entire responsibility for the defense of, and to pay, indemnity and hold Above All
				Party Rentals harmless from and hereby release Above All Party Rentals from, all claims for
				damage to property or bodily injury (including death) resulting from the use, operation or
				possession of the items, whether or not it be claimed or found that such damage or injury
				resulted in whole or part from Above All Party Rentals negligence, from the defective condition
				of the items, or any other cause. YOU AGREE THAT NO WARRANTIES EXPRESSED OR IMPLIED,
				INCLUDING MERCHANTIBILITY OR FITNESS FOR A PARTICULAR PURPOSE HAVE BEEN MADE IN
				CONNECTION WITH THE EQUIPMENT RENTED.
				<br><br>
				<b>Equipment Failure:</b> You agree to immediately discontinue the use of rented items should it at
				any time become unsafe or in a state of disrepair, and will immediately (one hour or less) notify
				Above All Party Rentals of the facts. Above All Party Rentals agrees at our discretion to make the
				items operable in a reasonable time, or provide a like items if available, or make a like item
				available at another time, or adjust rental charges, The provision does not relieve renter from
				obligations of contract. In all events Above All Party Rentals shall not be responsible for injury or
				damage resulting in failure or defect of rented item.
				<br><br>
				<b>Equipment Responsibility:</b> Renter is responsible for equipment from time of possession to time
				of return. Renter assumes the entire risk of loss, regardless of cause. If items are lost, stolen,
				damaged, renter will assume cost of replacemt or repair, including labor costs. Renter shall pay
				a reasonable cleaning charge for rented items returned dirty.
				<br><br>
				<b>Time of Return:</b> Renter's right of possession terminates upon the expiration of rental period set
				forth on the contract. Time is of the essence in this contract. Any extension must be agreed
				upon in writing.
				<br><br>
				<b>Late Returns:</b> Renter shall return rented items to Above All Party Rentals during regular
				business hours, promptly upon. or prior to expiration of rental period. If renter does not timely
				return, the rental rate shall continue until items are returned.
			  </div>

			 <!-- Modal footer -->
			  <div class="modal-footer">
				<button type="button" class="btn btn-success" data-dismiss="modal" id="createReservationBtn">Reserve</button>
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
			  </div>

		</div>
	</div>
</div>


<script type="text/javascript">
	$(document).ready(function() {
	  $('#newReservationModal').on('shown.bs.modal', function() {
		$('#equipmentNameInput').trigger('focus');
	  });
	  $('#newReservationModal').on('hide.bs.modal', function() {
		$('#equipmentNameInput').val('');
	  });
	});
</script>
<div class="modal fade" id="newEquipmentModal">
    <br><br><br><br>
	<div class="modal-dialog modal-lg">
		<div class="modal-content">

			  <!-- Modal Header -->
			  <div class="modal-header">
				<h4 class="modal-title">Create New Equipment</h4>
				<button type="button" class="close" data-dismiss="modal">&times;</button>
			  </div>

			  <!-- Modal body -->
			  <div class="modal-body">
					<input id="equipmentNameInput" class="form-control form-control-lg" type="text" placeholder="Equipment name goes here...">
			  </div>

			 <!-- Modal footer -->
			  <div class="modal-footer">
				<button type="button" class="btn btn-success" data-dismiss="modal" id="createEquipmentBtn">Create Equipment</button>
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
			  </div>

		</div>
	</div>
</div>


<script type="text/javascript">
	$(document).ready(function() {
	  $('#newEquipmentModal').on('shown.bs.modal', function() {
		$('#equipmentNameInput').trigger('focus');
	  });
	  $('#newEquipmentModal').on('hide.bs.modal', function() {
		$('#equipmentNameInput').val('');
	  });
	});
</script>
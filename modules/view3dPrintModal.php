
<div class="modal fade" id="view3dModel">
    <br><br><br><br>
	<div class="modal-dialog modal-lg">
		<div class="modal-content">

			  <!-- Modal Header -->
			  <div class="modal-header">
				<h4 class="modal-title">3D Print Submission</h4>
				<button type="button" class="close" data-dismiss="modal">&times;</button>
			  </div>

			  <!-- Modal body -->
			  <div id="body" class="modal-body">

			  </div>

			 <!-- Modal footer -->
			  <div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Exit</button>
			  </div>

		</div>
	</div>
</div>


<script type="text/javascript">
	$(document).ready(function() {
	  $('#view3dModel').on('shown.bs.modal', function(e) {
		$('#body').append("<div id=target></div>")
		var button = $(e.relatedTarget);
		var dbFile = button.data('whatever');
		let path = 'uploads/prints/' + dbFile;
		var madeleine = new Madeleine({
		target: 'target', // target div id
		data: path, // data path
		path: 'assets/Madeleine.js/src/' // path to source directory from current html file
		});
	  });
	  $('#view3dModel').on('hide.bs.modal', function() {
		$('#target').remove();
	});
	});
</script>
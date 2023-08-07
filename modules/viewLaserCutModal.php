
<div class="modal fade" id="viewLaserCutModel">
    <br><br><br><br>
	<div class="modal-dialog modal-lg">
		<div class="modal-content">

			  <!-- Modal Header -->
			  <div class="modal-header">
				<h4 class="modal-title">Laser Cut Submission</h4>
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
	  $('#viewLaserCutModel').on('shown.bs.modal', function(e) {
		$('#body').append("<div id=target></div>")
		var button = $(e.relatedTarget);
		var dbFile = button.data('whatever');
		let path = 'uploads/lasercuts/' + dbFile;
		var parser = new window.DxfParser();
		var dxf = parser.parseSync(path.result);
		cadCanvas = new ThreeDxf.Viewer(dxf, document.getElementById('body'), 400, 400)
	  });
	  $('#viewLaserCutModel').on('hide.bs.modal', function() {
		$('#target').remove();
	});
	});
</script>
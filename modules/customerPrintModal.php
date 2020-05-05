
<div class="modal fade" id="newReservationModal">
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

				<!-- <h5>This is just a reservation indicating that you will pick up the item at TekBots (KEC 1110) within the next hour.</h5>
				<div id="stl_cont">
				<script>
					// var stl_viewer=new StlViewer(document.getElementById("stl_cont"), { models: [ {id:0, filename:"foo.stl"} ] });
				</script>
				<script>
					var stl_viewer=new StlViewer ( document.getElementById("stl_cont") );
				</script>
				 <input type="file" onchange='stl_viewer.add_model({local_file:this.files[0]});' accept="*.*">
				</div>-->
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
	  $('#newReservationModal').on('shown.bs.modal', function(e) {
		$('#body').append("<div id=target></div>")
		var button = $(e.relatedTarget);
		var dbFile = button.data('whatever');
		let path = 'prints/' + dbFile;
		var madeleine = new Madeleine({
		target: 'target', // target div id
		data: path, // data path
		path: 'assets/Madeleine.js/src/' // path to source directory from current html file
		});
	  });
	  $('#newReservationModal').on('hide.bs.modal', function() {
		$('#target').remove();
	});
	});
</script>
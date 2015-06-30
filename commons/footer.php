	<div id="wait" class="modal"></div>
	<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="js/jquery.min.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="js/bootstrap.min.js"></script>
    <script src="js/bootstrap-dropdown.js"></script>

    <script type="text/javascript">
	    $(document).ready(function(){
	    	$('#wait').hide();
	    	
	    	$("#scanDir").click(function(){
	    		$('#wait').show();
	    	    $.ajax({
	    		      type: "POST",
	    		      dataType: "text",
	    		      url: 'backend/server.php',
	    		      data:{action:'actualize'},
	    		      success: function(data) {
	    		    	  $('#wait').hide();
	    		      }
	    		    });
	    		});

	    	$("#resetVotes").click(function(){
	    		$('#wait').show();
	    	    $.ajax({
	    		      type: "POST",
	    		      dataType: "text",
	    		      url: 'backend/server.php',
	    		      data:{action:'resetVotes'},
	    		      success: function(data) {
	    		    	  $('#wait').hide();
	    		      }
	    		    });
	    		});
	    });

    </script>
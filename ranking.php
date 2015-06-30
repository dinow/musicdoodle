<?php include("commons/header.php"); ?>
	
        <div class="table-responsive" id="mainTable">
          <table class="table table-hover table-condensed" >
            <thead>
              <tr>
              	<th>&nbsp;</th>
              	<th>Artist</th>
                <th>Album</th>
                <th>Track</th>
              </tr>
            </thead>
            <tbody id="tabend">

			</tbody>
          </table>
        </div>
	</div>
	<?php include("commons/footer.php"); ?>
    
    <script type="text/javascript">
	    $(document).ready(function(){
	    	$.ajax({
	  	      type: "POST",
	  	      dataType: "json",
	  	      url: 'backend/server.php',
	  	      data:{action:'getCurrentRanking'},
	  	      success: function(data) {
		  	     
	  	  	      	var listitems = "";
	  	  	    	$('#tabend').find('tr').remove(); 
	  	  	      	$.each(data, function(key, value){
	  	  	      		listitems += "<tr><td>"+value.VOTES+"</td><td>"+value.ARTIST+"</td><td>"+value.ALBUM+"</td><td>"+value.SONGNAME+"</td></tr>";
	  	  	      	});
	  	  	    	$('#tabend').append(listitems);
	  	      }
	  	    });
	    });
	</script>
</body>
</html>
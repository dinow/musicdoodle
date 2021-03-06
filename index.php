<?php include("commons/header.php"); ?>
	<select id="artistDown" class="form-control"></select>
        <div class="table-responsive" id="mainTable">
          <table class="table table-hover table-condensed" >
            <thead>
              <tr>
              	<th>&nbsp;</th>
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
		function voteForSong(songId){
			var currentTR= $("#"+songId);
		    var currentElem = currentTR.children(":first");
		    if (currentTR.attr("disabled") == "disabled") return false;
			$.ajax({
		      type: "POST",
		      dataType: "text",
		      url: 'backend/server.php',
		      data:{action:'voteForSong',songId: currentTR.attr('id')},
		      success: function(data) {
		  	    	currentElem.text(data);
		  	  		currentElem.addClass( "text-clicked" );
		  	  		currentTR.addClass( "tr-green" );
		  	  		//currentTR.attr("disabled", "1" );
		      }
		    });
		}

		$(document).ready(function(){
			$.ajax({
			      type: "POST",
			      dataType: "json",
			      url: 'backend/server.php',
			      data:{action:'getArtistList'},
			      success: function(data) {
				    var listitems = '';
		    		var $select = $('#artistDown');                        
		    	    $select.find('option').remove(); 
		    		$.each(data, function(key, value){
		    		    listitems += '<option value=' + value.id + '>' + value.name + '</option>';
		    		});
		    		$select.append(listitems);
		    		$("#artistDown").change();
			      }
			    });
			
			$("#artistDown").on("change", function() {
				var aId = $(this).val();
				console.log("aid: " + aId);
				$.ajax({
		  	      type: "POST",
		  	      dataType: "json",
		  	      url: 'backend/server.php',
		  	      data:{action:'getSongList',artistId: aId},
		  	      success: function(data) {
		  	    	  console.log(data);
		  	  	      	var listitems = "";
		  	  	    	$('#tabend').find('tr').remove(); 
		  	  	      	$.each(data, function(key, value){
		  	  	      		listitems += "<tr class='canBeClicked' onclick='voteForSong("+value.SONG_ID+");' id='"+value.SONG_ID+"'><td>"+value.VOTES+"</td><td>"+value.ALBUM+"</td><td>"+value.NAME+"</td></tr>";
		  	  	      	});
		  	  	    	$('#tabend').append(listitems);
		  	      }
		  	    });
			});
		});
	</script>
</body>
</html>
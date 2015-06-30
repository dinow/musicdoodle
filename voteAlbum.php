<?php include("commons/header.php"); ?>
	<select id="artistDown" class="form-control"></select>
        <div class="table-responsive" id="mainTable">
          <table class="table table-hover table-condensed" >
            <thead>
              <tr>
              	<th>Année</th>
                <th>Album</th> 
              </tr>
            </thead>
            <tbody id="tabend">

			</tbody>
          </table>
        </div>
</div>
	<?php include("commons/footer.php"); ?>
    
    <script type="text/javascript">


    function voteForAlbum(albumId){
    	var currentTR= $("#"+albumId);
        var currentElem = currentTR.children(":first");
        if (currentTR.attr("disabled") == "disabled") return false;
    	$.ajax({
          type: "POST",
          dataType: "text",
          url: 'backend/server.php',
          data:{action:'voteForAlbum',albumId: currentTR.attr('id')},
          success: function(data) {
      	    	//currentElem.text(data);
      	  		currentElem.addClass( "text-clicked" );
      	  		currentTR.addClass( "tr-green" );
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
		  		$.ajax({
		    	      type: "POST",
		    	      dataType: "json",
		    	      url: 'backend/server.php',
		    	      data:{action:'getAlbumList',artistId: aId},
		    	      success: function(data) {
		    	    	    console.log(data);
		    	  	      	var listitems = "";
		    	  	    	$('#tabend').find('tr').remove(); 
		    	  	      	$.each(data, function(key, value){
		    	  	      		listitems += "<tr class='canBeClicked' onclick='voteForAlbum("+value.ALBUM_ID+");' id='"+value.ALBUM_ID+"'><td>"+value.YEAR+"</td><td>"+value.ALBUM+"</td></tr>";
		    	  	      	});
		    	  	    	$('#tabend').append(listitems);
		    	      }
		    	    });
		  	});
	    });
	</script>
</body>
</html>
<?php 
include("commons/template.php");
include("commons/header.php");

/*$mysqli = new mysqli("localhost", "mdoodle", "mdoodle", "mdoodle_db");
if ($mysqli->connect_errno) {
    echo "Echec lors de la connexion à MySQL : (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
}

$template = new Template('./templates/');
$template->set_filenames(array('songLine' => 'songLine.tpl'));
$template->set_filenames(array('dropdownLine' => 'dropdownLine.tpl'));
*/
?>
<select id="artistDown"></select>
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
<script>
$(document).ready(function(){

	$("#scanDir").click(function(){
    $.ajax({
	      type: "POST",
	      dataType: "text",
	      url: 'backend/server.php',
	      data:{action:'actualize'},
	      success: function(data) {
	  	    	alert(data);
	      }
	    });
	});
	$.ajax({
	      type: "POST",
	      dataType: "json",
	      url: 'backend/server.php',
	      data:{action:'getArtistList'},
	      success: function(data) {
		    console.log(data);
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
	
	$("#artistDown").on('change', function() {
		$.ajax({
  	      type: "POST",
  	      dataType: "text",
  	      url: 'backend/server.php',
  	      data:{action:'fillSongList',artistName: $(this).val()},
  	      success: function(data) {
  	  	    	console.log(data);
  	  	    	$('#tabend').text(data);
  	      }
  	    });
	});

	
	
    $(".canBeClicked").click(function(){
        var currentElem = $(this).children(":first");
        var masterElem = $(this);
        if (masterElem.attr("disabled") == "disabled") return false;
    	$.ajax({
  	      type: "POST",
  	      dataType: "text",
  	      url: 'backend/server.php',
  	      data:{action:'voteForSong',songId: $( this ).attr('id')},
  	      success: function(data) {
  	  	    	console.log(data);
  	  	  		currentElem.text(data);
  	  	  		currentElem.addClass( "text-clicked" );
  	  	  		masterElem.attr("disabled", "1" );
  	      }
  	    });
    });
});
</script>

</body>
</html>
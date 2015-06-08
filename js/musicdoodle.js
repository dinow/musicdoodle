
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
  	  		currentTR.attr("disabled", "1" );
      }
    });
}

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
		    	  console.log("done");
		    	  $('#wait').hide();
		      }
		    });
		});
	
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

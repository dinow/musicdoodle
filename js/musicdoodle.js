function voteForSong(song_id){
	
	$.ajax({
	      type: "POST",
	      dataType: "json",
	      url: 'backend/server.php',
	      data:{action:'voteForSong',songId:song_id},
	      success: function(data) {
	        alert(data);
	      }
	    });
}
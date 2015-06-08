<?php
include("musicDoodle.php");
if (is_ajax()) {
	$musicDoodle = new MusicDoodle();
	switch ($_POST['action']) {
		case 'voteForSong':
			echo $musicDoodle->voteForSong($_POST['songId']);
			break;
		case 'actualize':
			echo $musicDoodle->actualizeDB();
			break;
		case 'getArtistList':
			echo $musicDoodle->getArtistList();
			break;
		case 'getSongList':
			echo $musicDoodle->getSongList($_POST['artistId']);
			break;
	}
}

function is_ajax() {
	return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
}
?>
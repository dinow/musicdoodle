<?php
include("musicDoodle.php");
if (is_ajax()) {
	$musicDoodle = new MusicDoodle();
	switch ($_POST['action']) {
		case 'voteForSong':
			echo $musicDoodle->voteForSong($_POST['songId']);
			break;
		case 'voteForAlbum':
			echo $musicDoodle->voteForAlbum($_POST['albumId']);
			break;
		case 'actualize':
			echo $musicDoodle->actualizeDB('Z:/');
			break;
		case 'resetVotes':
			echo $musicDoodle->resetVotes();
			break;
		case 'getArtistList':
			echo $musicDoodle->getArtistList();
			break;
		case 'getSongList':
			echo $musicDoodle->getSongList($_POST['artistId']);
			break;
		case 'getAlbumList':
			echo $musicDoodle->getAlbumList($_POST['artistId']);
			break;
		case 'getCurrentRanking':
			echo $musicDoodle->getCurrentRanking();
			break;
	}
}

function is_ajax() {
	return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
}
?>
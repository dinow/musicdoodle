<?php
	include("musicDoodle.php");
	$musicDoodle = new MusicDoodle();
	$songPath = $musicDoodle->getCurrentSong();
	
	header($_SERVER["SERVER_PROTOCOL"] . " 200 OK");
	header("Cache-Control: public"); // needed for i.e.
	header("Content-Type: application/zip");
	header("Content-Transfer-Encoding: Binary");
	header("Content-Length:".filesize($songPath));
	header("Content-Disposition: attachment; filename=music.mp3");
	readfile($songPath);
	die();
?>
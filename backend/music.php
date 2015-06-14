<?php
	include("musicDoodle.php");
	$musicDoodle = new MusicDoodle();
	$songPath = $musicDoodle->getCurrentSong();
	
	$file = '/var/log/apache2/musicdoodle.log';
	// Open the file to get existing content
	$current = file_get_contents($file);
	// Append a new person to the file
	$current .= "path:".$songPath."\n";
	// Write the contents back to the file
	file_put_contents($file, $current);
	
	
	header($_SERVER["SERVER_PROTOCOL"] . " 200 OK");
	header("Cache-Control: public"); // needed for i.e.
	header("Content-Type: application/zip");
	header("Content-Transfer-Encoding: Binary");
	header("Content-Length:".filesize($songPath));
	header("Content-Disposition: attachment; filename=music.mp3");
	readfile($songPath);
	die();
?>
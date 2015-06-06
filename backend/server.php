<?php
if (is_ajax()) {
	if ($_POST['action']=='voteForSong') {
		voteForSong($_POST['songId']);
	}else if ($_POST['action']=='actualize') {
		actualizeDB();
	}else if ($_POST['action']=='getArtistList') {
		getArtistList();
	}else if ($_POST['action']=='fillSongList') {
		fillSongList($_POST['artistId']);
	}
}
function is_ajax() {
	return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
}

function getArtistList(){
	$mysqli = new mysqli("localhost", "mdoodle", "mdoodle", "mdoodle_db");
	$stmt = $mysqli->prepare("SELECT distinct artists.id, artists.name FROM artists ORDER BY name ASC");
	$stmt->execute();
	$stmt->bind_result($artist_id, $artistName);
	$artists = array();
	while ($stmt->fetch()) {
		$artists[] = array(
				'id' => $artist_id,
				'name' => $artistName
		);
	}
	$mysqli->close();
	echo json_encode($artists);
}

function fillSongList($artistId){
	$mysqli = new mysqli("localhost", "mdoodle", "mdoodle", "mdoodle_db");
	$stmt = $mysqli->prepare("SELECT songs.id, songs.name, songs.album, IFNULL(song_hits.hits,0) hits FROM songs LEFT OUTER JOIN song_hits on songs.id = song_hits.song_id where songs.artist_id like ? ORDER BY songs.album, songs.name ASC");
	$stmt->bind_param("i", $artistId);
	$stmt->execute();
	
	$stmt->bind_result($songId, $songName, $songAlbum, $hits);
	$songs = array();
	
	while ($stmt->fetch()) {
		$songs[] = array(
        	'SONG_ID' => $songId,
        	'VOTES' => $hits,
			'ALBUM' => $songAlbum,
			'NAME' => $songName
    		);
			
		}
	$stmt->close();
	$mysqli->close();
	echo json_encode($songs);
}

function actualizeDB(){
	$songCpt = 0;
	$dir    = 'Z:/';
	$mysqli = new mysqli("localhost", "mdoodle", "mdoodle", "mdoodle_db");
	
	$mysqli->query("delete from song_hits");
	$mysqli->query("delete from songs");
	$mysqli->query("delete from artists");
	
	$stmt = $mysqli->prepare("INSERT INTO songs (artist_id, album, name, path, tracknumber) VALUES (?,?,?,?,?)");
	
	
	$currentPath = $dir;
	foreach (getSubFiles($currentPath) as $artistDir){
		$artistId = getCurrentArtistEntryId($mysqli, $artistDir);
		$currentPath = $dir.'/'.$artistDir;
		if (is_dir($currentPath)){
			$songCpt++;
			foreach (getSubFiles($currentPath) as $albumDir){
				$currentPath = $dir.'/'.$artistDir.'/'.$albumDir;
				if (is_dir($currentPath)){
					foreach (getSubFiles($currentPath) as $songFile){
						$currentPath = $dir.'/'.$artistDir.'/'.$albumDir.'/'.$songFile;
						if (is_dir($currentPath)){
							foreach (getSubFiles($currentPath) as $subSongFile){
								$currentPath = $dir.'/'.$artistDir.'/'.$albumDir.'/'.$songFile.'/'.$subSongFile;
								if (isMP3($subSongFile)){
									$songFileName = getCleanSongName($subSongFile);
									$trackNo = 0;
									$stmt->bind_param("isssi", $artistId, $albumDir, $songFileName, $currentPath, $trackNo);
									$stmt->execute();
								}
							}
						}else if (isMP3($songFile)){
							$songFileName = getCleanSongName($songFile);
							$trackNo = 0;
							$stmt->bind_param("isssi", $artistId, $albumDir, $songFileName, $currentPath, $trackNo);
							$stmt->execute();
						}
					}
				}
			}
		}
	}
	$stmt->close();
	$mysqli->close();
	echo 'done: ' . $songCpt;
}


function getCurrentArtistEntryId($mysqli, &$artistDir){
	$stmt_artist = $mysqli->prepare("SELECT artists.id from artists where artists.name like ?");
	$stmt_artist->bind_param("s", $artistDir);
	$stmt_artist->execute();
	
	$stmt_artist->bind_result($artistId);
	$actualArtistId = -1;
	while ($stmt_artist->fetch()) {
		$actualArtistId = $artistId;
	}
	$stmt_artist->close();
	
	if ($actualArtistId == -1){
		$stmt = $mysqli->prepare("INSERT INTO artists (name) VALUES (?)");
		$stmt->bind_param("s", $artistDir);
		$stmt->execute();
		$stmt->close();
		
		$stmt = $mysqli->prepare("SELECT artists.id from artists where artists.name like ?");
		$stmt->bind_param("s", $artistDir);
		$stmt->execute();
		
		$stmt->bind_result($artistId);
		$actualArtistId = -1;
		while ($stmt->fetch()) {
			$actualArtistId = $artistId;
		}
		$stmt->close();
		
	}
	return $actualArtistId;
}

function isMP3($file){
	$dotPos = strrpos($file, '.');
	if ($dotPos === false){
		return false;
	}
	$fileExt = substr($file,$dotPos);
	return $fileExt == '.mp3';
	
}

function getCleanSongName($fileName){
	$dotPos = strrpos($fileName, '.');
	if ($dotPos === false){
		return $fileName;
	}
	return substr($fileName,0,$dotPos);
}

function getSubFiles($dir){
	return array_diff(scandir($dir), array('..', '.'));
}

function voteForSong($id){
	$mysqli = new mysqli("localhost", "mdoodle", "mdoodle", "mdoodle_db");
	$previousCount = 0;
	$wasThere = false;
	$stmt = $mysqli->prepare("SELECT IFNULL(song_hits.hits,0) hits FROM song_hits where song_hits.song_id = ?");
	$stmt->bind_param("i", $id);
	$stmt->execute();
	$stmt->bind_result($previousCount);
	while ($stmt->fetch()) {
		$wasThere = true;
	}
	$stmt->close();
	
	$previousCount = $previousCount + 1;
	if ($wasThere){
		$stmt = $mysqli->prepare("UPDATE song_hits set hits = ? where song_id = ?");
		$stmt->bind_param("ii", $previousCount, $id);
		$stmt->execute();
		$stmt->close();
	}else{
		$stmt = $mysqli->prepare("INSERT INTO song_hits (song_id, hits) VALUES (?,?)");
		$stmt->bind_param("ii", $id, $previousCount);
		$stmt->execute();
		$stmt->close();
	}
	$mysqli->close();
	echo $previousCount;
}

?>
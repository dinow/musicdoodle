<?php
	class MusicDoodle{
		private $mysqli = null;
		
		private function connect(){
			$this->mysqli = new mysqli("localhost", "mdoodle", "mdoodle", "mdoodle_db");
		}
		
		private function disconnect(){
			$this->mysqli->close();	
		}
		
		
		public function getCurrentRanking(){
			$this->connect();
			$stmt = $this->mysqli->prepare("select albums.name as album, songs.name as sname,  artists.name as aname, song_hits.hits hits from song_hits join songs on songs.id = song_hits.song_id join artists on songs.artist_id = artists.id JOIN albums ON songs.album_id = albums.id order by song_hits.hits DESC");
			$stmt->execute();
			$stmt->bind_result($album,$songName, $artistName, $hits);
			$artists = array();
			while ($stmt->fetch()) {
				$artists[] = array(
						'VOTES' => $hits,
						'ALBUM' => $album,
						'ARTIST' => $artistName,
						'SONGNAME' => $songName
				);
			}
			$stmt->close();
			$this->disconnect();
			$songs = array();
			return json_encode($artists);
		}
		
		public function getCurrentSong(){
			$this->connect();
			$stmt = $this->mysqli->prepare("SELECT songs.path, song_hits.song_id from songs join song_hits on song_hits.song_id = songs.id where song_hits.hits = (select max(song_hits.hits) from song_hits)");
			$stmt->execute();
			$stmt->bind_result($songPath, $songId);
			$stmt->fetch();
			$stmt->close();
			
			if ($songId == ""){
				return $this->getRandomSong();
			}else{
				$stmt = $this->mysqli->prepare("delete from song_hits where song_id = ?");
				$stmt->bind_param("i", $songId);
				$stmt->execute();
				$stmt->close();
				$this->disconnect();
				return $songPath;
			}
		}
		
		private function getRandomSong(){
			$stmt = $this->mysqli->prepare("SELECT min(songs.id) as minid, max(songs.id) as maxid from songs");
			$stmt->execute();
			$stmt->bind_result($minId, $maxId);
			$stmt->fetch();
			$stmt->close();
			$songPath = "";
			while($songPath == ""){
				$randId = rand($minId, $maxId);
				$stmt = $this->mysqli->prepare("SELECT path from songs where id = ?");
				$stmt->bind_param("i", $randId);
				$stmt->execute();
				$stmt->bind_result($songPath);
				$stmt->fetch();
				$stmt->close();
			}
			return $songPath;
		}
		
		public function getArtistList(){
			$this->connect();
			$stmt = $this->mysqli->prepare("SELECT distinct artists.id, artists.name FROM artists ORDER BY name ASC");
			$stmt->execute();
			$stmt->bind_result($artist_id, $artistName);
			$artists = array();
			while ($stmt->fetch()) {
				$artists[] = array(
						'id' => $artist_id,
						'name' => $artistName
				);
			}
			$stmt->close();
			$this->disconnect();
			return json_encode($artists);
		}
		
		public function getAlbumList($artistId){
			$this->connect();
			$cleanId = trim(strip_tags($artistId));
			$stmt = $this->mysqli->prepare("SELECT albums.id, albums.name, IFNULL(albums.year,0) year FROM albums where albums.artist_id like ? ORDER BY albums.name ASC");
			$stmt->bind_param("i", $cleanId);
			$stmt->execute();
		
			$stmt->bind_result($albumId, $albumName, $year);
			$songs = array();
		
			while ($stmt->fetch()) {
				$songs[] = array(
						'ALBUM_ID' => $albumId,
						'ALBUM' => $albumName,
						'YEAR' => $year
				);
					
			}
			$stmt->close();
			$this->disconnect();
			return json_encode($songs);
		}
		
		public function getSongList($artistId){
			$this->connect();
			$cleanId = trim(strip_tags($artistId));
			$stmt = $this->mysqli->prepare("SELECT songs.id, songs.name as sname, albums.name as aname, IFNULL(song_hits.hits,0) hits FROM songs JOIN albums on songs.album_id = albums.id LEFT OUTER JOIN song_hits on songs.id = song_hits.song_id where songs.artist_id like ? ORDER BY albums.name, songs.name ASC");
			$stmt->bind_param("i", $cleanId);
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
			$this->disconnect();
			return json_encode($songs);
			
		}
		
		public function resetVotes(){
			$this->connect();	
			$this->mysqli->query("delete from song_hits");
			$this->disconnect();
		}
		
		public function actualizeDB($dir){
			$songCpt = 0;
			
			
			$this->connect();
			
			$this->mysqli->query("delete from song_hits");
			$this->mysqli->query("delete from songs");
			$this->mysqli->query("delete from albums");
			$this->mysqli->query("delete from artists");
		
			$stmt = $this->mysqli->prepare("INSERT INTO songs (artist_id, album_id, name, path, tracknumber) VALUES (?,?,?,?,?)");
		
		
			$currentPath = $dir;
			foreach ($this->getSubFiles($currentPath) as $artistDir){
				$artistId = $this->getCurrentArtistEntryId($artistDir);
				$currentPath = $dir.'/'.$artistDir;
				if (is_dir($currentPath)){
					foreach ($this->getSubFiles($currentPath) as $albumDir){
						$currentPath = $dir.'/'.$artistDir.'/'.$albumDir;
						$albumId = $this->getCurrentAlbumEntryId($albumDir, $artistId);
						if (is_dir($currentPath)){
							foreach ($this->getSubFiles($currentPath) as $songFile){
								$currentPath = $dir.'/'.$artistDir.'/'.$albumDir.'/'.$songFile;
								if (is_dir($currentPath)){
									foreach ($this->getSubFiles($currentPath) as $subSongFile){
										$currentPath = $dir.'/'.$artistDir.'/'.$albumDir.'/'.$songFile.'/'.$subSongFile;
										if ($this->checkAndInsertSong($stmt, $subSongFile, $artistId, $albumId, $currentPath)){
											$songCpt++;
										}
									}
								}else if ($this->checkAndInsertSong($stmt, $songFile, $artistId, $albumId, $currentPath)){
									$songCpt++;
								}
							}
						}
					}
				}
			}
			$stmt->close();
			$this->disconnect();
			return $songCpt;
		}
		
		private function checkAndInsertSong($stmt, $songFile, $artistId, $albumId, $currentPath){
			if ($this->isMP3($songFile)){
				$songFileName = $this->getCleanSongName($songFile);
				$trackNo = 0;
				$stmt->bind_param("isssi", $artistId, $albumId, $songFileName, $currentPath, $trackNo);
				$stmt->execute();
				return true;
			}else{
				return false;
			}
			
		}
		
		
		private function getCurrentArtistEntryId($artistDir){
			$artistDir = trim(strip_tags($artistDir));
			$stmt_artist = $this->mysqli->prepare("SELECT artists.id from artists where artists.name like ?");
			$stmt_artist->bind_param("s", $artistDir);
			$stmt_artist->execute();
		
			$stmt_artist->bind_result($artistId);
			$actualArtistId = -1;
			while ($stmt_artist->fetch()) {
				$actualArtistId = $artistId;
			}
			$stmt_artist->close();
		
			if ($actualArtistId == -1){
				$stmt = $this->mysqli->prepare("INSERT INTO artists (name) VALUES (?)");
				$stmt->bind_param("s", $artistDir);
				$stmt->execute();
				$stmt->close();
		
				$stmt = $this->mysqli->prepare("SELECT artists.id from artists where artists.name like ?");
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
		
		private function getCurrentAlbumEntryId($albumDir, $artistId){
			$albumDir = trim(strip_tags($albumDir));
			$inner_stmt = $this->mysqli->prepare("SELECT albums.id from albums where albums.name like ? and albums.artist_id like ?");
			$inner_stmt->bind_param("si", $albumDir, $artistId);
			$inner_stmt->execute();
		
			$inner_stmt->bind_result($albumId);
			$actualAlbumId = -1;
			while ($inner_stmt->fetch()) {
				$actualAlbumId = $albumId;
			}
			$inner_stmt->close();
		
			if ($actualAlbumId == -1){
				$year = $this->getYearFromAlbumName($albumDir);
				if ($year != 0){
					$stmt = $this->mysqli->prepare("INSERT INTO albums (artist_id, name, year) VALUES (?, ?, ?)");
					$stmt->bind_param("isi", $artistId, $albumDir, $year);
				}else{
					$stmt = $this->mysqli->prepare("INSERT INTO albums (artist_id, name) VALUES (?, ?)");
					$stmt->bind_param("is", $artistId, $albumDir);
				}
				$stmt->execute();
				$stmt->close();
				
				
		
				$stmt = $this->mysqli->prepare("SELECT albums.id from albums where albums.name like ? and albums.artist_id like ?");
				$stmt->bind_param("si", $albumDir, $artistId);
				$stmt->execute();
		
				$stmt->bind_result($albumId);
				$actualAlbumId = -1;
				while ($stmt->fetch()) {
					$actualAlbumId = $albumId;
				}
				$stmt->close();
		
			}
			return $actualAlbumId;
		}
		
		private function isMP3($file){
			$dotPos = strrpos($file, '.');
			if ($dotPos === false){
				return false;
			}
			$fileExt = substr($file,$dotPos);
			return $fileExt == '.mp3';
		}
		
		private function getYearFromAlbumName($albumName){
			$maybeYear = substr($albumName,0,4);
			return intval($maybeYear, 10);
		}
		
		private function getCleanSongName($fileName){
			$dotPos = strrpos($fileName, '.');
			if ($dotPos === false){
				return $fileName;
			}
			return substr($fileName,0,$dotPos);
		}
		
		private function getSubFiles($dir){
			return array_diff(scandir($dir), array('..', '.'));
		}
		
		public function voteForSong($id){
			$this->connect();
			$id = trim(strip_tags($id));
			$previousCount = $this->_voteForSong($id);
			$this->disconnect();
			return $previousCount;
		}
		
		private function _voteForSong($id){
			$previousCount = 0;
			$wasThere = false;
			$stmt = $this->mysqli->prepare("SELECT IFNULL(song_hits.hits,0) hits FROM song_hits where song_hits.song_id = ?");
			$stmt->bind_param("i", $id);
			$stmt->execute();
			$stmt->bind_result($previousCount);
			while ($stmt->fetch()) {
				$wasThere = true;
			}
			$stmt->close();
			
			$previousCount = $previousCount + 1;
			if ($wasThere){
				$stmt = $this->mysqli->prepare("UPDATE song_hits set hits = ? where song_id = ?");
				$stmt->bind_param("ii", $previousCount, $id);
				$stmt->execute();
				$stmt->close();
			}else{
				$stmt = $this->mysqli->prepare("INSERT INTO song_hits (song_id, hits) VALUES (?,?)");
				$stmt->bind_param("ii", $id, $previousCount);
				$stmt->execute();
				$stmt->close();
			}
			return $previousCount;
		}
		
		public function voteForAlbum($id){
			$this->connect();
			$id = trim(strip_tags($id));
			$stmt = $this->mysqli->prepare("SELECT songs.id from songs where songs.album_id = ?");
			$stmt->bind_param("i", $id);
			$stmt->execute();
			$stmt->bind_result($songId);
			$songs = array();
			while ($stmt->fetch()) {
				$songs[] = $songId;
			}
			$stmt->close();
			
			foreach ($songs as $sId){
				$this->_voteForSong($sId);
			}
			
			
			$this->disconnect();
			return 1;
		}
		
	}


?>
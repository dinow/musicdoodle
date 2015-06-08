<?php
	class MusicDoodle{
		private $mysqli = null;
		
		private function connect(){
			$this->mysqli = new mysqli("localhost", "mdoodle", "mdoodle", "mdoodle_db");
		}
		
		private function disconnect(){
			$this->mysqli->close();	
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
		
		public function getSongList($artistId){
			$this->connect();
			$cleanId = trim(strip_tags($artistId));
			$stmt = $this->mysqli->prepare("SELECT songs.id, songs.name, songs.album, IFNULL(song_hits.hits,0) hits FROM songs LEFT OUTER JOIN song_hits on songs.id = song_hits.song_id where songs.artist_id like ? ORDER BY songs.album, songs.name ASC");
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
		
		public function actualizeDB(){
			$songCpt = 0;
			$dir    = 'Z:/';
			
			$this->connect();
			
			$this->mysqli->query("delete from song_hits");
			$this->mysqli->query("delete from songs");
			$this->mysqli->query("delete from artists");
		
			$stmt = $this->mysqli->prepare("INSERT INTO songs (artist_id, album, name, path, tracknumber) VALUES (?,?,?,?,?)");
		
		
			$currentPath = $dir;
			foreach ($this->getSubFiles($currentPath) as $artistDir){
				$artistId = $this->getCurrentArtistEntryId($artistDir);
				$currentPath = $dir.'/'.$artistDir;
				if (is_dir($currentPath)){
					foreach ($this->getSubFiles($currentPath) as $albumDir){
						$currentPath = $dir.'/'.$artistDir.'/'.$albumDir;
						if (is_dir($currentPath)){
							foreach ($this->getSubFiles($currentPath) as $songFile){
								$currentPath = $dir.'/'.$artistDir.'/'.$albumDir.'/'.$songFile;
								if (is_dir($currentPath)){
									foreach ($this->getSubFiles($currentPath) as $subSongFile){
										$currentPath = $dir.'/'.$artistDir.'/'.$albumDir.'/'.$songFile.'/'.$subSongFile;
										if ($this->checkAndInsertSong($stmt, $subSongFile, $artistId, $albumDir, $currentPath)){
											$songCpt++;
										}
									}
								}else if ($this->checkAndInsertSong($stmt, $songFile, $artistId, $albumDir, $currentPath)){
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
		
		private function checkAndInsertSong($stmt, $songFile, $artistId, $albumDir, $currentPath){
			if ($this->isMP3($songFile)){
				$songFileName = $this->getCleanSongName($songFile);
				$trackNo = 0;
				$stmt->bind_param("isssi", $artistId, $albumDir, $songFileName, $currentPath, $trackNo);
				$stmt->execute();
				return true;
			}else{
				return false;
			}
			
		}
		
		
		private function getCurrentArtistEntryId(&$artistDir){
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
		
		private function isMP3($file){
			$dotPos = strrpos($file, '.');
			if ($dotPos === false){
				return false;
			}
			$fileExt = substr($file,$dotPos);
			return $fileExt == '.mp3';
		
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
			$cleanId = trim(strip_tags($id));
			$previousCount = 0;
			$wasThere = false;
			$stmt = $this->mysqli->prepare("SELECT IFNULL(song_hits.hits,0) hits FROM song_hits where song_hits.song_id = ?");
			$stmt->bind_param("i", $cleanId);
			$stmt->execute();
			$stmt->bind_result($previousCount);
			while ($stmt->fetch()) {
				$wasThere = true;
			}
			$stmt->close();
		
			$previousCount = $previousCount + 1;
			if ($wasThere){
				$stmt = $this->mysqli->prepare("UPDATE song_hits set hits = ? where song_id = ?");
				$stmt->bind_param("ii", $previousCount, $cleanId);
				$stmt->execute();
				$stmt->close();
			}else{
				$stmt = $this->mysqli->prepare("INSERT INTO song_hits (song_id, hits) VALUES (?,?)");
				$stmt->bind_param("ii", $cleanId, $previousCount);
				$stmt->execute();
				$stmt->close();
			}
			$this->disconnect();
			return $previousCount;
		}
		
	}


?>
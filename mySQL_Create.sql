CREATE TABLE `artists` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`),
  UNIQUE KEY `name_UNIQUE` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=1824 DEFAULT CHARSET=utf8;

CREATE TABLE `albums` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(45) not null,
    `year` int(4),
    `artist_id` int(11) NOT NULL,
    PRIMARY KEY(`id`),
    KEY `album_artist_idx` (`artist_id`),
    CONSTRAINT `album_artist` FOREIGN KEY (`artist_id`) REFERENCES `artists` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=82669 DEFAULT CHARSET=utf8;

CREATE TABLE `songs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(45) NOT NULL,
  `path` varchar(255) NOT NULL,
  `trackNumber` int(11) DEFAULT NULL,
  `artist_id` int(11) NOT NULL,
  `album_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `path_UNIQUE` (`path`),
  KEY `song_artist_idx` (`artist_id`),
  CONSTRAINT `song_artist` FOREIGN KEY (`artist_id`) REFERENCES `artists` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  KEY `song_album_idx` (`album_id`),
  CONSTRAINT `song_album` FOREIGN KEY (`album_id`) REFERENCES `albums` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=82669 DEFAULT CHARSET=utf8;

CREATE TABLE `song_hits` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `song_id` int(11) NOT NULL,
  `hits` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `song_id_UNIQUE` (`song_id`),
  UNIQUE KEY `id_UNIQUE` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
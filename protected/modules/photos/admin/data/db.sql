SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

CREATE TABLE IF NOT EXISTS `photos` (
  `id` int(11) NOT NULL auto_increment,
  `sub` int(11) default NULL,
  `sort` int(11) default '0',
  `active` enum('0','1') default '0',
  `url` varchar(128) default NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `url` (`url`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=12;

CREATE TABLE IF NOT EXISTS `ru_photos` (
  `photos_id` int(11) default NULL,
  `name` varchar(128) NOT NULL,
  `body_m` varchar(255) default NULL,
  `body` text,
  `title` varchar(128) default NULL,
  `keywords` text,
  `description` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `photo` (
  `id` int(11) NOT NULL auto_increment,
  `photos_id` int(11) NOT NULL,
  `sort` int(11) NOT NULL,
  `active` enum('0','1') NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=140 ;

CREATE TABLE IF NOT EXISTS `ru_photo` (
  `photo_id` int(11) NOT NULL,
  `name` varchar(64) NOT NULL,
  PRIMARY KEY  (`photo_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
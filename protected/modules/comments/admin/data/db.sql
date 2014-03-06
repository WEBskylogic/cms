SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

CREATE TABLE IF NOT EXISTS `comments` (
  `id` int(11) NOT NULL auto_increment,
  `language` varchar(2) default 'ru',
  `author` varchar(128) default NULL,
  `text` text,
  `date` datetime default '0000-00-00 00:00:00',
  `content_id` int(11) NOT NULL default '0',
  `type_id` int(11) default '1',
  `session_id` varchar(255) NOT NULL,
  `active` enum('0','1') NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `comments_type` (
  `id` int(3) NOT NULL auto_increment,
  `comment` varchar(128) default '',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Тип Отзыва' AUTO_INCREMENT=1 ;
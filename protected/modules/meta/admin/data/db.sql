
CREATE TABLE IF NOT EXISTS `meta_data` (
  `id` int(11) NOT NULL auto_increment,
  `url` varchar(128) default NULL,
  `active` enum('0','1') NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `url` (`url`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='TEXT RU:SEO оптимизация' AUTO_INCREMENT=1 ;
@@@
CREATE TABLE IF NOT EXISTS `@@ru_meta_data@@` (
  `meta_id` int(11) NOT NULL,
  `title` varchar(100) DEFAULT NULL,
  `body` longtext,
  `keywords` text,
  `description` text,
  KEY `meta_id` (`meta_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='TEXT RU:SEO оптимизация';
@@@1
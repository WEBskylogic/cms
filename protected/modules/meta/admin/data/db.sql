CREATE TABLE IF NOT EXISTS `meta_data` (
  `id` int(11) NOT NULL auto_increment,
  `url` varchar(128) default NULL,
  `title` varchar(100) default NULL,
  `body` longtext,
  `keywords` text,
  `description` text,
  `active` enum('0','1') NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `url` (`url`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='TEXT RU:SEO оптимизация' AUTO_INCREMENT=3 ;
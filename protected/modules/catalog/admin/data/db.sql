

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


CREATE TABLE IF NOT EXISTS `catalog` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sub` int(11) DEFAULT NULL,
  `sort` int(11) DEFAULT '0',
  `active` enum('0','1') DEFAULT '0',
  `url` varchar(128) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `url` (`url`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

--
-- Dumping data for table `catalog`
--

INSERT INTO `catalog` (`id`, `sub`, `sort`, `active`, `url`) VALUES
(2, NULL, 0, '1', 'myagkie-igrushki'),
(3, NULL, 0, '1', 'tverdie-igrushki');


CREATE TABLE IF NOT EXISTS `ru_catalog` (
  `cat_id` int(11) DEFAULT NULL,
  `name` varchar(64) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `keywords` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `body` longtext
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `ru_catalog`
--

INSERT INTO `ru_catalog` (`cat_id`, `name`, `title`, `keywords`, `description`, `body`) VALUES
(2, 'Мягкие игрушки', 'ф', 'фф', 'ввв', '&lt;p&gt;описени&lt;/p&gt;'),
(3, 'Твердые игрушки', 'в', 'ввввввввввв', 'ввввввввввввввв', '&lt;p&gt;ываыва&lt;/p&gt;');

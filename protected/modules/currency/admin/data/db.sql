CREATE TABLE IF NOT EXISTS `currency` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `icon` varchar(5) NOT NULL,
  `rate` float(12,2) NOT NULL,
  `name` varchar(32) NOT NULL,
  `position` int(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `currency`
--

INSERT INTO `currency` (`id`, `icon`, `rate`, `name`, `position`) VALUES
(1, 'грн.', 0.00, 'Гривна', 1);
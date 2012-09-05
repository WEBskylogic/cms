CREATE TABLE IF NOT EXISTS `payment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(128) DEFAULT NULL,
  `active` enum('0','1') NOT NULL,
  `sort` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5 ;

--
-- Dumping data for table `payment`
--

INSERT INTO `payment` (`id`, `name`, `active`, `sort`) VALUES
(1, 'Безналичный', '1', 0),
(2, 'Наличный', '1', 0),
(3, 'Кредитной картой', '1', 0),
(4, 'Банковский перевод', '1', 0);
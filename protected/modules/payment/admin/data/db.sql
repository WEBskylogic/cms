
CREATE TABLE IF NOT EXISTS `payment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `active` enum('0','1') NOT NULL,
  `sort` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
@@@
CREATE TABLE IF NOT EXISTS `@@ru_payment@@` (
  `payment_id` int(11) NOT NULL,
  `name` varchar(128) NOT NULL,
  KEY `payment_id` (`payment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
@@@1
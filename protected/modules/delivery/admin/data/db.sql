CREATE TABLE IF NOT EXISTS `delivery` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `price` float(12,2) NOT NULL,
  `active` enum('0','1') NOT NULL,
  `sort` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=8 ;
@@@
CREATE TABLE IF NOT EXISTS `@@ru_delivery@@` (
  `delivery_id` int(11) NOT NULL,
  `name` varchar(128) NOT NULL,
  KEY `delivery_id` (`delivery_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
@@@1
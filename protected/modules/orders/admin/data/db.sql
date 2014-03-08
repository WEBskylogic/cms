SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

CREATE TABLE IF NOT EXISTS `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status_id` int(11) NOT NULL,
  `delivery_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `date_add` datetime DEFAULT '0000-00-00 00:00:00',
  `date_edit` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `sum` float(12,2) DEFAULT NULL,
  `discount` int(11) NOT NULL,
  `comment` text,
  `username` varchar(128) NOT NULL,
  `post_index` varchar(256) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(100) DEFAULT NULL,
  `country` varchar(256) NOT NULL,
  `city` varchar(100) DEFAULT NULL,
  `address` varchar(100) DEFAULT NULL,
  `payment` varchar(256) NOT NULL,
  `amount` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=16 ;

CREATE TABLE IF NOT EXISTS `orders_product` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `orders_id` int(11) DEFAULT NULL,
  `name` varchar(256) NOT NULL,
  `price` float(12,2) DEFAULT NULL,
  `discount` int(11) NOT NULL,
  `amount` int(11) DEFAULT NULL,
  `sum` float(12,2) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=43 ;

CREATE TABLE IF NOT EXISTS `orders_status` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(24) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=6 ;

INSERT INTO `orders_status` (`id`, `name`) VALUES
(1, 'Новый'),
(2, 'Обрабатывается'),
(3, 'Оплачен'),
(4, 'Отменен'),
(5, 'Закрыт');

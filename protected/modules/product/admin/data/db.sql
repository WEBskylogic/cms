SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

CREATE TABLE IF NOT EXISTS `product` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `brend_id` int(11) NOT NULL,
  `status_id` int(11) NOT NULL,
  `code` varchar(32) NOT NULL,
  `price` float(12,2) NOT NULL,
  `discount` int(11) NOT NULL,
  `cnt` int(11) DEFAULT NULL,
  `url` varchar(128) DEFAULT NULL,
  `active` enum('0','1') DEFAULT '0' COMMENT '1-ON / 0- OFF',
  `date_add` datetime DEFAULT '0000-00-00 00:00:00',
  `date_edit` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `sort` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `url` (`url`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=11 ;

CREATE TABLE IF NOT EXISTS `product_catalog` (
  `product_id` int(11) DEFAULT NULL,
  `catalog_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `product_photo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `active` enum('0','1') NOT NULL,
  `sort` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=49 ;

CREATE TABLE IF NOT EXISTS `product_status` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `url` varchar(128) NOT NULL,
  `comment` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5 ;

INSERT INTO `product_status` (`id`, `url`, `comment`) VALUES
(1, 'discount', 'Скидки'),
(2, 'novelty', 'Новые поступления'),
(3, 'top-sellers', 'Популярные товары'),
(4, 'recommend', 'Рекомендуемые товары');

CREATE TABLE IF NOT EXISTS `product_status_set` (
  `product_id` int(11) NOT NULL,
  `status_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `ru_product` (
  `product_id` int(11) DEFAULT NULL,
  `name` varchar(256) NOT NULL,
  `body_m` longtext,
  `body` longtext,
  `title` varchar(128) DEFAULT NULL,
  `keywords` text,
  `description` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `ru_product_photo` (
  `photo_id` int(11) NOT NULL,
  `name` varchar(128) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
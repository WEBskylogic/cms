CREATE TABLE IF NOT EXISTS `user_status` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `comment` varchar(24) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status_id` int(11) NOT NULL DEFAULT '0',
  `referral_id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `surname` varchar(100) DEFAULT NULL,
  `patronymic` varchar(100) DEFAULT NULL,
  `pass` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `info` varchar(512) NOT NULL,
  `phone` varchar(100) DEFAULT NULL,
  `skype` varchar(32) NOT NULL,
  `city` varchar(100) DEFAULT NULL,
  `address` varchar(100) DEFAULT NULL,
  `post_index` varchar(100) DEFAULT NULL,
  `start_date` datetime DEFAULT NULL,
  `discount` decimal(4,2) DEFAULT NULL,
  `active_email` int(11) DEFAULT NULL,
  `mailer` int(11) DEFAULT NULL,
  `active` enum('0','1') NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=12 ;

INSERT INTO `user_status` (`id`, `comment`) VALUES
(1, 'Пользователь'),
(2, 'Менеджер');
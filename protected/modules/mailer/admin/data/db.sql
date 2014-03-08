
SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

CREATE TABLE `mailer` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sort` int(11) DEFAULT '0',
  `active` enum('0','1') DEFAULT '1',
  `reset_pass` enum('0','1') NOT NULL DEFAULT '0',  
  `date_add` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_stop` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 AVG_ROW_LENGTH=4096 COMMENT='Шаблоны писем';


CREATE TABLE `ru_mailer` (
  `pages_id` int(11) DEFAULT NULL COMMENT 'Код группы категории',
  `name` varchar(255) DEFAULT NULL COMMENT 'Наименование',
  `text` text,
  KEY `pages_id_new_new` (`pages_id`),
  CONSTRAINT `FK_ru_mailbody_id` FOREIGN KEY (`pages_id`) REFERENCES `mailer` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AVG_ROW_LENGTH=4096 COMMENT='TEXT RU: Шаблоны писем';

CREATE TABLE `mail_queue` (
  `id` int(11) NOT NULL AUTO_INCREMENT, 
  `mailbody_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `delivered` enum('0','1') DEFAULT '0',
  `date_add` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_sent` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `mailbody_id` (`mailbody_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `mail_queue_ibfk_1` FOREIGN KEY (`mailbody_id`) REFERENCES `mailer` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `mail_queue_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
 ) ENGINE=InnoDB AUTO_INCREMENT=105 DEFAULT CHARSET=utf8 AVG_ROW_LENGTH=4096 COMMENT='Очередь рассылки'
 
 


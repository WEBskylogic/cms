

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


CREATE TABLE IF NOT EXISTS `menu` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sub` int(11) DEFAULT NULL,
  `sort` int(11) DEFAULT '0',
  `active` enum('0','1') DEFAULT '0',
  `url` varchar(128) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `url` (`url`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=71 ;



INSERT INTO `menu` (`id`, `sub`, `sort`, `active`, `url`) VALUES
(64, NULL, 1, '1', '/'),
(65, NULL, 2, '1', 'o-nas'),
(66, NULL, 7, '1', 'registratsiya'),
(67, NULL, 5, '1', 'galereya'),
(68, NULL, 6, '1', 'kontakti'),
(69, NULL, 3, '1', 'pravila'),
(70, NULL, 4, '1', 'news');

CREATE TABLE IF NOT EXISTS `ru_menu` (
  `menu_id` int(11) DEFAULT NULL,
  `name` varchar(64) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `keywords` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `body` longtext
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



INSERT INTO `ru_menu` (`menu_id`, `name`, `title`, `keywords`, `description`, `body`) VALUES
(64, 'Главная', '', '', '', ''),
(65, 'О нас', '', '', '', ''),
(66, 'Регистрация', '', '', '', ''),
(67, 'Галерея', '', '', '', ''),
(68, 'Контакты', '', '', '', '&lt;p&gt;asdasdasd&lt;/p&gt;'),
(69, 'Правила', '', '', '', ''),
(70, 'Статьи', '', '', '', '');

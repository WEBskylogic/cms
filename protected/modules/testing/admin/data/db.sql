CREATE TABLE IF NOT EXISTS `testing` (

   `id` int(11) NOT NULL AUTO_INCREMENT,

   `url` varchar(255) NOT NULL,

   `subject` varchar(255) NOT NULL,

   `text` text NOT NULL,

   `date_add` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,

   `active` int(11) NOT NULL,

   PRIMARY KEY (`id`)
 
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8
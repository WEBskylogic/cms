CREATE TABLE `params` (
  `id` INTEGER(11) NOT NULL AUTO_INCREMENT,
  `sub` INTEGER(11) DEFAULT NULL,
  `sort` INTEGER(11) DEFAULT 0,
  `active` ENUM('0','1') DEFAULT '0' COMMENT '1-выводить/0-не выводить',
  `url` VARCHAR(128) COLLATE utf8_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
)ENGINE=InnoDB
AUTO_INCREMENT=1 CHARACTER SET 'utf8' COLLATE 'utf8_general_ci'
COMMENT='справочник: Свойства продукции'
;

CREATE TABLE `params_product` (
  `params_id` INTEGER(11) DEFAULT NULL,
  `product_id` INTEGER(11) DEFAULT NULL,
  KEY `params_id` (`params_id`),
  KEY `product_id` (`product_id`)
)ENGINE=InnoDB
CHARACTER SET 'utf8' COLLATE 'utf8_general_ci'
COMMENT='смежная таблица:Настройки характеристик для продукции'
;
@@@
CREATE TABLE `@@ru_params@@` (
  `params_id` INTEGER(11) DEFAULT NULL COMMENT 'Код группы категории',
  `name` VARCHAR(64) COLLATE utf8_general_ci DEFAULT NULL COMMENT 'Наименование категории номенклатуры',
  `info` TEXT COLLATE utf8_general_ci
)ENGINE=InnoDB
CHARACTER SET 'utf8' COLLATE 'utf8_general_ci'
COMMENT='TEXT RU справочник: Свойства продукции'
;
@@@1
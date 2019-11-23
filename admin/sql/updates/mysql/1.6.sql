CREATE TABLE IF NOT EXISTS `#__vikrentitems_customers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `first_name` varchar(64) NOT NULL,
  `last_name` varchar(64) NOT NULL,
  `email` varchar(128) DEFAULT NULL,
  `phone` varchar(64) DEFAULT NULL,
  `country` varchar(32) DEFAULT NULL,
  `cfields` text DEFAULT NULL,
  `pin` int(5) NOT NULL DEFAULT 0,
  `ujid` int(5) NOT NULL DEFAULT 0,
  `address` varchar(256) DEFAULT NULL,
  `city` varchar(64) DEFAULT NULL,
  `zip` varchar(16) DEFAULT NULL,
  `doctype` varchar(64) DEFAULT NULL,
  `docnum` varchar(128) DEFAULT NULL,
  `docimg` varchar(128) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `company` varchar(128) DEFAULT NULL,
  `vat` varchar(64) DEFAULT NULL,
  `gender` char(1) DEFAULT NULL,
  `bdate` varchar(16) DEFAULT NULL,
  `pbirth` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `#__vikrentitems_customers_orders` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `idcustomer` int(10) NOT NULL,
  `idorder` int(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

ALTER TABLE `#__vikrentitems_categories` ADD COLUMN `ordering` int(10) NOT NULL DEFAULT 1;
ALTER TABLE `#__vikrentitems_ordersitems` ADD COLUMN `cust_cost` decimal(12,2) DEFAULT NULL;
ALTER TABLE `#__vikrentitems_ordersitems` ADD COLUMN `cust_idiva` int(10) DEFAULT NULL;
ALTER TABLE `#__vikrentitems_ordersitems` ADD COLUMN `extracosts` varchar(2048) DEFAULT NULL;
ALTER TABLE `#__vikrentitems_seasons` ADD COLUMN `year` int(5) DEFAULT NULL;
ALTER TABLE `#__vikrentitems_seasons` ADD COLUMN `idprices` varchar(256) DEFAULT NULL;
ALTER TABLE `#__vikrentitems_seasons` ADD COLUMN `promo` tinyint(1) NOT NULL DEFAULT 0;
ALTER TABLE `#__vikrentitems_seasons` ADD COLUMN `promotxt` text DEFAULT NULL;
ALTER TABLE `#__vikrentitems_seasons` ADD COLUMN `promodaysadv` int(5) DEFAULT NULL;
ALTER TABLE `#__vikrentitems_custfields` ADD COLUMN `flag` varchar(64) DEFAULT NULL;
ALTER TABLE `#__vikrentitems_optionals` ADD COLUMN `onceperitem` tinyint(1) NOT NULL DEFAULT 0;

INSERT INTO `#__vikrentitems_config` (`param`,`setting`) VALUES ('enablepin','1');
INSERT INTO `#__vikrentitems_config` (`param`,`setting`) VALUES ('typedeposit','pcent');
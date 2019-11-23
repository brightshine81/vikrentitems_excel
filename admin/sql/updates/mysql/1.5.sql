ALTER TABLE `#__vikrentitems_orders` ADD COLUMN `adminnotes` text DEFAULT NULL;
ALTER TABLE `#__vikrentitems_optionals` ADD COLUMN `onlyonce` tinyint(1) NOT NULL DEFAULT 0;
ALTER TABLE `#__vikrentitems_items` ADD COLUMN `isgroup` tinyint(1) NOT NULL DEFAULT 0;
CREATE TABLE IF NOT EXISTS `#__vikrentitems_groupsrel` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `parentid` int(10) NOT NULL,
  `childid` int(11) DEFAULT NULL,
  `units` int(11) DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
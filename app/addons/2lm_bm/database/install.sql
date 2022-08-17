CREATE TABLE IF NOT EXISTS `?:bluemedia_order_hash` (
  `order_id` int(11) NOT NULL,
  `hash` varchar(256) DEFAULT NULL,
  `remoteID` VARCHAR(20) NULL DEFAULT NULL,
  PRIMARY KEY (`order_id`)
);

CREATE TABLE IF NOT EXISTS `?:bluemedia_log` (
	`log_id` INT(11) NOT NULL AUTO_INCREMENT,
	`order_id` INT(11) NULL DEFAULT NULL,
	`date` DATETIME NULL DEFAULT NULL,
	`action` VARCHAR(255) NULL DEFAULT NULL,
	`data` TEXT NULL,
	`data_raw` TEXT NULL,
	PRIMARY KEY (`log_id`),
	INDEX `order_ids` (`order_id`),
	INDEX `action_idx` (`action`)
);

CREATE TABLE IF NOT EXISTS `?:bluemedia_order_refunds` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`user_id` INT(11) NOT NULL,
	`order_id` INT(11) NOT NULL,
	`amount` DECIMAL(12,2) NULL DEFAULT NULL,
	`remote_id` VARCHAR(20) NULL DEFAULT NULL,
	`remote_out_id` VARCHAR(20) NULL DEFAULT NULL,
	`timestamp` DATETIME NULL DEFAULT NULL,
	PRIMARY KEY (`id`),
	INDEX `order_idk` (`order_id`)
);

CREATE TABLE IF NOT EXISTS `?:bluemedia_subscriptions` (
	`user_id` INT(11) NOT NULL,
	`order_id` INT(11) NOT NULL,
	`bm_order_id` VARCHAR(25) NOT NULL,
	`client_hash` VARCHAR(64) NOT NULL,
	`status` ENUM('pending','activated','disactivated') NULL DEFAULT NULL,
	`timestamp` DATETIME NULL DEFAULT NULL,
	`created` DATETIME NULL DEFAULT NULL,
	`updated` DATETIME NULL DEFAULT NULL,
	`type` VARCHAR(100) NOT NULL DEFAULT 'AUTO',
	`redirect_url` VARCHAR(255) NULL DEFAULT NULL,
	`remote_id` VARCHAR(20) NULL DEFAULT NULL,
	`comment` VARCHAR(255) NULL DEFAULT NULL,
	PRIMARY KEY (`order_id`, `type`),
	INDEX `order_idk` (`order_id`),
	INDEX `status` (`status`),
	INDEX `client_hash` (`client_hash`)
);

ALTER TABLE `?:products` ADD COLUMN `bluemedia_exclude_from_rp` CHAR(1) NOT NULL DEFAULT 'N';

REPLACE INTO ?:privileges (privilege, is_default, section_id) VALUES ('manage_2lm_bm_refund', 'Y', 'addons');

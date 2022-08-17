DROP TABLE IF EXISTS `?:bluemedia_order_hash`;
DROP TABLE IF EXISTS `?:bluemedia_log`;
DROP TABLE IF EXISTS `?:bluemedia_order_refunds`;
DROP TABLE IF EXISTS `?:bluemedia_subscriptions`;

ALTER TABLE `?:products` DROP `bluemedia_exclude_from_rp`;

DELETE FROM ?:privileges WHERE privilege = 'manage_2lm_bm_refund';

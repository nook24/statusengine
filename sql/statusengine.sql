CREATE DATABASE statusengine CHARACTER SET utf8 COLLATE utf8_general_ci;
use statusengine;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

INSERT INTO `users` (`id`, `username`, `password`, `created`, `modified`) VALUES
(1, 'admin', '$2a$10$SmXYYLizIyKfY9o3HVREVelCZZWAydz7GYXXydRbhDIILRv1fsrLi', '2015-06-28 21:23:58', '2015-06-28 21:23:58');


-- v1.1
CREATE TABLE IF NOT EXISTS `users` (
  `user_id` int(7) NOT NULL AUTO_INCREMENT,
  `username` varchar(15) NOT NULL,
  `password` char(40) NOT NULL,
  `email` varchar(100) NOT NULL,
  `activated` int(1) NOT NULL DEFAULT '0',
  `confirmation` char(40) NOT NULL,
  `reg_date` int(11) NOT NULL,
  `last_login` int(11) NOT NULL DEFAULT '0',
  `group_id` int(2) NOT NULL DEFAULT '1',
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

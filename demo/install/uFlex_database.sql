-- v1.0
CREATE TABLE IF NOT EXISTS `users` (
  `user_id` int(7) NOT NULL AUTO_INCREMENT,
  `username` varchar(15) NOT NULL,
  `first_name` varchar(15) NOT NULL,
  `last_name` varchar(15) NOT NULL,
  `website` varchar(50) NOT NULL,
  `password` varchar(35) NOT NULL,
  `email` varchar(35) NOT NULL,
  `activated` int(1) NOT NULL DEFAULT '0',
  `confirmation` varchar(35) NOT NULL,
  `reg_date` int(11) NOT NULL,
  `last_login` int(11) NOT NULL DEFAULT '0',
  `group_id` int(2) NOT NULL DEFAULT '1',
  PRIMARY KEY (`user_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
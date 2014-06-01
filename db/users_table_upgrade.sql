-- Upgrades the table from v1.0 to v1.1 --
-- IMPORTANT: BackUp your Database before running this script --

--  Note: if your users table was other than `users` update the line below --
RENAME TABLE `users` TO `Users`;
-- --
ALTER IGNORE TABLE `Users` CHANGE `user_id` `ID` int( 7 ) unsigned NOT NULL AUTO_INCREMENT;
ALTER IGNORE TABLE `Users` CHANGE `username` `Username` varchar ( 15 ) NOT NULL;
ALTER IGNORE TABLE `Users` CHANGE `password` `Password` char ( 40 ) NOT NULL;
ALTER IGNORE TABLE `Users` CHANGE `email` `Email` varchar ( 100 ) NOT NULL;
ALTER IGNORE TABLE `Users` CHANGE `activated` `Activated` tinyint ( 1 ) unsigned NOT NULL DEFAULT 0;
ALTER IGNORE TABLE `Users` CHANGE `confirmation` `Confirmation` char ( 40 ) NOT NULL;
ALTER IGNORE TABLE `Users` CHANGE `reg_date` `RegDate` int ( 11 ) unsigned NOT NULL;
ALTER IGNORE TABLE `Users` CHANGE `last_login` `LastLogin` int ( 11 ) unsigned NOT NULL DEFAULT 0;
ALTER IGNORE TABLE `Users` CHANGE `group_id` `GroupID` tinyint unsigned NOT NULL DEFAULT 1;
ALTER TABLE `Users` ENGINE=InnoDB;
-- Demo specific fields
ALTER IGNORE TABLE `users` ADD `website` VARCHAR( 50 ) NOT NULL AFTER `Username` ;
ALTER IGNORE TABLE `users` ADD `last_name` VARCHAR( 15 ) NOT NULL AFTER `Username` ;
ALTER IGNORE TABLE `users` ADD `first_name` VARCHAR( 15 ) NOT NULL AFTER `Username` ;

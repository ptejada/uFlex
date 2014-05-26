-- Demo specific fields
ALTER IGNORE TABLE `Users` ADD `website` VARCHAR( 50 ) NOT NULL AFTER `Username` ;
ALTER IGNORE TABLE `Users` ADD `last_name` VARCHAR( 15 ) NOT NULL AFTER `Username` ;
ALTER IGNORE TABLE `Users` ADD `first_name` VARCHAR( 15 ) NOT NULL AFTER `Username` ;

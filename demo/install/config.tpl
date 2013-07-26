<?php
	include('inc/class.uFlex.php');
	
	//Instantiate the uFlex object
	$user = new uFlex(false);
	
	//Add database credentials and information 
	$user->db['host'] = "#!db_host";
	$user->db['user'] = "#!db_user";
	$user->db['pass'] = "#!db_pass";
	$user->db['name'] = "#!db_name"; //Database name
	
	/*
	 * Instead of editing the class.uFlex.php file directly you may make
	 * the changes in this space before running the ->start() command.
	 * For example: if want to to change the default username from "Guess"
	 * to "Stranger" you do this:
	 * 
	 * 		$user->opt["default_user"]["username"] = "Stranger";
	 * 
	 * You may change and customize all the options and configurations like
	 * this, even the error messages. By exporting your customizations outside
	 * the class file it will be easier to maintain your application configuration
	 * and update the class core itself in the future. Just remember to start
	 * the object with the first parameter 'false', new uFlex(false), to halt
	 * the object construction.
	 */
		
	//Starts the object by triggering the constructor
	$user->start();
	
	include('inc/functions.php');
?>
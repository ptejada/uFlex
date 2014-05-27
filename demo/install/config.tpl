<?php

include('inc/functions.php');
include(dirname(__FILE__) .'/../../autoload.php');

//Instantiate the uFlex User object
$user = new \ptejada\uFlex\User();

//Add database credentials and information
$user->config->database->host = "#!db_host";
$user->config->database->user = "#!db_user";
$user->config->database->password = "#!db_pass";
$user->config->database->name = "#!db_name"; //Database name

/*
 * Instead of editing the Class files directly you may make
 * the changes in this space before calling the ->start() method.
 * For example: if we want to change the default Username from "Guess"
 * to "Stranger" you do this:
 *
 * $user->config->userDefaultData->Username = 'Stranger';
 *
 * You may change and customize all the options and configurations like
 * this, even the error messages. By exporting your customizations outside
 * the class file it will be easier to maintain your application configuration
 * and update the class core itself in the future.
 */

//Starts the object by triggering the constructor
$user->start();


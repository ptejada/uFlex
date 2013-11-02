<?php
	//Add validation for custom fields, first_name, last_name and website
	
	$user->addValidation(Array(
		"first_name"	=> Array(
			"limit" => "0-15",
			"regEx" => "/\w+/"
			),
		
		"last_name"	=> Array(
			"limit" => "0-15",
			"regEx" => "/\w+/"
			),
			
		"webste"	=> Array(
			"limit" => "0-50",
			"regEx" => "@((https?://)?([-\w\.]+)+(:\d+)?(/([\w/_\.]*(\?\S+)?)?)?)@"
		)
	));
	/*
	$user->addValidation("first_name","0-15","/\w+/");
	$user->addValidation("last_name","0-15","/\w+/");
	$user->addValidation("website","0-50","@((https?://)?([-\w\.]+)+(:\d+)?(/([\w/_\.]*(\?\S+)?)?)?)@");
	*/
?>
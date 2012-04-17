<?php
	include("../core/config.php");

	if($user->signed) redirect("../");
	
	//Proccess Registration
	if(count($_POST)){
		
		//Add validation for custom fields, first_name, last_name and website
		$user->addValidation("first_name","0-15","/\w+/");
		$user->addValidation("last_name","0-15","/\w+/");
		$user->addValidation("website","0-50","@((https?://)?([-\w\.]+)+(:\d+)?(/([\w/_\.]*(\?\S+)?)?)?)@");
				
		//Register User
		$registered = $user->register($_POST);		
		if(!$registered){
			$_SESSION['NoteMsgs'] = $user->error();
			$_SESSION["regData"] = $_POST;
			redirect();		
		}else{
			$_SESSION['NoteMsgs'][] = "User Registered Successfully";
			$_SESSION['NoteMsgs'][] = "You may login now!";
			redirect("../?page=login");			
		}
	}else{
		redirect();		
	}
	
?>

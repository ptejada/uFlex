<?php
	include("../core/config.php");
	
	//Proccess Update
	if(count($_POST)){
		
		foreach($_POST as $name=>$val){
			if($user->data[$name] == $val){
			
				unset($_POST[$name]);
			}		
		}
		
		//Add validation for custom fields, first_name, last_name and website
		$user->addValidation("first_name","0-15","/[a-zA-Z]+/");
		$user->addValidation("last_name","0-15","/[a-zA-Z]+/");
		$user->addValidation("website","0-50","@((https?://)?([-\w\.]+)+(:\d+)?(/([\w/_\.]*(\?\S+)?)?)?)@");
		
		if(count($_POST)){
			//Update info
			$user->update($_POST);			
		}else{
			//Nothing has changed
			$_SESSION['NoteMsgs'][0] = "No need to update!";
		}
		
		//If there are errors
		if($user->has_error()){
			//There were errors while updating information
			$_SESSION['NoteMsgs'] = $user->error();	
		}elseif(count($_POST)){
			//Updates have been saved successfully!
			$_SESSION['NoteMsgs'][0] = "Information Updated!";
		}
	}
	
	redirect();
	
?>
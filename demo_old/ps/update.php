<?php
	include("../core/config.php");
	include("../core/validations.php");
	
	//Proccess Update
	if(count($_POST)){
		
		foreach($_POST as $name=>$val){
			if($user->data[$name] == $val){
			
				unset($_POST[$name]);
			}
		}
		
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
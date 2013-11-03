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
			$user->error('No need to update!');
		}

		echo json_encode(array(
			'error'    => $user->error(),
			'confirm'  => "Account Updated!",
			'form'    => $user->form_error(),
		));
	}
<?php
	include("../core/config.php");	
	
	//Proccess Password change
	if(count($_POST)){
		$hash = @$_POST['c'];
		unset($_POST['c']);
		
		if(!$user->signed and $hash){
			//Change password with confirmation hash
			$user->new_pass($hash,$_POST);
			$redirectPage = "login";
		}else{
			//Change the password of signed in user without a confirmation hash 
			$user->update($_POST);
			$redirectPage = "account";
		}

		echo json_encode(array(
			'error'    => $user->error(),
			'confirm'  => "Password Changed",
			'form'     => $user->form_error(),
		));
	}
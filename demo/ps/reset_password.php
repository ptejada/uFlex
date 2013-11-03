<?php
	include("../core/config.php");
	
	//Proccess Update
	if(count($_POST)){
		$res = $user->pass_reset($_POST['email']);

		$errorMessage = '';
		$confirmMessage = '';

		if($res){
			//Hash succesfully generated
			//You would send an email to $res['email'] with the URL+HASH $res['hash'] to enter the new password
			//In this demo we will just redirect the user directly
			
			$url = "account/update/password?c=" . $res['hash'];
			$confirmMessage = "Use the link below to change your password <a href='{$url}'>Change Password</a>";

		}else{
			$errorMessage = $user->error();
			$errorMessage = $errorMessage[0];
		}

		echo json_encode(array(
			'error'    => $user->error(),
			'confirm'  => $confirmMessage,
			'form'    => array(
				'email' => $errorMessage
			)
		));
	}
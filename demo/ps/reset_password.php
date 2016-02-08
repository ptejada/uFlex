<?php
	include("../core/config.php");
	
	//Process Update
	if(count($_POST)){
        /*
         * Covert POST into a Collection object
         * for better handling
         */
        $input = new \ptejada\uFlex\Collection($_POST);

		$res = $user->resetPassword($input->Email);

		$errorMessage = '';
		$confirmMessage = '';

		if($res){
			//Hash succesfully generated
			//You would send an email to $res['Email'] with the URL+HASH $res['hash'] to enter the new Password
			//In this demo we will just redirect the user directly
			
			$url = 'account/update/password?c=' . $res->Confirmation;
			$confirmMessage = "Use the link below to change your password <a href='{$url}'>Change Password</a>";

		}else{
			$errorMessage = $user->log->getErrors();
			$errorMessage = $errorMessage[0];
		}

		echo json_encode(array(
			'error'    => $user->log->getErrors(),
			'confirm'  => $confirmMessage,
			'form'    => array(
				'Email' => $errorMessage
			)
		));
	}
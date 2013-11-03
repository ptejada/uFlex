<?php
	include("../core/config.php");
	include("../core/validations.php");
	
	//Proccess Registration
	if(count($_POST)){
		//Register User
		$user->register($_POST);

		echo json_encode(array(
			'error'    => $user->error(),
			'confirm'  => "User Registered Successfully. You may login now!",
			'form'     => $user->form_error(),
		));
	}
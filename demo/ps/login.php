<?php

include("../core/config.php");

//Proccess Login
if(count($_POST)){
	$username = isset($_POST['username']) ? $_POST['username'] : false;
	$password = isset($_POST['password']) ? $_POST['password'] : false;
	$auto = isset($_POST['auto']) ? $_POST['auto'] : false;

	$user->login($username,$password,$auto);

	$errMsg = '';

	if($user->has_error()){
		$errMsg = $user->error();
		$errMsg = $errMsg[0];
	}

	echo json_encode(array(
		'error'    => $user->error(),
		'confirm'  => "You are now login as <b>$user->username</b>",
		'form'    => array(
			'password' => $errMsg
		)
	));
}


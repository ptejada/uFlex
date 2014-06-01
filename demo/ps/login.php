<?php

include('../core/config.php');

//Process Login
if(count($_POST)){
    /*
     * Covert POST into a Collection object
     * for better value handling
     */
    $input = new \ptejada\uFlex\Collection($_POST);

	$user->login($input->Username, $input->Password, $input->auto);

	$errMsg = '';

	if($user->log->hasError()){
		$errMsg = $user->log->getErrors();
		$errMsg = $errMsg[0];
	}

	echo json_encode(array(
		'error'    => $user->log->getErrors(),
		'confirm'  => "You are now login as <b>$user->Username</b>",
		'form'     => $user->log->getFormErrors(),
	));
}


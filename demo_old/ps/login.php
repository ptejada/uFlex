<?php
	include("../core/config.php");
	
	//Proccess Login
	if(count($_POST)){
		$username = isset($_POST['username']) ? $_POST['username'] : false;
		$password = isset($_POST['password']) ? $_POST['password'] : false;
		$auto = isset($_POST['auto']) ? $_POST['auto'] : false;
		
		$user->login($username,$password,$auto);
		if($user->has_error()){
			$_SESSION['NoteMsgs'] = $user->error();
		}
	}
	
	redirect();
?>

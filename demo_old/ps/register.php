<?php
	include("../core/config.php");
	include("../core/validations.php");

	if($user->signed) redirect("../");
	
	//Proccess Registration
	if(count($_POST)){
		//Register User
		$registered = $user->register($_POST);
		if(!$registered){
			$_SESSION['NoteMsgs'] = $user->error();
			$_SESSION["regData"] = $_POST;
			redirect();		
		}else{
			$_SESSION['NoteMsgs'][] = "User Registered Successfully";
			$_SESSION['NoteMsgs'][] = "You may login now!";
			redirect("../?page=login");
		}
	}else{
		redirect();
	}
	
?>

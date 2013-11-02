<?php
	include("../core/config.php");
	
	//Proccess Update
	if(count($_POST)){
		$res = $user->pass_reset($_POST['email']);
		
		if($res){
			//Hash succesfully generated
			//You would send an email to $res['email'] with the URL+HASH $res['hash'] to enter the new password
			//In this demo we will just redirect the user directly
			
			$url = "../?page=change-password&c=" . $res['hash'];
			$_SESSION["NoteMsgs"][] = "You may change your password";
			
			//Redirect
			redirect($url);
		}else{
			$_SESSION["NoteMsgs"] = $user->error();
			
			redirect();
		}
	}
	
?>

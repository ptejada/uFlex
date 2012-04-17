<?php
	include("../core/config.php");
	
	//Proccess Login
	if(count($_POST)){
	  @$username = $_POST['username'];
	  @$password = $_POST['password'];
	  @$auto = $_POST['auto'];
	  
	  @$user = new uFlex($username,$password,$auto);
	  if($user->has_error()){
		  $_SESSION['NoteMsgs'] = $user->error();	  	
	  }
	}
	
	redirect();
?>

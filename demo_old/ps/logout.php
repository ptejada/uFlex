<?php
	include("../core/config.php");
	
	$user->logout();
	
	$_SESSION["NoteMsgs"][] = "Logout Successful";
	
	redirect("../");	
?>

<?php
	include("inc/config.php");
	
	$user->logout();
	
	header("Location: index.php");	
?>

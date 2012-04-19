<?php	
	include("class.uFlex.php");
	
	$user = new uFlex();
	
	$user->login();
?>

<pre>
<?php 
	print_r($user->report()); 
	print_r($_SESSION);
?>

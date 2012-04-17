<?php
	include("inc/config.php");
	
	//If user is not signed in refirect
	if(!$user->signed) header("Location: index.php");
	
?>
<html>
<head>
	<link rel="stylesheet" type="text/css" href="style.css" />
	<title>My Account | uFlex Demo</title>
</head>

<body>
	<h1>My Account</h1>
	<hr>
	
	<a href="update.php">Update Information</a>
	<a href="change_password.php">Change Password</a>
	<a href="logout.php">Logout</a>
	<table border=0>
		<tr><td></td><td></td></tr>
		<?php
			foreach($user->data as $field=>$val){
				echo "<tr><td>{$field}</td><td>=></td><td> {$val}</td></tr>";
			}
		?>
	</table>
</body>
</html>
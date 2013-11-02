<?php
	if($user->signed) redirect("./?page=account");
?>	
	<h1>Login</h1>
	
	<div class="report">
		<?php echo showMsg()?>
	</div>
	
	<form method="post" action="ps/login.php">
		<label>Username or Email:</label>
		<input name="username" type="text" value="">
		
		
		<label>Password:</label>
		<input name="password" type="password">
		
		
		<label>Remember me?:</label>
		<input name="auto" type="checkbox" style="display: inline-block">
		
		
		<input value="SignIn" type="submit">
	</form>
	
	<a href="?page=register">Register a New Account</a>
	<br>
	<a href="?page=forgot-password">Forgot password?</a>
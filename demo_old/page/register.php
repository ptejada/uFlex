<?php
	if($user->signed) redirect(".");
	
	$d = @$_SESSION["regData"];
	unset($_SESSION["regData"]);
?>
	<h1>Register</h1>
	
	<div class="report">
		<?php echo showMsg()?>
	</div>

	<form method="post" action="ps/register.php">
		<label>Username:</label><span class="required">*</span>
		<input name="username" type="text" value="<?php echo @$d['username']?>">
		
		
		<label>First Name:</label>
		<input name="first_name" type="text" value="<?php echo @$d['first_name']?>">
		
		
		<label>Last Name:</label>
		<input name="last_name" type="text" value="<?php echo @$d['last_name']?>">
		
		
		<label>Password:</label><span class="required">*</span>
		<input name="password" type="password" value="<?php echo @$d['password']?>">
		
		
		<label>Re-enter Password:</label><span class="required">*</span>
		<input name="password2" type="password" value="<?php echo @$d['password2']?>">
		
		
		<label>Email: </label><span class="required">*</span>
		<input name="email" type="text" value="<?php echo @$d['email']?>">
		
		
		<label>Website: </label>
		<input name="website" type="text" value="<?php echo @$d['website']?>">
		
		
		<label>Group: </label><br>
		<select name="group_id" value="<?php echo @$d['group_id']?>">
			<option value="1">User</option>
			<option value="2">Developer</option>
			<option value="3">Designer</option>
		</select><br><br>
		
		
		<input value="Register" type="submit">
	</form>
	
	<a href="/login/">Login</a>

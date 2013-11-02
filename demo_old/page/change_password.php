	<h1>Change Password</h1>
	
	<div class="report">
		<?php echo showMsg()?>
	</div>

	<form method="post" action="ps/change_password.php">
		<label>New Password:</label><span class="required">*</span>
		<input name="password" type="password">		
		
		<label>Retype New Password:</label><span class="required">*</span>
		<input name="password2" type="password">
		
		<input name="c" type="hidden" value="<?php echo getVar("c")?>"></input>	 
		
		<input value="Change" type="submit">
	</form>
	
	<a href=".">Back to my account / Login</a>
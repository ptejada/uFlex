	<h1>Forgot Password</h1>
	
	<div class="report">
		<?php echo showMsg()?>
	</div>

	<form method="post" action="ps/reset_password.php">
		<p>Enter the email associated with your account</p>			
		<label>Email: </label><span class="required">*</span>
		<input name="email" type="text" value="">
		
				
		<input value="Reset Password" type="submit">
	</form>
	
	<a href="?page=login">Login</a>

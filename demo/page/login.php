<?php
	if($user->isSigned()) redirect("./account");
?>
<div class="row">
	<div class="col-sm-6 col-sm-offset-3">
		<h2>Login</h2>

		<hr/>

		<form method="post" action="ps/login.php" data-success="<?php echo $base?>/account">
			<div class="form-group">
				<label>Username or Email:</label>
				<input name="Username" type="text" class="form-control" required autofocus>
			</div>

			<div class="form-group">
				<label>Password:</label>
				<input name="Password" type="password" class="form-control" required>
			</div>

			<div class="form-group text-center">
				<button type="submit" class="btn btn-primary">Sign In</button>
				<br/><br/>
				<a href="register">Register a New Account</a>
				<br>
				<a href="resetPassword">Forgot Password?</a>
			</div>
		</form>

	</div>
</div>
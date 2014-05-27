<div class="row">
	<div class="col-sm-6 col-sm-offset-3">
		<h2>Change Password</h2>

		<hr/>

		<form method="post" action="<?php echo $base?>/ps/change_password.php" data-success="<?php echo $base?>/account">
			<div class="form-group">
				<label>New Password:</label>
				<input name="Password" type="password" class="form-control" required autofocus>
			</div>

			<div class="form-group">
				<label>Confirm New Password:</label>
				<input name="Password2" type="password" class="form-control" required>
			</div>
			<input name="c" type="hidden" value="<?php echo getVar("c")?>">

			<div class="form-group text-center">
				<button type="submit" class="btn btn-primary">Change Password</button>
				<br/>
				<a href="<?php echo $base?>/account" class="">Back to my account / Login</a>
			</div>
		</form>

	</div>
</div>

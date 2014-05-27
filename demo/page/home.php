<div class="row">
	<div class="col-sm-5 col-sm-offset-4">
		<?php if($user->isSigned()): ?>
			<a class="btn btn-default btn-block" href="account">My Account</a>
			<a class="btn btn-default btn-block" href="ps/logout.php">LogOut</a>
		<?php else: ?>
			<a class="btn btn-default btn-block" href="login">LogIn</a>
			<a class="btn btn-default btn-block" href="register">Register</a>
			<a class="btn btn-default btn-block" href="resetPassword">Forgot Password?</a>
		<?php endif; ?>
	</div>
</div>


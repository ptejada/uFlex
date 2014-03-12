<?php
	//If user is not signed in refirect
	if(!$user->signed) redirect("../login");
?>
<div class="row">
	<div class="col-sm-6 col-sm-offset-3">
		<h2>Update Account</h2>

		<hr/>

		<form method="post" action="<?php echo $base ?>/ps/update.php" data-success="<?php echo $base?>/account">
			<div class="form-group">
				<label>Username:</label>
				<input disabled name="username" type="text" value="<?php echo $user->data['username']?>" class="form-control">
			</div>

			<div class="form-group">
				<label>First Name:</label>
				<input name="first_name" type="text" value="<?php echo $user->data['first_name']?>" class="form-control">
			</div>

			<div class="form-group">
				<label>Last Name:</label>
				<input name="last_name" type="text" value="<?php echo $user->data['last_name']?>" class="form-control">
			</div>

			<div class="form-group">
				<label>Email: </label>
				<input name="email" type="text" required value="<?php echo $user->data['email']?>" class="form-control">
			</div>

			<div class="form-group">
				<label>Website: </label>
				<input name="website" type="text" value="<?php echo $user->data['website']?>" class="form-control">
			</div>

			<div class="form-group text-center">
				<button type="submit" class="btn btn-primary">Update</button>
				<br>
				<a href="<?php echo $base?>/account" class="">Back to my account</a>
			</div>
		</form>

	</div>
</div>

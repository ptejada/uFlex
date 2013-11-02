	<h1>Update</h1>
	
	<div class="report">
		<?php echo showMsg()?>
	</div>

	<form method="post" action="ps/update.php">
		<label>Username:</label>
		<input disabled name="username" type="text" value="<?php echo $user->data['username']?>">
		
		
		<label>First Name:</label>
		<input name="first_name" type="text" value="<?php echo $user->data['first_name']?>">
		
		
		<label>Last Name:</label>
		<input name="last_name" type="text" value="<?php echo $user->data['last_name']?>">
		
					   
		<label>Email: </label><span class="required">*</span>
		<input name="email" type="text" value="<?php echo $user->data['email']?>">
		
		
		<label>Website: </label>
		<input name="website" type="text" value="<?php echo $user->data['website']?>">
		
				
		<input value="Update" type="submit">
	</form>
	
	<a href="?page=account">Back to my account</a>

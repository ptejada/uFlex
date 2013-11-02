<?php
	//If user is not signed in refirect
	if(!$user->signed) redirect("./?page=login");	
?>

	<h1>My Account</h1>
	
	<div class="report">
		<?php echo showMsg()?>
	</div>
	
	<a class="md_bnt" href="?page=account-update">Update Information</a>
	<a class="md_bnt" href="?page=change-password">Change Password</a>
	<a class="md_bnt" href="ps/logout.php">Logout</a>
	<hr>
	<img src="http://www.gravatar.com/avatar/<?php echo md5($user->data['email'])?>?d=monsterid">
	<br>
	<br>
	<form>
		
		<?php
			foreach($user->data as $field=>$val){
				echo "<label><b>{$field}</b></label> <input type='text' value='{$val}' disabled='disabled' />";
			}
		?>
	</form>
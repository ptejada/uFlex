	<div class="report">
		<?php echo showMsg()?>
	</div>

<?php 
	if($user->signed){ ?>
		<a class="lg_bnt" href="?page=account">My Account</a>
		<a class="lg_bnt" href="ps/logout.php">LogOut</a>
	<?php }else{ ?>
		<a class="lg_bnt" href="?page=login">LogIn</a>
		<a class="lg_bnt" href="?page=register">Register</a>
		<a class="lg_bnt" href="?page=forgot-password">Forgot Password?</a>
	<?php } ?>

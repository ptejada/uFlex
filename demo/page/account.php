<?php
	//If user is not signed in refirect
	if(!$user->signed) redirect("./login");
?>
<div class="row">
	<div class="col-xs-12">
		<?php echo gravatar($user->data['email'], 50); ?>
		<div class="btn-group pull-right2">
			<a class="btn btn-primary" href="account/update">Update Information</a>
			<a class="btn btn-primary" href="account/update/password">Change Password</a>
		</div>
		<table class="table">
			<?php foreach($user->data as $name=>$value): ?>
				<tr>
					<th><?php echo $name?></th>
					<td><?php echo $value?></td>
				</tr>
			<?php endforeach; ?>
		</table>
	</div>
</div>
<div class="row">
	<div class="col-xs-10 col-xs-offset-1">

		<?php
		//Display random users
		$sql = "SELECT *
				FROM :table WHERE activated=1
				ORDER BY RAND()
				LIMIT 24
			";

		$stmt = $user->getStatement($sql);
		$stmt->execute();
		$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

		if($data){

			p("Random Users :)", 2);
			echo '<hr>';

			foreach($data as $u){
				echo '<div class="col-sm-2 col-xs-4 text-center">';
				echo "<a class='center-block userBox' href='user?id={$u['user_id']}' title='{$u['username']}'>";
				echo gravatar($u['email']);
				echo "<span class='label label-primary center-block'>{$u['username']}</span>";
				echo '</div></a>';
			}
		}else{
			p("No users Available", 2);
		}
		?>
	</div>
</div>
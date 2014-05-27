<div class="row">
	<div class="col-xs-10 col-xs-offset-1">

		<?php
		//Display random users
		$sql = "SELECT *
				FROM _table_ WHERE Activated=1
				ORDER BY RAND()
				LIMIT 24
			";

		$stmt = $user->table->getStatement($sql);
		$stmt->execute();

		if($stmt->rowCount()){

			p("Random Users :)", 2);
			echo '<hr>';

			while( $u = $stmt->fetch() ){
				echo '<div class="col-sm-2 col-xs-4 text-center">';
				echo "<a class='center-block userBox' href='user?id={$u->ID}' title='{$u->Username}'>";
				echo gravatar($u->Email);
				echo "<span class='label label-primary center-block'>{$u->Username}</span>";
				echo '</div></a>';
			}
		}else{
			p("No users Available", 2);
		}
		?>
	</div>
</div>
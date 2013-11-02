<?php
	maxArg(2);
	
	$uid = getVar("id");
	
	if($uid){
		//Display single users
		
		$select = (intval($uid)!=0) ? "user_id" : "username";
		
		$data = $user->getRow(Array($select => $uid));
		
		if($data){
			?>
			<h3><?php echo $data['username']?>'s Profile</h3>
			<img src="http://www.gravatar.com/avatar/<?php echo md5($data['email'])?>?d=monsterid">
			<table border=0>
				<tr><td></td><td></td></tr>
				<?php
					foreach($data as $field=>$val){
						if($field == "email") continue;
						echo "<tr><td>{$field}</td><td>  =>  </td><td> {$val}</td></tr>";
					}
				?>
			</table>
			<?php
		}else{
			p("User doesn't exists", 2);
		}
	}else{
		//Display random users
		$sql = "SELECT * 
				FROM :table 
				WHERE activated=1
				ORDER BY RAND()
				LIMIT 24
			";
		
		$data = $user->getStatement($sql);
		$data->execute();
		$data = $data->fetchAll(PDO::FETCH_ASSOC);
		
		if($data){
			
			p("Fun Random Users :)", 2);
			
			foreach($data as $u){
				?>
				<a href="?page=user&id=<?php echo $u['user_id']?>">
					<img src="http://www.gravatar.com/avatar/<?php echo md5($u['email'])?>?d=monsterid">
					<br>
					<?php echo $u['username']?>
				</a>
				<?php
			}
		}else{
			p("No users Available", 2);
		}
	}
?>
<?php
	//error_reporting(E_ALL);
	//ini_set('display_errors', false);

	$R = array();
	$S = array();
	
	if($_POST){
		
		$db_host = $_POST['db_host'];
		$db_user = $_POST['db_user'];
		$db_pass = $_POST['db_pass'];
		$db_name = $_POST['db_name'];
		
		$db_link = mysql_connect($db_host, $db_user, $db_pass);
		
		if(!$db_link){
			$R['connection'] = "Could not connect to <b>{$db_host}</b> with username <b>{$db_user}</b> and password <b>{$db_pass}</b>";
		}else{
			$connection = mysql_select_db($db_name, $db_link);
			
			if(!$connection){
				$R['database'] = "Could not access the database <b>{$db_name}</b>";
				
				//Create Database
				$created = mysql_query("CREATE DATABASE {$db_name}");
				
				if($created){
					
					$S[] = "Database Created OK!";
					
					//Retry to connect to database
					$connection = mysql_select_db($db_name, $db_link);
					
					if($connection) unset($R['database']);
				}
			}
			
			if($connection){
				//Import database
				$sql = file_get_contents("uFlex_database.sql");
				
				$import = mysql_query($sql);
				
				if($import) $S[] = "Database Populated OK!";
			}
			
			//Create Configuration file
			if($import){				
				$config = file_get_contents("config.tpl");
				
				foreach($_POST as $tag=>$val){
					$config = str_replace("#!".$tag,$val,$config);
				}
				
				$file = fopen("../core/config.php","w+");
				
				if(fwrite($file,$config) == false){
					$R["config"] = "Could not generate <b>config.php</b>";
				}else{
					$S[] = "Configuration File generated OK!";
					$S[] = "<a href='../'>CLICK HERE YOU ARE DONE!</a>";
					//All completed Ok!
				}
				
				fclose($file);
			}
			
		}
		
	}
?>
<html>
<head>
	<link rel=stylesheet type=text/css href="style" />
	<title>uFlex Demo Installation</title>
	<style>
		label, input {
			font-size: 1.3em;
		}
		label+input {
			background: #000;
			color: #fff;
			padding: 2px 5px;
		}
		label {
			font-weight: bold;
		}
		.success {
			background: #88ff88;
			font-size: 1em;
			margin: 5px auto;
		}
		.error {
			font-size: 1em;
			padding: ;
			background: #ffaaaa;
			margin: 5px auto;
		}
		.error > div, .success > div {
			padding: 10px;
		}
		.error .type {
			padding-right: 5px;
			font-size: 1.2em;
		}
	</style>
</head>
<body>
	<h1>uFlex DEMO configuration</h1>
	<div class="success">
		<?php
			foreach($S as $msg){
				echo "<div>{$msg}</div>";
			}
		?>
	</div>
	<div class="error">
		<?php
			foreach($R as $name=>$error){
				echo "<div><b class=type>{$name}:</b>{$error}</div>";
			}
		?>
	</div>
	<form method="post" action="">
		<label>Database Host:</label>
		<input type="text" name="db_host" value="<?php echo @$_POST['db_host']?>" />
		<hr />
		
		<label>Database User:</label>
		<input type="text" name="db_user" value="<?php echo @$_POST['db_user']?>" />
		<hr />
		
		<label>Database Password:</label>
		<input type="password" name="db_pass" value="<?php echo @$_POST['db_pass']?>" />
		<hr />
		
		<label>Database Name:</label>
		<input type="text" name="db_name" value="<?php echo @$_POST['db_name']?>" />
		<hr />
		
		<input type="submit" value="Generate Configuration" />		
	</form>
</body>

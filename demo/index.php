<?php
	if(!file_exists("core/config.php")){
		header("Location: install/");
	}
	
	include("core/config.php");
	
	$page = @$_GET['page'];
	
	$page = !$page ? "home" : $page;
	
	$ext = ".php";
	
	$page_inc = "page/" . str_replace("-", "_", $page) . $ext;
	
	//Page not found
	if(!file_exists($page_inc) || strpos($page_inc, "..")) send404();
		
	$page_title = ucfirst($page);
?>
<html>
<head>
	<link rel=stylesheet type=text/css href="style/style.css" />
	<title><?php echo $page_title?> | uFlex</title>
</head>
<body>
	<div id="wrapper">
		<div id="banner">
			<h1>uFlex - Demo</h1>
			<div id="nav">
				<?php
					if($user->signed){
						?>
						<span>
							<a href="ps/logout.php">
								Logout(<?php echo $user->username?>)
							</a>
						</span>
						<?php
					}
				?>
				<a href=".">Home</a>
				<a> | </a>
				<a href="?page=user">Users</a>
			</div>	
			<hr>		
		</div>
		<div id="content">
			<?php include($page_inc); ?>
		</div>
		<div id="footer">
			<hr>
			Copyright Test &copy; 2012 - 
			<a href="http://ptejada.com/projects/uFlex/">uFlex Home</a> -
			v<?php echo uFlex::version?>
			<hr>
		</div>
	</div>

</body>
</html>
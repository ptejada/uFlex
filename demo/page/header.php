<html>
<html>
<head>
	<title><?php echo $pageTitle?> | uFlex</title>
	
	<meta name="robots" content="noindex">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	
	<link href="http://bootswatch.com/readable/bootstrap.min.css" rel="stylesheet">
	<link rel=stylesheet type=text/css href="<?php echo $base?>/static/css/style.css" />

</head>
<body>
<div id="wrapper">
	<div class="container">
		<nav class="navbar navbar-default" role="navigation">
			<!-- Brand and toggle get grouped for better mobile display -->
			<div class="navbar-header">
				<button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
					<span class="sr-only">Toggle navigation</span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</button>
				<a class="navbar-brand" href="<?php echo $base?>/">uFlex</a>
			</div>

			<!-- Collect the nav links, forms, and other content for toggling -->
			<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
				<ul class="nav navbar-nav">
					<li><a href="<?php echo $base?>/users">Users</a></li>
				</ul>
				<ul class="nav navbar-nav navbar-right">
					<li></li>
				</ul>
				<p class="navbar-text pull-right">
					<?php if($user->isSigned()): ?>
						<a href="<?php echo $base?>/ps/logout.php" class="btn btn-default btn-xs navbar-btn">
							Logout (<?php echo $user->Username?>)
						</a>
					<?php else: ?>
						<a href="<?php echo $base?>/login" class="btn btn-default btn-xs navbar-btn">
							LogIn
						</a>
					<?php endif; ?>
				</p>
			</div><!-- /.navbar-collapse -->
		</nav>
	</div>

	<div id="content" class="container">

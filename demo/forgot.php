<?php
	include("inc/config.php");
	
	//Proccess Update
	if(count($_POST)){
		$res = $user->pass_reset($_POST['email']);
		
		if($res){
			//Hash succesfully generated
			//You would send an email to $res['email'] with the URL+HASH $res['hash'] to enter the new password
			//In this demo we will just redirect the user directly
			
			$url = "change_password.php?c=" . $res['hash'];
			
			//Redirect
			header("Location: {$url}",true);
		}
	}
	
?>
<html>
<head>
	<link rel="stylesheet" type="text/css" href="style.css" />
	<title>Update | uFlex Demo</title>
</head>

<body>
	<h1>Update</h1>
	<hr>
	<div class="report">
		<ul>
		<?php
			if(count($_POST)){
				foreach($user->error() as $i=>$x){
					echo "<li>$x</li>";
				}
			}
		?>
		</ul>
	</div>

    <form method="post" action="">
        <p>Enter the email associated with your account</p>            
        <label>Email: </label><span class="required">*</span>
        <input name="email" type="text" value="">
        
                
        <input value="Reset Password" type="submit">
    </form>
    
    <a href="myAccount.php">Login</a>
</body>
</html>
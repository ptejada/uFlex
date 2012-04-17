<?php
	include("inc/config.php");

	if($user->signed) header("Location: index.php");	
	
	//Proccess Registration
	if(count($_POST)){
		
		//Add validation for custom fields, first_name, last_name and website
		$user->addValidation("first_name","0-15","/\w+/");
		$user->addValidation("last_name","0-15","/\w+/");
		$user->addValidation("website","0-50","@((https?://)?([-\w\.]+)+(:\d+)?(/([\w/_\.]*(\?\S+)?)?)?)@");
		
		//Register User
		$user->register($_POST);
		
		//If there is not error
		if(!$user->has_error()){
			//A workaround to display a confirmation message in this specific  Example
			$user->error("User Registered! You may Login");
		}
	}
	
?>
<html>
<head>
	<link rel="stylesheet" type="text/css" href="style.css" />
	<title>Register | uFlex Demo</title>
</head>

<body>
	<h1>Register</h1>
	<hr>
	<div class="report">
		<ul>
		<?php
			if(count($_POST) and $user->has_error()){
				foreach($user->error() as $i=>$x){
					echo "<li>$x</li>";
				}
			}
		?>
		</ul>
	</div>

    <form method="post" action="">
        <label>Username:</label><span class="required">*</span>
        <input name="username" type="text" value="<?=@$_POST['username']?>">
        
        
        <label>First Name:</label>
        <input name="first_name" type="text" value="<?=@$_POST['first_name']?>">
        
        
        <label>Last Name:</label>
        <input name="last_name" type="text" value="<?=@$_POST['last_name']?>">
        
        
        <label>Password:</label><span class="required">*</span>
        <input name="password" type="password" value="<?=@$_POST['password']?>">
        
        
        <label>Re-enter Password:</label><span class="required">*</span>
        <input name="password2" type="password" value="<?=@$_POST['password2']?>">
        
        
        <label>Email: </label><span class="required">*</span>
        <input name="email" type="text" value="<?=@$_POST['email']?>">
        
        
        <label>Website: </label>
        <input name="website" type="text" value="<?=@$_POST['website']?>">
        
        
        <label>Group: </label>
        <select name="group_id" value="<?=@$_POST['group_id']?>">
            <option value="1">User</option>
            <option value="2">Developer</option>
            <option value="3">Designer</option>
        </select>
        
        
        <input value="Register" type="submit">
    </form>
    
    <a href="login.php">Login</a>
</body>
</html>
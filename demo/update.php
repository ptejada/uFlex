<?php
	include("inc/config.php");
	
	//Proccess Update
	if(count($_POST)){
		
		foreach($_POST as $name=>$val){
			if($user->data[$name] == $val){
			
				unset($_POST[$name]);
			}		
		}
		
		//Add validation for custom fields, first_name, last_name and website
		$user->addValidation("first_name","0-15","/[a-zA-Z]+/");
		$user->addValidation("last_name","0-15","/[a-zA-Z]+/");
		$user->addValidation("website","0-50","@((https?://)?([-\w\.]+)+(:\d+)?(/([\w/_\.]*(\?\S+)?)?)?)@");
		
		if(count($_POST)){
			//Update info
			$user->update($_POST);			
		}else{
			$user->error("No need to update!");
		}
		
		//If there is not error
		if(!$user->has_error()){
			//A workaround to display a confirmation message in this specific  Example
			$user->error("Information Updated!");
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
        <label>Username:</label><span class="required">*</span>
        <input disabled name="username" type="text" value="<?=$user->data['username']?>">
        
        
        <label>First Name:</label>
        <input name="first_name" type="text" value="<?=$user->data['first_name']?>">
        
        
        <label>Last Name:</label>
        <input name="last_name" type="text" value="<?=$user->data['last_name']?>">
        
                       
        <label>Email: </label><span class="required">*</span>
        <input name="email" type="text" value="<?=$user->data['email']?>">
        
        
        <label>Website: </label>
        <input name="website" type="text" value="<?=$user->data['website']?>">
        
                
        <input value="Update" type="submit">
    </form>
    
    <a href="myAccount.php">Back to my account</a>
</body>
</html>
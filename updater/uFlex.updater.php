<html>
<head>
<style>
p {
    font-size: 13pt;
}
p span {
    background: #ddd;
    padding: 2px 5px;
}
.error {
    color:#f00;
}
h2+div {
    margin-left: 20px;
}
</style>
</head>
<body>

<?php
    function error($err=false){
        $err = "<h2 class='error'>$err</h2><h1>Ok <a href='{$_SERVER['SCRIPT_NAME']}'>Continue</a></h1>";
        die($err);
    }
    $toVersion = 0.75;
    $fromVersion = 0.61;
    
    $classFile = "class.uFlex.php";
    $template = "class.uFlex.update.tpl";
    $backupFile = "class.uFlex.bak-{$fromVersion}.php";
    
    if(@$_GET['restore']){
        if(!rename($backupFile,$classFile)){
            echo "<p>File <span>$classFile</span> could NOT be restored</p>";       
        }else{
            echo "<p>File <span>$classFile</span> was restored successfully. Is like nothing happened</p>";
        };
        error();
    }
?>

    <h1>PHP uFlex class updater - v<?php echo $fromVersion?> to v<?php echo $toVersion?></h1>
    <hr>
    <p>
        This script will
        <strong>Update</strong> <span><?php echo $classFile?></span> From <strong>Version</strong> <span><?php echo $fromVersion?></span> <strong>To</strong> <span><?php echo $toVersion?></span>
        using the <strong>Template</strong> <span><?php echo $template?></span> <br><br>
        Your old version will be backedUp as <span><?php echo $backupFile?></span>
    </p>
    <hr>
    
    <?php
        if(@!$_GET['confirm']){
            echo "<h1>Ok <a href={$_SERVER['SCRIPT_NAME']}?confirm=1>Procced to update</a></h1>";
            exit();
        }
        
        //Check if file exists
        if(@!file_exists($classFile)) error("File '$classFile' is not present in current directory");
        
        require_once($classFile);
        
        if(!class_exists("uFlex")) error("The class 'uFlex' is not defined");
        //Create old uFlex oject
        @$u = new uFlex();
        
        //Check old version
        if($fromVersion != uFlex::version) error("Your current $classFile version(".uFlex::version.") is not compatible for the update Or is already updated");
        
        //Check if template exists
        if(@!file_exists($template)) error("File '$template' is not present in current directory");
        
        //Get template
        $new = @file_get_contents($template);
        
        //Validate template
        if(!preg_match("/const version \= " .$toVersion . "/",$new)) error("The template '{$template}' is not version $toVersion");
        
        //Check in backup exists
        if(@file_exists($backupFile)) error("Warning: the backup file '{$backupFile}' already exists. For security reasons and to avoid unwanted data lost, you must delete OR rename this file manually");
        
        //Back up old file
        if(!copy($classFile,$backupFile)) error("Could not backed up old $classFile");
		
        $master = array(
            "{{::debug}}"                   => uFlex::debug,
            "{{::salt}}"                    => uFlex::salt,
            "{{_table_name}}"               => $u->opt['table_name'],
            "{{_cookie_time}}"              => $u->opt['cookie_time'],
            "{{_cookie_name}}"              => $u->opt['cookie_name'],
            "{{_cookie_path}}"              => $u->opt['cookie_path'],
            "{{_cookie_host}}"              => $u->opt['cookie_host'],
            "{{_user_session}}"             => $u->opt['user_session'],
            "{{_default_user-_username}}"   => $u->opt['default_user']['username'],
            "{{_default_user-_user_id}}"    => $u->opt['default_user']['user_id'],
            "{{_default_user-_password}}"   => $u->opt['default_user']['password'],
            "{{_default_user-_signed}}"     => $u->opt['default_user']['signed'],
            
            "{{_username-_limit}}"          => $u->validations['username']['limit'],
            "{{_username-_regEx}}"          => $u->validations['username']['regEx'],
            "{{_password-_limit}}"          => $u->validations['password']['limit'],
            "{{_password-_regEx}}"          => $u->validations['password']['regEx'],
            "{{_email-_limit}}"             => $u->validations['email']['limit'],
            "{{_email-_regEx}}"             => $u->validations['email']['regEx'],
            
            "{{_1}}"		=> $u->errorList[1],
            "{{_2}}"		=> $u->errorList[2],
            "{{_3}}"		=> $u->errorList[3],
            "{{_4}}"		=> $u->errorList[4],
            "{{_5}}"		=> $u->errorList[5],
            "{{_6}}"		=> $u->errorList[6],
            "{{_7}}"		=> $u->errorList[7],
            "{{_8}}"		=> $u->errorList[8],
            "{{_9}}"		=> $u->errorList[9],
            "{{_10}}"		=> $u->errorList[10],
            "{{_11}}"		=> $u->errorList[11],
            "{{_12}}"		=> $u->errorList[12],
            "{{_13}}"		=> $u->errorList[13],
            "{{_14}}"		=> $u->errorList[14],
            
            //Special replacements
            ' => "false"'                   => ' => false',
            ' => ""'                        => ' => false'
            );
        $holders = array_keys($master);
        $replacements = array_values($master);
        
        $new = str_replace($holders,$replacements,$new);
        
        //Replace integer enclosed in quotes Ex. "0"
        $new = preg_replace("%=>\s?\"(\d)\"%","=> $1",$new);
        
        //Save new file
        if(!file_put_contents($classFile,$new)) error("New file could not be saved");
        
        //Check file for errors
        $syntax = file_get_contents("http://" . dirname($_SERVER['HTTP_HOST']  . $_SERVER['REQUEST_URI']) . "/{$classFile}");
        if($syntax){
            echo "<h2>The upgrade was successfull however we found the following syntax error on the NEW $classFile file:</h2>";
            echo "<div>{$syntax}</div>";
            echo "<h2>You can either try to fix this error(s) manually OR manually upgrade the class yourself</h2>";
            echo "<h2>Restore '{$classFile}' back to the original?
                    <a href='{$_SERVER['SCRIPT_NAME']}?restore=1'>Yes</a> | 
                    <a href='{$_SERVER['SCRIPT_NAME']}'>No</a>
                    </h2>";
            exit();
        }
    ?>
    <h2>Success! You have upgraded to version <?php echo $toVersion?></h2>
    <p>
        Even thoug this script did not detected any errors with the upgrade PLEASE manually test your application 
        and check that everthing is working corrently. Also refer to the changelog and check what is NEW and what has change
        ( Rarely changes will affect the way you use the class)
    </p>
    <p>
        You may delete the extra files <span><?php echo $template?></span> and <span>uFlex.updater.php</span>.
        You may want to keep the backup file <span><?php echo $backupFile?></span> until you test that everything is working as it should. 
    </p>
    
</body>
</html>
<?php
if (file_exists("../core/config.php")) {
    header("Location: ../");
}
if ($_POST) {

    $db_host = $_POST['host'];
    $db_user = $_POST['user'];
    $db_pass = $_POST['pass'];
    $db_name = $_POST['name'];

    $response = array(
        'error' => true,
        'form'  => array('error' => ''),
    );

    try{
        $dbh = new PDO("mysql:host=$db_host", $db_user, $db_pass);
    } catch ( PDOException $e ){
        $response['form']['error'] = "Unable to connect to <b>{$db_host}</b> with username <b>{$db_user}</b> and password <b>{$db_pass}</b>";
        $dbh = false;
    }

    $confirm = array();

    if ($dbh) {
        if ($dbh->query("USE {$db_name};")) {
            $dbAccess = true;
            $confirm[] = "Using Existing Database!";
        } else {
            if ($dbh->exec("CREATE DATABASE {$db_name}")) {
                // Database created
                $dbAccess = true;
                $confirm[] = "Database Created OK!";
            } else {
                $dbAccess = false;
                $response['form']['error'] = "Database <b>{$db_name}</b> does not exists and we are unable to created. Make sure the user <b>{$db_user}</b> has permission to create the database or manually create it.";
            }
        }

    }

    if ($dbAccess) {
        //Import database
        $sql = file_get_contents("users_table.sql");
        $import1 = $dbh->query($sql);

        $sql = file_get_contents("demo.sql");
        $import2 = $dbh->query($sql);

        //Create Configuration file
        if ($import1) {
            $confirm[] = "Database Populated OK!";

            $config = file_get_contents("config.tpl");

            foreach ($_POST as $tag => $val) {
                $config = str_replace("#!db_" . $tag, $val, $config);
            }

            $file = fopen("../core/config.php", "w+");

            if (fwrite($file, $config) == false) {
                $response['form']['error'] = "Could not generate <b>config.php</b>";
            } else {
                $confirm[] = "Configuration File generated OK!";
                $confirm[] = "<a href='../'>CLICK HERE YOU ARE DONE!</a>";
            }

            fclose($file);
        } else {
            $response['form']['error'] = "Can not create tables in the <b>{$db_name}</b> database. Double check the user permission.";
        }
    }

    $response['error'] = $response['form']['error'] ? true : false;

    $response['confirm'] = '<ul><li>' . implode('</li><li>', $confirm) . '</li><ul>';

    echo json_encode($response);
    exit();
}
?>
<html>
<head>
    <title>uFlex Demo Installation</title>

    <meta name="robots" content="noindex">
    <link href="http://bootswatch.com/readable/bootstrap.min.css" rel="stylesheet">

</head>
<body>
<div class="container">
    <h1>uFlex DEMO configuration</h1>
    <hr/>
    <form method="post" action="" class="form-horizontal" autocomplete="off">
        <div class="form-group">
            <label class="control-label col-sm-3">Database Host:</label>

            <div class="col-sm-5">
                <input type="text" required name="host" class="form-control" value="localhost"/>
            </div>
        </div>

        <div class="form-group">
            <label class="control-label col-sm-3">Database User:</label>

            <div class="col-sm-5">
                <input type="text" required name="user" class="form-control" value="root"/>
            </div>
        </div>

        <div class="form-group">
            <label class="control-label col-sm-3">User Password:</label>

            <div class="col-sm-5">
                <input type="password" name="pass" class="form-control"/>
            </div>
        </div>

        <div class="form-group">
            <label class="control-label col-sm-3">Database Name:</label>

            <div class="col-sm-5">
                <input type="text" required name="name" class="form-control"/>
            </div>
        </div>

        <div class="form-group">
            <div class="col-sm-5 col-sm-offset-3">
                <input type="hidden" required name="error" class="form-control"/>
            </div>
        </div>

        <div class="form-group text-left">
            <div class="col-sm-5 col-sm-offset-3">
                <button type="submit" class="btn btn-primary">Generate Configuration</button>
            </div>
        </div>
    </form>
</div>

<script src="http://code.jquery.com/jquery-2.0.3.min.js"></script>
<script src="http://netdna.bootstrapcdn.com/bootstrap/3.0.1/js/bootstrap.min.js"></script>
<script src="../static/js/main.js"></script>

</body>

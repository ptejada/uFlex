<?php
// ---------------------------------------------------------------------------
// 	  uFlex - An all in one authentication system PHP class
//    Copyright (C) 2010  Pablo Tejada
//
//    This program is free software: you can redistribute it and/or modify
//    it under the terms of the GNU General Public License as published by
//    the Free Software Foundation, either version 3 of the License, or
//    (at your option) any later version.
//
//    This program is distributed in the hope that it will be useful,
//    but WITHOUT ANY WARRANTY; without even the implied warranty of
//    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//    GNU General Public License for more details.
//
//    You should have received a copy of the GNU General Public License
//    along with this program.  If not, see http://www.gnu.org/licenses/gpl-3.0.html.
// --------------------------------------------------------------------------- 
// V 0.15 - Last modified 4/27/2010
//  +Registration Method
//  	-Custome and Built-in fields validation
//  	-Extendable: add as many fields and validation as required
//  	-Built-in Redundancy check for email and username
//  	-Built-in account activation by email
//  +Update Method to update anyfield on database
//  	-Built-in Redundancy check for email
//  	-Custome and Built-in fields validation
//  +Automatic user session handler
//  	-Remember user with cookies
//  	-Handles sessions on new object
//  +Class wide console
//  	-track and log Errors
//  	-Report every steps for each method
//  	-log validations, connection, SQL queries etc...
// ---------------------------------------------------------------------------

/*Thought the Class Official name is userFlex the object is simply named uFlex*/
class uFlex{
	//Constants
	const version = 0.15;	
	const salt = "sd5a4"; //IMPORTANT: Please change this value as it will make this copy unique and secured
	//End of constants\\\\
	var $id;
	var $username;
	var $pass;
	var $signed;
	var $data;
	var $console;
	var $log;
	var $confirm;	
	var $tmp_data;	
	var $validations;

///////////////////////////////////////////////////////////////////////////////////////////////////////
/*
Register A New User
-Takes two parameter the firs been required
	@info = array object (takes an associatve array, 
				the index being the fieldname(column in database) 
				and the value its content(value)
	+optional second parameter
	@activation = boolean(true/false)
		default = false 
Returns activation hash if second parameter @activation is true
Returns true if second parameter @activation is false
Returns false on Error
*/
///////////////////////////////////////////////////////////////////////////////////////////////////////
	function register($info,$activation=false){
		$this->log = "registration";  //Index for Errors and Reports
		
		//Saves Registration Data in Class
		$this->tmp_data = $info;
		
		//Match fields
		foreach($info as $index=>$val){
			if(isset($info[$index.(2)])){
				if($info[$index] != $info[$index.(2)]){
					$this->error("{$index}s did not match");
					return false;
				}else{
					$this->report("{$index}s matched");
				}
			}
		}
		
		//Check for errors
		if($this->has_error()) return false;
		
		//Validate Fields Submited Fields
		$validation = array(
			"username" => array(
								"limit" => "3-15",
								"regEx" => "/^([a-zA-Z0-9_])+$/"
								),
			"password" => array(
								"limit" => "3-15",
								"regEx" => false
								),
			"email" => array(
								"limit" => "4-45",
								"regEx" => "/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/",
								"optional" => true
								)
			);
		//Add Built in Validation to the Array
		$this->addValidation($validation);
		
		//Validate All Fields in the validations array
		foreach($this->validations as $field=>$opt){
			$this->validate($field,$opt['limit'],$opt['regEx']);
		}
		//Check for errors
		if($this->has_error()) return false;
		
	//Built in actions for special fields
		//Hash Password
		if(isset($info['password'])){ 
			$this->hash_pass($info['password']);
			$info['password'] = $this->pass;
		}		
		//Check for Email in database
		if(isset($info['email'])){
			if($this->check_field('email',$info['email'],"This Email is Already in Use")){
				$this->form_error('email');
			}
		}
		//Check for username in database
		if(isset($info['username'])){
			if($this->check_field('username',$info['username'],"This Username is not available")){
				$this->form_error('username');
			}
		}
		
		//Check for errors
		if($this->has_error()) return false;
		
		//Updates $Info, add defaults, and clean left overs
		$info['password'] = $this->pass;
		$info['confirmation'] = $this->confirm;
		$info['reg_date'] = time();
		
		//Generates the Confirmation Code
		$this->uConfirm();
		
		//Activates user upon registration if there is not an activation method
		if(!$activation){
			$info['activated'] = 1;
		}
		
		//Prepare Info for SQL Insertion
		foreach($info as $index => $val){
			if(!preg_match("/2$/",$index)){ //Skips double fields
				$into[] = $index;
				$values[] = "'" . mysql_real_escape_string($val) . "'";
			}
		}		
		
		$into = implode(", ",$into);
		$values = implode(",",$values);
		
		//Prepare New User	Query
		$sql = "INSERT INTO users ($into) 
					VALUES($values)";
		//exit($sql);
		
		//Enter New user to Database
		if($this->check_sql($sql,true) ){
			$this->report("New User \"{$info['username']}\" has been registered");
			if($activation) return "{$this->confirm}:{$this->tmp_data['username']}";
			return true;
		}else{
			$this->error("New User Registration Failed");
			return false;
		}
	}

///////////////////////////////////////////////////////////////////////////////////////////////////////
/*
Similar to the register method function in structure
This Method validates and updates any field in the database
-Takes one parameter
	@info = array object (takes an associatve array, 
				the index being the fieldname(column in database) 
				and the value its content(value)
On Success returns true
On Failure return false	
*/
///////////////////////////////////////////////////////////////////////////////////////////////////////
	function update($info){
		$this->log = "update";  //Index for Errors and Reports
		
		//Saves Updates Data in Class
		$this->tmp_data = $info;
		
		//Check if there have being Changes
		foreach($info as $index=>$val){
			if($this->data[$index] == $val){
				$this->error("{$index} is the same. no changes were made");
				return false;
			}elseif(isset($info[$index.(2)])){
				//Check for equal fields
				if($info[$index] != $info[$index.(2)]){
					$this->error("{$index}s did not match");
					return false;
				}else{
					$this->report("{$index}s match");
				}
			}
		}
		
		//Defaults or Built in Validations
		$validation = array(
			"username" => array(
								"limit" => "3-15",
								"regEx" => "/^([a-zA-Z0-9_])+$/"
								),
			"password" => array(
								"limit" => "3-15",
								"regEx" => false
								),
			"email" => array(
								"limit" => "4-45",
								"regEx" => "/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/",
								"optional" => true
								)
			);
		//Add Built in Validation to the quene
		$this->addValidation($validation);
		
		//Validate All Fields in the info array with the validation array
		foreach($this->validations as $field=>$opt){
			if(isset($info[$field])){
				$this->validate($field,$opt['limit'],$opt['regEx']);
			}
		}
		//Check for errors
		if($this->has_error()) return false;
		
	//Built in actions for special fields
		//Hash Password
		if(isset($info['password'])){ 
			$this->hash_pass($info['password']);
			$info['password'] = $this->pass;
		}		
		//Check for Email in database
		if(isset($info['email'])){
			if($this->check_field('email',$info['email'],"This Email is Already in Use")){
				$this->form_error('email');
			}
		}
		
		//Check for errors
		if($this->has_error()) return false;
				
		//Prepare Info for SQL Insertion
		foreach($info as $index => $val){
			if(!preg_match("/2$/",$index)){ //Skips double fields
				$value = "'".mysql_real_escape_string($val)."'";
				$set[] = "{$index}={$value}";
			}			
		}		
		
		$set = implode(", ",$set);
		
		//Prepare User Update	Query
		$sql = "UPDATE users SET $set 
					WHERE user_id='{$this->id}'";		
		//exit($sql);
		
		//Check for Changes
		if($this->check_sql_change($sql,true) ){
			$this->report("Information Updated");
			$_SESSION['updated'] = true;
			return true;
		}else{
			$this->error("The Changes Could not be made");
			return false;
		}
	}

///////////////////////////////////////////////////////////////////////////////////////////////////////
/*
Adds validation to quene list for either the Registration or Update Method
Single Entry:
	Requires the first two parameters
		@name = string (name of the field to be validated)
		@limit    = string (range in the format of "5-10")
			*to make a field optional start with 0 (Ex. "0-10")
	Optional third paramenter
		@regEx = string (Regular Expresion to test the field)
Multiple Entry:
	Takes only the first argument
		@name = Array Object (takes an object in the following format:
			array(
				"username" => array(
									"limit" => "3-15",
									"regEx" => "/^([a-zA-Z0-9_])+$/"
									),
				"password" => array(
									"limit" => "3-15",
									"regEx" => false
									)
				);
*/
///////////////////////////////////////////////////////////////////////////////////////////////////////
	function addValidation($name,$limit=false,$regEx=false){
		$this->log = "registration";
		if(is_array($name)){
			if(!is_array($this->validations)) $this->validations = array(); //If is not an array yet, make it one
			$new = array_merge($this->validations,$name);
			$this->validations = $new;
			$this->report("New Validation Object added");
		}else{
			$this->validations[$name]['limit'] = $limit;
			$this->validations[$name]['regEx'] = $regEx;
			$this->report("The $name field has been added for validation");
		}
	}
	
///////////////////////////////////////////////////////////////////////////////////////////////////////	
/*
Activates Account with hash
Takes Only and Only the URL c parameter of the comfirmation page
	@hash = string
Returns true on account activation and false on failure
*/
///////////////////////////////////////////////////////////////////////////////////////////////////////
	function activate($hash){
		$d = explode(":",$hash);
		$this->confirm = $d[0];
		$this->username = $d[1];		
		$sql = "UPDATE users SET activated=1 WHERE confirmation='{$d[0]}' AND username='{$d[1]}'";
		
		if($this->check_sql_change($sql,true)){
			$this->report("Account has been Activated");
			return true;
		}else{
			$this->error("Account could not be activated");
			return false;
		}
	}

///////////////////////////////////////////////////////////////////////////////////////////////////////
/*
Method to reset password, sents an email with a confirmation code to reset password
-Takes one parameter and is required
	@email = string(user email to reset password)
On Success it returns a hash which could then be use to construct the confimration URL
On Failure it returns false
*/
///////////////////////////////////////////////////////////////////////////////////////////////////////
	function pass_reset($email){
		$this->log = "pass_reset";
		$this->uConfirm();
		$sql = "SELECT username,user_id FROM users WHERE email='{$email}'";
		$this->
		$query = mysql_query($sql);
		if(!$query){
			$this->error(mysql_error()); 
			return false;
		}
		$row = mysql_fetch_assoc($query);
		if(count($row) > 1){
			//Send Email
			$code = $this->confirm.":{$row['user_id']}";
			return $code;
		}else{
			$this->error("We don't have an account with this email");
			return false;
		}
	}

///////////////////////////////////////////////////////////////////////////////////////////////////////
/*
Reset a Password with a Confirmation hash from the pass_reset method	
-Takes two parameters
	@hash = string (pass_reset method hash)
	@new  = string (New password)
					*Make sure to validate and comfirm new password in javascript for now
Returns true on a successfull password change
Returns false on error
*/
///////////////////////////////////////////////////////////////////////////////////////////////////////
	function new_pass($hash,$new){
		$d = explode(":",$hash);
		$this->confirm = $d[0];
		$this->id = $d[1];
		$pass = $this->hash_pass($new);
		$sql = "UPDATE users SET password='{$pass}' WHERE confirmation='{$d[0]}' AND user_id='{$d[1]}'";
		
		if($this->check_sql($sql,true)){
			$this->report("Your Password has been Changed");
			return true;
		}else{
			$this->error("Password could not be changed");
			return false;
		}
	}

 /*////////////////////////////////////////////\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\*\
////////Private and Secondary Methods below this line\\\\\\\\\\\\\
 \*\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\/////////////////////////////////////////////*/
/*Star up function*/
	function uFlex($user=false,$pass=false,$auto=false){
		$this->log = "login";  //Index for Reports and Errors;
		session_start();	
		//$this->username = $user;
		//$this->pass = $pass;
		
		$result = $this->login($user,$pass,$auto);
		if($result == false){
			$this->id = 0;
			$this->username = "Guess";
			$this->pass = "";
			$this->signed = false;
			$_SESSION = array("username" => "Guess",
							  "user_id" => 0,
							  "signed" => false
							  );
			$this->report("User is Guess");
		}else{
			
		}
		return true;
	}
	
	private function login($user=false,$pass=false,$auto=false){
		$this->log = "login";  //Index for Reports and Errors;
		//Session Login
		if(@$_SESSION['signed']){
			$this->report("User Is signed in from session");
			$this->update_from_session();
			if(isset($this->data['updated'])){
				//Get User From database because its info has change during current session
				$update = mysql_fetch_assoc(mysql_query("SELECT * FROM users WHERE user_id='{$this->id}'"));
				$this->update_session($update);
				unset($_SESSION['updated']);
				$this->report("Session Updated From Database");
			}
			return true;
		}		
		if(isset($_COOKIE['auto'])){
		//Cookies Login	
			$c = $_COOKIE['auto'];
			$c = explode(":",$c);  //passHash:id => asd5453asf54a:52
			//if($c[1] != 1){ return false; $this->report("No auto Login"); } //No AutoLogin set return false
			$this->id = $c[1];
			$this->pass = $c[0];
			$auto = true;
			$this->report("Attemping Login with cookies");
		}else{
		//Credetials Login
			if(!$user == false && !$pass == false){
				$this->username = $user;
				$this->hash_pass($pass);
				$this->report("Creadentials recieved");
			}else{
				$this->error("No Username or Password provided");
				return false;
			}
		}

		$this->report("I got info Quering Database to autenticate user");
		//Query Database and check login
		$query = mysql_query("SELECT * FROM users WHERE user_id='{$this->id}' OR username='{$this->username}' AND password='{$this->pass}'");
		if(mysql_num_rows($query) == 1){
			$this->data = mysql_fetch_assoc($query);
			$d = $this->data;
			//If Account is not Activated
			if($d['activated'] != 1){
				if($d['last_login'] == 0){
					//Account has not been activated
					$this->error("Your Account has not been Activated. Check your Email for instructions");
				}else{
					//Account has been deactivated
					$this->error("Your account has been deactivated. Please contact Administrator");
				}
				return false;
			}
			//Account is Activated and user is logged in
			$this->update_session($d);
			
			//If auto Remember User
			if($auto){
				$this->setCookie();
			}
			//Update last_login
			$time = time();
			$sql = "UPDATE users SET last_login='{$time}' WHERE user_id='{$this->id}'";
			$this->check_sql($sql,true);
			//Done
			$this->report("User Logged in Successfully");
			return true;
		}else{
			$this->error("Wrong Username or Password");
			return false;
		}
	}
	
	function logout(){
		$this->log = "login";
		setcookie("auto", "", time()-3600,"/",".".$_SERVER['HTTP_HOST']); //Deletes the Auto Coookie
		session_unset();
		$this->report("User Logged out");
	}
	
	private function setCookie(){
		$value = $this->pass;
		$value .= ":";
		$value .= $this->id;
		setcookie("auto",$value,strtotime("+15 days"),"/",".".$_SERVER['HTTP_HOST']);
		$this->report("Cookies have been updated for auto login");
	}
	
	private function update_session($d){
		$_SESSION = $d;
		$_SESSION['signed'] = true;
		
		$this->id = $d['user_id'];
		$this->username = $d['username'];
		$this->pass = $d['password'];
		$this->signed = true;
		
		$this->report("session updated");
	}
	
	private function update_from_session(){
		$d = $_SESSION;
		
		$this->id = $d['user_id'];
		$this->data = $d;
		$this->username = $d['username'];
		$this->pass = $d['password'];
		$this->signed = true;
	}
	function hash_pass($pass){
		$salt = uFlex::salt;
		$this->pass = md5($salt.$pass.$salt);
		return $this->pass;
	}
	
	function report($str=false){
		$index = $this->log;
		if($str){
			$str = ucfirst($str);
			$this->console['reports'][$index][] = $str; //Strore Report
		}else{
			if($index){
				return $this->console['reports'][$index]; //Return the $index Reports Array
			}else{
				return $this->console['reports']; //Return the Full Reports Array
			}
		}
	}

	function error($str=false){
		$index = $this->log;
		if($str){
			$str = ucfirst($str); //Style String by making first character uppercase
			$this->console['errors'][$index][] = $str; //Strore Error
			$this->report("Error: {$str}"); //Report The error
		}else{
			if($index){
				return $this->console['errors'][$index]; //Return the $index Errors Array
			}else{
				return $this->console['errors']; //Return the Full Error Array
			}
		}
	}
	
	//Adds fields with errors to the console
	function form_error($field=false){
		$index = $this->log;
		if($field){
			$this->console['form'][$index][] = $field;	
		}else{
			if($index){
				return $this->console['form'][$index]; //Return the $index Errors Array
			}else{
				return $this->console['form']; //Return the Full form Array
			}
		}
	}
	
	//Check for errors in the console
	function has_error(){
		//Check for errors
		if($this->console['errors'][$this->log] != ""){
			$count = count($this->console['errors'][$this->log]);
			$this->report("$count Error(s) Found!");
			return true;
		}else{
			$this->report("No Error Found!");
			return false;
		}
	}
	
	//Generates a unique comfirm hash
	function uConfirm($length = false){
		$code = md5(uniqid(rand(), true));
		if($length != false){
			$this->confirm = substr($code, 0, $length);
		}else{
			$this->confirm = $code;
		}
	}
	
	//Test field in database for a value
	function check_field($field, $val, $err=false){
		$query = mysql_query("SELECT {$field} FROM users WHERE {$field}='{$val}' ");
			if(mysql_num_rows($query) >= 1){
				if($err){
					$this->error($err);
				}else{
					$this->error("There was a match for  $field = $val");
				}
				return true;
			}else{
				$this->report("No Match on Field $field=$val");
				return false;
			}
	}
	
	//Executes SQL query and checks for success
	function check_sql($sql,$debug = false){
		$this->report("SQL: {$sql}"); //Log the SQL Query first
		if (!mysql_query($sql)){
			if($debug){
				$this->error(mysql_error());
			}
			return false;
		}else{
			return true;
		}
	}
	
	//Executes SQL query and expects a change in database
	function check_sql_change($sql,$debug = false){
		$this->report("SQL: {$sql}"); //Log the SQL Query
		if (!mysql_query($sql)){
			if($debug){
				$this->error(mysql_error());
				return false; //die('Error: ' . mysql_error());
			}else{
				return false;
			}
		}else{
			$rows = mysql_affected_rows();
			if($rows > 0){
				//Good, Rows where affected
				$this->report("$rows row(s) where Affected");
				return true;
			}else{
				//Bad, No Rows where Affected
				$this->report("No row was Affected");
				return false;
			}
		}
	}
	
	//Validates field($name) in tmp_data
	private function validate($name,$limit,$regEx=false){
		$str = $this->tmp_data[$name];
		$l = explode("-",$limit);
		$min = intval($l[0]);
		$max = intval($l[1]);
		if(!$max || !$min){
			$this->error("Invalid second paramater for the $name validation");
			return false;
		}
		if(!$str){
			if(!isset($this->tmp_data[$name])){
				$this->error("missing index $name from the POST array");
			}
			if(strlen($str) == $min){
				$this->report("$name is blank and optional - skipped");
				return true;
			}
			$this->error("$name is required");
			$this->form_error($name);
			return false;
		}
		if(strlen($str) > $max){
			$this->error("The $name is larger than $max character/digits");
			$this->form_error($name);
			return false;
		}
		if(strlen($str) < $min){
			$this->error("The $name is too short. it should at least be $min character/digits long");
			$this->form_error($name);
			return false;
		}
		if($regEx){
			preg_match_all($regEx,$str,$match);
			//print_r($match);echo count($match[0])."+";
			
			if(count($match[0]) != 1){
				$this->error("The $name \"{$str}\" is not valid");
				$this->form_error($name);
				return false;
			}
		}
		
		$this->report("The $name is Valid");
		return true;
	}
}
	


?>
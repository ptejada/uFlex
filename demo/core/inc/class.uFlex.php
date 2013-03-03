<?php
/*
	Copyright (c) 2012 Pablo Tejada, http://crusthq.com/projects/uFlex/

	Permission is hereby granted, free of charge, to any person obtaining
	a copy of this software and associated documentation files (the
	"Software"), to deal in the Software without restriction, including
	without limitation the rights to use, copy, modify, merge, publish,
	distribute, sublicense, and/or sell copies of the Software, and to
	permit persons to whom the Software is furnished to do so, subject to
	the following conditions:
	
	The above copyright notice and this permission notice shall be
	included in all copies or substantial portions of the Software.
	
	THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
	EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
	MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
	NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
	LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
	OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
	WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/

class uFlex {
	//Constants
	const version = 0.93;
	const salt = "sd5a4"; //IMPORTANT: //IMPORTANT: This constant is deprecated, useless you are upgrading class
	//End of constants\\\\
	/**
	 * PDO / database credentials
	 */
	var $db = array(
		"host" => '',
		"user" => '',
		"pass" => '',
		"name" => '',	//Database name
		"dsn" => ''	//Alterntive PDO DSN string
	);
	var $id;		//Current user ID
	var $sid;		//Current User Session ID
	var $username;	//Signed username
	var $pass;		//Holds the user password hash
	var $signed;	//Boolean, true = user is signed-in
	var $data;		//Holds entire user database row
	var $console;	//Cotainer for errors and reports
	var $log;		//Used for traking errors and reports
	var $confirm;	//Holds the hash for any type of comfirmation
	var $tmp_data;	//Holds the temporary user information during registration and other methods
	var $opt = array( //Array of Internal options
		"table_name" => "users",
		"cookie_time" => "+30 days",
		"cookie_name" => "auto",
		"cookie_path" => "/",
		"cookie_host" => false,
		"user_session" => "userData",
		"default_user" => array(
				"username" => "Guest",
				"user_id" => 0,
				"password" => 0,
				"signed" => false
				)
		);
	var $validations = array( //Array for default field validations
			"username" => array(
					"limit" => "3-15",
					"regEx" => '/^([a-zA-Z0-9_])+$/'
					),
			"password" => array(
					"limit" => "3-15",
					"regEx" => ''
					),
			"email" => array(
					"limit" => "4-45",
					"regEx" => '/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/'
					)
		);
		
	var $encoder = array(
			"a","b","c","d","e","f","g","h","i","j","k","l","m","n","o","p","q","r","s","t","u","v","w","x","y","z",
			"A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z",
			0,2,3,4,5,6,7,8,9
		);
	
	//Array of errors
	var $errorList = array(
				//Database Error while caling register functions
			1   => "New User Registration Failed", 
				//Database Error while calling update functions
			2   => "The Changes Could not be made", 
				//Database Error while calling activate function
			3   => "Account could not be activated", 
				//When calling pass_reset and the given email doesn't exist in database
			4   => "We don't have an account with this email", 
				//When calling new_pass, the confirmation hash did not match the one in database
			5   => "Password could not be changed. The request can't be validated", 
			6   => "Logging with cookies failed",
			7   => "No Username or Password provided",
			8   => "Your Account has not been Activated. Check your Email for instructions",
			9   => "Your account has been deactivated. Please contact Administrator",
			10  => "Wrong Username or Password",
				//When calling check_hash with invalid hash
			11  => "Confirmation hash is invalid", 
				//Calling check_hash hash failed database match test
			12  => "Your identification could not be confirmed", 
				//When saving hash to database fails
			13  => "Failed to save confirmation request", 
			14 	=> "You need to reset your password to login"
		);
		
		
/** EDITS BELOW THIS LINE WILL NOT BE KEPT WHEN UPDATING CLASS USING THE UPDATER SCRIPT **/
	
	/**
	 * Public function to initiate a login request at any time
	 * 
	 * @access public
	 * @param string $user username or email 
	 * @param string $pass password 
	 * @param bool|int $auto boolean to remember or not the user 
	 */
	function login($user=false,$pass=false,$auto=false){
		//reconstruct object
		self::__construct($user,$pass,$auto);
	}

	/**
	 * Register A New User
	 * 
	 * Takes two parameters, the first being required
	 * 
	 * @param array $info An associatve array, 
					the index being the fieldname(column in database) 
					and the value its content(value)
	 * @param bool $activation Default is false, if true the user will need required further steps to activate account 
	 * 				Otherwise the account will be activated if registration succeeds
	 * 
	 * @return array|bool Returns activation hash if second parameter $activation is true 
	 * 						Returns true if second parameter $activation is false 
	 * 						Returns false on Error
	 */

	function register($info,$activation = false){
		$this->logger("registration"); //Index for Errors and Reports

		//Saves Registration Data in Class
		$this->tmp_data = $info;

		//Validate All Fields
		if(!$this->validateAll()) return false; //There are validations error

		//Set Registration Date
		$info['reg_date'] = $this->tmp_data['reg_date'] = time();
		
		//Built in actions for special fields		
		//Hash Password
		if(isset($info['password'])){
			$this->hash_pass($info['password']);
			$info['password'] = $this->pass;
		}
		//Check for Email in database
		if(isset($info['email']))
			if($this->check_field('email',$info['email'],"This Email is Already in Use"))
				return false;

		//Check for username in database
		if(isset($info['username']))
			if($this->check_field('username',$info['username'], "This Username is not available"))
				return false;

		//Check for errors
		if($this->has_error()) return false;

		//User Activation
		if(!$activation){ //Activates user upon registration
			$info['activated'] = 1;
		}

		//Prepare Info for SQL Insertion
		foreach($info as $index => $val){
			if(!preg_match("/2$/",$index)){ //Skips double fields
				$into[] = $index;
				//For the statement
				$data[$index] = $val;
			}
		}
		
		$intoStr = implode(", ",$into);
		$values = ":" . implode(", :",$into);

		//Prepare New User	Query
		$sql = "INSERT INTO :table ({$intoStr})
				VALUES({$values})";

		//Enter New user to Database
		if($this->check_sql($sql, $data)){
			$this->report("New User has been registered");
			$this->id = $this->db->lastInsertId();
			if($activation){
				//Insert Validation Hash
				$this->make_hash($this->id);
				$this->save_hash();
				return $this->confirm;
			}
			return true;
		}else{
			$this->error(1);
			return false;
		}
	}

	/**
	 * Validates and updates any field in the database for the current user
	 * 
	 * Similar to the register method function in structure, 
	 * this Method validates and updates any field in the database
	 * 
	 * @param array $info An associatve array, 
					the index being the fieldname(column in database) 
					and the value its content(value)
	 * @return bool Returns true on success anf false on errror
	 */
	function update($info){
		$this->logger("update"); //Index for Errors and Reports

		//Saves Updates Data in Class
		$this->tmp_data = $info;

		//Validate All Fields
		if(!$this->validateAll())
			return false; //There are validations error

		//Built in actions for special fields
		//Hash Password
		if(isset($info['password'])){
			$info['password'] = $this->hash_pass($info['password']);
		}
		//Check for Email in database
		if(isset($info['email']))
			if($this->check_field('email',$info['email'],"This Email is Already in Use"))
				return false;

		//Check for errors
		if($this->has_error()) return false;

		//Prepare Info for SQL Insertion
		foreach($info as $index => $val){
			if(!preg_match("/2$/",$index)){ //Skips double fields
				$set[] = "{$index}=:{$index}";
				//For the statement
				$data[$index] = $val;
			}
		}
		
		$set = implode(", ",$set);

		//Prepare User Update	Query
		$sql = "UPDATE :table SET $set 
				WHERE user_id={$this->id}";		
		
		//Check for Changes
		if($this->check_sql($sql, $data)){
			$this->report("Information Updated");
			$_SESSION['uFlex']['update'] = true;
			return true;
		}else{
			$this->error(2);
			return false;
		}
	}

	/**
	 * Adds validation to queue for either the Registration or Update Method
	 * 
	 * Single Entry:
	 * 	Requires the first two parameters
	 * 		$name  = string (name of the field to be validated)
	 * 		$limit = string (range of the accepted value length in the format of "5-10")
	 * 			- to make a field optional start with 0 (Ex. "0-10")
	 * 
	 * 	Optional third paramenter
	 * 		$regEx = string (Regular Expresion to test the field)
	 * _____________________________________________________________________________________________________
	 * 
	 * Multiple Entry:
	 * 	Takes only the first argument
	 * 		$name = Array Object (takes an object in the following format:
	 * 			array(
	 * 				"username" => array(
	 * 						"limit" => "3-15",
	 * 						"regEx" => "/^([a-zA-Z0-9_])+$/"
	 * 						),
	 * 				"password" => array(
	 * 						"limit" => "3-15",
	 * 						"regEx" => false
	 * 						)
	 * 				);
	 * 
	 * @param string|array $name Name of the field to validate or an array of all the fields and their validations
	 * @param string $limit A range of the accepted value length in the format of "5-10",
	 * 						to make a field optional start with 0 (Ex. "0-10")
	 * @return null
	 */
	function addValidation($name,$limit = "0-1",$regEx = false){
		$this->logger("registration");
		if(is_array($name)){
			if(!is_array($this->validations))
				$this->validations = array(); //If is not an array yet, make it one
			$new = array_merge($this->validations,$name);
			$this->validations = $new;
			$this->report("New Validation Object added");
		}else{
			$this->validations[$name]['limit'] = $limit;
			$this->validations[$name]['regEx'] = $regEx;
			$this->report("The $name field has been added for validation");
		}
	}

	/**
	 * Activates Account with hash
	 * 
	 * Takes Only and Only the URL parameter of a confirmation page 
	 * which would be the hash returned by the register method
	 * 
	 * @param string $hash Hash returned in the register method
	 * @return bool Returns true account activation and false on failure
	 */
	function activate($hash){
		$this->logger("activation");

		if(!$this->check_hash($hash)) return false;

		$sql = "UPDATE :table SET activated=1, confirmation='' WHERE user_id=:id AND confirmation=:hash";
		$data = Array(
			"hash"	=> $hash,
			"id"	=> $this->id
		);
		if($this->check_sql($sql, $data)){
			$this->report("Account has been Activated");
			return true;
		}else{
			$this->error(3);
			return false;
		}
	}

	/**
	 * Method to reset password, Returns confirmation code to reset password
	 * 
	 * @param string $email User email to reset password
	 * @return array|false On Success it returns an array(email,username,user_id,hash) 
	 * 						which could then be use to construct the confirmation URL and Email.
	 * 						On Failure it returns false
	 */
	function pass_reset($email){
		$this->logger("pass_reset");

		$user = $this->getRow(Array("email" => $email));

		if($user){
			if(!$user['activated'] and !$user['confirmation']){
				//The Account has been manually disabled and can't reset password
				$this->error(9);
				return false;
			}
			
			$this->make_hash($user['user_id']);
			$this->id = $user['user_id'];
			$this->save_hash(true);

			$data = array(
				"email" => $email, 
				"username" => $user['username'],
				"user_id" => $user['user_id'],
				"hash" => $this->confirm
			);
			return $data;
		}else{
			$this->error(4);
			return false;
		}
	}

	/**
	 * Changes a Password with a Confirmation hash from the pass_reset method
	 * 
	 * This is for users that forget their passwords to change the signed in user password use ->update()
	 * 
	 * @param string pass_reset method hash
	 * @param array $new An array with indexes 'password' and 'password2'
	 * 					Example:
	 * 					array(
	 * 						[password] => pass123
	 * 						[password2] => pass123
	 * 					)
	 * @return bool Returns true on a successful password change. 
	 * 				Returns false on error
	 */
	function new_pass($hash,$newPass){
		$this->logger("new_pass");

		if(!$this->check_hash($hash)) return false;
		
		$this->tmp_data = $newPass;
		if(!$this->validateAll()) return false; //There are validations error

		$pass = $this->hash_pass($newPass['password']);

		$sql = "UPDATE :table SET password=:pass, confirmation='', activated=1 WHERE confirmation=:hash AND user_id=:id";
		$data = Array(
			"id"	=> $this->id,
			"pass" 	=> $pass,
			"hash" 	=> $hash
		);
		if($this->check_sql($sql, $data)){
			$this->report("Password has been changed");
			return true;
		}else{
			//Error
			$this->error(5);
			return false;
		}
	}

	/**
	 * Public function to start a delayed constructor
	 * 
	 * When you initialize the class like `new uFlex(false)` the object construction will be halted until this method is called.
	 */
	 function start(){
	 	$this->__construct();
	 }
	
 /*////////////////////////////////////////////\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\*\
////////Protected and Secondary Methods below this line\\\\\\\\\\\\\
 \*\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\/////////////////////////////////////////////*/
	/**
	 * Object constructor
	 */
	function __construct($name='', $pass=false, $auto=false){
		if($name === false) return;
		
		$this->logger("login"); //Index for Reports and Errors;
		
		if(!isset($_SESSION) and !headers_sent()){
			session_start();
			$this->report("Session is been started...");
		}elseif(isset($_SESSION)){
			$this->report("Session has already been started");
		}else{
			$this->error("Session could not be started");
			return; //Finish Execution
		}
		
		$this->sid = session_id();

		$result = $this->loginUser($name, $pass, $auto);
		
		if(!$result){
			$this->session($this->opt['default_user']);
			$this->update_from_session();
			$this->report("User is {$this->username}");
		}else{
			if(!$auto and isset($_SESSION['uFlex']['remember'])){
				unset($_SESSION['uFlex']['remember']);
				$this->setCookie();
			}
		}
	}
	
	/**
	 * Protected Login proccesor function
	 * 
	 * @ignore
	 */
	protected function loginUser($user = false,$pass = false,$auto = false){
		//Session Login
		if($this->session("signed")){
			$this->report("User Is signed in from session");
			$this->update_from_session();
			if(isset($_SESSION['uFlex']['update'])){
				$this->report("Updating Session from database");
				//Get User From database because its info has change during current session
				$update = $this->getRow(Array("user_id" => "$this->id"));
				$this->update_session($update);
				$this->log_login(); //Update last_login
			}
			return true;
		}
		//Cookies Login
		if(isset($_COOKIE[$this->opt['cookie_name']]) and !$user and !$pass){
			$c = $_COOKIE[$this->opt['cookie_name']];
			$this->report("Attemping Login with cookies");
			if($this->check_hash($c,true)){
				$auto = true;
				$getBy = "user_id";
				$user = $this->id;
				$this->signed = true;
			}else{
				$this->error(6);
				$this->logout();
				return false;
			}
		}else{
		//Credentials Login
			if($user && $pass){
				if(preg_match($this->validations['email']['regEx'],$user)){
					//Login using email
					$getBy = "email";
				}else{
					//Login using username
					$getBy = "username";
				}
				
				$this->report("Credentials received");
			}else{
				$this->error(7);
				return false;
			}
		}

		$this->report("Querying Database to authenticate user");
		//Query Database for user
		$userFile = $this->getRow(Array($getBy => $user));
		
		if($userFile and !$this->signed){
			$this->tmp_data = $userFile;
			$this->hash_pass($pass);
			$this->signed = $this->pass == $userFile["password"] ? true : false;
			
			//Try legacy hash
			if(!$this->signed){
				$this->legacy_hash_pass($pass);
				$this->signed = $this->pass == $userFile["password"] ? true : false;
				
				//Update password hash in database
				if($this->signed){
					$this->data = $userFile;
					$this->id = $userFile['user_id'];
					$this->update(Array("password" => $pass));
					$this->log = "login";
				}
			}
		}else if($this->signed){
			//Continue login from cookie
		}else{
			$this->error(10);
			return false;
		}
		
		if($this->signed){
			//If Account is not Activated
			if($userFile['activated'] == 0){
				if($userFile['last_login'] == 0){
					//Account has not been activated
					$this->error(8);
				}else if(!$userFile['confirmation']){
					//Account has been deactivated
					$this->error(9);
				}else{
					//Account deativated due to a password reset or reactivation request
					$this->error(14);
				}
				return false;
			}
			
			//Account is Activated and user is logged in
			$this->update_session($userFile);

			//If auto Remember User
			if($auto){
				$this->setCookie();
			}
			
			//Update last_login
			$this->log_login();
			
			//Done
			$this->report("User Logged in Successfully");
			return true;
		}else{
			if(isset($_COOKIE[$this->opt['cookie_name']])){
				$this->logout();
			}
			$this->error(10);
			return false;
		}
	}
	
	/**
	 * Logout the user
	 * 
	 * Logs out the current user and deletes any autologin cookies
	 * 
	 * @return void
	 */
	function logout(){
		$this->logger("logout");
		
		if(!$this->opt['cookie_host'])
			$this->opt['cookie_host'] = $_SERVER['HTTP_HOST'];
		
		$deleted = setcookie($this->opt['cookie_name'],"",time() - 3600,
			$this->opt['cookie_path'],$this->opt['cookie_host']); //Deletes the Auto Coookie
			
		$this->signed = 0;
		//Import default user object
		$_SESSION[$this->opt['user_session']] = $this->data = $this->opt['default_user'];
		
		if(!$deleted && !headers_sent()){
			$this->report("The Autologin cookie could not be deleted");
		}
		$this->report("User Logged out");
	}
	
	/**
	 * Logs user last login in database
	 * @ignore
	 */
	protected function log_login(){
		//Update last_login
		$time = time();
		$sql = "UPDATE :table SET last_login=:time WHERE user_id=:id";
		if($this->check_sql($sql, Array("time" => $time, "id" => $this->id)))
			$this->report("Last Login updated");
	}
	
	/**
	 * Set the autologin cookie for the current user
	 */
	function setCookie(){
		if($this->pass and $this->id){

			$code = $this->make_hash($this->id,$this->pass);
			
			if(!$this->opt['cookie_host'])
				$this->opt['cookie_host'] = $_SERVER['HTTP_HOST'];
			
			if(!headers_sent()){
				//echo "PHP";
				setcookie($this->opt['cookie_name'],$code,strtotime($this->opt['cookie_time']),
					$this->opt['cookie_path'],$this->opt['cookie_host']);
			}else{
				//Headers have been sent use JavaScript to set cookie
				$time = intval($this->opt['cookie_time']);
				echo "<script>";
				echo '
				  function setCookie(c_name,value,expiredays){
					var exdate=new Date();
					exdate.setDate(exdate.getDate()+expiredays);
					document.cookie=c_name+ "=" +escape(value)+((expiredays==null) ? "" : "; expires="+exdate.toUTCString()); path=escape("'.
							$this->opt["cookie_path"].'");
				  }
				';
				echo "setCookie('{$this->opt['cookie_name']}','{$code}',{$time})";
				echo "</script>";
			}

			$this->report("Cookies have been updated for auto login");
		}else{
			$this->error("Info required to set the cookie {$this->opt['cookie_name']} is not available");
		}
	}

	/**
	 * Manage the session uFlex object variables
	 * 
	 * This method has many functions:
	 * session([String]) => Get the value of the index
	 * session([String], [String|mixed]) => Set the specifeid index with the given value
	 * session([Array]) => Overwrite the whole uFlex session space with a given array
	 * 
	 * @param string|array $index Session index to get or set
	 * @param mixed[] $val The value to the set the $index with
	 */
	function session($index=false, $val=false){
		//Get uFlex session index value
		if(is_string($index) and !$val){
			return @$_SESSION[$this->opt['user_session']][$index];
		}
		
		//Set the value for a uFlex index
		if(is_string($index) and $val){
			$_SESSION[$this->opt['user_session']][$index] = $val;
			return;
		}
		
		//Overwrite the whole uFlex session space with a given array
		if(is_array($index) and !$val){
			$_SESSION[$this->opt['user_session']] = $index;
			return;	
		}

		//return full session user data
		return $_SESSION[$this->opt['user_session']];
	}
	
	/**
	 * Updates the session and the object with the provided array
	 * @ignore
	 */
	protected function update_session($d){
		unset($_SESSION['uFlex']['update']);

		$this->session($d);
		$this->session("signed",1);

		$this->report("Session updated");
		$this->update_from_session();
	}
	
	/**
	 * Update the object with the PHP session information
	 */
	protected function update_from_session(){
		$d = $this->session();

		$this->id = $d['user_id'];
		$this->data = $d;
		$this->username = $d['username'];
		$this->pass = $d['password'];
		$this->signed = $d['signed'];

		$this->report("Session has been imported to the object");
	}

	/**
	 * The legacy password hasher for backwrads compatibility 
	 */
	function legacy_hash_pass($pass){
		$salt = uFlex::salt;
		$this->pass = md5($salt.$pass.$salt);
		return $this->pass;
	}
	
	/**
	 * The password hasher
	 * 
	 * Hashes a clear text password for the current user
	 * 
	 * @ignore
	 */
	protected function hash_pass($pass){
		
		$regdate = false;
		
		if(isset($this->data['reg_date']))
			$regdate = $this->data['reg_date'];
		
		if(!$regdate and isset($this->tmp_data['reg_date']))
			$regdate = $this->tmp_data['reg_date'];
		
		if(!$regdate){
			return $this->legacy_hash_pass($pass);
		}
		
		$pre = $this->encode($regdate);
		$pos = substr($regdate, 5, 1);
		$post = $this->encode($regdate * (substr($regdate, $pos, 1)));
		$this->pass = md5($pre.$pass.$post);
		return $this->pass;
	}
	
	/**
	 * Log the type of request being initialized and log with the error amd report methods
	 * 
	 * @param string $log Request name
	 * @return object Self $this object
	 */
	function logger($log){
		$this->log = $log;
		unset($this->console['errors'][$log]);
		unset($this->console['form'][$log]);
		$this->report(">>Startting new $log request");
		return $this;
	}
	
	/**
	 * Add a report log entry
	 * 
	 * @param string|false $str Text information, default is false
	 * @return true|array If $str is false returns the arary of reports in the current logger
	 */
	function report($str = false){
		$index = $this->log;
		if($str){
			if(is_string($str))
				$str = ucfirst($str);
			
			$this->console['reports'][$index][] = $str; //Strore Report
			return true;
		}else{
			if($index){
				return $this->console['reports'][$index]; //Return the $index Reports Array
			}else{
				return $this->console['reports']; //Return the Full Reports Array
			}
		}
	}

	/**
	 * Add an error log entry
	 * 
	 * @param string|false $str Text information, default is false
	 * @return true|array If $str is false returns the arary of errors in the current logger
	 */
	function error($str = false){
		$index = $this->log;
		if($str){
			$err = is_int($str) ? $this->errorList[$str] : $str;
			$this->console['errors'][$index][] = $err; //Store Error
			if(is_int($str)){
				$this->report("Error[{$str}]: {$err}"); //Report The error
			}else{
				$this->report("Error: {$str}"); //Report The error				
			}
		}else{
			if($index){
				if(!isset($this->console['errors'][$index]))
					return false;
				return $this->console['errors'][$index]; //Return the $index Errors Array
			}else{
				return $this->console['errors']; //Return the Full Error Array
			}
		}
	}

	/**
	 * Add a form field error log entry
	 * 
	 * @param string|false $field Field name, default is false
	 * @param string|false $error Text information, default is false
	 * @return true|array If $field and $error is false returns the arary of form field errror in the current logger
	 */
	function form_error($field = false,$error = false){
		$index = $this->log;
		if($field){
			if($error){
				$this->console['form'][$index][$field] = $error;
				$this->error($error);
			}else{
				$this->console['form'][$index][] = $field;
			}
		}else{
			if($index){
				if(!isset($this->console['form'][$index]))
					return false;
				return $this->console['form'][$index]; //Return the $index Errors Array
			}else{
				return $this->console['form']; //Return the Full form Array
			}
		}
	}

	/**
	 * Check if there is an error in the current logger
	 * 
	 * @return bool
	 */
	function has_error($index = false){
		//Check for errors
		$index = $index?$index:$this->log;
		$count = @count($this->console['errors'][$index]);
		if($count){
			$this->report("$count Error(s) Found!");
			return true;
		}else{
			$this->report("No Error Found!");
			return false;
		}
	}

	/**
	 * Generates a unique comfirm hash
	 */
	function make_hash($uid,$hash = false){
		$e_uid = $this->encode($uid);
		$e_uid_length = strlen($e_uid);
		$e_uid_length = str_pad($e_uid_length,2,0,STR_PAD_LEFT);
		$e_uid_pos = rand(10,32 - $e_uid_length - 1);

		if(!$hash){
			$hash = md5(uniqid(rand(),true));
		}
		//$code = substr($code, 0, $length);
		$code = $e_uid_pos.$e_uid_length;
		$code .= substr($hash,0,$e_uid_pos - strlen($code));
		$code .= $e_uid;
		$code .= substr($hash,strlen($code));

		$this->confirm = $code;
		return $code;
	}
	
	/**
	 * Checks and validates a confirmation hash
	 */
	function check_hash($hash,$bypass = false){
		if(strlen($hash) != 32 || !preg_match("/^[0-9]{4}/",$hash)){
			$this->error(11);
			return;
		}

		$e_uid_pos = substr($hash,0,2);
		$e_uid_length = substr($hash,2,2);
		$e_uid = substr($hash,$e_uid_pos,$e_uid_length);

		$uid = $this->decode($e_uid);

		$args = Array(
			"user_id" => $uid
		);
		
		//return false;
		$user = $this->getRow($args);

		//Bypass hash confirmation and get the user by partially matching its password
		if($bypass){
			preg_match("/^([0-9]{4})(.{2,".($e_uid_pos - 4)."})(".$e_uid.")/",$hash,$exerpt);
			$pass = $exerpt[2];
			
			if(strpos($user['password'], $pass) === false){
				$this->error(12);
				return false;
			}
		}else if($user['confirmation'] != $hash){
			$this->report("The user ID and the confirmation hash did not match");
			$this->error(12);
			return false;
		}
		
		if($this->signed and $this->id == $user['user_id']){
			$this->logout(); //FLAGGED
		}

		//Hash is valid import user's info to object
		$this->data = $user;
		$this->id = $user['user_id'];
		$this->username = $user['username'];
		$this->pass = $user['password'];

		$this->report("Hash successfully validated");
		return true;
	}

	/**
	 * Saves the confirmation hash in the database
	 */
	function save_hash($activated = false){
		if($this->confirm and $this->id){
			$sql = "UPDATE :table SET confirmation=:hash, activated=".(($activated)?'activated':0)." WHERE user_id=:id";
			$data = Array(
				"id"	=> $this->id,
				"hash"	=> $this->confirm
			);
			
			if(!$this->check_sql($sql, $data)){
				$this->error(13);
				return false;
			}else{
				$this->report("Confirmation hash has been saved");
			}
		}else{
			$this->report("Can't save Confirmation hash");
			return false;
		}
		return true;
	}
	
	/**
	 * Connects to the database
	 */
	function connect(){
		if(is_object($this->db)) return true;
		
		/* Connect to an ODBC database using driver invocation */
		$user = $this->db['user'];
		$pass = $this->db['pass'];
		$host = $this->db['host'];
		$name = $this->db['name'];
		$dsn = $this->db['dsn'];
		
		if(!$dsn){
			$dsn = "mysql:dbname={$name};host={$host}";
		}
		
		$this->report("Connecting to database...");
		
		try{
			$this->db = new PDO($dsn, $user, $pass);
			$this->report("Connected to database.");
		}catch(PDOException $e){
			$this->error("Failed to connect to database, [SQLSTATE] " . $e->getCode());
		}
		
		if(is_object($this->db)) return true;
		return false;
	}
	
	/**
	 * Test field in database for a value
	 */
	function check_field($field,$val,$err = false){
		$res = $this->getRow(Array($field => $val));
		
		if($res){
			if($err){
				$this->form_error($field,$err);
			}else{
				$this->form_error($field,"The $field $val exists in database");
			}
			$this->report("There was a match for $field = $val");
			return true;
		}else{
			$this->report("No Match for $field = $val");
			return false;
		}
	}

	/**
	 * Executes SQL query and checks for success
	 */
	function check_sql($sql, $args=false){
		$st = $this->getStatement($sql);
		
		if(!$st) return false;
		
		if($args){
			$st->execute($args);
			$this->report("SQL Data Sent: [" . implode(', ',$args) . "]"); //Log the SQL Query first		
		}else{
			$st->execute();
		}
		
		$rows = $st->rowCount();
		
		if($rows > 0){
			//Good, Rows where affected
			$this->report("$rows row(s) where Affected");
			return true;
		}else{
			//Bad, No Rows where Affected
			$this->report("No rows were Affected");
			return false;
		}
	}

	/**
	 * Get a single user row depending on arguments
	 */
	function getRow($args){
		$sql = "SELECT * FROM :table WHERE :args LIMIT 1";
		
		$st = $this->getStatement($sql, $args);
		
		if(!$st) return false;
		
		if(!$st->rowCount()){
			$this->report("Query returned empty");
			return false;
		}
		
		return $st->fetch(PDO::FETCH_ASSOC);
	}
	
	/*
	 * Get a PDO statment
	 */
	function getStatement($sql, $args=false){
		if(!$this->connect()) return false;
		
		if($args){
			foreach($args as $field => $val){
				$finalArgs[] = " {$field}=:{$field}";
			}
			
			$finalArgs = implode(" AND", $finalArgs);
			
			if(strpos($sql, " :args")){
				$sql = str_replace(" :args", $finalArgs, $sql);
			}else{
				$sql .= $finalArgs;
			}
		}
		
		//Replace the :table placeholder
		$sql = str_replace(" :table ", " {$this->opt["table_name"]} ", $sql);
		
		$this->report("SQL Statement: {$sql}"); //Log the SQL Query first
		
		if($args) $this->report("SQL Data Sent: [" . implode(', ',$args) . "]"); //Log the SQL Query first		
		
		//Prepare the statement
		$res = $this->db->prepare($sql);
		
		if($args) $res->execute($args);
		
		if($res->errorCode() > 0 ){
			$error = $res->errorInfo();
			$this->error("PDO({$error[0]})[{$error[1]}] {$error[2]}");
			return false;
		}
		
		return $res;
	}

	/**
	 * Validates All fields in ->tmp_data array
	 * 
	 */
	function validateAll(){
		$info = $this->tmp_data;
		foreach($info as $field => $val){
			//Match double fields
			if(isset($info[$field.(2)])){
				if($val != $info[$field.(2)]){
					$this->form_error($field, ucfirst($field) . "s did not match");
				}else{
					$this->report(ucfirst($field) . "s matched");
				}
			}

			$this->tmp_data[$field] = trim($val); //Trim white spaces at end and start

			//Validate field
			if(!isset($this->validations[$field]))
				continue;
			$opt = $this->validations[$field];
			$this->validate($field,$opt['limit'],$opt['regEx']);
		}
		return $this->has_error() ? false : true;
	}

	//Validates field($name) in tmp_data
	protected function validate($name,$limit,$regEx = false){
		$Name = ucfirst($name);
		$str = $this->tmp_data[$name];
		$l = explode("-",$limit);
		$min = intval($l[0]);
		$max = intval($l[1]);
		if(!$max and !$min){
			$this->error("Invalid second paramater for the $name validation");
			return false;
		}
		if(!$str){
			if(!isset($this->tmp_data[$name])){
				$this->report("missing index $name from the POST array");
			}
			if(strlen($str) == $min){
				$this->report("$Name is blank and optional - skipped");
				return true;
			}
			$this->form_error($name,"$Name is required.");
			return false;
		}
		if(strlen($str) > $max){
			$this->form_error($name,"The $Name is larger than $max characters.");
			return false;
		}
		if(strlen($str) < $min){
			$this->form_error($name,"The $Name is too short. it should at least be $min characters long");
			return false;
		}
		if($regEx){
			preg_match_all($regEx,$str,$match);
			if(count($match[0]) != 1){
				$this->form_error($name,"The $Name \"{$str}\" is not valid");
				return false;
			}
		}

		$this->report("The $name is Valid");
		return true;
	}

	//Encoder
	function encode($d){
		$k=$this->encoder;preg_match_all("/[1-9][0-9]|[0-9]/",$d,$a);$n="";$o=count($k);foreach($a[0]as$i){if($i<$o){
			$n.=$k[$i];}else{$n.="1".$k[$i-$o];}}
		return $n;
	}
	//Decoder
	function decode($d){
		$k=$this->encoder;preg_match_all("/[1][a-zA-Z]|[2-9]|[a-zA-Z]|[0]/",$d,$a);$n="";$o=count($k);foreach($a[0]as$i){
			$f=preg_match("/1([a-zA-Z])/",$i,$v);if($f==true){	$i=$o+array_search($v[1],$k);}else{$i=array_search($i,$k);}$n.=$i;}
		return $n;
	}	
}
?>
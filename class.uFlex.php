<?php
	/*
		Copyright (c) 2013 Pablo Tejada, http://ptejada.com/projects/uFlex/

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
	/**
	 * uFlex class file
	 *
	 * @author Pablo Tejada
	 * @package uFlex
	 */

	/**
	 * Class uFlex
	 */
	class uFlex{
		/**
		 * Class Version
		 * @var int
		 */
		const version = 0.96;
		/**
		 * @var PDO|array An array of database credentials or a PDO object if
		 * connected to the database
		 */
		var $db = array(
			"host" => "",
			"user" => "",
			"pass" => "",
			"name" => "",	//Database name
			"dsn" => ""		//Alternative PDO DSN string
		);

		/**
		 * Holds a unique clone number of the instance clones
		 *
		 * @var int
		 * @ignore
		 */
		protected $clone = 0;

		/**
		 * @var int Current user ID
		 */
		var $id;

		/**
		 * @var string Current User PHP Session ID
		 */
		var $sid;

		/**
		 * @var string Current user username
		 */
		var $username;

		/**
		 * @var string Holds the user password hash
		 */
		var $pass;

		/**
		 * @var bool Flag of weather a user is signed-in or not
		 */
		var $signed;

		/**
		 * @var array Holds entire user database row as an associative array
		 */
		var $data;

		/**
		 * @var array Container to hold errors and reports
		 */
		var $console;

		/**
		 * @var string Pointer for tracking errors and reports in the console
		 */
		protected $log;

		/**
		 * @var string Holds the hash for any type of confirmation
		 */
		var $confirm;

		/**
		 * @var array Holds the temporary user information during registration and other methods
		 */
		var $tmp_data;

		/**
		 * Array of Internal options:
		 *
		 * Array of internal options:
		 * <pre>
		 * [table_name]: Name of the users table
		 * [cookie_time]: Autologin cookie lifetime
		 * [cookie_name]: Autologin cookie name
		 * [cookie_path]: Autologin cookie path
		 * [cookie_host]: Autologin cookie host
		 * [user_session]: $_SESSION index to use
		 * [default_user]: An associative array with properties of the default array
		 * </pre>
		 * @var array
		 */
		var $opt = array(
			"table_name" => "users",
			"cookie_time" => "+30 days",
			"cookie_name" => "auto",
			"cookie_path" => "/",
			"cookie_host" => false,
			"user_session" => "userData",
			"default_user" => array(
				"username" => "Guess",
				"user_id" => 0,
				"password" => 0,
				"signed" => false
			)
		);

		/**
		 * @var array Array for default field validations
		 */
		var $validations = array(
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

		/**
		 * Required for the integer encoder and decoder functions
		 * @var array
		 * @access protected
		 * @ignore
		 */
		protected $encoder = array(
			"a","b","c","d","e","f","g","h","i","j","k","l","m","n","o","p","q","r","s","t","u","v","w","x","y","z",
			"A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z",
			0,2,3,4,5,6,7,8,9
		);

		/**
		 * @var array Array of errors text. Could use overwritten for multilingual support
		 */
		var $errorList = array(
			//Database Error while calling register functions
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

		/**
		 * Login a user with username|email and password
		 *
		 * @access public
		 * @api
		 *
		 * @param string|bool $user username or email
		 * @param string|bool $pass password in plain text
		 * @param bool $auto boolean to remember or not the user
		 *
		 * @return bool True if user logged in successfully, false otherwise
		 */
		public function login($user=false,$pass=false,$auto=false){
			//reconstruct object
			return self::__construct($user,$pass,$auto);
		}

		/**
		 * Register A New User
		 *
		 * Takes two parameters, the first being required
		 *
		 * @access public
		 * @api
		 *
		 * @param array $info An associative array,
		 *				the index being the field name(column in database)
		 *				and the value its content(value)
		 * @param bool $activation Default is false, if true the user will need required further steps to activate account
		 * 				Otherwise the account will be activated if registration succeeds
		 *
		 * @return array|bool Returns activation hash if second parameter $activation is true
		 * 						Returns true if second parameter $activation is false
		 * 						Returns false on Error
		 */
		public function register($info,$activation = false){
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
			$data = array();
			$into = array();
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
		 * @access public
		 * @api
		 *
		 * @param array $info An associative array,
		 *			    the index being the field name(column in database)
		 *				and the value its content(value)
		 *
		 * @return bool Returns true on success anf false on error
		 */
		public function update($info){
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
			$data = array();
			$set = array();
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

				if($this->clone == 0)
					$_SESSION['uFlex-' . $this->clone . '-update'] = true;

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
		 * <pre>
		 *  Requires the first two parameters
		 * 		$name  = string (name of the field to be validated)
		 * 		$limit = string (range of the accepted value length in the format of "5-10")
		 * 			- to make a field optional start with 0 (Ex. "0-10")
		 *
		 * 	Optional third parameter
		 * 		$regEx = string (Regular Expression to test the field)
		 * </pre>
		 * _____________________________________________________________________________________________________
		 *
		 * Multiple Entry:
		 * <pre>
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
		 * </pre>
		 *
		 * @access public
		 * @api
		 *
		 * @param string|array $name Name of the field to validate or an array of all the fields and their validations
		 * @param string $limit A range of the accepted value length in the format of "5-10",
		 * 						to make a field optional start with 0 (Ex. "0-10")
		 * @param string|bool $regEx Regular expression to the test the field with
		 * @return null
		 */
		public function addValidation($name,$limit = "0-1",$regEx = false){
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
		 * Activates Account with a hash
		 *
		 * Takes Only and Only the URL parameter of a confirmation page
		 * which would be the hash returned by the register method
		 *
		 * @access public
		 * @api
		 *
		 * @param string $hash Hash returned in the register method
		 * @return bool Returns true account activation and false on failure
		 */
		public function activate($hash){
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
		 * @access public
		 * @api
		 *
		 * @param string $email User email to reset password
		 * @return array|bool On Success it returns an array(email,username,user_id,hash)
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
		 * @access public
		 * @api
		 *
		 * @param string $hash hash returned by the pass_reset() method
		 * @param array $newPass An array with indexes 'password' and 'password2'
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
		 * When you initialize the class like `new uFlex(false)` the object
		 * construction will be halted until this method is called.
		 *
		 * @access public
		 * @api
		 *
		 * @return void
		 */
		function start(){
			$this->__construct();
		}

		/**
		 * User factory
		 *
		 * Returns a clone of the uFlex instance which allows simple user managing
		 * capabilities such as updating a user field, resetting its password and so on.
		 *
		 * @api
		 *
		 * @param int $id
		 * @return bool|uFlex Returns false if user does not exists in database
		 */
		function manageUser( $id=0 ){
			$user = clone $this;
			$user->logger("Cloning");

			if( $id > 0){
				$user->report("Fetching user from database");
				$data = $user->getRow(array("user_id"=>$id));
				if($data){
					$user->id = $data['user_id'];
					$user->data = $data;
					$user->username = $data['username'];
					$user->pass = $data['password'];
					$user->signed = true;

					$user->report("User imported to object");
					return $user;
				}
			}

			return false;
		}

		/*////////////////////////////////////////////\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\*\
		////////Protected and Secondary Methods below this line\\\\\\\\\\\\\
		 \*\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\/////////////////////////////////////////////*/
		/**
		 * Object constructor
		 *
		 * @ignore
		 */
		function __construct($name='', $pass=false, $auto=false){
			if($name === false) return false;

			$this->logger("login"); //Index for Reports and Errors;

			if(!isset($_SESSION) and !headers_sent()){
				session_start();
				$this->report("Session is been started...");
			}elseif(isset($_SESSION)){
				$this->report("Session has already been started");
			}else{
				$this->error("Session could not be started");
				return false; //Finish Execution
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

			return $result;
		}

		/**
		 * Protected Login processor function
		 *
		 * @param string|bool $user username or email
		 * @param string|bool $pass password in plain text
		 * @param bool $auto autologin cookie flag
		 * @ignore
		 * @protected
		 *
		 * @return bool True if user was successfully logged in, false otherwise
		 */
		protected function loginUser($user = false,$pass = false,$auto = false){
			//Session Login
			if($this->session("signed")){
				$this->report("User Is signed in from session");
				$this->update_from_session();
				if(isset($_SESSION['uFlex-' . $this->clone . '-update'])){
					$this->report("Updating Session from database");
					//Get User From database because its info has change during current session
					$update = $this->getRow(Array("user_id" => "$this->id"));
					$this->update_session($update);
					$this->log_login(); //Update last_login
					//Cleaning session flag
					unset($_SESSION['uFlex-' . $this->clone . '-update']);
				}
				return true;
			}
			//Cookies Login
			if(isset($_COOKIE[$this->opt['cookie_name']]) and !$user and !$pass){
				$c = $_COOKIE[$this->opt['cookie_name']];
				$this->report("Attempting Login with cookies");
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
						//Account deactivated due to a password reset or reactivation request
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
				$this->opt['cookie_path'],$this->opt['cookie_host']); //Deletes the Auto Cookie

			$this->signed = 0;
			//Import default user object
			$this->session($this->data = $this->opt['default_user']);

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
		protected function setCookie(){
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
		 * session([String], [String|mixed]) => Set the specified index with the given value
		 * session([Array]) => Overwrite the whole uFlex session space with a given array
		 *
		 * @param string|array|bool $index Session index to get or set
		 * @param mixed[]|bool $val The value to the set the $index with
		 *
		 * @return bool|mixed[]
		 */
		function session($index=false, $val=false){
			//Get uFlex session index value
			if(is_string($index) and !$val){
				return @$_SESSION[$this->opt['user_session']][$index];
			}

			//Set the value for a uFlex index
			if(is_string($index) and $val){
				$_SESSION[$this->opt['user_session']][$index] = $val;
				return true;
			}

			//Overwrite the whole uFlex session space with a given array
			if(is_array($index) and !$val){
				$_SESSION[$this->opt['user_session']] = $index;
				return true;
			}

			//return full session user data
			return $_SESSION[$this->opt['user_session']];
		}

		/**
		 * Updates the session and the object with the provided array
		 * @ignore
		 */
		protected function update_session($d){
			$this->session($d);
			$this->session("signed",1);

			$this->report("Session updated");
			$this->update_from_session();
		}

		/**
		 * Update the object with the PHP session information
		 * @ignore
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
		 * The password hash maker
		 *
		 * Hashes a clear text password for the current user
		 *
		 * @ignore
		 */
		protected function hash_pass($pass){

			$registrationDate = false;

			if(isset($this->data['reg_date']))
				$registrationDate = $this->data['reg_date'];

			if(!$registrationDate and isset($this->tmp_data['reg_date']))
				$registrationDate = $this->tmp_data['reg_date'];

			$pre = $this->encode($registrationDate);
			$pos = substr($registrationDate, 5, 1);
			$post = $this->encode($registrationDate * (substr($registrationDate, $pos, 1)));
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
			$this->report(">> Starting new $log request");
			return $this;
		}

		/**
		 * Add a report log entry
		 *
		 * @param string|bool $str Text information, default is false
		 *
		 * @return bool|mixed[] If $str is false returns the array of reports in the current logger
		 */
		function report($str = false){
			$index = $this->log;
			if($str){
				if(is_string($str))
					$str = ucfirst($str);

				$this->console['reports'][$index][] = $str; //Store Report
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
		 * @param string|bool $str Text information, default is false
		 * @return bool|array If $str is false returns the array of errors in the current logger
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

			return false;
		}

		/**
		 * Add a form field error log entry
		 *
		 * @param string|bool $field Field name, default is false
		 * @param string|bool $error Text information, default is false
		 * @return bool|array If $field and $error is false returns the array of form field error in the current logger
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

			return false;
		}

		/**
		 * Check if there is an error in the current logger
		 *
		 * @param bool|string $index Stack name to look for errors
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
		 * Generates a unique confirm hash
		 *
		 * @param int $uid user id
		 * @param bool|string $hash optional hash to implement
		 * @return string
		 */
		protected function make_hash($uid,$hash = false){
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
		 *
		 * @param string $hash hashed string to check
		 * @param bool $bypass bypass hash confirmation and get the user by partially matching its password
		 * @return bool
		 */
		function check_hash($hash,$bypass = false){
			if(strlen($hash) != 32 || !preg_match("/^[0-9]{4}/",$hash)){
				$this->error(11);
				return false;
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
				preg_match("/^([0-9]{4})(.{2,".($e_uid_pos - 4)."})(".$e_uid.")/",$hash,$excerpt);
				$pass = $excerpt[2];

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
		 *
		 * @param bool $activated whether to set the user as activate or not
		 *
		 * @return bool
		 */
		protected function save_hash($activated = false){
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
		 *
		 * Check if the database connection exists if not connects to the database
		 *
		 * @return bool
		 */
		protected function connect(){
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
		 * Test field value in database
		 *
		 * Check for the uniqueness of a value in a specified field/column.
		 * For example could be use to check for the uniqueness of a username
		 * or email prior to registration
		 *
		 * @param string $field The name of the field
		 * @param string|int $val The value for the field to check
		 * @param bool|string $err Custom error string to log if field value is not unique
		 * @return bool
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
		 *
		 * @param string $sql SQL query string
		 * @param bool|array $args Array of arguments to execute $sql with
		 *
		 * @return bool
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
		 *
		 * @param array $args field and value pair set to look up user for
		 * @return bool|mixed
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

		/**
		 * Get a PDO statement
		 *
		 * @param string $sql SQL query string
		 * @param bool|mixed[] $args argument to execute the statement with
		 * @return bool|PDOStatement
		 */
		function getStatement($sql, $args=false){
			if(!$this->connect()) return false;

			if($args){
				$finalArgs = array();
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
		 */
		protected function validateAll(){
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

		/**
		 * Validates a field in tmp_data
		 *
		 * @param string $name field name
		 * @param string $limit valid value length range, Ex: '0-10'
		 * @param bool|string $regEx regular expression to test the field against
		 * @return bool
		 */
		protected function validate($name,$limit,$regEx = false){
			$Name = ucfirst($name);
			$str = $this->tmp_data[$name];
			$l = explode("-",$limit);
			$min = intval($l[0]);
			$max = intval($l[1]);
			if(!$max and !$min){
				$this->error("Invalid second parameter for the $name validation");
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

		/**
		 * Encodes an integer
		 * @param int $d integer to encode
		 * @return string encoded integer string
		 */
		protected function encode($d){
			$k=$this->encoder;preg_match_all("/[1-9][0-9]|[0-9]/",$d,$a);$n="";$o=count($k);foreach($a[0]as$i){if($i<$o){
				$n.=$k[$i];}else{$n.="1".$k[$i-$o];}}
			return $n;
		}

		/**
		 * Decodes a string into an integer
		 * @param string $d string to decode into an integer
		 * @return int
		 */
		protected function decode($d){
			$k=$this->encoder;preg_match_all("/[1][a-zA-Z]|[2-9]|[a-zA-Z]|[0]/",$d,$a);$n="";$o=count($k);foreach($a[0]as$i){
				$f=preg_match("/1([a-zA-Z])/",$i,$v);if($f==true){	$i=$o+array_search($v[1],$k);}else{$i=array_search($i,$k);}$n.=$i;}
			return $n;
		}
		/**
		 * Magic method to handle object cloning
		 * @ignore
		 */
		function __clone(){
			$this->clone++;

			$this->opt["user_session"] .= "_c" . $this->clone;
			$this->opt["cookie_name"] .= "_c" . $this->clone;

			$this->sid = $this->id = $this->username = $this->pass = $this->signed = $this->log = $this->confirm = false;
			$this->data = $this->tmp_data = $this->console = $this->log =  array();

			//Import default user object
			$this->data = $this->opt['default_user'];
		}
	}
?>
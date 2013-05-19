Getting Started
=========================

* Check the examples here <http://ptejada.com/projects/uFlex/examples/>
* Try the demo in this package and view its source
* See the methods documentation here <http://ptejada.com/projects/uFlex/documentation/>

This class is developed, maintained and tested in a **PHP 5.3.x** environment.

Updating your current uFlex Class version
====================================

Just replace your old *class.uFlex.php* file with the new one.

Configuring
====================================

As of v0.86 a new method for changing the default values of the class options and properties was introduced. This new method
does not requires you to directly update the class file itself. The advantage of this method is that you can have all your
configurations in a separate file, making it as simple as drag and drop to update the class file in the future. If you are using 
this method you may just replace the *class.uFlex.php* with the new one.

Example:
```php
<?php
	//Instantiate the uFlex object
	$user = new uFlex(false);
	
	//Add database credentials and information 
	$user->db['host'] = "localhost";
	$user->db['user'] = "test";
	$user->db['pass'] = "test";
	$user->db['name'] = "uflex_test"; //Database name
	
	/*
	 * You can update any customizable property of the class before starting the object
	 * construction process
	 */
	
	//Start object construction
	$user->start();
?>
```

Here is an excerpt from the PHP class file which lists the customizable properties you could change prior to calling
`start()` on a uFlex instance

```php
	/**
	 * PDO / database credentials
	 */
	var $db = array(
		"host" => "",
		"user" => "",
		"pass" => "",
		"name" => "",	//Database name
		"dsn" => ""		//Alternative PDO DSN string
	);
	...
	var $opt = array( //Array of Internal options
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

```

Extending the class to create your own user management object
==========================================================

In PHP you area able extends classes just like in any object oriented programming language. Therefore you could extend the `uFlex` class
and add your methods or modifications without having to modify the `uFlex` class itself.

```php
<php
	class User extends uFlex{
		/*
		 * Add your default properties values
		 * such as database connection credentials
		 * user default information
		 * Or cookie preferences
		 */
		
		/*
		 * Create your own methods
		 */
		function updateAvatar(){}
		
		function linkOpeniDAccout(){}
	}
?>
```
For a robust example of how you can extend the `uFlex` class check this file [iD class][iD]

[iD]: https://github.com/ptejada/iD/blob/master/core/inc/class.iD.php

Getting Started
=========================

* Check the examples here <http://crusthq.com/projects/uFlex/examples/>
* Try the demo in this package and view its source
* See the methods documention here <http://crusthq.com/projects/uFlex/documentation/>

This class is developed, mantained and tested in a **PHP 5.3.x** enviroment.

Updating your current uFlex Class version
====================================

### If you have edited/modify the class options...

	
~~As of v0.51 the usual uFlex package now includes an 'updater' folder which includes a script(*uFlex.updater.php*) that will~~
~~will automatically migrate all customizable variables inside the class that may have have changed/updated. You can always ~~
~~manually migrated what you have changed.~~

As of v0.86 a new method of changing the dafault values of the classs options and properties was introduced. This new method 
does not requires you to directly update the class file itselft. The advantage of this method is that you can have all your
configurations in a separate file, making it as simple as drag and drop to update the class file in the future. If you are using 
this method you may just replace the *class.uFlex.php* with the new one.

Example:
```php
<?php
	//Instanciate the uFlex object
	$user = new uFlex(false);
	
	//Add database credentials and information 
	$user->db['host'] = "localhost";
	$user->db['user'] = "test";
	$user->db['pass'] = "test";
	$user->db['name'] = "uflex_test"; //Database name
	
	/*
	 * You can update any customizable property of the class before starting the object
	 * construction proccess
	 */
	
	//Start object construction
	$user->start();
?>
```
	
	
### If you have NOT touched(modified) the class at all...
	
Just replace your old *class.uFlex.php* file with the new one.

Extending the class to create your user manement object
==========================================================

In PHP you area able extends classes just like in object oriented programming language. Therefore you could extend the `uFlex` class
and add your methods or modifications without having to modifiy the `uFlex` class itself.

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

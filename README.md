uFlex 1.0
=========================

A simple all-in-one PHP user Authentication library.
This library is developed, maintained and tested in a **PHP 5.3.x** environment. The UnitTest also runs
on Travis-CI for **PHP 5.4.x** and **PHP 5.5.x**.

The single class file `class.uFlex.php` code can be found on the [Legacy Branch](https://github.com/ptejada/uFlex/tree/legacy)

[![Build Status](https://travis-ci.org/ptejada/uFlex.svg?branch=1.0-DEV)](https://travis-ci.org/ptejada/uFlex)

Getting Started
=========================

If using [Composer](https://getcomposer.org/) just add `ptejada/uflex` to your dependency. Note the casing on `uflex`,
all lowercase. Ex:

```
{
    "require": {
        "ptejada/uflex": "1.*"
    }
}
```

When using [Composer](https://getcomposer.org/) use the `vendor/autoload.php` script to include the library in your
project.

If not using Composer then clone this repository in your project. Use the `autoload.php` script to include the library
in your project.

For more information:

* Check the examples here <http://ptejada.com/projects/uFlex/examples/>
* Try the demo in this package and review its source
* See the methods documentation here <http://ptejada.com/projects/uFlex/documentation/>
* For more detailed documentation check generated PHPDoc <http://ptejada.com/docs/uFlex/>

Upgrading from previous version...
====================================

If not using Composer instead of including a PHP class you will include the `autoload.php` script in
your application which will auto include the library classes as required.

If using composer then the just include the `vendor/autoload.php` in your application if is not already
been included.

Overall version 1.0 takes a more object oriented approach and follows conventions more closely.
For more information check out the [[API Changes]]

Configuring
====================================

When the `User` class is instantiated not much happens. This is to allow the class to be configured.
Once the configured the `start()` method must be call in order for the user authentication process
to start. For Example:

```php
<?php
    //Instantiate the uFlex object
    $user = new ptejada\uFlex\User();

    //Add database credentials and information
    $user->config->database->host = "localhost";
    $user->config->database->user = "test";
    $user->config->database->password = "test";
    $user->config->database->name = "uflex_test"; //Database name

    /*
     * You can update any customizable property of the class before starting the object
     * construction process
     */

    //Start object construction
    $user->start();
?>
```

Here is an excerpt from the PHP class file which lists the customizable `config` properties you could change prior to calling
`start()` on a `User` instance. Note: the `config` property is a `Collection`

```php
	'cookieTime'      => '30',
    'cookieName'      => 'auto',
    'cookiePath'      => '/',
    'cookieHost'      => false,
    'userTableName'   => 'users',
    'userSession'     => 'userData',
    'userDefaultData' => array(
        'Username' => 'Guess',
        'ID'  => 0,
        'Password' => 0,
    ),
    'database' => array(
        'host'     => 'localhost',
        'name'     => '',
        'user'     => '',
        'password' => '',
        'dsn'      => '',
    )
```

What is a `Collection`?
================================

A `Collection` is an object representation of an array. `Collection`s have many uses throughout this project and are
easy to use. What a `Collection` does for us is handle the errors for undefined indexes and stream line the code.

Consider this example working with plain arrays:

```php
<?php
    $data = array(
        'name' => 'pablo',
    );

    if (isset($data['quote']) && $data['quote'])
    {
        echo $data['name'] . "'s quote is: " . $data['quote'];
    }
    else
    {
        echo $data['name'] . " has no quote";
    }
?>
```

Here is the same code using a `Collection`:

```php
<?php
    $data = ptejada\uFlex\Collection(array(
        'name' => 'pablo',
    ));

    if ($data->quote)
    {
        echo  "{$data->name}'s quote is:  $data->quote";
    }
    else
    {
        echo "{$data->name} has no quote";
    }
?>
```

For more information check the [API Documentation][Collection Specs] on Collections.



Extending the `User` Class to create your own user management object
==========================================================

In PHP you area able extends classes just like in any object oriented programming language. Therefore you could extend
the `User` class and add your methods or modifications without having to modify the class file itself.

```php
<php
	class User extends ptejada\uFlex\User{
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
		
		function linkOpeniD(){}
	}
?>
```


[API Changes]: https://github.com/ptejada/iD/blob/master/app/iD/models/Auth.php

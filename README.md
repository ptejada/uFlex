Getting Started
=========================

* Check the examples here <http://crusthq.com/projects/uFlex/examples/>
* Try the demo in this package and view its source
* See the methods documention here <http://crusthq.com/projects/uFlex/documentation/>

Please note you should have stablish an sql connection before starting a uFlex object

Updating your curent uFlex version
===================================

## If you have edited/modify the class options...

	Inside the uFlex Class there is a constant declared named salt (uFlex::salt).
	It is strongly adviseble that you change its value as it will make your authentication systen unique.
	If you did changed it you will have to migrate the _*::salt*_ constant to the new class file.
    
	 
	As of v.51 the usual uFlex package now includes an 'updater' folder which includes a script(*uFlex.updater.phps*) that will
	will automatically migrate the value of uFlex::salt along with all other customizable variables inside the class that 
	you have changed. You can always manually migrated what you have changed.
    
	
## If you have NOT touched(modified) the class at all...
	
	Just replace your old *class.uFlex.php* file with the new one. Set Up your database and that Should be it.
  
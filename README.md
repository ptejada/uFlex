Getting Started
=========================

* Check the examples here <http://crusthq.com/projects/uFlex/examples/>
* Try the demo in this package and view its source
* See the methods documention here <http://crusthq.com/projects/uFlex/documentation/>

This class is developed, mantained and tested in a **PHP 5.3.x** enviroment.
Updating your current uFlex Class version
====================================

### If you have edited/modify the class options...

	
As of v0.51 the usual uFlex package now includes an 'updater' folder which includes a script(*uFlex.updater.php*) that will
will automatically migrate all customizable variables inside the class that may have have changed/updated. You can always 
manually migrated what you have changed.

As of v0.86 a new method of changing the dafault values of the classs options and properties was introduced. This new method 
does not requires you to directly update the class file itselft. The advantage of this method is that you can have all your
configurations in a separate file, making it as simple as drag and drop to update the class file in the future. If are using 
this method you may just replace the *class.uFlex.php* with the new one.
	
	
### If you have NOT touched(modified) the class at all...
	
Just replace your old *class.uFlex.php* file with the new one.

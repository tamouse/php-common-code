<?php

/**
* Abstract Class PropertyObject -- allows true properties for PHP objects.
*
* PropertyObject makes php "properties" (really just attributes in common OOP parlance) into
* true properties, with access methods.
* @author zzzzBov 
* @link http://www.php.net/manual/en/language.oop5.properties.php
* @version 05-Jun-2010 04:21 
* @copyright none
* @package none
**/

/**
* Define DocBlock
**/

/**
* Example:
* <code>
* <?php
* require_once('class.PropertyObject.php');
* 
* 
* class MyPropertyObject extends PropertyObject {
* 
* 	private $prop1;
* 
* 	public function get_prop1()
* 	{
* 		return $this->prop1;
* 	}
* 	public function set_prop1($value)
* 	{
* 		$this->prop1 = $value;
* 		return $this->prop1;
* 	}
* 	public function isset_prop1()
* 	{
* 		return isset($this->prop1);
* 	}
* 	public function unset_prop1()
* 	{
* 		unset($this->prop1);
* 		return TRUE;
* 	}
* 
* }
* 
* $myobj = new MyPropertyObject;
* 
* $myobj->__set('prop1','something');
* echo "is set? ".($myobj->__isset('prop1')?'yes':'no').PHP_EOL;
* echo "is=".$myobj->__get('prop1').PHP_EOL;
* $myobj->__unset('prop1');
* echo "is set? ".($myobj->__isset('prop1')?'yes':'no').PHP_EOL;
* 
* // Output:
* // is set? yes
* // is=something
* // is set? no
* ?>
* </code>
*
* @package default
* @author Tamara Temple
*/


/**
* PropertyObject - abstract object that will turn attributes into true properties
*
* @package none
* @author zzzzBov
* @version 05-Jun-2010 04:21 
* @link http://www.php.net/manual/en/language.oop5.properties.php
*/
abstract class PropertyObject {

	/**
	* __get - return a property`s value
	* 
	*
	* @param string $name - name of property to get
	* @return mixed
	* @author Tamara Temple
	*/
public function __get($name) {
	if (method_exists($this, ($method = 'get_'.$name)))
	{
		return $this->$method();
	}
	else return;
}

/**
* __isset - test to see if a property is set
*
* @param string $name 
* @return boolean
* @author Tamara Temple
*/
public function __isset($name)
{
	if (method_exists($this, ($method = 'isset_'.$name)))
	{
		return $this->$method();
	}
	else return;
}

/**
* __set - sets the property's value
*
* @param string $name - name of property to set
* @param string $value - value to set property to
* @return void
* @author Tamara Temple
*/
public function __set($name, $value)
{
	if (method_exists($this, ($method = 'set_'.$name)))
	{
		$this->$method($value);
	}
}

/**
* __unset - unsets the property
*
* @param string $name 
* @return void
* @author Tamara Temple
*/
public function __unset($name)
{
	if (method_exists($this, ($method = 'unset_'.$name)))
	{
		$this->$method();
	}
}
}

?>

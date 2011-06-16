<?php

# Example to show usage of PropertyObject

require_once('class.PropertyObject.php');


class MyPropertyObject extends PropertyObject {
	
	/**
	 * prop1 - example property
	 *
	 * @var string
	 **/
	private $prop1;
	
	public function get_prop1()
	{
		return $this->prop1;
	}
	public function set_prop1($value)
	{
		$this->prop1 = $value;
		return $this->prop1;
	}
	public function isset_prop1()
	{
		return isset($this->prop1);
	}
	public function unset_prop1()
	{
		unset($this->prop1);
		return TRUE;
	}
	
}

$myobj = new MyPropertyObject;

$myobj->__set('prop1','something');
echo "is set? ".($myobj->__isset('prop1')?'yes':'no').PHP_EOL;
echo "is=".$myobj->__get('prop1').PHP_EOL;
$myobj->__unset('prop1');
echo "is set? ".($myobj->__isset('prop1')?'yes':'no').PHP_EOL;

// Output:
// is set? yes
// is=something
// is set? no


?>





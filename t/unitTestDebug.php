<?php
/**
 *  unitTestDebug
 * simpletest unit test template: http://www.simpletest.org/
 *
 *  Created by Tamara Temple on 2011-06-23.
 *  Copyright (c) 2011 Tamara Temple Web Development. All rights reserved.
 *
 * @author Tamara Temple
 * @version 2011-06-23
 * @package undefined
 */

error_reporting(-1); // turn on all errors
ini_set('display_errors','on'); // make sure we're showing errors
ini_set('display_startup_errors','on'); // including ones that occur at startup

if (!defined('testlog')) define('testlog', 'TestDebug.log');
if (!defined('banner')) define('banner', PHP_EOL.'--------------------------------------------------------------------------------'.PHP_EOL);


require_once('simpletest/autorun.php');

include '../class.Debug.php';


class TestConstructor extends UnitTestCase
{
	function setUp()
	{
		echo "in setup\n";
		
		error_log(PHP_EOL.banner.__CLASS__.' start '.date("Y/m/d.H:i:s").PHP_EOL,3,testlog);
	}
	function testConstructor()
	{
		$dbg = new Debug();
		$this->assertIsA($dbg,'Debug',"debug object should of type Debug");
		$this->assertTrue($dbg->is_on(),"debugging should be on");
		$this->assertFalse($dbg->nohtml(),"debugging should be emitting HTML");
		$this->assertFalse($dbg->hold(),"debug messages should not be buffered");
	}
}

/**
* 
*/
class TestPrint extends UnitTestCase
{
	
	function setUp()
	{
		error_log(PHP_EOL.banner.__CLASS__.' start '.date("Y/m/d.H:i;s").PHP_EOL,3,testlog);
	}
	function testPrint()
	{
		$dbg= new Debug();
		$testmsg = __CLASS__.'::'.__METHOD__.' Test message';
		ob_start();
		$dbg->p($testmsg,$dbg,__FILE__,__LINE__);
		$dbgout = ob_get_contents();
		ob_clean();
		$this->assertFalse(empty($dbgout),"debug output should not be empty");
		$this->assertPattern("/$testmsg/",$dbgout,"debug output should contain the test message");
	}
}


?>
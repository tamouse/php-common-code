<?php



error_reporting(-1);
ini_set('display_errors', 'on');
ini_set('display_startup_errors', 'on');

require_once('simpletest/autorun.php');
include '../class.MimeToExt.php';

/**
* 
*/
class TestMimeToExt extends UnitTestCase
{
	private static $testlog = 'testlog.log';
	function setUp()
	{
		error_log(PHP_EOL.'==================%<=========================='.' testConstruction start '.date("Y/m/d.H:i:s").PHP_EOL,3,self::$testlog);
	}
	function testConstructor()
	{
		
		try {
			$mime = new MimeToExt();
		} catch (Exception $e) {
			error_log("Exception caught after instatiating MimeToExt: [".$e->getCode()."] ".$e->getMessage().PHP_EOL,3,self::$testlog);
			$this->fail();
		}
		$this->assertIsA($mime,'MimeToExt');
		$this->assertFalse($mime->isset());
		
		$version = explode('.',PHP_VERSION);
		if ($version[0] >= 5 && $version[1] >= 3) {
			try {
				$this->assertTrue($mime->using_finfo_p());				
			} catch (Exception $e) {
				error_log("Exception caught after calling using_finfo_p [".$e->getCode()."] ".$e->getMessage().PHP_EOL,3,self::$testlog);
				$this->fail();
			}
		} else {
			$this->assertFalse($mime->using_finfo_p());
		}
		
		unset($mime);
		
		$orgfn='../classMimeToExt.php';
		try {
			$mime = new MimeToExt($orgfn);
		} catch (Exception $e) {
			error_log("Exception caught after instatiating MimeToExt: [".$e->getCode()."] ".$e->getMessage().PHP_EOL,3,self::$testlog);
			$this->assertTrue(FALSE);
		}
		$this->assertIsA($mime,'MimeToExt');
		$this->assertTrue($mime->isset());
		$retfn = $mime->get_fn();
		error_log('orgfn='.$orgfn.' retfn='.$retfn.PHP_EOL,3,self::$testlog);
		$this->assertEqual($orgfn,$retfn);
		
		error_log(''.PHP_EOL,3,self::$testlog);
		
	}
	 

}

/**
* test getting file type and extention
*/
class TestTypeAndExt extends UnitTestCase
{
	private static $testlog = 'testlog.log';
	function setUp()
	{
		error_log(PHP_EOL.'==================%<=========================='.' TestTypeAndExt start '.date("Y/m/d.H:i:s").PHP_EOL,3,self::$testlog);
	}
	
	 function testTypeAndExtMethod()
	 {
	 	try {
	 		$mime=new MimeToExt();
	 	} catch (Exception $e) {
	 		error_log("Exception caught after instantiating MimeToExt [".$e->getCode()."] ".$e->getMessage().PHP_EOL,3,self::$testlog);
	 		$this->fail();
	 	}
		$dir = '/private/tmp/';
	 	$dh = opendir($dir);
		assertTrue((FALSE !== $dh));
	 	while (($file = readdir($dh)) !== false) {
	 		if (!preg_match('/^comic/', $file)) {continue;}
	 		$mime->set_fn($dir . $file);
			try {
				$type = $mime->type();
			} catch (Exception $e) {
				error_log("Exception caught during retrieval of mime type [".$e->getCode()."] ".$e->getMessage().PHP_EOL,3,self::$testlog);
				$this->fail();
			}
			try {
				$ext = $mime->ext();
			} catch (Exception $e) {
				error_log("Exception caught during retrieval of file extension [".$e->getCode()."] ".$e->getMessage().PHP_EOL,3,self::$testlog);
				$this->fail();
				
			}
	 		error_log( "filename: $file : filetype: $type  extention:  $ext\n",3,self::$testlog);
	 	}
	 	closedir($dh);
	 	$this->pass();
	 }
}

?>
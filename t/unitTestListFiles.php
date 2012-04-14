<?php
/**
 * unit tests for list_files function in functions.inc.php
 */


error_reporting(-1); // turn on all errors
ini_set('display_errors','on'); // make sure we're showing errors
ini_set('display_startup_errors','on'); // including ones that occur at startup

if (!defined('testlog')) define('testlog', 'test.log');
if (!defined('banner')) define('banner', PHP_EOL.'--------------------------------------------------------------------------------'.PHP_EOL);

require_once("simpletest/autorun.php");
require_once("../functions.inc.php");

$testdir=null;
error_log(PHP_EOL.banner.__FILE__.' started '.date("Y-m-dTH:i:s").PHP_EOL,3,testlog);

function mktemp($dir="/tmp",$template="tmp")
{
  return realpath($dir).DIRECTORY_SEPARATOR.$template.time();
}

class TestListDirectory extends UnitTestCase
{
  function setUp() {
    global $testdir;
    error_log(__CLASS__.':'.__FUNCTION__.PHP_EOL,3,testlog);
    echo __CLASS__.':'.__FUNCTION__.PHP_EOL;
    $testdir = realpath(".").DIRECTORY_SEPARATOR."testdir-".time().".dir";
    $this->assertFalse(file_exists($testdir),"$testdir exists and it should not");
    $this->assertTrue(mkdir($testdir,0777,true),"making directory $testdir");
    $this->assertTrue(is_dir($testdir),"make sure directory exists and is a directory");
    // make a bunch of temp files
    for($i=0;$i<10;$i++) {
      shell_exec("touch $testdir/test-$i.txt");
    }
    for($i=10;$i<20;$i++) {
      mkdir("$testdir/testdir-$i.dir");
    }
  }

  function tearDown() {
    global $testdir;
    echo __CLASS__.':'.__FUNCTION__.PHP_EOL;
    shell_exec("/bin/rm -rf $testdir");
    error_log(__CLASS__.':'.__FUNCTION__.PHP_EOL,3,testlog);
  }
  
  function testExists() {
    echo __CLASS__.':'.__FUNCTION__.PHP_EOL;
    error_log(__CLASS__.':'.__FUNCTION__.PHP_EOL,3,testlog);
    $this->assertTrue(function_exists('list_files'),"checking if function esists");
  }

  function testNullCall() {
    echo __CLASS__.':'.__FUNCTION__.PHP_EOL;
    error_log(__CLASS__.':'.__FUNCTION__.PHP_EOL,3,testlog);
    $this->assertTrue(is_array(list_files('.')),"should return an array");
    $this->assertFalse(list_files(null),"shoul return FALSE");
  }

  function testBadValues()
  {
    echo __CLASS__.':'.__FUNCTION__.PHP_EOL;
    error_log(__CLASS__.':'.__FUNCTION__.PHP_EOL,3,testlog);
    $this->assertFalse(list_files(0),'should return FALSE');
    $this->assertFalse(list_files(''),"should return FALSE");
  }

  function testDirectory()
  {
    echo __CLASS__.':'.__FUNCTION__.PHP_EOL;
    global $testdir;
    error_log(__CLASS__.':'.__FUNCTION__.PHP_EOL,3,testlog);
    $files = list_files($testdir);
    echo __CLASS__.':'.__FUNCTION__.':'.__LINE__.':'." \$files:".print_r($files,true).PHP_EOL;
    $this->assertTrue(is_array($files),"should return an array of files");
    foreach ($files as $file) {
      $this->assertTrue(is_file($testdir.DIRECTORY_SEPARATOR.$file), "should be just a file");
    }
  }
}
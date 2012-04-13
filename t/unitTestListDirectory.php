<?php
/**
 * unit tests for list_directories function in functions.inc.php
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
    $testdir = realpath(".").DIRECTORY_SEPARATOR."testdir.dir";
    $this->assertFalse(file_exists($testdir),"$testdir exists and it should not");
    $this->assertTrue(mkdir($testdir,0777,true),"making directory $testdir");
    $this->assertTrue(is_dir($testdir),"make sure directory exists and is a directory");
  }

  function tearDown() {
    global $testdir;
    shell_exec("/bin/rm -rf $testdir");
    error_log(__CLASS__.':'.__FUNCTION__.PHP_EOL,3,testlog);
  }
  
  function testExists() {
    error_log(__CLASS__.':'.__FUNCTION__.PHP_EOL,3,testlog);
    $this->assertTrue(function_exists('list_directories'),"checking if function esists");
  }

  function testNullCall() {
    error_log(__CLASS__.':'.__FUNCTION__.PHP_EOL,3,testlog);
    $this->assertTrue(is_array(list_directories('.')),"should return an array");
    $this->assertFalse(list_directories(null),"shoul return FALSE");
  }

  function testBadValues()
  {
    error_log(__CLASS__.':'.__FUNCTION__.PHP_EOL,3,testlog);
    $this->assertFalse(list_directories(0),'should return FALSE');
    $this->assertFalse(list_directories(''),"should return FALSE");
  }

  function testNotDirectory() {
    global $testdir;
    error_log(__CLASS__.':'.__FUNCTION__.PHP_EOL,3,testlog);
    $this->assertTrue(is_dir($testdir), "check to make sure $testdir is a directory");
    $testfn = mktemp($testdir,"test-");
    $fh = fopen($testfn,"w");
    fwrite($fh,"nothing");
    fclose($fh);

    $this->assertFalse(list_directories($testfn),"make sure function returns false if passed a non-directory file");

    unlink($testfn);
  }


  function testIsADirectory() {
    global $testdir;
    error_log(__CLASS__.':'.__FUNCTION__.PHP_EOL,3,testlog);
    $this->assertTrue(is_dir($testdir),"check that $testdir is a directory");
    $testfn = mktemp($testdir,"test-");
    $this->assertFalse(file_exists($testfn),"ensure file does not exist");
    $this->assertTrue(mkdir($testfn,0777,true),"make $testfn directory");
    $dlist=list_directories($testdir);
    $this->assertTrue(is_array($dlist),"make sure return from function is an arary");
    $this->assertTrue(in_array(basename($testfn),$dlist),"make sure $testfn is in directory list");
    rmdir($testfn);
  }

  function testManyDirectories() {
    global $testdir;
    error_log(__CLASS__.':'.__FUNCTION__.PHP_EOL,3,testlog);
    foreach (range(0,10) as $i) {
      $testfn[$i] = mktemp($testdir,"test-$i-");
      $this->assertTrue(mkdir($testfn[$i],0777,true),"make test directory $testfn");
    }
    $dlist=list_directories($testdir);
    $this->assertTrue(is_array($dlist),"make sure return is an array");
    foreach (range(0,10) as $i) {
      $this->assertTrue(in_array(basename($testfn[$i]),$dlist),"make sure test directory is in return list");
      $this->assertTrue(is_dir($testfn[$i]),"make sure test directory is a directory");
      rmdir($testfn[$i]);
    }
  }

}

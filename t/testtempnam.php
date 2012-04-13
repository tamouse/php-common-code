<?php
$d=tempnam(".","dir"); 		/* create a temp named file */
unlink($d); 			/* unlink it because we're going to make it a directory */
mkdir($d,777,true);		/* make the directory */
echo "$d is ". (is_dir($d)?'':'NOT')." a directory\n";


$f=tempnam($d,"file");		/* using the first directory, create a new temp named file */
unlink($f);			/* unlink it as we're going to make it a directory */
mkdir($f,777,true);		/* make the directory */
echo "$f is ". (is_dir($f)?'':'NOT')." a directory\n";
?>

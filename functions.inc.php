<?php
/**
 * General reusable functions
 *
 * @author Tamara Temple <tamara@tamaratemple.com>
 * @version $Id$
 * @copyright Tamara Temple Development,  2010-
 * @license GPLv3
 * @package common code
 *
 * License Info:
 * 
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 * 
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 * 
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 *
 **/

/**
 * Define DocBlock
 **/

/**
 * This library depends on several constants that may be defined elsewhere (typically in a config.inc file)
 **/
if (!defined('APPBOTTOMDIR')) define("APPBOTTOMDIR",basename(dirname(__FILE__)));	// you really want to define this elsewhere, say in a config.inc file to be what is accurate for your application. This will break if you have subdirectories under your standard application root.

if (!defined('APP_NAME')) 			define('APP_NAME', 			'MyApp');
// must be carefull with these next two!!! Can set up a circular dependency between a config.inc file and functions.inc
//if (!defined('APP_ROOT'))				define('APP_ROOT', 			get_app_root());
//if (!defined('APP_URI_BASE'))			define('APP_URI_BASE', 		get_uri_base());
// Another alternative is to use the dirname function
if (!defined('APP_ROOT'))				define('APP_ROOT',dirname(__FILE__) . DIRECTORY_SEPARATOR); // Assuming this file is in the root of your application. If it's somewhere below, you should wrap up dirname() with the appropriate number of dirname()'s to get to the root directory.
if (!defined('APP_URI_BASE'))			define('APP_URI_BASE',		isset($_SERVER['SCRIPT_NAME']) ? dirname($_SERVER['SCRIPT_NAME']).DIRECTORY_SEPARATOR : NULL); // Again, assuming that the script being called is in the root of your application. If it's not, something more complicated like the get_uri_base() function below should be used.
if (!defined('DEBUG')) 				define('DEBUG', 			'FALSE'); // DEBUG could be defined by testing the GET or POST params for a debug=true entry:
/*
if ((isset($_GET['debug']) && $_GET['debug']=='true') ||
	(isset($_POST['debug]) && $_POST['debug']=='true')) {
	define('DEBUG',TRUE);
	ini_set('display_errors',TRUE);
    ini_set('display_startup_errors',TRUE);
    
} else {
	define('DEBUG',FALSE);
}
*/
if (!defined('DEBUGPREFIX')) 			define('DEBUGPREFIX', 		'<p class="debug">DEBUG: ');
if (!defined('DEBUGSUFFIX')) 			define('DEBUGSUFFIX', 		'</p>');
if (!defined('DEFAULT_REDIRECT')) 	define('DEFAULT_REDIRECT', 	'index.php');
if (!defined('DEFAULT_RETURN'))		define('DEFAULT_RETURN', 	'index.php');
if (!defined('DIEPREFIX'))			define('DIEPREFIX', 		'<p class="error">DIE: ');
if (!defined('DIESUFFIX'))			define('DIESUFFIX', 		'</p>');
if (!defined('MIMETYPE'))				define('MIMETYPE',			'mimetype --database=/sw/share/mime -b'); 
// note: the mimetype program is part of the Perl package File::MimeInfo available on the CPAN site:  http://search.cpan.org/~pardus/File-MimeInfo-0.15/lib/File/MimeInfo.pm
// the shared mime database is available on Freedesktop website - http://www.freedesktop.org/wiki/Software/shared-mime-info

/**
 * Build a redirect path including messages, errors, and additional query data
 *
 * @return redirect url
 * @author Tamara Temple <tamara@tamaratemple.com>
 **/
function buildredirect($u)
{
	global $errors, $messages, $additional_query_parms;
	
	$redirect = $u;
	$options = Array();
	if (isset($additional_query_parms) && !empty($additional_query_parms)) {
		$options = $additional_query_parms;
	}
	if (isset($messages) && !empty($messages)) {
		$options['messages'] = $messages;
	}
	if (isset($errors) && !empty($errors)) {
		$options['errors'] = $errors;
	}
	debug_var("\$options:",$options);
	if (!empty($options)) {
		$redirect .= "?" . http_build_query($options);
	}
	return $redirect;
	
}

/**
 * Build a url string based on the parts given
 *
 * @return url string
 * @author Tamara Temple <tamara@tamaratemple.com>
 **/
function build_url($parts)
{
	$uri = (isset($parts['schema']) ? $parts['schema'] : 'http') . '://';
	if (isset($parts['user'])) {
		$uri .= $parts['user'];
		if (isset($parts['pass'])) {
			$uri .= ':' . $parts['pass'];
		}
		$uri .= '@';
	}
	if (isset($parts['host'])) {
		$uri .= $parts['host'];
	} else {
		return NULL; /* no host given, bogus url */
	}
	if (isset($parts['port'])) {
		$uri .= ":".$parts['port'];
	}
	if (isset($parts['path'])) {
		$uri .= $parts['path'];
	} else {
		$uri .= '/';
	}
	if (isset($parts['query'])) {
		$uri .= '?' . $parts['query'];
	}
	if (isset($parts['fragment'])) {
		$uri .= '#' . $parts['fragment'];
	}
	return $uri;
}

/**
 * debug function - print a message if DEBUG === TRUE
 *
 * @return void
 * @author Tamara Temple <tamara@tamaratemple.com>
 *
 * CHANGED: made function a bit more useful by being the ONLY debug function to call, and to allow $var, $file, and $line to be optional parameters. If $var is an array, it will print out via print_r, otherwise it's just echoed as is. $file and $line are used to supply __FILE__ and __LINE__ parameters specifically. This is possibly prettier than making every debug call look like >> debug(basename(__FILE__).'@'.__LINE__.' '."message"); << as it will now read as: >> debug("message",'',__FILE__,__LINE__); << instead.
 *
 *
 **/
function debug($msg,$var='',$file='',$line='')
{
	if (DEBUG) {
		echo DEBUGPREFIX.PHP_EOL;
		if (!empty(basename($file))) echo basename($file);
		if (!empty($line)) echo '@'.$line;
		echo "DEBUG: $msg".PHP_EOL;
		if (!empty($var)) {
			if (is_array($var)) {
				echo htmlspecialchars(print_r($var,true));				
			} else {
				echo htmlspecialchars($var);
			}
		}
		echo DEBUGSUFFIX.PHP_EOL;
	}
}

/**
 * debug var function - DEPRICATED - use debug instead now
 **/
function debug_var($msg,$var='',$file='',$line='')
{
	debug($msg,$var,$file,$line);
}

/**
 * Determine the extension of the file by checking it's mimetype
 *
 * @return ext - string
 * @author Tamara Temple <tamara@tamaratemple.com>
 **/
function determine_extension($fn)
{
	// First we create a static lookup table
	static $mimetoext = array(
		'image/jpeg' => 'jpg',
		'image/gif' => 'gif',
		'image/png' => 'png'
		);
	
	$result = get_mimetype($fn);
	$ext = isset($mimetoext['result']) ? $mimetoext['result'] : 'dat';
	return $ext;
}


/**
 * perform a redirect to the indicated url $u, applying other paramters as needed.
 *
 * @return none - will either redirect or exit
 * @author Tamara Temple
 **/
function do_redirect($u)
{
	if (!isset($u)) $u = DEFAULT_REDIRECT;
	$u = buildredirect($u);
	debug("Redirect: \$u=$u");
	if (!DEBUG) header("Location: $u"); else exit("<p><a href='$u'>Redirect to $u</a></p>");
}

/**
 * Emit a fatal error message to the log and output, and die
 *
 * @return void
 * @author Tamara Temple <tamara@tamaratemple.com>
 **/
function emit_fatal_error($msg)
{
	error_log(APP_NAME . " FATAL ERROR: $msg");
	// Dump accumulated errors and messages
	if (isset($GLOBALS['messages'])) 	debug_var("Messages:",$GLOBALS['messages']);
	if (isset($GLOBALS['errors'])) 		debug_var("Errors:",$GLOBALS['errors']);
	die(DIEPREFIX . "FATAL ERROR: $msg" . DIESUFFIX);
}


/**
 * Get All records from a table, returning them in an indexed array of records as associative arrays
 *
 *  $tblname is a required paramter
 *
 * This function supports an array of options that may be used to customize the sql query.
 *  $options= array('columns'=>scalar column specification or array('column1','column2','column3'...),
 *                  'where'=>scalar where clause or or array('where clause1','where clause2',...),
 *                  'sort'=>scalar sort clause or array('sort clause1','sort clause2',...)
 *                 )
 *
 * If any of these are omitted (and they're all optional), the following behaviour is used:
 *    if columns is empty or missing, use "*" as the column selector
 *    if sort is empty or missing, don't do any ordering of the records on retrieval
 *    if where is empty or missing, don't provide any selection criteria
 *
 * Thus, calling get_all_array(TABLENAME) will return all rows of the table specified by TABLENAME
 *
 * This function uses mysqli object oriented calls. The database descriptor is passed in $db.
 *
 * DONE: to make this function even more portable, pass in the database descriptor
 * 
 * @return indexed array of records as associative arrays
 * @author Tamara Temple <tamara@tamaratemple.com>
 **/
function get_all_array($db,$tblname,$options=NULL)
{
	if (isset($options)) {
		debug_var("get_all_array \$options:",$options);
		foreach ($options as $key => $value) {
			switch ($key) {
				case 'columns':
					if (is_array($value)) {
						$columns = $value;
					} else {
						$columns[] = $value;
					}
					break;
				case 'sort':
					if (is_array($value)) {
						$orderparts = $value;
					} else {
						$orderparts[] = $value;
					}
					break;
				
				case 'where':
					if (is_array($value)) {
						$whereparts = $value;
					} else {
						$whereparts[] = $value;
					}
					break;
					
				default:
					# code...
					break;
			}
		}
	}
	
	$sql_a = Array(); /* initialize the sql query */
	$sql_a[] = "SELECT";
	$sql_a[] = (!empty($columns) ? join(", ",$columns) : "*");
	$sql_a[] = "FROM $tblname";
	if (!empty($whereparts)) $sql_a[] = " WHERE ".join(" AND ",$whereparts);
	if (!empty($orderparts)) $sql_a[] = " ORDER BY ".join(",",$orderparts);
	$sql_s = join(" ",$sql_a);
	debug("get_all_array \$sql_s=$sql_s");
	$result=$db->query($sql_s);
	if ($result === FALSE) emit_fatal_error(APP_NAME." in ".__FILE__."@".__LINE__." "."SQL Query Failure. \$sql_s=$sql_s. error=".$db->error);
	$all_rows = Array();
	if ($result->num_rows > 0) {
		if (method_exists('mysqli_result','fetch_all')) {
			$all_rows = $result->fetch_all(MYSQLI_ASSOC);
		} else {
			/* vesion is too old, have to do it by hand */
			while ($row = $result->fetch_assoc()) {
				$all_rows[] = $row;
			}
		}
	}
	$result->free();
	return $all_rows;
}


/**
 * Returns the application root of a given application
 *
 * Note: a possibly better practice is to set the APP_ROOT using the dirname function on the PHP Magic Constant __FILE__:
 *      define('APP_ROOT',dirname(__FILE__)); // assuming where you're setting that is in the base directory of your app
 *
 * Depends on constant APPBOTTOMDIR to determine where in the path to stop
 *
 * @return string containing the application root
 * @author Tamara Temple <tamara@tamaratemple.com>
 **/
function get_app_root()
{
	/**
	 * Get the Application Root directory path. Used for finding files and directories on the server's file system
	 */
	$script_path_arr = explode(DIRECTORY_SEPARATOR, dirname($_SERVER['SCRIPT_FILENAME'])); /* get the elements of the path to the current script */
	$app_path_arr = Array();
	foreach ($script_path_arr as $script_path_element) {
		$app_path_arr[] = $script_path_element;
		if ($script_path_element == APPBOTTOMDIR) break; /* stop processing when we get to the application base dir */
	}
	return (!empty($app_path_arr) ? join(DIRECTORY_SEPARATOR,$app_path_arr) . DIRECTORY_SEPARATOR : NULL); /* path to application root */
}

/**
 * Get the file $fn's mimetype
 *
 * @return the mime type as reported by the mimetype command
 * @author Tamara Temple <tamara@tamaratemple.com>
 **/
function get_mimetype($fn)
{
	$cmd = MIMETYPE." ".escapeshellcmd($fn)." 2>/dev/null";
	debug("\$cmd=$cmd");
	$result = rtrim(`$cmd`);
	debug(__FILE__." in ".__FUNCTION__." at ".__LINE__.": result of mimetype command: $result");
	return $result;
}


/**
 * Get a single record given the $id of the record
 *
 * @return single record returned as associative array
 * @author Tamara Temple <tamara@tamaratemple.com>
 **/
function get_one_assoc($tbl,$id)
{
	global $db;
	$sql = "SELECT * FROM ".$tbl." WHERE `id`=".$id." LIMIT 1";
	$result = $db->query($sql);
	if ($result === FALSE) emit_fatal_error(APP_NAME." in ".__FILE__."@".__LINE__." Could not execute \$sql=$sql: error=".$db->error);
	$row = $result->fetch_assoc();
	$result->free();
	return $row;
}

/**
 * Return the referer of this page, removing the query string and application path info
 *
 * @return $referer 
 * @author Tamara Temple <tamara@tamaratemple.com>
 **/
function get_referer()
{
	global $errors;
	if (!isset($_SERVER['HTTP_REFERER'])) return DEFAULT_RETURN;
	debug("HTTP_REFERER=".$_SERVER['HTTP_REFERER']);
	$referer = $_SERVER['HTTP_REFERER'];
	if (!preg_match('!'.APP_URI_BASE.'!',$referer)) {
		$errors[] = 'Referer not from this application';
		return DEFAULT_RETURN;
	}
	$referer = preg_replace('/\?.*/','',$referer);
	if (!isset($referer)) {
		$errors[] = 'Error occured when cleaning HTTP_REFERER in get_referer';
		return DEFAULT_RETURN;
	}
	$referer = preg_replace('!^[a-z]+://[^/]*'.APP_URI_BASE.'!','',$referer);
	if (!isset($referer)) {
		$errors[] = 'Error occured when cleaning up path in get_referer';
		return DEFAULT_RETURN;
	}
	debug("\$referer=$referer");
	return $referer;
}

/**
 * Return a string of the current application's URI base
 *
 * Note: a possibly better practice is to simply use dirname on $_SERVER['SCRIPT_NAME'] where the app's uri base is set as a constant:
 *      define('APP_URI_BASE', isset($_SERVER['SCRIPT_NAME']) ? dirname($_SERVER['SCRIPT_NAME']) : NULL);
 * Uses constant APPBOTTOMDIR
 *
 * @return string of URI base for application
 * @author Tamara Temple <tamara@tamaratemple.com>
 **/
function get_uri_base()
{
	if (!isset($_SERVER['SCRIPT_NAME'])) return NULL; // if no script name, this can't be called as a URI
	
	/**
	 * Get the Application URI path. Used for creating URI's for elements in the application that are called through the web server
	 */
	$script_uri_path_arr = explode(DIRECTORY_SEPARATOR, dirname($_SERVER['SCRIPT_NAME'])); /* get the elements of the URI path to the current script */
	//debug_var("\$script_uri_path_arr",$script_uri_path_arr);
	foreach ($script_uri_path_arr as $script_uri_path_element) {
		$app_uri_base_arr[] = $script_uri_path_element;
		if ($script_uri_path_element == APPBOTTOMDIR) break; /* stop processing when we get to the application base dir */
	}
	return (!empty($script_uri_path_arr) ? join(DIRECTORY_SEPARATOR,$script_uri_path_arr) : NULL);
}



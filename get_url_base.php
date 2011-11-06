<?php  // -*- mode: php; time-stamp-start: "version [\"<]"; time-stamp-format: "%Y-%3b-%02d %02H:%02M"; -*- 
/**
 * get_app_url_base - function to retrieve an application's base directory given a known bottom
 *                    directory 
 *
 * @author Tamara Temple <tamara@tamaratemple.com>
 * @version <2011-Nov-06 01:28>
 * @since 2011/11/06
 * @copyright (c) 2011 Tamara Temple Web Development
 * @license GPLv3
 *
 */


 /**
 * Return a string of the current application's URI base
 *
 * Note: a possibly better practice is to simply use dirname on $_SERVER['SCRIPT_NAME'] where the
 *      app's uri base is set as a constant from a file in a well-known location:
 *      define('APP_URI_BASE', isset($_SERVER['SCRIPT_NAME']) ? dirname($_SERVER['SCRIPT_NAME']) :
 *      NULL); 
 * @param string $appbottom -- bottom directory of the application
 * @return string of URI base for application
 * @author Tamara Temple <tamara@tamaratemple.com>
 **/
function get_app_url_base($appbottom)
{
  if (!isset($_SERVER['SCRIPT_NAME'])) return NULL; // if no script name, this can't be called as a
						    // URI 

  /* get the elements of the URI path to the current script */
  $script_uri_path_arr = explode(DIRECTORY_SEPARATOR, dirname($_SERVER['SCRIPT_NAME'])); 

  foreach ($script_uri_path_arr as $script_uri_path_element) {
    $app_uri_base_arr[] = $script_uri_path_element;
    if ($script_uri_path_element == $appbottom) break; // stop processing when we get to the
						       // application base dir 
  }
  return (!empty($app_uri_path_arr) ? join(DIRECTORY_SEPARATOR,$app_uri_path_arr) : NULL);
}


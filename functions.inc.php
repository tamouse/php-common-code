<?php // -*- mode: php; time-stamp-start: "version [\"<]"; time-stamp-format: "%Y-%3b-%02d %02H:%02M"; -*- 
/**
 * General reusable functions
 *
 * @author Tamara Temple <tamara@tamaratemple.com>
 * @version <2012-Apr-13 22:45>
 * @copyright Tamara Temple Web Development,  2010-
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
 * Build a url string based on the parts given.
 *
 * @param array $parts -- the various parts of a url to be built
 * @return string - built up url
 * @author Tamara Temple <tamara@tamaratemple.com>
 *
 * the keys of the array $parts are as follows:
 *   scheme -- the protocol scheme (http, ftp, data, gopher, etc)
 *             (optional, default is http)
 *   user -- the user name used to authenticate with the remote
 *           service (optional, default is none)
 *   pass -- the password associated with the user. (optional, default
 *           is none) The pass entry is only used if user is also
 *           given.
 *   host -- the hostname of the remote system. (Required. If omitted,
 *           the function returns FALSE)
 *   port -- port number to connect to service on. (Optional)
 *   path -- path spec to further direct the application
 *   fragment -- to direct the application to a location within the
 *               page. Typically only used for http-type urls
 *   query -- query string to pass information to the remote
 *            application. (Optional.) Note, if query is an array
 *            instead of a string, it will be created using
 *            http_build_query() 
 **/
function build_url($parts)
{
  $uri = (isset($parts['schema']) ?
	  $parts['schema'] :
	  'http') . '://';
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
  if (isset($parts['fragment'])) {
    $uri .= '#' . $parts['fragment'];
  }
  if (isset($parts['query'])) {
    $uri .= '?' . ((is_array($parts['query'])) ?
		   http_build_query($parts['query']) :
		   $parts['query']);
  }
  return $uri;
}


/**
 * perform a redirect to the indicated url $u, applying other
 * paramters as needed. Note: this function may need to be customized
 * to your local use of debugging. This function assumes the use of
 * the class.Debug.php module in this package.
 *
 * @param string/array $u - contains information about where to
 *                          redirect to. If an array is given, the url
 *                          will be built using build_url()
 * @return none - will either redirect or exit
 * @author Tamara Temple
 **/
function do_redirect($u)
{
  global $dbg;
  $redirect = ((is_array($u)) ? build_url($u) : $u);
  $dbg->p("Redirect: ",$redirect,__FILE__,__LINE__,__FUNCTION__);
  if ($dbg->is_on())
    exit("<p><a href='$redirect'>Redirect to $redirect</a></p>");
  else
    header("Location: $redirect");
}

/**
 * Emit a fatal error message to the log and output, and die
 *
 * @param string $msg - message to emit
 * @return void
 * @author Tamara Temple <tamara@tamaratemple.com>
 **/
function emit_fatal_error($msg)
{
  error_log("FATAL ERROR: $msg".PHP_EOL);
  // Dump accumulated errors and messages
  if (isset($GLOBALS['messages'])) {
    echo _wrap(implode(PHP_EOL,array_map("_list_wrap",$GLOBALS['messages'])),'ul','messages');
  }
  if (isset($GLOBALS['errors'])) {
    echo _wrap(implode(PHP_EOL,array_map("_list_wrap",$GLOBALS['errors'])),'ul','errors');
  }
  die("FATAL ERROR: $msg".PHP_EOL);
}


/**
 * Validate the referer to this page
 *
 * @param string $match - regular expression to match to determine if
 *                        this is an okay referer string 
 * @return $referer 
 * @author Tamara Temple <tamara@tamaratemple.com>
 **/
function valid_referer($match)
{
  if (!isset($_SERVER['HTTP_REFERER'])) return NULL;
  $referer = $_SERVER['HTTP_REFERER'];
  
  // ensure regex delimiters are escaped in $match:
  $match = str_replace('!', '\!', $match);

  return (preg_match('!'.$match.'!',$referer)) ? $referer : FALSE;

}


/**
 * create an image tag given an image source and optional alt text
 *
 * @param string $src - location of image
 * @param string $alt - optional alt text for image
 * @param array $attr - optional attributes
 * @param boolean $escapetext - whether to excape html entities
 * @return string - image tag
 * @author Tamara Temple <tamara@tamaratemple.com>
 **/
function _img($src,$alt=NULL,$attr=NULL, $escapetext=FALSE)
{
  global $dbg;
  $out = '<img src="' . $src . '"';
  if (!empty($alt) && is_string($alt))
    $out .= ' alt="'.
      ($escapetext)?htmlentities($alt):$alt.'"';
  if (!empty($attr) && is_array($attr)) {
    while (list($k,$v) = each($attr)) {
      $out .= " $k=\"" .
	($escapetext)?htmlentities($v):$v.
	'"';
    }
  }
  $out .= ' />';
  return $out;
}


/**
 * wrap a link around some text
 *
 * @param string $text - subject of link
 * @param string $href - the link target
 * @param array $query - query string to append to link target
 * @param array $attr - other link tag attributes
 * @param boolean $escapetext - flag to indicate inner text should be escaped
 * @return string - link html
 * @author Tamara Temple <tamara@tamaratemple.com>
 **/
function _link($text, $href, $query=NULL, $attr=NULL, $escapetext=FALSE)
{
  $out = '<a href="' . $href;
  if (!empty($query) && is_array($query)) {
    $out .= '?' . http_build_query($query);
  }
  $out .='"';
  if (!empty($attr) && is_array($attr)) {
    while (list($k,$v) = each($attr)) {
      $out .= " $k=\"$v\"";
    }
  }
  $out .= '>';
  $out .= ($escapetext) ? htmlentities($text) : $text;
  $out .= '</a>';
  return $out;
}

/**
 * wrap text inside an html tag, optionally including a class
 *
 * @param string $text - the text to be wrapped (will be passed through htmlentities)
 * @param string $tag (optional) - tag to use to wrap text. defaults to 'p'
 * @param string $class (optional) - class to use in wrapping item. defaults to none
 * @param array $attr (optional) - array containing extra tag attributes
 * @param bool $escape (optional) - whether to encode html entities or not
 * @return string - wrapped text
 * @author Tamara Temple <tamara@tamaratemple.com>
 **/
function _wrap($text,$tag='p',$class=NULL,$attr=NULL,$escape=NULL)
{
  $out = '<'.$tag;
  if (!empty($class)) {
    $out .= ' class="'.$class.'"';
  }
  if (!empty($attr) && is_array($attr)) {
    while (list($k,$v) = each($attr)) {
      $out .= " $k=\"$v\"";
    }
  }
  $out .= '>';
  $out .= ($escape) ? htmlentities($text) : $text;
  $out .= "</$tag>".PHP_EOL;
  return $out;
}

// simple utility useful in array_map or array_walk for building a
// list output of an array
function _list_wrap($s)
{
  return _wrap($s,'li');
}

/**
 * List directories under the given directory path
 *
 * @returns array - directories
 * @author Tamara Temple <tamara@tamaratemple.com>
 * @param string $d - defaults to current directory
 **/
function list_directories ($d='.')
{
  if (!isset($d) || empty($d) || !is_dir($d)) return FALSE;
  $f = scandir($d, 1);
  if (FALSE === $f) return FALSE;
  $dirlist=array();
  foreach($f as $file) {
    // remove non-directories and . directories
    $filepath = realpath($d.DIRECTORY_SEPARATOR.$file);
    if ((is_file($filepath)) || ($file == ".") || ($file == "..")) {
      $dirlist[] = $file;
    }
  }
  sort($dirlist);
  return $dirlist;
    
} // END function list_directories


/**
 * List non-directory files in the given directory path
 *
 * @returns array - non-directory files
 * @author Tamara Temple <tamara@tamaratemple.com>
 * @param string $d - defaults to current directory
 **/
function list_files ($d='.')
{
  if (!isset($d) || empty($d) || !is_dir($d)) return FALSE;
  $f = scandir($d,1);
  if (FALSE === $f) return FALSE;
  $filelist=array();
  foreach ($f as $file) {
    $filepath = realpath($d.DIRECTORY_SEPARATOR.$file);
    if (is_file($filepath)) {
      $filelist[]=$file;
    }
  }
  sort($filelist);
  return($filelist);
} // END function list_files
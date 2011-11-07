<?php  // -*- mode: php; time-stamp-start: "version [\"<]"; time-stamp-format: "%Y-%3b-%02d %02H:%02M"; -*- 
/**
 *
 * db-functions.inc - some moderately useful database functions
 *
 * @author Tamara Temple <tamara@tamaratemple.com>
 * @since 2011/11/06
 * @version <2011-Nov-07 04:16>
 * @copyright (c) 2011 Tamara Temple Web Development
 * @license GPLv3
 *
 */


/**
 * Get records from a table, returning them in an indexed array of records as associative arrays
 *
 * @param object $db - mysqli data base object
 * @param string $tblname is a required paramter
 * @param array $options
 * This function supports an array of options that may be used to customize the sql query.
 *   array('columns'=>scalar column specification or array('column1','column2','column3'...),
 *         'where'=>scalar where clause or or array('where clause1','where clause2',...),
 *              -- note, where clauses will be ANDed together
 *         'sort'=>scalar sort clause or array('sort clause1','sort clause2',...)
 *        )
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
 * @return indexed array of records as associative arrays
 * @author Tamara Temple <tamara@tamaratemple.com>
 **/
function get_all_array($db,$tblname,$options=NULL)
{
  if (isset($options)) {
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
  $result=$db->query($sql_s) or
    die("In ".__FILE__."@".__LINE__." ".__FUNCTION__.
	"Fatal error in query: $sql: (".$db->errno.") ".$db->error);
  $all_rows = Array();
  if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
      $all_rows[] = $row;
    }
  }
  $result->free();
  return $all_rows;
}



/**
 * Get a single record given the numeric id of the record
 *
 * @param object $db - mysqli data base object
 * @param string $tbl - MySQL Table name
 * @param numeric $id - the identifier for the record
 * @param string (optional) $id_field - name of field to use to select
 *                                      the record (default is "id")
 * @return single record returned as associative array
 * @author Tamara Temple <tamara@tamaratemple.com>
 **/
function get_one_assoc($db,$tbl,$id,$id_field="id")
{
  if (!is_numeric($id)) return FALSE;
  $sql = "SELECT * FROM $tbl WHERE `$id_field`=$id LIMIT 1";
  $result = $db->query($sql) or
    die(__FILE__."@".__LINE__." in ".__FUNCTION__.
	"Fatal error running $sql: (".$db->errno.") ".$db->error.PHP_EOL);
  $row = NULL;
  if ($result->num_rows > 0)
    $row = $result->fetch_assoc();
  $result->free();
  return $row;
}


<?php
/*	Database Class - A simple interface to a MySQL database
 *
 *	Copyright (c) 2007 Oliver C Dodd
 *
 *  Permission is hereby granted, free of charge, to any person obtaining a 
 *  copy of this software and associated documentation files (the "Software"),
 *  to deal in the Software without restriction, including without limitation
 *  the rights to use, copy, modify, merge, publish, distribute, sublicense,
 *  and/or sell copies of the Software, and to permit persons to whom the 
 *  Software is furnished to do so, subject to the following conditions:
 *  
 *  The above copyright notice and this permission notice shall be included in
 *  all copies or substantial portions of the Software.
 *  
 *  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 *  FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL 
 *  THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 *  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 *  FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER
 *  DEALINGS IN THE SOFTWARE.
 */
/** DATABASE ACCESS CONSTANTS
 *  define these before including the class to bypass sending parameters into
 *  the constructor
 */
if (!defined('DB_SERVER'))	define('DB_SERVER',	'localhost');
if (!defined('DB_USER'))	define('DB_USER',	'root');
if (!defined('DB_PASS'))	define('DB_PASS',	'');
if (!defined('DB_DATABASE'))	define('DB_DATABASE',	'mysql');
if (!defined('DB_DEBUG_SQL'))	define('DB_DEBUG_SQL',	false);

class Database
{
	/*--------------------------------------------------------------------*\
	|* CONSTANTS                                                          *|
	\*--------------------------------------------------------------------*/
	/* order                                                              */
	const A			= ' ASC';
	const D			= ' DESC';
	/* columns                                                            */
	const COLUMN_NAME	= 'Field';
	const COLUMN_TYPE	= 'Type';
	const COLUMN_NULL	= 'Null';
	const COLUMN_KEY	= 'Key';
	const COLUMN_DEFAULT	= 'Default';
	const COLUMN_EXTRA	= 'Extra';
	/* formats                                                            */
	const TIMESTAMP_FORMAT	= 'Y-m-d H:i:s';
	
	/*--------------------------------------------------------------------*\
	|* VARIABLES                                                          *|
	\*--------------------------------------------------------------------*/
	public $server;		//database server address
	public $user;		//database username
	public $pass;		//database password
	public $database;	//database name
	
	public static $db;	//database object
	public static $created	= false;
	
	/*--------------------------------------------------------------------*\
	|* CONSTRUCTOR/DESTRUCTOR                                             *|
	\*--------------------------------------------------------------------*/
	public function __construct($s=DB_SERVER,$u=DB_USER,$p=DB_PASS,$db=DB_DATABASE)
	{
		$this->server = $s;
		$this->database = $db;
		$this->user = $u;
		$this->pass = $p;
	}
	public function __destruct() { $this->close();	}
	
	public static function getInstance()
	{
		if (!self::$db) self::$db = new Database();
		return self::$db;
	}
	
	/*--------------------------------------------------------------------*\
	|* LOW-LEVEL FUNCTIONS                                                *|
	\*--------------------------------------------------------------------*/
	/** connect() - connect to the mysql database
	 *  @param $selectDB	- select the database? defaults to true
	 *  @param $createDB	- create the database if if doesn't exist?
	 */
	public function connect($selectDB=true,$createDB=true)
	{
		@mysql_connect($this->server,$this->user,$this->pass)
			or die($this->error());
		if ($createDB&&!self::$created) {
			if ( !$this->query("CREATE DATABASE IF NOT EXISTS ".$this->database) )
				return false;
			self::$created = true;
		}
		if ($selectDB) 
			if (!@mysql_select_db($this->database))
				if (DB_DEBUG_SQL) echo $this->error()."<br />\n";
	}
	
	/** close() - close the connection to the database */
	public function close() { return @mysql_close(); }
	
	/** query() - run a SQL query to the database
	 *  @param $sql		- the SQL statement
	 */
	public function query($sql)
	{
		$r =  mysql_query($sql);
		if (DB_DEBUG_SQL) echo $sql."<br />\n".(@mysql_error())."<br />\n";
		return $r;
	}
	
	/** results() - fetch the current result row as an associative array.
	 *  @param $result	- the result resource
	 */
	public function results($result) { return mysql_fetch_assoc($result); }
	
	/** rows() - the number of rows affected by the SQL query */
	public function rows() { return mysql_affected_rows(); }
	
	/** error() - return the last error */
	public function error() { return @mysql_error(); }
	
	/** lastInsertID() - return the auto incremented id of last insert */
	public function lastInsertID() { return @mysql_insert_id(); }
	
	/*--------------------------------------------------------------------*\
	|* MID-LEVEL FUNCTIONS                                                *|
	\*--------------------------------------------------------------------*/
	/** get() - execute a SQL query and fetch the results as an array of
	 *	associative arrays
	 *  @param $sql		- the SQL statement
	 */
	public function get($sql)
	{
		$a = array();
		$r = $this->query($sql);
		if ($r) for ($i = 0; $i < $this->rows(); $i++)
			$a[$i] = $this->results($r);
		return $a;
	}
	
	/** getCol() - execute a SQL query and fetch an array of one column
	 *  @param $sql		- the SQL statement
	 */
	public function getCol($sql,$col)
	{
		$a = array();
		$r = $this->get($sql);
		foreach ($r as $i => $result) $a[$i] = $result[$col];
		return $a;
	}
	
	/** getRow() - execute a SQL query and fetch a single row
	 *  @param $sql		- the SQL statement
	 */
	public function getRow($sql)
	{
		$r = $this->query($sql);
		if ($r) return $this->results($r);
	}
	
	/** set() - execute a SQL query and return the resource, not results
	 *  @param $sql		- the SQL statement
	 */
	public function set($sql) { return $this->query($sql); }
	
	/*--------------------------------------------------------------------*\
	|* HIGH-LEVEL FUNCTIONS                                               *|
	\*--------------------------------------------------------------------*/
	/* GET                                                                */
	/** select() - run a select statment and return the results
	 *  @param $table	- the table
	 *  @param $cols	- the columns to return, defaults to *
	 *  @param $where	- conditions for a match
	 *  @param $order	- the order of the results
	 *  @param $limit	- the upper limit on the number of results
	 */
	public function select($table,$cols='*',$where='',$order='',$limit='')
	{
		if ($where !== '') $where = " WHERE $where";
		if ($order !== '') $order = " ORDER BY $order";
		if ($limit !== '') $limit = " LIMIT $limit";
		return $this->get("SELECT $cols FROM $table$where$order$limit");
	}
	
	/** selectOne()/select1() - select one row from a table
	 *  @param $table	- the table
	 *  @param $cols	- the columns to return, defaults to *
	 *  @param $where	- conditions for a match
	 *  @param $order	- the order of the results
	 */
	public function selectOne($table,$cols='*',$where='',$order='')
	{
		if ($where !== '') $where = " WHERE $where";
		if ($order !== '') $order = " ORDER BY $order";
		$l = " LIMIT 1";
		return $this->getRow("SELECT $cols FROM $table$where$order$l");
	}
	public function select1($table,$cols='*',$where='',$order='')
	{
		return $this->selectOne($table,$cols,$where,$order);
	}
	
	/** distinct() - select distinct values for columns
	 *  @param $table	- the table
	 *  @param $cols	- the columns to return, defaults to *
	 *  @param $where	- conditions for a match
	 *  @param $order	- the order of the results
	 *  @param $limit	- the upper limit on the number of results
	 */
	public function distinct($table,$cols='*',$where='',$order='',$limit='')
	{
		if ($where !== '') $where = " WHERE $where";
		if ($order !== '') $order = " ORDER BY $order";
		if ($limit !== '') $limit = " LIMIT $limit";
		return $this->get("SELECT DISTINCT $cols FROM ".
			"$table$where$order$limit");
	}
	
	/** distinctCol()/distinct1() - select distinct values for one column
	 *  @param $table	- the table
	 *  @param $col		- the column
	 *  @param $where	- conditions for a match
	 *  @param $order	- the order of the results
	 *  @param $limit	- the upper limit on the number of results
	 */
	public function distinctCol($table,$col,$where='',$order='',$limit='')
	{
		if ($where !== '') $where = " WHERE $where";
		if ($order !== '') $order = " ORDER BY $col $order";
		if ($limit !== '') $limit = " LIMIT $limit";
		return $this->getCol("SELECT DISTINCT $col FROM ".
			"$table$where$order$limit",$col);
	}
	public function distinct1($table,$col,$where='',$order='',$limit='')
	{
		return $this->distinctCol($table,$col,$where,$order,$limit);
	}
	
	/** distinctValueCount - select distinct values for one column and count
	 *  @param $table	- the table
	 *  @param $col		- the column
	 *  @param $where	- conditions for a match
	 *  @param $order	- the order of the results
	 */
	public function distinctValueCount($table,$col,$where='')
	{
		if ($where !== '') $where = " WHERE $where";
		$r = $this->get("SELECT DISTINCT $col,count(1) FROM ".
			"$table$where GROUP BY $col");
		$counts = array();
		foreach ($r as $row)
			$counts[$row[$col]] = $row['count(1)'];
		return $counts;
	}
	
	/* ADD                                                                */
	/** insert() - insert values into a table
	 *  @param $table	- the table
	 *  @param $vals	- the column values
	 *  @param $cols	- the columns to set
	 */
	public function insert($table,$vals='',$cols='')
	{
		if ($vals !== '') $vals = " VALUES ($vals)";
		if ($cols !== '') $cols = " ($cols)";
		return $this->set("INSERT INTO $table$cols$vals");
	}

	/** insertArray() - insert raw array values into a table
	 *  @param $table		- the table
	 *  @param $vals		- array specifying column => value
	 *  @param $associative	- the columns to set
	 */
	function insertArray($table,$vals, $associative=false)
	{	if (!is_array($vals)) return false;
		$colstr=''; $valstr=''; $i = 0;
		foreach($vals as $k=>$v) {
			$colstr .= (($i)?',`':'`').addslashes($k).'`';
			$valstr .= (($i)?',\'':'\'').addslashes($v).'\'';
			$i++;
		}
		return $this->insert($table,$valstr,$colstr);
	} // end else

	
	/* EDIT                                                               */
	/** replace() - insert into table if primary key does not exist, else
	 *	replace the values for that primary key
	 *  @param $table	- the table
	 *  @param $vals	- the column values
	 *  @param $cols	- the columns to set
	 */
	public function replace($table,$vals='',$cols='')
	{
		if ($vals !== '') $vals = " VALUES ($vals)";
		if ($cols !== '') $cols = " ($cols)";
		return $this->set("REPLACE INTO $table$cols$vals");
	}
	
	/** update() - update the values of the table that meet a condition
	 *  @param $table	- the table
	 *  @param $vars	- the column_name = value list
	 *  @param $where	- conditions for a match, defaults to all
	 */
	public function update($table,$vars='',$where='')
	{
		if ($vars !== '') $vars = " SET $vars";
		if ($where !== '') $where = " WHERE $where";
		return $this->set("UPDATE $table$vars$where");
	}
	
	/* DELETE                                                             */
	/** delete() - delete rows from the table that meet a condition
	 *  @param $table	- the table
	 *  @param $where	- conditions for a match, defaults to all
	 */
	public function delete($table,$where='')
	{
		if ($where !== '') $where = " WHERE $where";
		return $this->set("DELETE FROM $table$where");
	}
	
	/* COUNT                                                              */
	/** count() - count rows from the table that meet a condition
	 *  @param $table	- the table
	 *  @param $where	- conditions for a match, defaults to all
	 */
	public function count($table,$where='')
	{
		if ($where !== '') $where = " WHERE $where";
		$row =  $this->getRow("SELECT COUNT(*) FROM $table$where");
		return ($row) ? array_shift($row) : 0;
	}
	
	/*--------------------------------------------------------------------*\
	|* TABLES                                                             *|
	\*--------------------------------------------------------------------*/
	/** tables() - returns the list of tables 
	 */
	public function tables()
	{
		$tablesIn = $this->get("SHOW TABLES;");
		$tables = array();
		foreach ($tablesIn as $i => $tableIn)
			if (is_array($tableIn)&&count($tableIn))
				$tables[$i] = current($tableIn);
		return $tables;
	}
	
	/*--------------------------------------------------------------------*\
	|* COLUMNS                                                            *|
	\*--------------------------------------------------------------------*/
	/** columns() - returns column information 
	 *  @param $table	- the table
	 *  @param $keys	- string or array of info to return
	 * 			(COLUMN_NAME,COLUMN_TYPE,etc.), null means all
	 */
	public function columns($table,$keys=null)
	{
		$columns = $this->get("SHOW COLUMNS FROM $table;");
		if (!$keys) return $columns;
		$cols = array();
		if (!is_array($keys)) $keys = array($keys);
		foreach ($columns as $i => $c) {
			if (count($keys) > 1) {
				$cols[$i] = array();
				foreach ($keys as $k) $cols[$i][$k] = @$c[$k];
			}
			else $cols[$i] = @$c[$keys[0]];
		}
		return $cols;
	}
	
	/** columnNames() - returns column names 
	 *  @param $table	- the table
	 */
	public function columnNames($table)
	{
		return $this->columns($table,self::COLUMN_NAME);
	}
	
	/** columnTypes() - returns a column name => column type array
	 *  @param $table	- the table
	 */
	public function columnTypes($table)
	{
		$columns = $this->columns($table,array(self::COLUMN_NAME,
			self::COLUMN_TYPE));
		$cols = array();
		foreach ($columns as $i => $c)
			$cols[$c[self::COLUMN_NAME]] = $c[self::COLUMN_TYPE];
		return $cols;
	}
	
	/*--------------------------------------------------------------------*\
	|* FORMATTING                                                         *|
	\*--------------------------------------------------------------------*/
	/** timestamp() - format a timestamp
	 *  @param $t	- the time integer
	 */
	public static function timestamp($t=null)
	{
		return date(self::TIMESTAMP_FORMAT,($t === null) ? time() : $t);
	}
	
	/** escape() - escapes inputs to prevent code injection,
	 *	returns as an array
	 *  @param [$args]	- variable length list of arguments
	 */
	public static function escape()
	{
		$vars = func_get_args();
		return self::escapeArray($vars);
	}
	
	/** escape() - escapes inputs to prevent code injection,
	 *	returns as an array
	 *  @param $a	- array of arguments
	 */
	public static function escapeArray($a)
	{
		if (!is_array($a)) return array();
		/* use a method that doesn't require connecting to the db */
		foreach ($a as $i => $v)
			if (is_scalar($v)) $a[$i] = str_replace(
						array("\x00","\n","\r","\x1a"),
						array('\x00','\n','\r','\x1a'),
						addslashes($v));
		return (count($a) == 1) ? current($a) : $a;
	}
	
	/** values() - pass in a variable list of args and return as a comma
	 *	seperated list of values (escape input first)
	 *  @param [$args]	- variable length list of arguments
	 */
	public static function values()
	{
		$vars = func_get_args();
		return self::valuesFromArray($vars);
	}
	public static function valuesFromArray($vars,$associative=false,$d=",")
	{
		if (!is_array($vars)) return "";
		$values = array();
		foreach ($vars as $i => $v) {
			//$v = self::escape($v);
			if (is_numeric($v))	$values[$i] = "'$v'";//$v;
			elseif (is_bool($v))	$values[$i] = ($v) ? 1 : 0;
			elseif (is_string($v))	$values[$i] = "'$v'";
			//else $values[$i] = '"'.addslashes(var_export($v,true)).'"';
			if ($associative) $values[$i] = "`$i`=".$values[$i];
		}
		return implode($d,$values);
	}
	
	/** associativeValues() -  return as a comma seperated list of column
	 *			value pairs (escape input first)
	 *  @param $a		- associative array of column/value pairs
	 */
	public static function associativeValues($a,$d=",")
	{
		return self::valuesFromArray($a,true,$d);
	}
}
?>
<?php
/*	_ Class - A group of wrapper functions that take care of variable
 *		checking/access for arrays and php globals, data "cleaning"
 *		function calling, data outputting, and more.
 *
 *	Copyright (c) 2006-2007 Oliver C Dodd
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
class _
{
	/*--------------------------------------------------------------------*\
	|* ARRAYS                                                             *|
	\*--------------------------------------------------------------------*/
	/** A() - check an array for a specific key and return the value if it
	 * 	exists or a default if it doesn't
	 *  @param $a		array
	 *  @param $k		key
	 *  @param $d		default value
	 *  @param $set		set key to default value if key does not exist?
	 */
	public static function A(&$a,$k,$d='',$set=false)
	{
		if (!is_array($a)) return $d;
		if (!isset($a[$k])) {
			if ($set) $a[$k] = $d;
			return $d; }
		return $a[$k];
	}
	
	/** Aa() - check an array for an array of keys and return the values if
	 * 	they exist or a defaults if they don't
	 *  @param $a		array
	 *  @param $k		array (or csv) of keys
	 *  @param $d		array (or csv) of default values or a single
	 *			default string
	 */
	public static function Aa($a,$ka=array(),$da=array())
	{
		if (!is_array($ka)) $ka = explode(',',$ka);
		if (!is_array($da)) $da = explode(',',$da);
		if ($da&&(count($ka) > count($da)))
			for ($x = count($da);$x < count($ka);$x++) $da[$x] = '';
		$v = array();
		if($da)	foreach ($ka as $i => $k) $v[$i] = _::A($a,$k,$da[$i]);
		else	foreach ($ka as $i => $k) $v[$i] = _::A($a,$k);
		return $v;
	}
	
	/** Ais() - check and array to see if a value at a specific key is
	 *	equivalent to a supplied value
	 *  @param $a		array
	 *  @param $k		key
	 *  @param $v		value to check
	 */
	public static function Ais($a,$k,$v)
	{
		return (is_array($a)&&isset($a[$k])&&($a[$k]===$v))?true:false;
	}
	
	/** Ain() - verify array and check for presence of specified key
	 *  @param $a		array
	 *  @param $k		key
	 */
	public static function Ain($a,$k)
	{
		return (is_array($a)&&isset($a[$k]))?true:false;
	}
	
	/** Anz() - verify array and check for presence of specified key and
	 *          that it is not zero length
	 *  @param $a		array
	 *  @param $k		key
	 */
	public static function Anz($a,$k)
	{
		return (is_array($a)&&isset($a[$k])&&strlen($a[$k]))?true:false;
	}

	/** Asz() - returns the size of the specified array
	 *  @param $a		array
	 */
	public static function Asz($a)
	{
		return is_array($a)?count($a):0;
	}

	/** V() - check an array for a specific key and return the value if it
	 * 	exists or a default if it doesn't
	 *  @param $a		array
	 *  @param $k		key
	 *  @param $d		default value
	 */
	public static function V($a,$k,$d='')
	{
		if (!is_array($a)) return $d;
		if (!isset($a[$k])) return $d;
		return $a[$k];
	}

	/** K() - find the key with a specified value or return a default
	 *  @param $a		array
	 *  @param $v		value
	 *  @param $d		default key
	 */
	public static function K($a,$v,$d='')
	{
		$k = array_search($v,$a,true);
		return ($k !== false) ? $k : $d;
	}
	
	/** Ka() - find all keys with a specified value, return as an array
	 *  @param $a		array
	 *  @param $v		value
	 *  @param $d		default key
	 */
	public static function Ka($a,$v,$d='')
	{
		$ka = array_keys($a,$v,true);
		return ($ka !== array()) ? $ka : array($d);
	}
	
	/** AM() - merge two arrays with an optional recursive flag
	 *  @param $a1		array to be merged into
	 *  @param $a2		merging array
	 *  @param $_r		recursive flag
	 */
	public static function AM($a1,$a2,$_r=true)
	{
		$a = (is_array($a1)) ? $a1 : array();
		if (!is_array($a2)) return $a;
		foreach ($a2 as $k => $v)
			if ($_r&&is_array(_::A($a1,$k))&&is_array($v))
				$a[$k] = _::AM($a1[$k],$v,true);
			else	$a[$k] = $v;
		return $a;
	}
	
	/** AVK() - set the keys of an array to their corresponding values
	 *  @param $a		array
	 */
	public static function AVK($a)
	{
		$avk = array();
		foreach ($a as $v) $avk[$v] = $v;
		return $avk;
	}
	
	/** AKV() - set the values of an array to their corresponding keys
	 *  @param $a		array
	 */
	public static function AKV($a)
	{
		foreach ($a as $k => $v) $a[$k] = $k;
		return $a;
	}
	
	/*--------------------------------------------------------------------*\
	|* OBJECTS/CLASSES                                                    *|
	\*--------------------------------------------------------------------*/
	/** O() - check an object for a specific key/member variable and return
	 * 	the value if it exists or a default if it doesn't
	 *  @param $o		object
	 *  @param $k		key
	 *  @param $d		default value
	 *  @param $set		set key to default value if key does not exist?
	 */
	public static function O(&$o,$k,$d='',$set=false)
	{
		if (!is_object($o)) return $d;
		if (!isset($o->{$k})) {
			if ($set) $o->{$k} = $d;
			return $d; }
		return $o->{$k};
	}
	
	/** OM() - merge two objects or an object and an array */
	public static function OM($o1,$o2)
	{
		$o = (is_object($o1)) ? $o1 : (object)null;
		if (!is_object($o2)&&!is_array($o2)) return $o;
		foreach ($o2 as $k => $v) $o->{$k} = $v;
		return $o;
	}
	
	/*--------------------------------------------------------------------*\
	|* FUNCTIONS                                                          *|
	\*--------------------------------------------------------------------*/
	/** F() - call a function
	 *  @param $n		function name
	 *  @param $args	arguments to be passed to the function
	 *  @param $d		default return value
	 */
	public static function F($n,$args=array(),$d=null)
	{
		if (!is_callable($n)) return $d;
		if (!is_array($args)) $args = explode(',',$args);
		$i = max(strrpos($n,'->'),strrpos($n,'::'));
		$f = ($i) ? array(substr($n,0,$i),substr($n,$i+2)) : $n;
		return call_user_func_array($f,$args);
	}
	
	/** FA() - run a function on the values of an array and return the
	 *	altered array
	 *  @param $a		array
	 *  @param $f		function name
	 *  @param $args	arguments to be passed to the function
	 *  @param $_r		recursive flag
	 */
	public static function FA($a,$f,$args=array(),$_r=false)
	{
		if (!is_callable($f)) return $a;
		if (!is_array($a)) return array();
		if (!is_array($args)) $args = explode(',',$args);
		$i = max(strrpos($f,'->'),strrpos($f,'::'));
		$n = ($i) ? array(substr($f,0,$i),substr($f,$i+2)) : $f;
		$aa = array();
		foreach ($a as $k => $v) {
			$vargs = is_array($v)	? array_merge($v,$args)
						: array_merge(array($v),$args);
			if (is_array($v)&&$_r) $aa[$k] = self::FA($v,$f,$args,$_r);
			else $aa[$k] = call_user_func_array($n,$vargs); }
		return $aa;
	}
	
	/** RF() - call a function using request parameters
	 *  @param $class	class name
	 *  @param $die		exit on completion?
	 */
	public static function RF($class,$die=true)
	{
		//request: class=function&args[]=arg1&args[]=arg2&args[]=arg3..
		$f = _::REQUEST($class);
		if (!$f) return;
		$r = _::F("$class::$f",_::REQUEST('args'));
		if ($die) die($r);
		else return $r;
	}
	
	/*--------------------------------------------------------------------*\
	|* CONSTANTS                                                          *|
	\*--------------------------------------------------------------------*/
	/** C() - check for a constant, return default if not defined
	 *  @param $c		constant name
	 *  @param $d		default value
	 *  @param $define	define if not defined?
	 */
	public static function C($c,$d='',$define=false)
	{
		if (defined($c)) return constant($c);
		if ($define) define($c, $d);
		return $d;
	}
	
	/** Cis() - check whether a constant equals a supplied value */
	public static function Cis($c,$v)
	{
		return (defined($c)&&constant($c)===$v);
	}
	
	/*--------------------------------------------------------------------*\
	|* DISINFECT                                                          *|
	\*--------------------------------------------------------------------*/
	/** clean() - attempts to remove malicious code from user supplied input
	 *  @param $disinfect	use the Disinfect::xss function to clean data?
	 *
	 *  Note: define ALLOWED_TAGS to bypass allowed_tags parameter
	 *  $allowed_tags example: '<a><b><br><center><font><img><span><u>'
	 */
	public static function clean($s,$disinfect=false,$allowed_tags='')
	{
		//clean array/object
		if (is_array($s)||is_object($s)) {
			foreach ($s as $k => $v)
				$s[$k] = _::clean($v,$disinfect,$allowed_tags);
			return $s;
		}
		//if constant ALLOWED_TAGS is defined, use instead of parameter
		$allowed_tags = _::C('ALLOWED_TAGS',$allowed_tags);
		//clean string
		if (get_magic_quotes_gpc()) $s = stripslashes($s);
		$s = rawurldecode($s);
		return $disinfect
			? (class_exists('Disinfect')
				? Disinfect::xss($s,$allowed_tags)
				: strip_tags($s,$allowed_tags))
			: htmlentities($s);
	}
	
	/** uA() - treat the supplied array as user input and return a clean
	 *	value from a supplied key
	 *  @param $a			array
	 *  @param $k			key
	 *  @param $d			default
	 *  @param $disinfect		pass the disinfect flag into clean()?
	 *  @param $allowed_tags	the allowed html tags
	 */
	public static function uA($a,$k,$d='',$disinfect=false,$allowed_tags='')
	{
		return _::clean(_::A($a,$k,$d),$disinfect,$allowed_tags);
	}
	
	/*--------------------------------------------------------------------*\
	|* PHP GLOBAL ARRAYS                                                  *|
	\*--------------------------------------------------------------------*/
	/** GLOBALS() - checks the $_SERVER global array */
	public static function GLOBALS($k,$d='') { return _::A($GLOBALS,$k,$d); }
	
	/** SERVER() - checks the $_SERVER global array */
	public static function SERVER($k,$d='')
	{
		return isset($_SERVER) ? _::A($_SERVER,$k,$d) : $d;
	}
	
	/** SESSION() - checks the $_SESSION global array */
	public static function SESSIONis($k,$v) { return _::Ais($_SESSION,$k,$v); }
	public static function SESSIONin($k) { return _::Ain($_SESSION,$k); }
	public static function SESSIONnz($k) { return _::Anz($_SESSION,$k); }
	public static function SESSION($k,$d='',$set=false)
	{
		return (isset($_SESSION)) ? _::A($_SESSION,$k,$d,$set) : $d;
	}
	
	/** FILES() - checks the $_FILES global array */
	public static function FILES($k,$d='') {return _::A($_FILES,$k,$d); }
	
	/** GET() - checks the $_GET global array */
	public static function GET($k,$d='') { return _::A($_GET,$k,$d); }
	public static function GETa($ka,$da=array()) { return _::Aa($_GET,$ka,$da); }
	public static function GETis($k,$v) { return _::Ais($_GET,$k,$v); }
	public static function GETin($k) { return _::Ain($_GET,$k); }
	public static function GETnz($k) { return _::Anz($_GET,$k); }
	public static function uGET($k,$d='',$disinfect=false,$allowed_tags='')
	{
		return _::uA($_GET,$k,$d,$disinfect,$allowed_tags);
	}
	public static function cleanGET($disinfect=false,$allowed_tags='')
	{
		$_GET = _::clean($_GET,$disinfect,$allowed_tags);
		return $_GET;
	}
	
	/** POST() - checks the $_POST global array
	 * if POST varibles sent through AJAX aren't appearing, try setting the
	 * raw flag to true so that the HTTP_RAW_POST_DATA will be added to the
	 * POST array
	 */
	public static function POST($k,$d='',$raw=false)
	{
		if ($raw) _::rawPOST();
		return _::A($_POST,$k,$d);
	}
	public static function POSTa($ka,$da=array()) { return _::Aa($_POST,$ka,$da); }
	public static function POSTis($k,$v) { return _::Ais($_POST,$k,$v); }
	public static function POSTin($k) { return _::Ain($_POST,$k); }
	public static function POSTnz($k) { return _::Anz($_POST,$k); }
	public static function uPOST($k,$d='',$disinfect=false,$allowed_tags='')
	{
		return _::uA($_POST,$k,$d,$disinfect,$allowed_tags);
	}
	public static function cleanPOST($disinfect=false,$allowed_tags='')
	{
		$_POST = _::clean($_POST,$disinfect,$allowed_tags);
		return $_POST;
	}
	/** rawPOST() - add HTTP_RAW_POST_DATA to the $_POST global array */
	public static function rawPOST()
	{
		if (!array_key_exists('HTTP_RAW_POST_DATA', $GLOBALS)) return;
		$pairs = explode('&',$GLOBALS['HTTP_RAW_POST_DATA']);
		foreach ($pairs as $p) {
			$kv = explode('=', $p);
			if (count($kv) != 2) continue;
			if (!array_key_exists($kv[0], $_POST))
				$_POST[$kv[0]] = $kv[1];
		}
	}
	
	/** REQUEST() - checks the $_REQUEST global array */
	public static function REQUEST($k,$d='') { return _::A($_REQUEST,$k,$d); }
	public static function REQUESTa($ka,$da=array())
	{
		return _::Aa($_REQUEST,$ka,$da);
	}
	public static function REQUESTis($k,$v) { return _::Ais($_REQUEST,$k,$v); }
	public static function uREQUEST($k,$d='',$disinfect=false,$allowed_tags='')
	{
		return _::uA($_REQUEST,$k,$d,$disinfect,$allowed_tags);
	}
	public static function cleanREQUEST($disinfect=false,$allowed_tags='')
	{
		$_REQUEST = _::clean($_REQUEST,$disinfect,$allowed_tags);
		return $_REQUEST;
	}
	
	/** AJAX - returns a boolean indicating whether this is an AJAX request
	 *	note: this implementation relies on prototype.js
	 */
	public static function AJAX()
	{
		return isset($_SERVER['HTTP_X_REQUESTED_WITH']);
	}
	
	/*--------------------------------------------------------------------*\
	|* URL                                                                *|
	\*--------------------------------------------------------------------*/
	public static function URL($full=true)
	{
		$h = _::SERVER('SERVER_NAME');
		$s = _::SERVER('PHP_SELF');
		$q = _::SERVER('QUERY_STRING');
		$p = _::SERVER('SERVER_PORT');
		$p = $p != 80 ? ":$p" : "";
		return	($full ? "http://$h$p" : "").$s.($q ? "?$q" : "");
	}
	public static function SCRIPT() { return _::SERVER('PHP_SELF'); }
	
	/*--------------------------------------------------------------------*\
	|* PRINT/OUTPUT                                                       *|
	\*--------------------------------------------------------------------*/
	/** pr() - print a variable using print_r with options
	 *  @param $var		the variable to print
	 *  @param $return	return the output (true) or echo it (false)
	 *  @param $nl2br	convert newlines to <br />?
	 */
	public static function pr($var,$return=false,$nl2br=true)
	{
		$output = print_r($var,true);
		if ($nl2br) $output = nl2br($output);
		if ($return) return $output;
		else echo $output;
	}
	
	/** export() - dump/export variable as a PHP usable string
	 *  @param $var		the variable to export
	 *  @param $return	return the output (true) or echo it (false)
	 *  @param $nl2br	convert newlines to <br />?
	 */
	public static function export($var,$return=true,$nl2br=false)
	{
		$output = var_export($var,true);
		if ($nl2br) $output = nl2br($output);
		if ($return) return $output;
		else echo $output;
	}
	
	/** import() - import a variable dumped as a string using _::export()
	 */
	public static function import($s) { return @eval("return $s;"); }

	public static $_incparams = array();

	/** p() - returns parameters passed to include script using inc
	 */
	public static function p( $v, $d = '' ) 
	{ return ( isset( self::$_incparams[ $v ] ) ) ? self::$_incparams[ $v ] : $d; }

	/** pa() - returns entire script parameter array
	 */
	public static function pa() { return self::$_incparamsd; }

	/** inc() - Includes a script, passing parameters and returning the script output
	 *			output as a string.
	 *	@param $f	Script filename
	 */
	public static function inc( $f ) 
	{	$old = self::$_incparams; 
		self::$_incparams = func_get_args();
		ob_start(); 
		include( $f ); 	
		self::$_incparams = $old; 
		return ob_get_clean(); 
	}

}
?>
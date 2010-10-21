<?php
/*	+ PHP function collection - miscellaneous PHP functions that don't
 *		belong to any specific category
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
if (!defined('SCRIPT_START_TIME')) define('SCRIPT_START_TIME', microtime(true));
/*----------------------------------------------------------------------------*\
|* EXECUTION TIME                                                             *|
\*----------------------------------------------------------------------------*/
/** executionTime() - determine the execution time so far
 *  @param $t0	= the script start time, if not supplied uses either
 *		SCRIPT_START_TIME or $_SERVER['REQUEST_TIME']
 *  @param $now	= the time now, not required
 */
function executionTime($t0=null,$tN=null)
{
	if ($tN === null) $tN = microtime(true);
	$requestTime = (isset($_SERVER["REQUEST_TIME"]))
		? $_SERVER["REQUEST_TIME"] : round(SCRIPT_START_TIME);
	if ($t0 === null)
		$t0 = (floor(SCRIPT_START_TIME) > $_SERVER["REQUEST_TIME"])
			? $_SERVER["REQUEST_TIME"] : SCRIPT_START_TIME;
	return $tN - $t0;
}

/*----------------------------------------------------------------------------*\
|* SCRIPT ENTRY POINT                                                         *|
\*----------------------------------------------------------------------------*/
/** entryPoint() - return the top level/entry point script or, if $f
 *	parameter is supplied, returns whether $f is the top level
 *  @param $f	= script/file to check
 */
function entryPoint($f=null)
{
	$s = (isset($_SERVER['SCRIPT_NAME'])) ? $_SERVER['SCRIPT_NAME'] : null;
	$p = (isset($_SERVER['SCRIPT_FILENAME']))
		? realpath($_SERVER['SCRIPT_FILENAME'])
		: null;
	return ($f === null) ? $s : ($p === realpath($f));
}

/*----------------------------------------------------------------------------*\
|* VALUES/DEFINITIONS                                                         *|
\*----------------------------------------------------------------------------*/
/** def() - define a constant if not already defined
 *  @param $c	= the constant's name
 *  @param $v	= the constant's value
 *  @return	false	- the constant was already defined or error defining
 *		true	- the constant was defined with the supplied value
 */
function def($c,$v='') { return (defined($c)) ? false : define($c,$v); }

/*----------------------------------------------------------------------------*\
|* ARRAYS                                                                     *|
\*----------------------------------------------------------------------------*/
/** array_pluck() - get a value from an array at index k and then remove it
 *  @param $a	= the array
 *  @param $k	= the key/index
 */
function array_pluck(&$a,$k)
{
	if (!isset($a[$k])) return null;
	$v = $a[$k];
	unset($a[$k]);
	return $v;
}

/** array_diff_keys() - return unique keys and their values from multiple arrays
 */
function array_diff_keys()
{
	$a = array();
	$d = array();
	$args = func_get_args();
	foreach ($args as $i => $arg) {
		if (!is_array($arg)) continue;
		foreach ($arg as $k => $v) {
			if (!isset($a[$k])) $a[$k] = $v;
			else array_push($d,$k);
		}
	}
	foreach ($d as $dk) if (isset($a[$dk])) unset($a[$dk]);
	return $a;
}

/** array_flatten() - flatten a multidimensional array into one dimension
 *  @param $a	= the array
 *  @param $kd	= the key delimiter
 *  @param $kp	= the key prefix (used in the recursion)
 *  @param $f	= the flattened array (used in the recursion)
 */
function array_flatten($a=array(),$kd='',$kp='',&$f=array())
{
	foreach ($a as $k => $v) {
		if ($kp) $k = $kp.$kd.$k;
		if (is_array($v))	array_flatten($v,$kd,$k,$f);
		else 			$f[$k] = $v;
	}
	return $f;
}

/** array_duplicates() - return all duplicate values in an array
 *  @param $a	= the array
 */
function array_duplicates($a=array())
{
	$unique = array_unique($a);
	foreach($unique as $k => $v)
		unset($a[$k]);
	return array_unique($a);
}

/*----------------------------------------------------------------------------*\
|* EQUALITY                                                                   *|
\*----------------------------------------------------------------------------*/
/** allEqual() - are all passed in values equal?
 *  @param $n1,$n2,$n3,...	= the values to test
 */
function allEqual() { return allEqualValues(func_get_args()); }

/** allEqualValues() - are all values of an array equal?
 *  @param $a	= the array
 */
function allEqualValues($a) { return (count(array_unique($a)) == 1); }

/** allEqualCount() - are all sizes of the passed in arrays equal?
 *  @param $n1,$n2,$n3,...	= the values to test  
 */
function allEqualCount()
{
	$args = func_get_args();
	$counts = array();
	foreach ($args as $i => $arg) {
		if (!is_array($arg)) return false;
		$counts[$i] = count($arg); }
	return allEqualValues($counts);
}

/** allEqualLength() - are all string lengths of passed in values equal?
 *  @param $n1,$n2,$n3,...	= the values to test 
 *	*note: passing in arrays, objects, resources, etc. could
 *	cause problems as, for simplicity's sake, generated errors are ignored
 */
function allEqualLength()
{
	$args = func_get_args();
	$lengths = array();
	foreach ($args as $i => $arg) $lengths[$i] = @strlen($arg);
	return allEqualValues($lengths);
}

/*----------------------------------------------------------------------------*\
|* URL DECODE                                                                 *|
\*----------------------------------------------------------------------------*/
function rawurldecodearray($a)
{
	foreach ($a as $k => $v) $a[$k] = rawurldecode($v);
	return $a;
}

/*----------------------------------------------------------------------------*\
|* EMPTY                                                                      *|
\*----------------------------------------------------------------------------*/
/** anyEmpty() - check to see if any of the passed in values are empty
 */
function anyEmpty()
{
	$args = func_get_args();
	foreach ($args as $i => $arg)
		if (empty($arg)) return true;
	return false;
}
function anyEmptyValues($a=array())
{
	if (!is_array($a)) return empty($a);
	foreach ($args as $i => $arg)
		if (empty($arg)) return true;
	return false;
}
?>
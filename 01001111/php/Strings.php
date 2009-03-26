<?php
/*	String functions - additional string functions
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

/*----------------------------------------------------------------------------*\
|* CHARACTER CODES                                                            *|
\*----------------------------------------------------------------------------*/
/** Convert a string to an array of character codes
 *  @param $s	= the string
 */
function orda($s)
{
	$a = str_split($s);
	foreach ($a as $k => $v) $a[$k] = ord($v);
	return $a;
}

/** Convert a delimited list of character codes to an array of characters
 *  @param $cc	= the list of character codes
 *  @param $d	= the delimiter, defaults to a comma
 */
function chra($cc,$d=',')
{
	$a = explode($d,$cc);
	foreach ($a as $k => $v) $a[$k] = chr($v);
	return $a;
}

/** str2charcodes() - Convert a string to a delimited list of character codes
 *  @param $s	= the string
 *  @param $d	= the delimiter, defaults to a comma
 */
function str2charcodes($s,$d=',') { return implode($d,orda($s)); }

/** charcodes2str() - Convert a delimited list of character codes to a string
 *  @param $cc	= the list of character codes
 *  @param $d	= the delimiter, defaults to a comma
 */
function charcodes2str($cc,$d=',') { return implode('',chra($cc,$d)); }

/*----------------------------------------------------------------------------*\
|* NUMBER PARSING                                                             *|
\*----------------------------------------------------------------------------*/

/** parsenums() - parse a string for all numbers and return as an array
 *  @param $s	= the string
 */
function parsenums($s)
{
	$a = array();
	preg_match_all('%-{0,1}[0-9]*\.{0,1}[0-9]+%',$s,$a);
	return (isset($a[0])) ? $a[0] : array();
}

/** parsenum() - return the first encountered number in a string
 *  @param $s		= the string
 *  @param $startindex	= optional start index for the search
 */
function parsenum($s,$startindex=0)
{
	$s = substr($s,$startindex);
	$a = array();
	preg_match('%-{0,1}[0-9]*\.{0,1}[0-9]+%',$s,$a);
	return (isset($a[0])) ? $a[0] : "";
}

/** roundto() - round to a certain number of decimals and include trailing zeros
 *  @param $n	= the number
 *  @param $d	= the number of decimal places
 */
function roundto($n,$d) { return sprintf("%01.{$d}f",round($n,$d)); }

/*----------------------------------------------------------------------------*\
|* FORMATTING                                                                 *|
\*----------------------------------------------------------------------------*/

/** strchunk() - chunk a string into segments of length n, delimited by d
 *	and padded with char p
 *  @param $s	= the string
 *  @param $n	= the length of the chunk
 *  @param $d	= the delimiter character, defaults to " "
 *  @param $p	= the padding character, defaults to ""
 *  @param $t	= the padding type: STR_PAD_LEFT, STR_PAD_RIGHT,
 *		or STR_PAD_BOTH, defaults to LEFT
 */
function strchunk($s,$n=0,$d=' ',$p='',$t=STR_PAD_LEFT)
{
	if ($n === 0) return $s;
	$l = strlen($s);
	$m = ($n > $l) ? $n - $l : ceil($l/$n)*$n - $l;
	if ($p != "") $s = str_pad($s,$l+$m,$p,$t);
	$i = (($p !== "")&&($p !== null)) ? $n : $n-$m;
	$c = substr($s,0,$i);
	for ($i; $i < strlen($s); $i+=$n) $c .= $d.substr($s,$i,$n);
	return $c;
}

/** formatSize() - return a human readable size from a number of bytes
 *  @param $b	= the number of bytes
 *  @param $bpk	= bytes/kilobyte?  defaults to 1024 but 1000 can be used
 */
function formatSize($b=0,$bpk=1024)
{
	$sym = array('','k','M','G','T','P','E','Z','Y');
	$i = 0; while (($b >= $bpk)&&($i++ < count($sym))) $b /= $bpk;
	return round($b,2).$sym[$i].'B';
}

/*----------------------------------------------------------------------------*\
|* OUTPUT                                                                     *|
\*----------------------------------------------------------------------------*/
/** println() - print out a string followed by a newline (auto detects <br />)
 *  @param $s	= the string
 */
function println($s)
{
	$nl = (isset($_SERVER['SERVER_NAME'])) ? '<br />' : "\n";
	return print($s.$nl);
}

/*----------------------------------------------------------------------------*\
|* MISC                                                                       *|
\*----------------------------------------------------------------------------*/
/** makestr() - create a string of character(s) c repeated n times
 *  @param $n	= the length of the string
 *  @param $c	= the character(s)
 */
function makestr($n=0,$c=' ')
{
	$s = '';
	if (($n <= 0)||!is_numeric($n)||(strlen($c) <= 0)) return $s;
	while ($n--) $s .= $c;
	return $s;
}

/** spaces() - create a string of length n containing spaces
 *  @param $n		= the length of the string
 *  @param $nbsp	= html nonbreaking spaces?
 */
function spaces($n=0,$nbsp=false) { return makestr($n,($nbsp) ? '&nbsp;':' '); }

/** overtheyears() - year range beginning with the supplied year and ending now
 *  @param $y0		= year zero
 */
function overtheyears($y0)
{
	$yN = date("Y");
	return $y0 == $yN ? $y0 : "$y0 - $yN";
}
?>
<?php
/*	Math function collection - some helpful math functions
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

/** bound() - ensure a number falls between a min and a max value
 *  @param $n	= the number
 *  @param $min	= minimum value
 *  @param $max	= maximum value
 */
function bound($n=0,$min=0,$max=0)
{
	//if min > max, switch
	if ($min > $max) { $m = $min; $min = $max; $max = $m; }
	return min(max($n,$min),$max);
}

/** rollover() - roll a number over if it surpasses a certain value
 *  @param $n	= the number
 *  @param $max	= maximum value
 */
function rollover($n=0,$max=0)
{
	if ($max === 0) return 0;
	while($n < 0) $n += $max;
	while($n >= $max) $n -= $max;
	return $n;
}

/** dec2base() - convert a decimal integer to another base.  Differs from
 *	base_convert in that you can specify the symbols
 *  @param $d	= the decimal number
 *  @param $n	= the base - if 0 or undefined defaults to the length of sym
 *  @param $sym	= an array or string of symbols, defaults to alphanumeric chars
 */
function dec2base($d=0,$n=0,$sym="0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ")
{
	if (!is_array($sym)) $sym = str_split($sym);
	$n = (($n === null)||($n <= 1)) ? count($sym) : $n;
	$s = "";
	do { $s = $sym[$d%$n].$s; $d = floor($d/$n); } while($d>0);
	return $s;
}

/** base2dec() - convert a decimal integer to another base.  Differs from
 *	base_convert in that you can specify the symbols
 *  @param $s	= the number or string
 *  @param $n	= the base - if 0 or undefined defaults to the length of sym
 *  @param $sym	= an array or string of symbols, defaults to alphanumeric chars
 */
function base2dec($s=0,$n=0,$sym="0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ")
{
	if (!is_array($sym)) $sym = str_split($sym);
	$n = (($n === null)||($n <= 1)) ? count($sym) : $n;
	$d = 0;
	$s = str_split(strtoupper($s));
	for ($i = 0; $i < count($s); $i++)
		$d += _::K($sym,$s[$i],0) * pow($n,count($s)-$i-1);
	return $d;
}

/** logn() - logarithm with a base n
 *  @param $x	= the number
 *  @param $n	= the base
 */
function logn($x=0,$n=10) { return ($n >= 1) ? log10($x)/log10($n) : 0; }

/** mod() - modulus function, because x%y doesn't work, requires x % y
 *  @param $x	= the number
 *  @param $y	= the divisor
 */
function mod($x=0,$y=1) { return ($x % $y); }
?>
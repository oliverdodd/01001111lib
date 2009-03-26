<?php
/* 	Page - Rapidly create a web page layout based on the 01001111.css class.
 *	
 *	Copyright (c) 2006 Oliver C Dodd
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
class Page
{
	/*--------------------------------------------------------------------*\
	|* CONSTANTS                                                          *|
	\*--------------------------------------------------------------------*/
	//	content location	anchored sections, order based on CSS
	//	--------------
	//     | tl | tc | tr |		 -------------
	//      --------------		|      t      |
	//     | cl | cc | cr |		|l           r|
	//      --------------		|      b      |
	//     | bl | bc | br |		 -------------
	//      --------------
	const T = 0;
	const R = 1;
	const B = 2;
	const L = 3;
	const C = 4;
	
	/*--------------------------------------------------------------------*\
	|* FUNCTIONS                                                          *|
	\*--------------------------------------------------------------------*/
	/** PAGE BUILDER
	 *  @param c		- the main content of the page
	 *  @param v		- the vertical alignment of the page
	 *  @param h		- the horizontal alignment of the page
	 *  @param a		- an array of anchored sections
	 *  @param xclass	- any extra classes for the page
	 *  @param ltable	- use a table for layouts (to enforce cross-platform support)
	 */
	public static function build($c='',$v=self::T,$h=self::L,$a=array(),
		$xclass='',$ltable=false)
	{
		//alignment
		$ha = self::ha($h);
		$hac = self::hac($h);
		$vac = self::vac($v);
		$ac = "$hac $vac";
		//anchored edge sections
		$fa = self::faes($a,$ltable);
		//page
		$pg = self::div($c,'pg',"$ha $xclass");
		//build
		if ($ltable) return
			'<table class="glue">'.
				self::gluetd(self::div($c,'pg',$ha),$ac,1).
				$fa[self::T].$fa[self::L].$fa[self::R].$fa[self::B].
			'</table>';
		else {	if ($v == self::C || $v == self::B) $pg = self::glue($pg,$ac);
			return	$fa[self::T].$fa[self::L].$fa[self::R].
				(($v==self::C||$v==self::B) ? self::glue($pg,$ac) : $pg).
				$fa[self::B]; }
	}
	
	/** canvas/glue method of positioning the page
	 *  @param pg		- the page
	 *  @param ac		- the alignnemt classes
	 */
	public static function glue($pg='',$ac='')
	{
		return self::div(self::div($pg,'','glue '.$ac),'','canvas '.$ac);
	}
	
	/** Format the anchored edge sections
	 *  @param a		- an array of anchored edge section content
	 *  @param table	- is this going in a table?
	 */
	public static function faes($a=array(),$table=false)
	{
		$f = array();
		$f[self::T] = isset($a[self::T]) ? self::div($a[self::T],'top-edge') : '';
		$f[self::R] = isset($a[self::R]) ? self::div($a[self::R],'right-edge') : '';
		$f[self::B] = isset($a[self::B]) ? self::div($a[self::B],'bottom-edge') : '';
		$f[self::L] = isset($a[self::L]) ? self::div($a[self::L],'left-edge') : '';
		return $f;
	}
	
	/** div wrapper function
	 *  @param c		- the div content
	 *  @param id		- the div id
	 *  @param class	- the div class
	 */
	public static function div($c='',$id='',$class='')
	{
		if ($id) $id = ' id="'.$id.'"';
		if ($class) $class = ' class="'.$class.'"';
		return "<div$id$class>$c</div>";
	}
	
	/** td/tr glue function
	 *  @param c		- the td content
	 *  @param class	- the extra td class
	 *  @param tr		- wrap in a tr?
	 */
	public static function gluetd($c='',$class='',$tr=false)
	{
		$class = ' class="glue '.$class.'"';
		$colspan = ($tr) ? ' colspan="'.$tr.'"' : '';
		$td = "<td$class$colspan>$c</td>";
		return ($tr) ? self::gluetr($td) : $td;
	}
	public static function gluetr($c='')
	{
		return '<tr class="glue">'.$c.'</tr>';
	}
	
	/*--------------------------------------------------------------------*\
	|* ALIGNMENT                                                          *|
	\*--------------------------------------------------------------------*/
	//horizontal alignment
	public static function ha($h)
	{
		if ($h == self::L)	return 'a-left';
		if ($h == self::C)	return 'a-center';
		if ($h == self::R)	return 'a-right';
					return 'a-left';
	}
	//horizontal content alignment
	public static function hac($h)
	{
		if ($h == self::L)	return 'ac-left';
		if ($h == self::C)	return 'ac-center';
		if ($h == self::R)	return 'ac-right';
					return 'ac-left';
	}
	//vertical content alignment
	public static function vac($v)
	{
		if ($v == self::T)	return 'ac-top';
		if ($v == self::C)	return 'ac-middle';
		if ($v == self::B)	return 'ac-bottom';
					return 'ac-top';
	}
}
?>
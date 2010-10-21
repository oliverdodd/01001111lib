<?php
/*	Obfuscate Class and functions - obfuscating data for transferal between
 *		php and javascript.
 *
 *	Dependencies:	01001111 String methods, Prototype.js
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
class Obfuscate
{
	/*--------------------------------------------------------------------*\
	|* PHP                                                                *|
	\*--------------------------------------------------------------------*/
	/** htmlentities() - Encode all characters as HTML entities.
	 *  @param $s	= the string
	 */
	public static function htmlentities($s)
	{
		$entities = '';
		$sa = str_split($s);
		foreach ($sa as $c) $entities .= "&#".ord($c).";";
		return $entities;
	}
	
	/*--------------------------------------------------------------------*\
	|* PHP -> JAVASCRIPT                                                  *|
	\*--------------------------------------------------------------------*/
	/** php2js() - Abstract function that creates a html container and a
	 *	javascript segment to set the contents of the container.  Pass
	 *	a javascript function call to convert a php obfuscated string
	 *	back into the original.  See below implementations for examples.
	 *  @param $f	= the function call
	 */
	public static function php2js($f)
	{
		$id = sha1(microtime().$f);
		return	"<span id='$id'></span>".
			"<script type='text/javascript'>".
				"Element.update('$id',$f);".
			"</script>";
	}
	
	/** jsCharcodes() - Convert a string to a delimited list of
	 *	character codes in php and then convert back in javascript.
	 *  @param $s	= the string
	 */
	public static function jsCharcodes($s)
	{
		return self::php2js('String.fromCharCodes(['.str2charcodes($s).'])');
	}
	
	/** jsBackwards() - Reverse a string and use js to correct it.
	 *  @param $s	= the string
	 */
	public static function jsBackwards($s)
	{
		return self::php2js('("'.strrev($s).'").reverse()');
	}
	
	/*--------------------------------------------------------------------*\
	|* PHP + CSS                                                          *|
	\*--------------------------------------------------------------------*/
	
	/** phpcss() - Abstract function that creates a html container with a 
	 *	certain id or class that corresponds to a css style and php
	 *	generated content.
	 *  @param $s		= the string
	 *  @param $id		= the span id
	 *  @param $class	= the span class
	 */
	public static function phpcss($s,$id='',$class='')
	{
		$id = ($id) ? ' id="'.$id.'"' : '';
		$class = ($class) ? ' class="'.$class.'"' : '';
		return "<span$id$class>$s</span>";
	}
	
	/** cssBackwards() - Reverse a string and use css to correct it.
	 *	Requires 01001111.css
	 *  @param $s	= the string
	 */
	public static function cssBackwards($s)
	{
		return self::phpcss(strrev($s),'','backwards');
	}
}
?>
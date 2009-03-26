<?php
/*	_01001111 Class - 01001111 lib helper class
 *
 *	Copyright (c) 2008 Oliver C Dodd
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
def('d01001111', dirname(dirname(__FILE__)).'/');
def('pLIB', Filesystem::relativePath(dirname(d01001111)).'/');
def('p01001111', Filesystem::relativePath(d01001111,true,true));
class _01001111
{
	/*--------------------------------------------------------------------*\
	|* AUTOLOAD                                                           *|
	\*--------------------------------------------------------------------*/
	public static function autoload($c)
	{
		$f = Filesystem::find("$c.php",d01001111);
		if ($f) include_once $f;
		return $f ? $f : false;
	}
	
	/*--------------------------------------------------------------------*\
	|* JAVASCRIPT/CSS                                                     *|
	\*--------------------------------------------------------------------*/
	public static function js($tags=false)
	{
		$files = array(	pLIB.'js/prototype.js',
				pLIB.'js/effects.js',
				pLIB.'js/window.js',
				pLIB.'js/sha1.js',
				pLIB.'js/base64.js',
				p01001111.'js/+.js',
				p01001111.'js/dom+.js',
				p01001111.'js/Color.js',
				p01001111.'js/Flipbook.js',
				p01001111.'js/Validate.js');
		return $tags ? XHTML::javascripts($files) : $files;
	}
	public static function css($tags=false)
	{
		$files = array(	p01001111.'css/01001111.css');
		return $tags ? XHTML::stylesheets($files) : $files;
	}
}
?>
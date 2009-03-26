<?php
/*	Cookie class - A simple interface for managing Cookies
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

class Cookie
{
	/*--------------------------------------------------------------------*\
	|* GET/SET                                                            *|
	\*--------------------------------------------------------------------*/
	/** get() - get value of cookie for key and return the value if it
	 * 	exists or a default if it doesn't
	 *  @param $k		key
	 *  @param $d		default value
	 *  @param $set		set key to default value if key does not exist?
	 */
	public static function get($k,$d='',$set=false)
	{
		if (!isset($_COOKIE)) return $d;
		if (!isset($_COOKIE[$k])) {
			if ($set) Cookie::set($k,$d);
			return $d; }
		return $_COOKIE[$k];
	}
	/** set() - set value of cookie for key k
	 *  @param $k		key
	 *  @param $v		value
	 *  @param $expire	expiration time (UNIX timestamp)
	 */
	public static function set($k,$v='',$expire=0) { setcookie($k,$v,$expire); }
	
	/*--------------------------------------------------------------------*\
	|* DELETE/CLEAR                                                       *|
	\*--------------------------------------------------------------------*/
	/** delete() - delete cookie key k
	 *  @param $k		key
	 */
	public static function delete($k)
	{
		Cookie::set($k,'',1);
		if (isset($_COOKIE)&&isset($_COOKIE[$k])) unset($_COOKIE[$k]);
	}
	
	/** clear() - clear all cookies
	 */
	public static function clear()
	{
		if (!isset($_COOKIE)) return;
		foreach ($_COOKIE as $k => $v) Cookie::delete($k);
	}
}
?>
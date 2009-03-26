<?php
/*	Validate Class and functions - validate variables with regular
 *		expressions and callbacks.
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
class Validate
{
	/*--------------------------------------------------------------------*\
	|* COMMON REGULAR EXPRESSIONS                                         *|
	\*--------------------------------------------------------------------*/
	const NOT_EMPTY		= '/.+/';
	const NUMBER		= '/^[-+]?\\b[0-9]*\\.?[0-9]+\\b$/';
	const ALPHA		= '/^[A-Za-z]+$/';
	const ALPHA_WS 		= '/^[A-Za-z\s]+$/';
	const ALPHANUMERIC 	= '/^[A-Za-z0-9]+$/';
	const ALPHANUMERIC_WS	= '/^[A-Za-z0-9\s]+$/';
	
	const SAFE		= '/^[^"\'<>\\\]+$/';
	const USERNAME		= '/^[\w@.]+$/';
	
	const IP		= '/^([1-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])(\.([0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])){3}$/';
	
	const YEAR		= '/^[0-9]{4}$/';//'/^[12][0-9]{3}$/';
	/* timestamp */
	const DATE		= '/^(\d{4})-(0[1-9]|1[012])-(0[1-9]|[12]\d|3[01])$/';
	const TIME		= '/^([0-1]\d|2[0-3]):([0-5]\d):([0-5]\d)$/';
	
	const EMAIL		= '/^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$/i';
	const PHONE_NUMBER	= '/^[1-9]\d{2}-\d{3}\-\d{4}$/';
	const ZIP_CODE		= '/^\d{5}$/';
	
	/*--------------------------------------------------------------------*\
	|* VALIDATE                                                           *|
	\*--------------------------------------------------------------------*/
	public static function v($var,$validators=array())
	{
		if ($validators === null) return 1;
		if (is_scalar($validators)) $validators = array($validators);
		$matches = array();
		foreach ($validators as $v) {
			if (!$v) continue;
			elseif (is_callable($v)) {
				if (!call_user_func_array($v,$var)) return 0;
			}
			elseif(!preg_match_all($v,$var,$matches)) return 0;
		}
		return 1;
	}
}
?>
<?php
/**	include - include the 01001111 php, js, and css library files.
 *
 *	Copyright (c) 2006-2008 Oliver C Dodd
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
|* CURRENT VERSION / LAST UPDATE                                              *|
\*----------------------------------------------------------------------------*/
define('v01001111',	20100105);
/*----------------------------------------------------------------------------*\
|* START TIME                                                                 *|
\*----------------------------------------------------------------------------*/
define('SCRIPT_START_TIME', microtime(true));
/*----------------------------------------------------------------------------*\
|* ABSOLUTE PATH                                                              *|
\*----------------------------------------------------------------------------*/
define('d01001111', dirname(__FILE__).'/');
/*----------------------------------------------------------------------------*\
|* CORE PHP INCLUDES                                                          *|
\*----------------------------------------------------------------------------*/
include_once(d01001111.'php/_.php');
include_once(d01001111.'php/+.php');
include_once(d01001111.'php/Filesystem.php');
include_once(d01001111.'php/Cookie.php');
include_once(d01001111.'php/Disinfect.php');
include_once(d01001111.'php/HTTP.php');
include_once(d01001111.'php/IP.php');
include_once(d01001111.'php/Image.php');
include_once(d01001111.'php/Math.php');
include_once(d01001111.'php/Socket.php');
include_once(d01001111.'php/Strings.php');
include_once(d01001111.'php/Validate.php');
include_once(d01001111.'php/XHTML.php');
include_once(d01001111.'php/_01001111.php');
/*----------------------------------------------------------------------------*\
|* OMITTED INCLUDES                                                           *|
\*----------------------------------------------------------------------------*/
/** The following classes are omitted from the core 01001111 library includes
 *  due to potential naming conflicts or because they are not commonly used.
 * 	Uncomment, explicitly include, or use _01001111::autoload to load them.
 */
//include_once(d01001111.'php/Color.php');
//include_once(d01001111.'php/Database.php');
//include_once(d01001111.'php/Graphics.php');
//include_once(d01001111.'php/Logger.php');
//include_once(d01001111.'php/Obfuscate.php');
//include_once(d01001111.'php/PHP.php');
//include_once(d01001111.'php/Page.php');
//include_once(d01001111.'php/Random.php');
//include_once(d01001111.'php/Seriailze.php');
//include_once(d01001111.'php/System.php');
/*----------------------------------------------------------------------------*\
|* JSON FOR PHP < 5.2.0                                                       *|
\*----------------------------------------------------------------------------*/
if (!function_exists('json_encode')) {
	require_once dirname(__FILE__).'/../php/JSON.php';
	function json_encode($arg)
	{
		global $services_json;
		if (!isset($services_json))
			$services_json = new Services_JSON();
		return $services_json->encode($arg);
	}
	function json_decode($arg)
	{
		global $services_json;
		if (!isset($services_json))
			$services_json = new Services_JSON();
		return $services_json->decode($arg);
	}
}
?>
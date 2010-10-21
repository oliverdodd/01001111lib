<?php
/* 	Disinfect Class - class providing methods for disinfecting and
 *		sanitizing potentialy malicious input
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
class Disinfect
{
	/*--------------------------------------------------------------------*\
	|* ARRAY                                                              *|
	\*--------------------------------------------------------------------*/
	/** recursive() - call a function recursively over an array or object
	 *  @param $a	= the array or object
	 *  @param $f	= the function name
	 */
	private static function recursive($a,$f)
	{
		$fa = array(__CLASS__,$f);
		if (!$f||!is_callable($fa)) return $a;
		if (!is_array($a)&&!is_object($a))
			return call_user_func_array($fa,$a);
		foreach ($a as $k => $v)
			$a[$k] = call_user_func_array($fa,$v);
		return $a;
	}
	
	/*--------------------------------------------------------------------*\
	|* SQL                                                                *|
	\*--------------------------------------------------------------------*/
	/** sql() - escape a SQL statement to prevent injection attacks
	 *  @param $s			= the string or array of strings
	 *  @param $allowed_tags	= string containing allowed tags
	 */
	public static function sql($s)
	{
		return (is_array($s)||is_object($s))
			? Disinfect::recursive($s,__METHOD__)
			: str_replace(	array("\x00","\n","\r","\x1a"),
					array('\x00','\n','\r','\x1a'),
					addslashes($s));
	}
	
	/*--------------------------------------------------------------------*\
	|* HTML                                                               *|
	\*--------------------------------------------------------------------*/
	/** html() - convert string/array to html entities
	 *  @param $s			= the string or array of strings
	 *  @param $allowed_tags	= string containing allowed tags
	 */
	public static function html($s,$allowed_tags='')
	{
		return (is_array($s)||is_object($s))
			? Disinfect::recursive($s,__FUNCTION__)
			: htmlentities($s);
	}
	
	/** strip() - strip tags on a string
	 *  @param $s		= the string or array of strings
	 */
	public static function strip($s,$allowed_tags='')
	{
		return (is_array($s)||is_object($s))
			? Disinfect::recursive($s,__FUNCTION__)
			: strip_tags($s,$allowed_tags);
	}
	
	/*--------------------------------------------------------------------*\
	|* XSS                                                                *|
	\*--------------------------------------------------------------------*/
	/** xss() - remove tags and attributes to guard against code injection
	 *	and cross-site scripting
	 *  @param $s			= the string or array of strings
	 *  @param $allowed_tags	= string containing allowed tags
	 *	typical allowed tags: '<a><b><br><center><font><img><span><u>'
	 */
	public static function xss($s,$allowed_tags='')
	{
		if (is_array($s)||is_object($s))
			return Disinfect::recursive($s,__FUNCTION__);
		//non printable characters
		$s = preg_replace('/([\x00-\x08][\x0b-\x0c][\x0e-\x20])/','',$s);
		// standard character replacements
		$chrs =	'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'.
			'1234567890!@#$%^&*()~`";:?+/={}[]-_|\'\\';
		for ($i = 0; $i < strlen($chrs); $i++) {
			$pattern = array('/(&#0{0,8}'.ord($chrs[$i]).';?)/',
				'/(&#[x|X]0{0,8}'.dechex(ord($chrs[$i])).';?)/i',
				'/(\\\[0]*'.dechex(ord($chrs[$i])).')/i');
			$replace = array($chrs[$i],$chrs[$i],$chrs[$i]);
			$s = preg_replace($pattern, $replace, $s); }
		//comments
		$s = preg_replace('/\/\*[\s\S]*\*\//', '', $s);
		//convert newlines into linebreaks
		$s = nl2br($s);
		//strip tags, leave only allowed types
		$s = strip_tags($s,$allowed_tags);
		//scripts
		do $s = str_ireplace(self::$scripts,'',$s,$c); while($c);
		//replace all DOM events
		do $s = str_ireplace(self::$dom_events,'',$s,$c); while($c);
		//hopefully the string is clean
		return $s;
	}

/*----------------------------------------------------------------------------*\
|* XSS CONSTANTS                                                              *|
\*----------------------------------------------------------------------------*/
/** An array containing potential script identifiers
 */
public static $scripts = array(	'javascript:','javascript','vbscript:',
				'vbscript','script:','expression(');

/** An array containing the names of events that can be used to execute scripts
 */
public static $dom_events = array(
/*A*/
'onabort','onactivate','onafterprint','onafterupdate',
/*B*/
'onbeforeactivate','onbeforecopy','onbeforecut','onbeforedeactivate',
'onbeforeeditfocus','onbeforepaste','onbeforeprint','onbeforeunload',
'onbeforeupdate','onbegin','onblur','onbounce',
/*C*/
'oncellchange','onchange','onclick','oncontextmenu','oncontrolselect',
'oncopy','oncut',
/*D*/
'ondataavailable','ondataavailible','ondatasetchanged','ondatasetcomplete',
'ondblclick','ondeactivate','ondrag','ondragdrop','ondragend','ondragenter',
'ondragleave','ondragover','ondragstart','ondrop',
/*E*/
'onend','onerror','onerrorupdate','onexit',
/*F*/
'onfilterchange','onfinish','onfocus','onfocusin','onfocusout',
/*H, K, L*/
'onhelp','onkeydown','onkeypress','onkeyup','onlayoutcomplete','onload',
'onlosecapture',
/*M*/
'onmediacomplete','onmediaerror','onmousedown','onmouseenter','onmouseleave',
'onmousemove','onmouseout','onmouseover','onmouseup','onmousewheel','onmove',
'onmoveend','onmovestart',
/*O, P*/
'onoutofsync','onpaste','onpause','onprogress','onpropertychange',
/*R*/
'onreadystatechange','onrepeat','onreset','onresize','onresizeend',
'onresizestart','onresume','onreverse','onrowdelete','onrowenter','onrowexit',
'onrowinserted','onrowsdelete','onrowsinserted',
/*S*/
'onscroll','onseek','onselect','onselectionchange','onselectstart','onstart',
'onstop','onsubmit','onsynchrestored',
/*T, U*/
'ontimeerror','ontrackchange','onunload','onurlflip',
/*NON-ON*/
'fscommand','seeksegmenttime');
}
?>

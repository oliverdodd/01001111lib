<?php
/* 	PHP Class - A utility class for processing and executing PHP code
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
class PHP
{
	/* KNOWN ISSUES
	 *	- obfuscation will not handle multiple inheritance
	 */
	
	/*--------------------------------------------------------------------*\
	|* CONSTANTS                                                          *|
	\*--------------------------------------------------------------------*/
	/** REGEX patterns */
	const VARIABLE_NAME 	= '/[$]([A-Za-z_][\w]*)/';
	const MEMBER_VARIABLE	= '/->([A-Za-z_][\w]*)[^(\w]+/';
	
	const DEFINED_FUNCTION	= '/function ([A-Za-z_][\w]*)\(/';
	const MEMBER_FUNCTION	= '/->([A-Za-z_][\w]*)\(/';
	const STATIC_FUNCTION	= '/::([A-Za-z_][\w]*)\(/';
	
	
	const CLASS_DEFINITION	= '/class ([A-Za-z_][\w]*)[ .\n\r]*\{/';
	const CLASS_NEW		= '/new ([A-Za-z_][\w]*)\(/';
	const CLASS_STATIC	= '/([A-Za-z_][\w]*)::/';
	
	const COMMENT_ONE_LINE	= '@[^:](//(.*))@';//[^:] excludes urls
	const COMMENT_MULTI_LINE = '~(/\*.*?\*/)~s';
	const COMMENT_SHELL	= '/(\#.*)/';
	
	/*--------------------------------------------------------------------*\
	|* PARSE                                                              *|
	\*--------------------------------------------------------------------*/
	/** grep() - parse a PHP script for patterns
	 *  @param $c	- the regex
	 *  @param $s	- script as a string, a file name, or a directory
	 *  @return	- array of matches
	 */
	public static function grep($c,$s='',$fullPattern=false)
	{
		$matches = array();
		/*-DIRECTORY--------------------------------------------------*/
		if (is_dir($s)) {
			foreach (Filesystem::contents($s) as $p) {
				$matches = array_merge($matches,
					self::grep($c,"$s/$p",$fullPattern));
			}
			return array_unique($matches);
		}
		/*-FILE-------------------------------------------------------*/
		if (is_file($s)) {
			if (strcasecmp(Filesystem::extension($s),'php') !== 0)
				return $matches;
			else $s = Filesystem::load($s);
		}
		/*-STRING-----------------------------------------------------*/
		preg_match_all($c,$s,$matches);
		$i = $fullPattern ? 0 : 1;
		return isset($matches[$i])&&is_array($matches[$i])
			? array_unique($matches[$i])
			: array();
	}
	
	/** getTokens() - return all tokens of certain types in script
	 *  @param $types	- the types of tokens to return
	 *  @param $s		- script as a string, a file name, or a directory
	 *  @param $grabNextString - use the next token instead of the target token?
	 *			(class xxxx, or function xxxx)
	 *  @return		- array of matches
	 */
	public static function getTokens($types=array(),$s='',$grabNextString=false)
	{
		$matches = array();
		/*-DIRECTORY--------------------------------------------------*/
		if (is_dir($s)) {
			foreach (Filesystem::contents($s) as $p) {
				$matches = array_merge($matches,
					self::getTokens($types,"$s/$p",$grabNextString));
			}
			return array_unique($matches);
		}
		/*-FILE-------------------------------------------------------*/
		if (is_file($s)) {
			if (strcasecmp(Filesystem::extension($s),'php') !== 0)
				return $matches;
			else $s = Filesystem::load($s);
		}
		/*-STRING-----------------------------------------------------*/
		if (!is_array($types)) $types = array($types);
		$tokens = token_get_all($s);
		$grabNext = false;
		foreach ($tokens as $t) {
			if (!is_array($t)) continue;
			if ($grabNext) {
				if ($t[0] !== T_STRING) continue;
				array_push($matches,$t[1]);
				$grabNext = false;
				continue;
			}
			foreach ($types as $type) {
				if ($t[0] === $type) {
					if ($grabNextString) $grabNext = true;
					else array_push($matches,$t[1]);
				}
			}
		}
		return array_unique($matches);
	}
	
	/** getVariables() - return all variable names in the script */
	public static function getVariables($s='')
	{
		$variables = self::getTokens(T_VARIABLE,$s);
		foreach ($variables as $i => $v) {
			//remove dollar sign
			$variables[$i] = $v = substr($v,1);
			//remove globals
			switch(strtoupper($v)) {
				case ('THIS')		:
				case ('GLOBALS')	:
				case ('_SERVER')	:
				case ('_GET')		:
				case ('_POST')		:
				case ('_FILES')		:
				case ('_REQUEST')	:
				case ('_SESSION')	:
				case ('_ENV')		:
				case ('_COOKIE')	:
				case ('PHP_ERRORMSG')	:
				case ('HTTP_RAW_POST_DATA'):
				case ('HTTP_RESPONSE_HEADER'):
				case ('ARGC')		:
				case ('ARGV')		: unset($variables[$i]);
				default			: break;
			}
		}
		return $variables;
	}
	
	/** getFunctions() - return all functions defined in the script */
	public static function getFunctions($s='')
	{
		//$functions = self::grep(self::DEFINED_FUNCTION,$s);
		$functions = self::getTokens(T_FUNCTION,$s,true);
		foreach ($functions as $i => $f) {
			if (is_callable($f)) {
				unset($functions[$i]);
				continue;
			}
			switch(strtolower($f)) {
				case ('__construct')	:
				case ('__destruct')	:
				case ('__call')	:
				case ('__callstatic')	:
				case ('__get')	:
				case ('__set')	:
				case ('__isset')	:
				case ('__unset')	:
				case ('__sleep')	:
				case ('__wakeup')	:
				case ('__tostring')	:
				case ('__set_state')	:
				case ('__clone')	:
				case ('__autoload')	: unset($functions[$i]);
				default			: break;
			}
		}
		return $functions;
	}
	
	/** getClasses() - return all classes defined in the script */
	public static function getClasses($s='')
	{
		//$classes = self::grep(self::CLASS_DEFINITION,$s);
		$classes = self::getTokens(T_CLASS,$s,true);
		foreach ($classes as $i => $c) {
			if (strcasecmp($c,"self") === 0) {
				unset($classes[$i]);
				break;
			}
		}
		return $classes;
	}
	
	/** getComments() - return all comments in the script */
	public static function getComments($s='')
	{
		return self::getTokens(array(T_COMMENT,T_DOC_COMMENT),$s);
	}
	
	/*--------------------------------------------------------------------*\
	|* EXECUTE                                                            *|
	\*--------------------------------------------------------------------*/
	/** execute() - run a php file, include functionality, return the output
	 */
	function execute($file)
	{
		if (!(file_exists($file)&&is_file($file))) return '';
		ob_start();
		include $file;
		$contents = ob_get_contents();
		ob_end_clean();
		return $contents;
	}
	
	/*--------------------------------------------------------------------*\
	|* REDUCE / OBFUSCATE                                                 *|
	\*--------------------------------------------------------------------*/
	/** reduce() - reduce a PHP file (remove comments/whitespace) or the
	 *  entire (PHP) contents of a directory.
	 *	Note: requires the 01001111 library Filesystem class
	 *  @param $file	- the file or directory
	 *  @param $r		- recursive flag (recurse through subdirectories?)
	 */
	function reduce($file,$r=true)
	{
		if (is_dir($file)) {
			$contents = $r
				? Filesystem::contents($file)
				: Filesystem::files($file);
				foreach ($contents as $p)
					self::reduce("$file/$p",$r);
			return;
		}
		//limit to PHP files
		if (strcasecmp(Filesystem::extension($file),'php') === 0)
			Filesystem::save($file,php_strip_whitespace($file));
	}
	
	/** obfuscate() - obfuscate a PHP file (rename classes,functions,variables)
	 *  or the entire (PHP) contents of a directory.
	 *	Note: requires the 01001111 library Filesystem class
	 *  @param $f	- the file or directory
	 *  @param $r	- recursive flag (recurse through subdirectories?)
	 */
	function obfuscate($f,$r=true)
	{
		$search = $replace = array();
		/*-COMMENTS/WHITESPACE----------------------------------------*/
		self::reduce($f,$r);
		/*-CLASSES----------------------------------------------------*/
		$classes = self::getClasses($f);
		foreach ($classes as $c) {
			$oc		= self::obfuscateName($c);
			$search[]	= "/([^$>:\w])$c([\s]*::|{)/i";
			$replace[]	= "\$1$oc\$2";
			$search[]	= "/(new[\s]+)$c([\s]*\()/i";
			$replace[]	= "\$1$oc\$2";
			$search[]	= "/(class|extends|implements)([\s]+)$c([\W\s]+)/i";
			$replace[]	= "\$1 $oc\$3";
		}
		/*-FUNCTIONS--------------------------------------------------*/
		$functions = self::getFunctions($f);
		foreach ($functions as $func) {
			$of		= self::obfuscateName($func);
			$search[]	= "/([\W])$func([\s]*\()/i";
			$replace[]	= "\$1$of\$2";
		}
		/*-VARIABLES--------------------------------------------------*/
		$variables = self::getVariables($f);
		foreach ($variables as $v) {
			$ov		= self::obfuscateName($v);
			$search[]	= "/($|->])$v([\W])/i";
			$replace[]	= "\$1$ov\$2";
		}
		/*-REPLACE----------------------------------------------------*/
		return self::replace($search,$replace,$f,$r);
	}
	
	/** replace() - replace certain strings/patterns in a PHP file or in the
	 *   entire (PHP) contents of a directory.
	 *	Note: requires the 01001111 library Filesystem class
	 *  @param $search	- the search regex string(s)
	 *  @param $replace	- the replacement regex string(s)
	 *  @param $f		- the file or directory
	 *  @param $r		- recursive flag (recurse through subdirectories?)
	 */
	function replace($search,$replace,$f,$r=true)
	{
		if (is_dir($f)) {
			$contents = $r
				? Filesystem::contents($f)
				: Filesystem::files($f);
				foreach ($contents as $p)
					self::replace($search,$replace,"$f/$p",$r);
			return;
		}
		//limit to PHP files
		if (strcasecmp(Filesystem::extension($f),'php') === 0)
			Filesystem::save($f,preg_replace($search,$replace,
				Filesystem::load($f)));
		echo "replaced tokens in $f\n";
	}
	
	/** obfuscateName() - encode/obfuscate a variable/function/class name
	 */
	function obfuscateName($s)
	{
		//return "O".sha1($s);
		return "O".md5($s);
	}
}
?>
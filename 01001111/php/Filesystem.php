<?php
/* 	Filesystem Class - A simple interface for file and directory handling.
 *
 *	Copyright (c) 2006-2007 Oliver C Dodd
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
class Filesystem
{
	/** Common Parameters / Notes
	 *  @param $path/$dir/$file = the directory or file path
	 *  @param $_r		= recursive flag, recurse through directories?
	 *  @param $subdir	= subdir to prepend to name key when recursing
	 *  @param $_s		= use subdir flag, prevents a collision of the
	 *			same file name in multiple directories.
	 *  @param $content	= file content to save/append/prepend
	 *  @param $src		= source file
	 *  @param $dst		= destination file
	 *
	 *  Function names should be self explanatory.  It is important to note
	 *  that the . and .. directories are ignored to prevent problems in
	 *  recursion and security.
	 */
	/*--------------------------------------------------------------------*\
	|* CONSTANTS                                                          *|
	\*--------------------------------------------------------------------*/
	/** Filesystem::_, always set to / for portability */
	const _ 		= '/';
	const FILES_ONLY	= 1;
	const DIRECTORIES_ONLY	= 2;
	
	/*--------------------------------------------------------------------*\
	|* DIRECTORIES                                                        *|
	\*--------------------------------------------------------------------*/
	/** contents() - return all contents of a directory except . and ..
	 */
	public static function contents($path)
	{
		return (!is_dir($path))
			? array() : array_diff(scandir($path),array('.','..'));
	}
	
	/** directories() - return all subdirectories of a directory as an
	 *	associative array [name] => path.  (. and .. ignored)
	 */
	public static function directories($path)
	{
		$dirs = array();
		if (!$path) return $dirs;
		$contents = self::contents($path);
		foreach ($contents as $d) {
			$subdir = $path.self::_.$d;
			if (is_dir($subdir)) $dirs[$d] = $subdir; }
		return $dirs;
	}
	
	/** files() - return all files in a directory as an associative array
	 */
	public static function files($path,$_r=false,$subdir='',$_s=true)
	{
		$files = array();
		if (!$path) return $files;
		$subdir = ($subdir&&$_s) ? $subdir.self::_ : '';
		$contents = self::contents($path);
		foreach ($contents as $n) {
			$p = $path.self::_.$n;
			if (is_file($p)) $files[$subdir.$n] = $p;
			elseif ($_r&&is_dir($p))
				$files = array_merge($files,
					self::files($p,true,$subdir.$n,$_s));
		}
		return $files;
	}
	
	public static function mkdir($dir)
	{
		$dir = self::seperators($dir,DIRECTORY_SEPARATOR);
		return (is_dir($dir)) ? true : @mkdir($dir,0777,true);
	}
	
	public static function rmdir($dir,$_r=false)
	{
		if (!is_dir($dir)) return false;
		if ($_r) {
			$files = self::files($dir);
			$dirs = self::directories($dir);
			foreach($files as $f) if (!self::delete($f))
				continue;//return false;
			foreach($dirs as $d) if (!self::rmdir($d,true))
				continue;//return false;
		}
		return rmdir($dir);
	}
	
	public static function copydir($dir,$dst)
	{
		if (!is_dir($dir)) return false;
		$r = true;
		$files = self::files($dir);
		$dirs = self::directories($dir);
		foreach($files as $n => $p) 
			$r &= self::copy($p,"$dst/$n");
		foreach($dirs as $n => $p)
			$r &= self::copydir($p,"$dst/$n");
		return $r;
	}
	
	public static function movedir($dir,$dst)
	{
		return self::copydir($dir,$dst)&&self::rmdir($dir,true);
	}
	
	/*--------------------------------------------------------------------*\
	|* TREES                                                              *|
	\*--------------------------------------------------------------------*/
	/** tree() - return all contents (recursive) of a directory as a tree
	 */
	public static function tree($path,$flags=0,$depth=PHP_INT_MAX)
	{
		$tree = array();
		if (!$path) return $tree;
		$contents = self::contents($path);
		foreach ($contents as $d) {
			$c = $path.self::_.$d;
			if (is_dir($c)) {
				if ($depth) $tree[$d] = 
					self::tree($c,$flags,$depth-1);
				elseif ($flags !== self::FILES_ONLY)
					$tree[$d] = $c;
			}
			elseif ($flags !== self::DIRECTORIES_ONLY)
				$tree[$d] = $c;
		}
		return $tree;
	}
	
	/*--------------------------------------------------------------------*\
	|* FILES                                                              *|
	\*--------------------------------------------------------------------*/
	public static function load($file) { return @file_get_contents($file); }
	public static function save($file,$content,$flags=0)
	{
		if (!self::mkdir(dirname($file))) return false;
		return (file_put_contents($file,$content,$flags) !== false)
			? true : false;
	}
	public static function append($file,$content)
	{
		return self::save($file,$content,FILE_APPEND);
	}
	public static function prepend($file,$content)
	{
		return self::save($file,$content.self::load($file));
	}
	public static function copy($src,$dst)
	{
		return (self::mkdir(dirname($dst))) ? copy($src,$dst) : false;
	}
	public static function move($src,$dst)
	{
		return (copy($src,$dst)) ? @unlink($src) : false;
	}
	public static function delete($file) { return @unlink($file); }
	public static function extension($file)
	{
		return strtolower(ltrim(strrchr($file,'.'),'.'));
	}
	public static function upload($path,$file)
	{
		if (!is_array($file)) return false;
		if (!isset($file['name'])) return false;
		if (!isset($file['tmp_name'])) return false;
		$fpath = $path.self::_.$file['name'];
		if (!move_uploaded_file($file['tmp_name'],$fpath)) return false;
		return $fpath;
	}
	public static function exists($file) { return file_exists($file); }
	
	/*--------------------------------------------------------------------*\
	|* TIMES                                                              *|
	\*--------------------------------------------------------------------*/
	public static function modified($file,$format=null)
	{
		$t = filemtime($file);
		return ($format) ? date($format,$t) : $t;
	}
	
	public static function latest($dir,$includeDir=true)
	{
		$files = self::files($dir);
		$latestTime = $t = 0;
		$latestFile = "";
		foreach ($files as $n => $p) {
			if (($t = self::modified($p)) > $latestTime) {
				$latestTime = $t;
				$latestFile = $n;
			}
		}
		return $includeDir&&$latestFile
			? "$dir/$latestFile"
			: $latestFile;
	}
	
	/*--------------------------------------------------------------------*\
	|* LOAD/SAVE SERIALIZED PHP OBJECTS                                   *|
	\*--------------------------------------------------------------------*/
	public static function loadPHP($file)
	{
		$s = self::load($file);
		$c = @unserialize($s);
		if (($c === false)&&($s !== serialize(false))) $c = $s;
		return $c;
	}
	public static function savePHP($file,$content)
	{
		return self::save($file,serialize($content));
	}
	
	/*--------------------------------------------------------------------*\
	|* PATHS                                                              *|
	\*--------------------------------------------------------------------*/
	/** cleanPath() - convert slashes and make path absolute
	 *  @param $path	= the file/directory path
	 *  @param $absolute	= make path absolute?
	 */
	public static function cleanPath($path,$absolute=true)
	{
		if ($absolute) $path = realpath($path);
		return str_replace('\\','/',$path);
	}
	
	/** cleanDirPath() - convert slashes and more
	 *  @param $path	= the file/directory path
	 *  @param $absolute	= make path absolute?
	 *  @param $trailingFS	= add file separator to the end?
	 */
	public static function cleanDirPath($path,$absolute=true,$trailingFS=true)
	{
		$path = self::cleanPath($path,$absolute);
		if ($path[strlen($path)-1] !== '/') $path .= '/';
		return $path;
	}
	
	/** seperators() - uniform directory seperators
	 *  @param $path	= the file/directory path
	 *  @param $sc		= the seperator character
	 */
	public static function seperators($path,$sc=self::_)
	{
		return str_replace(array('\\','/'),$sc,$path);
	}
	
	/** pathComponents() - split a path into an array of its components
	 *  @param $path	= the file/directory path
	 */
	public static function pathComponents($path)
	{
		return explode('/',self::cleanPath($path));
	}
	
	/** dots() - are '.' or '..' contained in the specified path?
	 */
	public static function dots($path)
	{
		$ca = self::pathComponents($path);
		foreach ($ca as $c) if (($c==='.')||($c==='..')) return true;
		return false;
	}
	
	/** pathDiff() - return two paths from the point where they diverge
	 *  @param $p1			= the first ABSOLUTE file path
	 *  @param $p2			= the second ABSOLUTE file path
	 *  @param $returnArrays	= return as component arrays?
	 */
	public static function pathDiff(&$p1,&$p2,$returnArrays=false)
	{
		if (!is_array($p1)) $p1 = self::pathComponents($p1);
		if (!is_array($p2)) $p2 = self::pathComponents($p2);
		while ($p1&&$p2) {
			if (current($p1) !== current($p2)) break;
			else {	array_shift($p1);
				array_shift($p2); } }
		if (!$returnArrays) {
			$p1 = implode(self::_,$p1);
			$p2 = implode(self::_,$p2); }
	}
	
	/** relativePath() - translate an absolute path to a relative path
	 *  @param $file	= the ABSOLUTE file path
	 *  @param $dirOnly	= return only the directory?
	 *  @param $trailingFS	= trailing file seperator character?
	 */
	public static function relativePath($file=__FILE__,$dirOnly=false,
		$trailingFS=false)
	{
		$file = self::cleanPath($file);
		$script = $_SERVER['SCRIPT_FILENAME'];
		$sDir = dirname($script);
		$fDir = is_dir($file) ? $file : dirname($file);
		$fFile = is_dir($file) ? "" : basename($file);
		$rc = array();
		self::pathDiff($sDir,$fDir,true);
		while($sDir) {	array_shift($sDir);
				array_unshift($rc,'..'); }
		while($fDir) {	array_push($rc,array_shift($fDir)); }
		if (!$dirOnly&&$fFile) array_push($rc,$fFile);
		$path = implode(self::_,$rc);
		$path = ($path&&($dirOnly||is_dir($path))&&$trailingFS)
			? $path.self::_
			: $path;
		return str_replace(self::_.self::_,self::_,$path);
	}
	
	/** httpPath() - convert a file path to an http address
	 *  @param $f		= the file path
	 *  @param $sroot	= the site root
	 *  @param $froot	= the file root
	 */
	public static function httpPath($f,$sroot=null,$froot=null)
	{
		if ($sroot === null) $sroot = self::siteRoot();
		if ($froot === null) $froot = self::fileRoot();
		$f = self::seperators($f);
		$sroot = self::seperators($sroot);
		$froot = self::seperators($froot);
		$f = str_replace($froot,'',$f);
		return $sroot.'/'.ltrim($f,'/');
	}
	
	/*--------------------------------------------------------------------*\
	|* ROOT DIRECTORIES                                                   *|
	\*--------------------------------------------------------------------*/
	/** fileRoot() - get the absolute file root using the current script
	 */
	public static function fileRoot() { return self::cleanPath('.'); }
	
	/** siteRoot() - attempt to determine the http site address root
	 */
	public static function siteRoot()
	{
		if (isset($_SERVER['SCRIPT_URI']))
			return dirname($_SERVER['SCRIPT_URI']);
		if (isset($_SERVER['HTTP_HOST']))
			return 'http://'.$_SERVER['HTTP_HOST'];
	}
	
	/*--------------------------------------------------------------------*\
	|* ZIP                                                                *|
	\*--------------------------------------------------------------------*/
	function zip($files,$zipPath)
	{
		/* check os and run winzip if windows */
		if (stripos(self::os(),'Windows') !== false)
			return self::winzip($files,$zipPath);
		$zip = new ZipArchive();
		if (!$zip->open($zipPath,ZIPARCHIVE::CREATE)) return false;
		$err = false;
		foreach ($files as $n => $p)
			if (!$zip->addFile($p,$n)) $err = true;
		return (!$err&&$zip->close());
	}
	/** windows zip function - windows has an upper limit on the number of
	 * handles allowed to be open resulting in a reported success for 509
	 * files but no zip file saved and a failure for a file count > 509
	 */
	function winzip($files,$zipPath)
	{
		$zip = new ZipArchive();
		if (!$zip->open($zipPath,ZIPARCHIVE::CREATE)) return false;
		$err = false;
		$i = 0;
		foreach ($files as $n => $p) {
			/* fix for > 508 file zip archive failure on Windows */
			if ($i++ > 500 ) {
				$zip->close();
				if (!$zip->open($zipPath,ZIPARCHIVE::CREATE))
					return false;
				$i = 0;
			}
			if (!$zip->addFile($p,$n)) $err = true;
		}
		return (!$err&&$zip->close());
	}
	function zipDir($dir,$zipPath)
	{
		return self::zip(self::files($dir,true),$zipPath);
	}
	
	/*--------------------------------------------------------------------*\
	|* FIND                                                               *|
	\*--------------------------------------------------------------------*/
	function find($file,$dir=".",$recursive=true)
	{
		$dir = realpath($dir);
		$files = self::files($dir);
		foreach ($files as $n => $p)
			if (strcasecmp($file,$n) === 0) return $p;
		if (!$recursive) return false;
		$directories = self::directories($dir);
		foreach ($directories as $n => $p) {
			$r = self::find($file,$p,true);
			if (strcasecmp($file,basename($r)) === 0) return $r;
		}
		return false;
	}
	
	/*--------------------------------------------------------------------*\
	|* INCLUDE                                                            *|
	\*--------------------------------------------------------------------*/
	/** includeFiles() - include all files in a directory
	 */
	function includeFiles($dir,$recursive=true)
	{
		$files = self::files($dir);
		foreach ($files as $n => $p)
			if (self::extension($n) === 'php') include_once $p;
		if (!$recursive) return;
		$directories = self::directories($dir);
		foreach ($directories as $n => $p)
			self::includeFiles($p,true);
	}
	/** includeContents() - include file and return as string
	 */
	function includeContents($file)
	{
		if (!(self::exists($file)&&is_file($file))) return '';
		ob_start();
		include $file;
		$contents = ob_get_contents();
		ob_end_clean();
		return $contents;
	}
	
	/*--------------------------------------------------------------------*\
	|* OPERATING SYSTEM                                                   *|
	\*--------------------------------------------------------------------*/
	function os() { return @php_uname('s'); }
}
?>
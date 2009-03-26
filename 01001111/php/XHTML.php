<?php
/* 	XHTML Function Collection - A group of wrapper static functions that minimize
 *		repetition in bulding xhtml components by providing shortcuts and
 *		default attributes.
 *	
 *	Copyright (c) 2006-2009 Oliver C Dodd
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
class XHTML
{
	/*--------------------------------------------------------------------*\
	|* CONSTANTS                                                          *|
	\*--------------------------------------------------------------------*/
	const STRICT		= "Strict";
	const TRANSITIONAL	= "Transitional";
	const FRAMESET		= "Frameset";
	const NS = ' xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en"';
	
	/*--------------------------------------------------------------------*\
	|* GENERIC TAGS                                                       *|
	\*--------------------------------------------------------------------*/
	/** GENERIC TAG
	 *  @param type	- the tag type (a, script, body, etc.)
	 *  @param c		- the tag content
	 *  @param attrs	- an associative array or string of attributes
	 */
	public static function tag($type,$c='',$attrs=array())
	{
		return "<$type".self::attrs($attrs).">$c</$type>";
	}
	public static function tags($type,$a=array(),$attrs=array())
	{
		$s = "";
		foreach ($a as $c) $s.= self::tag($type,$c,$attrs);
		return $s;
	}
	
	/** GENERIC CLOSED TAG (<img src="example/gif" />)
	 *  @param type		- the tag type (img, link, br etc.)
	 *  @param attrs	- an associative array or string of attributes
	 */
	public static function ctag($type,$attrs=array())
	{
		return "<$type".self::attrs($attrs)." />";
	}
	
	/*--------------------------------------------------------------------*\
	|* ATTRIBUTES                                                         *|
	\*--------------------------------------------------------------------*/
	/** ATTRIBUTE STRING
	 *  @param attrs	- an associative array of attributes
	 *  @param ignore_null	- ignore null or empty string values?
	 */
	public static function attrs()
	{
		$attrs = '';
		$args = func_get_args();
		foreach ($args as $arg)
			if (is_scalar($arg)) $attrs .= " $arg ";
			else foreach ($arg as $k => $v)
				if (($v !== '')&&($v !== null))
					$attrs .= ' '.$k.'="'.$v.'" ';
		return $attrs;
	}
	
	/** FULL FORMATTED ATTRIBUTE STRING (with correct spacing)
	 *  @param attrs	- an associative array of attributes
	 *  @param x		- a string of extra attributes
	 *  @param ignore_null	- ignore null or empty string values?
	 */
	public static function fattrs($attrs,$x,$ignore_null=true)
	{
		$a = self::attrs($attrs,$ignore_null);
		if($a) $a = " $a";
		if ($x) $x = " $x";
		return $a.$x;
	}
	
	/*--------------------------------------------------------------------*\
	|* PAGE                                                               *|
	\*--------------------------------------------------------------------*/
	/** PAGE
	 *  @param content	- the page content (head + body)
	 *  @param dtd		- the document type definition
	 */
	public static function pg($content='',$dtd=self::TRANSITIONAL)
	{
		return self::dtd($dtd).'
		<html'.self::NS.'>
			'.$content.'
		</html>';
	}
	
	/** BODY
	 *  @param c		- the content
	 *  @param attrs	- any extra attributes (array or string)
	 */
	public static function body($c='',$attrs='')
	{
		return self::tag('body',$c,$attrs);
	}
	
	/*--------------------------------------------------------------------*\
	|* HEAD                                                               *|
	\*--------------------------------------------------------------------*/
	/** HEAD
	 *  @param title	- the page title
	 *  @param c		- any header
	 */
	public static function head($title='',$c='')
	{
		return '
		<head>	<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
			<title>'.$title.'</title>
			'.$c.'
		</head>';
	}
	
	/** DTD
	 *  @param type	- the DTD type (Strict, Transitional, Frameset)
	 */
	public static function dtd($type=self::TRANSITIONAL)
	{
		$t2 = strtolower($type);
		$t1 = ucfirst($t2);
		return	"<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 $t1//EN'".
			" 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-$t2.dtd'>";
	}
	
	/** javascripts - include external javascript files
	 *  @param scripts	- the locations of the external js files
	 */
	public static function javascripts()
	{
		$scripts = func_get_args();
		$js = '';
		foreach ($scripts as $s) {
			if (is_scalar($s)) $js .= self::javascript('',$s);
			else foreach ($s as $s2) $js .= self::javascript('',$s2);
		}
		return	$js;
	}
	
	/** javascript - inline javascript code
	 *  @param c	- the script content
	 *  @param src	- the script source
	 */
	public static function javascript($c='',$src=null)
	{
		return self::tag('script',$c,
			array('type'=>'text/javascript','src'=>$src));
	}
	
	/** stylesheets - include external css files
	 *  @param styles	- the locations of the external css files
	 */
	public static function stylesheets()
	{
		$styles = func_get_args();
		$css = '';
		foreach ($styles as $s) {
			if (is_scalar($s)) $css .= self::css($s);
			else foreach ($s as $s2) $css .= self::css($s2);
		}
		return	$css;
	}
	
	/** style - inline css style
	 *  @param c	- the style content
	 */
	public static function style($c='')
	{
		return self::tag('style',$c,array('type'=>'text/css'));
	}
	
	/** css - css stylesheet link
	 *  @param href	- the styleshet location
	 */
	public static function css($href='',$media='')
	{
		return self::ctag('link',array(
			'href'=>$href,'type'=>"text/css",'rel'=>"stylesheet",
			'media'=>$media));
	}
	
	/*--------------------------------------------------------------------*\
	|* GENERIC ELEMENTS                                                   *|
	\*--------------------------------------------------------------------*/
	/** GENERIC ELEMENT <tag id="id" [attrs]>content</tag>
	 *  @param t		- the tag name
	 *  @param c		- the content
	 *  @param id		- the id
	 *  @param attrs	- any extra attributes (array or string)
	 */
	public static function _($t,$c='',$id='',$attrs='')
	{
		return self::tag($t,$c,self::attrs(array('id'=>$id),$attrs));
	}
	
	/** GENERIC CLOSED ELEMENT <tag id="id" class="class" [attrs] />
	 *  @param t		- the tag name
	 *  @param id		- the id
	 *  @param attrs	- any extra attributes (array or string)
	 */
	public static function __($t,$id='',$attrs='')
	{
		return self::ctag($t,self::attrs(array('id'=>$id),$attrs));
	}
	
	/*--------------------------------------------------------------------*\
	|* BR / HR                                                            *|
	\*--------------------------------------------------------------------*/
	public static function br($n=1)
	{
		$s = '';
		while($n--) $s .= self::ctag('br');
		return $s;
	}
	public static function hr() { return self::ctag('hr'); }
	
	/*--------------------------------------------------------------------*\
	|* CONTAINERS / ANCHORS                                               *|
	\*--------------------------------------------------------------------*/
	/** DIV/P/SPAN
	 *  @param c		- the content
	 *  @param id		- the id
	 *  @param attrs	- any extra attributes (array or string)
	 */
	public static function div($c='',$id='',$attrs='')
	{
		return self::_('div',$c,$id,$attrs);
	}
	public static function p($c='',$id='',$attrs='')
	{
		return self::_('p',$c,$id,$attrs);
	}
	public static function span($c='',$id='',$attrs='')
	{
		return self::_('span',$c,$id,$attrs);
	}
	
	/** LINK + link with onclick event + link to open new window
	 *  @param href		- the link address
	 *  @param c		- the displayed text
	 *  @param oc		- the onclick event
	 *  @param attrs	- any extra attributes (array or string)
	 */
	public static function a($href,$c,$attrs='')
	{
		return self::tag('a',$c,self::attrs(array('href'=>$href),$attrs));
	}
	public static function l($href,$c,$attrs='')
	{
		return self::a('a',$href,$c,$attrs);
	}
	public static function jl($oc,$c,$attrs='')
	{
		return self::a('javascript:',$c,self::attrs(
			array('onclick'=>"$oc;return false;"),$attrs));
	}
	public static function lnw($href,$c,$attrs='')
	{
		//return self::jl("window.open('$href')",$c,$attrs);
		return self::a($href,$c,
			self::attrs(array('target'=>'_blank'),$attrs));
	}
	
	/*--------------------------------------------------------------------*\
	|* IMAGES                                                             *|
	\*--------------------------------------------------------------------*/
	/** IMAGE + image with context menu blocked + onclick image
	 *  @param src		- the image source
	 *  @param id		- the id
	 *  @param attrs	- any extra attributes (array or string)
	 */
	public static function img($src='',$id='',$attrs='')
	{
		return self::__('img',$id,self::attrs(array('src'=>$src),$attrs));
	}
	public static function ncmimg($src,$id='',$attrs='')
	{
		return self::img($src,$id,self::attrs(
			array('oncontextmenu'=>'return false;'),$attrs));
	}
	public static function ocimg($src='',$oc='',$id='',$attrs='')
	{
		return self::img($src,$id,self::attrs(
			array('onclick'=>"$oc; return false;"),$attrs));
	}
	
	/*--------------------------------------------------------------------*\
	|* FRAME                                                              *|
	\*--------------------------------------------------------------------*/
	/** IFRAME
	 *  @param src		- the iframe source
	 *  @param id		- the id
	 *  @param attrs	- any extra attributes (array or string)
	 */
	function iframe($src,$id,$attrs='')
	{
		return self::_('iframe','',$id,self::attrs(
			array(	'src'=>$src,
				'frameborder'=>"0",
				'marginwidth'=>"0",
				'marginheight'=>"0"),$attrs));
	}
	
	/*--------------------------------------------------------------------*\
	|* FORMS/INPUTS                                                       *|
	\*--------------------------------------------------------------------*/
	public static function stattr($a,$b=false) { return ($b)?" $a=\"$a\" ":''; }
	public static function checked($c=false) { return self::stattr('checked',$c); }
	public static function disabled($d=false) { return self::stattr('disabled',$d); }
	public static function readonly($r=false) { return self::stattr('readonly',$r); }
	public static function selected($s=false) { return self::stattr('selected',$s); }
	
	/** FORM
	 *  @param c		- the content
	 *  @param id		- the id
	 *  @param a		- the action
	 *  @param m		- the method, default is POST
	 *  @param attrs	- any extra attributes (array or string)
	 */
	public static function form($c='',$id='',$a='',$m='',$attrs='')
	{
		if (!$a) $a = isset($_SERVER['PHP_SELF'])
				? $_SERVER['PHP_SELF']
				: '';
		if (!$m) $m = 'POST';
		return self::_('form',$c,$id,self::attrs(
			array('action'=>$a,'method'=>$m),$attrs));
	}
	
	/** GENERIC INPUT
	 *  @param t		- the input type
	 *  @param id		- the input id
	 *  @param v		- the input value
	 *  @param attrs	- any extra attributes (array or string)
	 *  @param n		- name, id is used if empty
	 */
	public static function input($t,$id='',$v='',$attrs='',$n='')
	{
		if (!$n) $n = $id;
		return self::__('input',$id,self::attrs(
			array('type'=>$t,'name'=>$n,'value'=>$v),$attrs));
	}
	/** SPECIFIC INPUTS
	 *  @param id		- the input id
	 *  @param v		- the input value
	 *  @param attrs	- any extra attributes (array or string)
	 *  @param n		- name, id is used if empty
	 *  @param s		- size
	 *  @param oc		- onclick
	 *  @param md		- onmouseup
	 *  @param mu		- onmousedown
	 */
	/* text */
	public static function text($id,$v='',$attrs='')
	{
		return self::input('text',$id,$v,$attrs);
	}
	/* password */
	public static function password($id,$v='',$attrs='')
	{
		return self::input('password',$id,$v,$attrs);
	}
	/* button */
	public static function button($id,$v='',$oc='',$attrs='')
	{
		return self::input('button',$id,$v,self::attrs(
			array('onclick'=>$oc),$attrs));
	}
	public static function mbutton($id,$v='',$md='',$mu='',$attrs='')
	{
		return self::input('button',$id,$v,self::attrs(
			array('onmousedown'=>$md,'onmouseup'=>$mu),$attrs));
	}
	/* checkbox */
	public static function checkbox($id,$v='',$attrs='',$checked=false)
	{
		$c = self::checked($checked);
		return self::input('checkbox',$id,$v,self::attrs($attrs,$c));
	}
	public static function dcheckbox($id,$v='',$d='',$attrs='',$checked=false)
	{
		return	self::input('hidden','',$d,self::attrs(
				array('name'=>$id),$attrs)).
			self::checkbox($id,$v,$attrs,$checked);
	}
	/* hidden */
	public static function hidden($id,$v='',$attrs='')
	{
		return self::input('hidden',$id,$v,$attrs);
	}
	/* file */
	public static function file($id,$v='',$attrs='')
	{
		return self::input('file',$id,$v,$attrs);
	}
	/* submit */
	public static function submit($id='submit',$v='',$attrs='')
	{
		return self::input('submit',$id,$v,$attrs);
	}
	public static function jsubmit($id='submit',$v='',$oc='',$attrs='')
	{
		return self::submit($id,$v,self::attrs(
			array('onclick'=>"$oc;return false;"),$attrs));
	}
	/* radio */
	public static function radio($id,$n,$v='',$attrs='')
	{
		return self::input('radio',$id,$v,$attrs,$n);
	}
	
	/** TEXT AREA
	 *  @param id		- the id
	 *  @param v		- the value
	 *  @param cols		- the number of character columns
	 *  @param rows		- the number of rows
	 *  @param attrs	- any extra attributes (array or string)
	 *  @param n		- name, id is used if empty
	 */
	public static function textarea($id,$v='',$cols=25,$rows=10,$attrs='',$n='')
	{
		if (!$n) $n = $id;
		return self::_('textarea',$v,$id,self::attrs(
			array('name'=>$n,'cols'=>$cols,'rows'=>$rows),$attrs));
	}
	
	/** SELECT
	 *  @param id		- the id
	 *  @param o		- the options, converted if array
	 *  @param attrs	- any extra attributes (array or string)
	 *  @param n		- name, id is used if empty
	 */
	public static function select($id,$o='',$attrs='',$n='')
	{
		if (!is_scalar($o)) $o = self::options($o);
		if (!$n) $n = $id;
		return self::_('select',$o,$id,self::attrs(
			array('name'=>$n),$attrs));
	}
	
	/** OPTIONS - array to options
	 *  @param a		- the array or json encoded string
	 *  @param assoc	- associative? 
	 *			true/1: $k => $v :<option value="$v">$k</option>
	 *			2     : $k => $v :<option value="$k">$v</option>
	 *  @param si		- value of the selected option
	 */
	public static function options($a,$assoc=false,$si=null)
	{
		if (is_scalar($a)) $a = json_decode($a);
		$o = '';
		foreach ($a as $k => $v) {
			$n = (($assoc === 1)||$assoc === true) ? $k : $v;
			$val = ($assoc === 2) ? $k : $v;
			$s = self::selected(($si !== null)&&($si === $val));
			$o .= self::tag('option',$n,
				self::attrs(array('value'=>$val),$s));
		}
		return $o;
	}
	
	/*--------------------------------------------------------------------*\
	|* TABLES                                                             *|
	\*--------------------------------------------------------------------*/
	/** TABLE/TR/TD
	 *  @param c		- the content
	 *  @param id		- the id
	 *  @param class	- the class
	 *  @param attrs	- any extra attributes (array or string)
	 */
	public static function t($c='',$id='',$attrs='')
	{
		return self::_('table',$c,$id,$attrs);
	}
	public static function table($c='',$id='',$attrs='')
	{
		return self::t($c,$id,$attrs);
	}
	public static function tbody($c='',$attrs='') { return self::tag('tbody',$c,$attrs); }
	public static function thead($c='',$attrs='') { return self::tag('thead',$c,$attrs); }
	public static function tfoot($c='',$attrs='') { return self::tag('tfoot',$c,$attrs); }
	public static function tr($c='',$attrs='') { return self::tag('tr',$c,$attrs); }
	public static function td($c='',$attrs='') { return self::tag('td',$c,$attrs); }
	public static function th($c='',$attrs='') { return self::tag('th',$c,$attrs); }
	
	/*--------------------------------------------------------------------*\
	|* CONVERSION FUNCTIONS                                               *|
	\*--------------------------------------------------------------------*/
	/** Newline to paragraph (\n -> <P>)
	 *  @param s	- the content string
	 */
	public static function nl2p($s)
	{
		return "<p>".str_replace("\n","</p><p>",$s)."</p>";
	}
	
	/** Double Newline to Paragraph (\n\n -> <P> and \n -> <br />)
	 *  @param s	- the content string
	 */
	public static function nlnl2p($s)
	{
		return "<p>".nl2br(str_replace("\n\n","</p><p>",$s))."</p>";
	}
	
	/** Array to list
	 *  @param a		- the array or json encoded string
	 *  @param assoc	- include keys on nested arrays?
	 */
	public static function a2l($a,$assoc=true)
	{
		if (is_scalar($a)) $a = json_decode($a);
		$l = '';
		foreach ($a as $k => $v) {
			$n = ($assoc) ? "$k: " : '';
			if (!is_scalar($v))
				$l .= "<li>$n".self::a2l($v,$assoc)."</li>";
			else	$l .= "<li>$n$v</li>";
		}
		return "<ul>$l</ul>";
	}
	
	/** Array to table
	 *  @param a		- the two dimensional array
	 *  @param id		- the table id
	 *  @param colgroup	- the table column group
	 *  @param head		- the table head
	 *  @param foot		- the table foot
	 */
	public static function a2table($a,$id='',$colgroup=array(),
		$head=array(),$foot=array())
	{
		$t = '';
		$b = '';
		if (is_scalar($a)) $a = json_decode($a);
		if ($colgroup) {
			if (!is_scalar($colgroup))
				$t .= self::a2colgroup($colgroup);
			else	$t .= $colgroup;
		}
		if ($head)	$t .= self::tag('thead',self::a2tr($head,'th'));
		if ($foot)	$t .= self::tag('tfoot',self::a2tr($foot));
		foreach ($a as $r) $b .= self::a2tr($r);
				$t .= self::tag('tbody',$b);
		return self::tag('table',$t);
	}
	public static function a2tr($a,$t='td')
	{
		$tr = '';
		if (is_scalar($a)) $a = Unserialize::csv($a);
		foreach ($a as $d) $tr .= self::tag($t,$d);
		return self::tag('tr',$tr);
	}
	public static function d2tr($d,$t='td')
	{
		$tr = '';
		if (is_scalar($d)) $d = array($d);
		foreach ($d as $td) $tr .= self::tag($t,$td);
		return self::tag('tr',$tr);
	}
	public static function a2colgroup($a=array(),$cgattrs='')
	{
		$cols = '';
		if (is_scalar($a)) $a = Unserialize::csv($a);
		foreach ($a as $attr) $cols .= self::tag('col','',$attr);
		return self::tag('colgroup',$cols,$cgattrs);
	}
	public static function tabled($c='',$id='',$attrs='')
	{
		$td = self::tag('td',$c,$attrs);
		$tr = self::tag('tr',$td,$attrs);
		return self::_('table',$tr,$id,$attrs);
	}
}
?>
<?php
/*	Color class - A class for creating and converting colors.
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

class Color
{
	/*--------------------------------------------------------------------*\
	|* CONSTANTS                                                          *|
	\*--------------------------------------------------------------------*/
	const dWHITE	= 16777215;
	const dBLACK	= 0;
	
	/*--------------------------------------------------------------------*\
	|* VARIABLES                                                          *|
	\*--------------------------------------------------------------------*/
	//rgb
	public $r;	//0-255
	public $g;	//0-255
	public $b;	//0-255
	//hsv
	public $h;	//0-360
	public $s;	//0-100
	public $v;	//0-100
	//cmyk
	public $c;	//0-100
	public $m;	//0-100
	public $y;	//0-100
	public $k;	//0-100
	//dec/hex
	public $dec;
	public $hex;
	
	/*--------------------------------------------------------------------*\
	|* CREATION                                                           *|
	\*--------------------------------------------------------------------*/
	private function __construct($r,$g,$b,$h,$s,$v,$c,$m,$y,$k,$dec,$hex)
	{
		list($this->r,$this->g,$this->b,$this->h,$this->s,$this->v,
		$this->c,$this->m,$this->y,$this->k,$this->dec,$this->hex)
			= array($r,$g,$b,$h,$s,$v,$c,$m,$y,$k,$dec,$hex);
	}
	
	/** RGB() - create a color from decimal rgb values
	 *  @param $r	= red	(0 - 255)
	 *  @param $g	= green	(0 - 255)
	 *  @param $b	= blue	(0 - 255)
	 */
	public static function RGB($r=0,$g=0,$b=0)
	{
		//ensure numbers within bounds
		list($r,$g,$b) = self::bounda(array($r,$g,$b),0,255);
		//use _RGB to get the rest 
		list(,,,$h,$s,$v,$c,$m,$y,$k,$dec,$hex) = self::_RGB($r,$g,$b);
		return new Color($r,$g,$b,$h,$s,$v,$c,$m,$y,$k,$dec,$hex);
	}
	public static function _RGB($r=0,$g=0,$b=0)
	{
		//convert to hsv
		list($h,$s,$v) = self::rgb2hsv($r,$g,$b);
		//convert to cmyk
		list($c,$m,$y,$k) = self::rgb2cmyk($r,$g,$b);
		//convert to decimal
		$dec = self::rgb2dec($r,$g,$b);
		//convert to hex
		$hex = self::rgb2hex($r,$g,$b);
		return array($r,$g,$b,$h,$s,$v,$c,$m,$y,$k,$dec,$hex);
	}
	
	/** HSV() - create a color from decimal hsv values
	 *  @param $h	= hue			(0 - 360)
	 *  @param $s	= saturation		(0 - 100)
	 *  @param $v	= value/brightness	(0 - 100)
	 */
	public static function HSV($h=0,$s=0,$v=0)
	{
		//ensure numbers within bounds
		$h = self::bound($h,0,360);
		$s = self::bound($s,0,100);
		$v = self::bound($v,0,100);
		//convert to rgb
		list($r,$g,$b) = self::hsv2rgb($h,$s,$v);
		//use _RGB to get the rest 
		list($r,$g,$b,,,,$c,$m,$y,$k,$dec,$hex) = self::_RGB($r,$g,$b);
		return new Color($r,$g,$b,$h,$s,$v,$c,$m,$y,$k,$dec,$hex);
	}
	
	/** CMYK() - create a color from decimal cmyk values
	 *  @param $c	= cyan		(0 - 100)
	 *  @param $m	= magenta	(0 - 100)
	 *  @param $y	= yellow	(0 - 100)
	 *  @param $k	= black		(0 - 100)
	 */
	public static function CMYK($c=0,$m=0,$y=0,$k=0)
	{
		//ensure numbers within bounds
		list($c,$m,$y,$k) = self::bounda(array($c,$m,$y,$k),0,100);
		//convert to rgb
		list($r,$g,$b) = self::cmyk2rgb($c,$m,$y,$k);
		//use _RGB to get the rest 
		list($r,$g,$b,$h,$s,$v,,,,,$dec,$hex) = self::_RGB($r,$g,$b);
		return new Color($r,$g,$b,$h,$s,$v,$c,$m,$y,$k,$dec,$hex);
	}
	
	/** DEC/HEX() - create a color from decimal/hex rgb values
	 *  @param $dec	= decimal number	(0 - 16777215)
	 *  @param $hex	= hexadecimal number	(000000 - FFFFFF)
	 */
	public static function DEC($dec=0)
	{
		//ensure number within bounds
		$dec = self::bound($dec,0,self::dWHITE);
		//convert to rgb
		list($r,$g,$b) = self::dec2rgb($dec);
		//use _RGB to get the rest 
		list($r,$g,$b,$h,$s,$v,$c,$m,$y,$k,,$hex) = self::_RGB($r,$g,$b);
		return new Color($r,$g,$b,$h,$s,$v,$c,$m,$y,$k,$dec,$hex);
	}
	public static function HEX($hex=0)
	{
		//convert to rgb
		list($r,$g,$b) = self::hex2rgb($hex);
		//use _RGB to get the rest 
		list($r,$g,$b,$h,$s,$v,$c,$m,$y,$k,$dec,) = self::_RGB($r,$g,$b);
		return new Color($r,$g,$b,$h,$s,$v,$c,$m,$y,$k,$dec,$hex);
	}
	
	/*--------------------------------------------------------------------*\
	|* STATIC FUNCTIONS                                                   *|
	\*--------------------------------------------------------------------*/
	/** rgb2hsv() - convert from rgb to hsv
	 *  @param $r	= red	(0 - 255)
	 *  @param $g	= green	(0 - 255)
	 *  @param $b	= blue	(0 - 255)
	 */
	public static function rgb2hsv($r=0,$g=0,$b=0)
	{
		//ensure numbers within bounds
		list($r,$g,$b) = self::bounda(array($r,$g,$b),0,255);
		//convert
		$min = min($r,$g,$b);
		$max = max($r,$g,$b);
		$delta = $max - $min;
		$v = $max/255;
		if(($max == 0)||($delta == 0)) { $s = 0; $h = 0; }
		else {	$s = $delta/$max;
			if($r == $max)		$h = ($g - $b)/$delta;
			elseif($g == $max )	$h = 2 + ($b - $r)/$delta;
			else			$h = 4 + ($r - $g)/$delta;
			$h *= 60;
			if($h < 0)		$h += 360; }
		return self::rounda(array($h,$s*100,$v*100));
	}
	
	/** hsv2rgb() - convert from hsv to rgb
	 *  @param $h	= hue			(0 - 360)
	 *  @param $s	= saturation		(0 - 100)
	 *  @param $v	= value/brightness	(0 - 100)
	 */
	public static function hsv2rgb($h=0,$s=0,$v=0)
	{
		//ensure numbers within bounds
		$h = self::bound($h,0,360);
		$s = self::bound($s,0,100)/100;
		$v = self::bound($v,0,100)/100;
		//convert
		$r = 0; $g = 0; $b = 0;
		if ($s == 0) { $r = $g = $b = $v * 255; }
		else {	$h /= 60;
			$i = floor($h);
			$f = $h - $i;
			$p = $v*(1 - $s);
			$q = $v*(1 - $s*$f);
			$t = $v*(1 - $s*(1 - $f));
			
			if	($i==0)	{ $r = $v; $g = $t; $b = $p; }
			elseif	($i==1)	{ $r = $q; $g = $v; $b = $p; }
			elseif	($i==2)	{ $r = $p; $g = $v; $b = $t; }
			elseif	($i==3)	{ $r = $p; $g = $q; $b = $v; }
			elseif	($i==4)	{ $r = $t; $g = $p; $b = $v; }
			else		{ $r = $v; $g = $p; $b = $q; }
			$r *= 255;	$g *= 255;	$b *= 255; }
		return self::rounda(array($r,$g,$b));
	}
	
	/** rgb2cmyk() - convert from rgb to cmyk
	 *  @param $r	= red	(0 - 255)
	 *  @param $g	= green	(0 - 255)
	 *  @param $b	= blue	(0 - 255)
	 */
	public static function rgb2cmyk($r=0,$g=0,$b=0)
	{
		//ensure numbers within bounds
		list($r,$g,$b) = self::bounda(array($r,$g,$b),0,255);
		//convert
		$r /= 255;
		$g /= 255;
		$b /= 255;
		$c = 1 - $r;
		$m = 1 - $g;
		$y = 1 - $b;
		if (min($c,$m,$y) == 1) list($c,$m,$y,$k) = array(0,0,0,1);
		else {	$k = min($c,$m,$y);
			$c = ($c - $k)/(1 - $k);
			$m = ($m - $k)/(1 - $k);
			$y = ($y - $k)/(1 - $k); }
		return self::rounda(array($c*100,$m*100,$y*100,$k*100));
	}
	
	/** cmyk2rgb() - convert from cmyk to rgb
	 *  @param $c	= cyan		(0 - 100)
	 *  @param $m	= magenta	(0 - 100)
	 *  @param $y	= yellow	(0 - 100)
	 *  @param $k	= black		(0 - 100)
	 */
	public static function cmyk2rgb($c=0,$m=0,$y=0,$k=0)
	{
		//ensure numbers within bounds
		list($c,$m,$y,$k) = self::bounda(array($c,$m,$y,$k),0,100);
		//convert
		$c /= 100;
		$m /= 100;
		$y /= 100;
		$k /= 100;
		$r = 1 - ($c * (1 - $k)) - $k;
		$g = 1 - ($m * (1 - $k)) - $k;
		$b = 1 - ($y * (1 - $k)) - $k;
		return self::rounda(array($r*255,$g*255,$b*255));
	}
	
	/** rgb2dec/hex() - convert from rgb to dec/hex
	 *  @param $r	= red	(0 - 255)
	 *  @param $g	= green	(0 - 255)
	 *  @param $b	= blue	(0 - 255)
	 */
	public static function rgb2dec($r=0,$g=0,$b=0)
	{
		//ensure numbers within bounds
		list($r,$g,$b) = self::bounda(array($r,$g,$b),0,255);
		return ($r<<16)+($g<<8)+$b;
	}
	public static function rgb2hex($r=0,$g=0,$b=0)
	{
		$hex = dechex(self::rgb2dec($r,$g,$b));
		while(strlen($hex) < 6) $hex = "0$hex";
		return $hex;
	}
	
	/** dec/hex2rgb() - convert from dec/hex to rgb
	 *  @param $dec	= decimal number	(0 - 16777215)
	 *  @param $hex	= hexadecimal number 	(000000 - FFFFFF)
	 */
	public static function dec2rgb($dec=0)
	{
		$dec = self::bound($dec,0,self::dWHITE);
		$r = ($dec&0xFF0000)>>16;
		$g = ($dec&0xFF00)>>8;
		$b = $dec&0xFF;
		return array($r,$g,$b);
	}
	public static function hex2rgb($hex)
	{
		return self::dec2rgb(hexdec($hex));
	}
	
	/* COMBINATIONS */
	public static function hsv2cmyk($h=0,$s=0,$v=0)
	{
		list($r,$g,$b) = self::hsv2rgb($h,$s,$v);
		return self::rgb2cmyk($r,$g,$b);
	}
	public static function cmyk2hsv($c=0,$m=0,$y=0,$k=0)
	{
		list($r,$g,$b) = self::cmyk2rgb($c,$m,$y,$k);
		return self::rgb2hsv($r,$g,$b);
	}
	
	/*--------------------------------------------------------------------*\
	|* MANIPULATE                                                         *|
	\*--------------------------------------------------------------------*/
	/** invert() - invert the color */
	public function invert() { return self::DEC(self::dWHITE - $this->dec); }
	
	/** desaturate() - desaturate the color */
	public function desaturate() { return self::HSV($this->h,0,$this->v); }
	
	/** shifthue() - shift the hue by a number of degrees
	 *  @param $d	= the number of degrees (0 - 360) 
	 */
	public function shifthue($d=0)
	{
		return self::HSV(self::rollover($this->h + $d,360),
				 $this->s,$this->v);
	}
	
	/** complement() - complementary color */
	public function complement() { return $this->shifthue(180); }
	
	/** triad()
	 *  @param $n	= the triad number (0,1,2)
	 */
	public function triad($n=1) { return $this->shifthue($n*120); }
	
	/** square()
	 *  @param $n	= the quarter number (0,1,2,3)
	 */
	public function square($n=1) { return $this->shifthue($n*90); }
	
	/*--------------------------------------------------------------------*\
	|* MISC FUNCTIONS                                                     *|
	\*--------------------------------------------------------------------*/
	/** bound() - ensure a number falls between a min and a max value
	 *  @param $n	= the number
	 *  @param $min	= minimum value
	 *  @param $max	= maximum value
	 */
	public static function bound($n=0,$min=0,$max=0)
	{
		//if min > max, switch
		if ($min > $max) { $m = $min; $min = $max; $max = $m; }
		return min(max($n,$min),$max);
	}
	/** bounda() - run bound on an array
	 *  @param $a	= the number array
	 *  @param $min	= minimum value
	 *  @param $max	= maximum value
	 */
	public static function bounda($a=array(),$min=0,$max=0)
	{
		$b = array();
		foreach ($a as $i => $n) $b[$i] = self::bound($n,$min,$max);
		return $b;
	}
	/** rounda() - run round on an array
	 *  @param $a	= the number array
	 *  @param $d	= the number of decimals to round to
	 */
	public static function rounda($a=array(),$d=0)
	{
		$b = array();
		foreach ($a as $i => $n) $b[$i] = round($n,$d);
		return $b;
	}
	/** rollover() - roll a number over if it surpasses a certain value
	 *  @param $n	= the number
	 *  @param $max	= maximum value
	 */
	public function rollover($n=0,$max=0)
	{
		if ($max === 0) return 0;
		while($n < 0) $n += $max;
		while($n >= $max) $n -= $max;
		return $n;
	}
}
?>